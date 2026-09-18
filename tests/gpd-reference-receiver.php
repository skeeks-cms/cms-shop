<?php
// Independent disposable DB: verifies isolation and atomic application of the new stream.
define('YII_ENABLE_ERROR_HANDLER',false);
$loader=require '/deps/autoload.php';
foreach(glob('/shared-vendor/skeeks/*/composer.json') as $file){$m=json_decode(file_get_contents($file),true);foreach($m['autoload']['psr-4']??[] as $ns=>$paths)$loader->addPsr4($ns,array_map(fn($p)=>dirname($file).'/'.$p,(array)$paths),true);}
require '/deps/yiisoft/yii2/Yii.php';
new yii\console\Application(['id'=>'reference-client-test','basePath'=>__DIR__,'vendorPath'=>'/deps','extensions'=>[],
    'components'=>['db'=>['class'=>yii\db\Connection::class,'dsn'=>'mysql:host=gpd-receiver-db','username'=>'root']]]);
$db=Yii::$app->db;$name='reference_client_'.bin2hex(random_bytes(5));$db->createCommand("CREATE DATABASE $name")->execute();$db->createCommand("USE $name")->execute();
foreach(['m260917_030000_gpd_catalog_receiver','m260917_120000_gpd_catalog_application','m260917_180000_gpd_reference_receiver'] as $class)require dirname(__DIR__).'/src/migrations/'.$class.'.php';
use skeeks\cms\shop\gpd\CatalogReceiver;
use skeeks\cms\shop\gpd\CatalogApplier;
use skeeks\cms\shop\gpd\CatalogWriterInterface;
use skeeks\cms\shop\gpd\CatalogTransportInterface;
class ReferenceTestTransport implements CatalogTransportInterface {
    public $pages=[];
    public function request(string $method,string $endpoint,array $data=[]):array {
        if(!$this->pages)throw new RuntimeException('Unexpected request');return array_shift($this->pages);
    }
}
class ReferenceTestWriter implements CatalogWriterInterface {
    public $fail=false;
    public function prepare(array $item):void{}
    public function apply(array $item,array $state):array {
        Yii::$app->db->createCommand()->update('{{%shop_gpd_reference_state}}',['kind'=>'brands','source_id'=>7],['connection_id'=>'same','product_id'=>$item['id']])->execute();
        if($this->fail)throw new RuntimeException('write failure');return ['outcome'=>'updated'];
    }
}
$checks=0;function check($v,$m){global $checks;if(!$v)throw new RuntimeException($m);++$checks;}
try {
 foreach(['','sx_'] as $prefix) {
    $db->tablePrefix=$prefix;$db->schema->refresh();
    (new m260917_030000_gpd_catalog_receiver())->safeUp();(new m260917_120000_gpd_catalog_application())->safeUp();(new m260917_180000_gpd_reference_receiver())->up();
    $wire=new ReferenceTestTransport();$catalog=new CatalogReceiver($db,'same',$wire);$catalog->register(1,'https://fixture.invalid/v2',str_repeat('a',64));
    $receiver=new CatalogReceiver($db,'same',$wire,'references');$receiver->register(1,'https://fixture.invalid/v2',str_repeat('a',64));
    $wire->pages=[['protocol'=>1,'stream'=>'references','seeded'=>true],['total'=>1,'cursor'=>'r1','changes_cursor'=>'c1']];$receiver->receive();
    check($catalog->state()['phase']==='bootstrap','reference bootstrap does not alter catalog state');
    $item=['id'=>5,'revision'=>'1','product_revision'=>'1','operation'=>'upsert'];
    $wire->pages=[['items'=>[$item],'total'=>1,'done'=>true,'next_cursor'=>'r2','changes_cursor'=>'c1']];$receiver->receive();
    check($receiver->state()['phase']==='changes','reference bootstrap completed');
    check((int)$db->createCommand('SELECT COUNT(*) FROM {{%shop_gpd_catalog_state}}')->queryScalar()===0,'reference receipts do not pollute product state');
    $item['data']=['id'=>5,'kind'=>'brands','source_id'=>7,'payload'=>['id'=>7,'name'=>'Fixture']];$wire->pages=[['items'=>[$item]]];
    $writer=new ReferenceTestWriter();$applier=new CatalogApplier($db,'same',$wire,$writer,'references');$entry=$applier->page()[0];
    $writer->fail=true;try{$applier->apply($entry);throw new RuntimeException('expected failure');}catch(RuntimeException $e){check($e->getMessage()==='write failure','failure propagated');}
    $row=$db->createCommand('SELECT * FROM {{%shop_gpd_reference_state}}')->queryOne();check($row['kind']===null&&(int)$row['applied_revision']===0,'failed application rolls back identity and ACK');
    $writer->fail=false;$applier->apply($entry);check($applier->remaining()===0,'successful application commits ACK');
    check($applier->apply($entry)['outcome']==='unchanged','redelivery does not apply twice');
    $wire->pages=[['items'=>[['id'=>5,'revision'=>'2','product_revision'=>'2','position'=>'2','operation'=>'upsert']],'next_cursor'=>'c2','has_more'=>false]];
    $receiver->receive();check($applier->remaining()===1,'new reference revision remains applicable');
    check($catalog->state()['cursor']===null,'catalog cursor remains untouched');
 }
 echo "PASS $checks reference receiver/application checks with two prefixes.\n";
}finally{$db->createCommand("DROP DATABASE $name")->execute();}
