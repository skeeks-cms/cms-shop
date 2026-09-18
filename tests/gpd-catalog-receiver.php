<?php
// Private disposable MariaDB. Never bootstrap a site or read its credentials.
define('YII_ENABLE_ERROR_HANDLER', false);
$loader=require '/deps/autoload.php';
foreach (glob('/shared-vendor/skeeks/*/composer.json') as $file) {
    $manifest=json_decode(file_get_contents($file),true);
    foreach (($manifest['autoload']['psr-4']??[]) as $ns=>$paths) $loader->addPsr4($ns,array_map(fn($p)=>dirname($file).'/'.$p,(array)$paths),true);
}
require '/deps/yiisoft/yii2/Yii.php';
new yii\console\Application(['id'=>'gpd-receiver-test','basePath'=>__DIR__,'vendorPath'=>'/deps','extensions'=>[],
    'components'=>['db'=>['class'=>yii\db\Connection::class,'dsn'=>'mysql:host=gpd-receiver-db','username'=>'root']]]);
$db=Yii::$app->db;
$name='receiver_'.bin2hex(random_bytes(5));
$db->createCommand("CREATE DATABASE $name")->execute(); $db->createCommand("USE $name")->execute();
require dirname(__DIR__).'/src/migrations/m260917_030000_gpd_catalog_receiver.php';
require dirname(__DIR__).'/src/migrations/m260917_120000_gpd_catalog_application.php';
use skeeks\cms\shop\gpd\CatalogReceiver;
use skeeks\cms\shop\gpd\CatalogTransportInterface;
use skeeks\cms\shop\gpd\ProtocolException;
class FixtureTransport implements CatalogTransportInterface {
    public $responses=[];
    public function request(string $method,string $endpoint,array $data=[]): array {
        if (!$this->responses) throw new RuntimeException('Unexpected API call: '.$endpoint);
        [$expected,$response]=array_shift($this->responses);
        if ($expected!==$endpoint) throw new RuntimeException('Expected '.$expected.', got '.$endpoint);
        if ($response instanceof Throwable) throw $response;
        if (is_callable($response)) return $response($data);
        return $response;
    }
}
class ReceiverFixtureContext extends \skeeks\cms\job\runtime\JobContext {
    public $position=[];
    public function getPayload() {return [];}
    public function getCursor() {return $this->position;}
    public function setCursor(array $cursor) {$this->position=$cursor;}
    public function get($key,$default=null) {return $this->getPayload()[$key]??$default;}
}
class ReceiverFixtureReporter extends \skeeks\cms\job\runtime\JobReporter {
    public $result=[]; public $cancel=false; public $errors=0; public $totals=[];
    public function init() {}
    public function setStage(string $stage,?string $message=null):void {$this->run->progress_message=$message;}
    public function setTotal(?int $total):void {$this->totals[]=$total;}
    public function advance(int $by=1):void {$this->run->progress_current+=$by;}
    public function countSuccess(int $by=1):void {}
    public function setResult(array $result):void {$this->result=$result;$this->run->result_json=json_encode($result);}
    public function heartbeat():void {}
    public function isCancelled():bool {return $this->cancel;}
    public function itemError(string $type,$id,string $message,array $row=[]):void {$this->errors++;}
}
$checks=0;
function check($value,$message) {global $checks;if(!$value)throw new RuntimeException($message);$checks++;}
function failWith($reason,callable $fn) {try{$fn();}catch(ProtocolException $e){check($reason===$e->reason,'Expected '.$reason.', got '.$e->reason);return;}throw new RuntimeException('No failure: '.$reason);}
function card($id,$revision,$operation='upsert') {return ['id'=>$id,'revision'=>(string)$revision,'operation'=>$operation]+($operation==='upsert'?['product_revision'=>(string)$revision]:[]);}
function qstate($id) {return (new yii\db\Query())->from('{{%shop_gpd_catalog_state}}')->where(['connection_id'=>'test','product_id'=>$id])->one();}
function changes($items,$next) {return ['items'=>$items,'has_more'=>false,'next_cursor'=>$next];}
try {
foreach (['','sx_'] as $prefix) {
    $db->tablePrefix=$prefix; $db->schema->refresh();
    (new m260917_030000_gpd_catalog_receiver())->safeUp();
    (new m260917_120000_gpd_catalog_application())->safeUp();
    $t=new FixtureTransport(); $r=new CatalogReceiver($db,'test',$t);
    $fingerprint=hash('sha256','fixture-only');
    $r->register(1,'https://fixture.invalid/v2',$fingerprint);
    $r->register(1,'https://fixture.invalid/v2',$fingerprint);
    failWith('connection_identity_changed',fn()=>$r->register(2,'https://fixture.invalid/v2',$fingerprint));
    failWith('connection_identity_changed',fn()=>$r->register(1,'https://fixture.invalid/v2',hash('sha256','another')));
    $t->responses=[['status',['protocol'=>1,'stream'=>'catalog','seeded'=>true]],['bootstrap',['total'=>2,'cursor'=>'s0','changes_cursor'=>'c0']]];
    check($r->receive()['more'] && $r->state()['phase']==='snapshot','Bootstrap persisted');
    $t->responses=[['manifest',['total'=>2,'items'=>[card(10,1)],'done'=>false,'next_cursor'=>'s1','changes_cursor'=>'c0']]];
    $r->receive();
    check($r->state()['cursor']==='s1' && (string)qstate(10)['revision']==='1','Item and cursor persist together');
    $t->responses=[['manifest',['total'=>2,'items'=>[card(10,1)],'done'=>true,'next_cursor'=>'s2','changes_cursor'=>'c0']]];
    failWith('duplicate_snapshot_product',fn()=>$r->receive());
    check($r->state()['cursor']==='s1','Duplicate snapshot page cannot advance cursor');
    // Simulated connection-write failure AFTER item write: both must roll back.
    $table=$db->quoteTableName($prefix.'shop_gpd_connection');
    $db->createCommand("CREATE TRIGGER receiver_fail BEFORE UPDATE ON $table FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='fixture rollback'")->execute();
    $page=['total'=>2,'items'=>[['id'=>20,'revision'=>'2','status'=>'pending']],'done'=>true,'next_cursor'=>'s2','changes_cursor'=>'c0'];
    $t->responses=[['manifest',$page]];
    try {$r->receive();throw new RuntimeException('Missing DB failure');}catch(yii\db\Exception $e) {}
    check(!qstate(20) && $r->state()['cursor']==='s1','Failed cursor write rolls back receipt');
    $db->createCommand('DROP TRIGGER receiver_fail')->execute();
    $r=new CatalogReceiver($db,'test',$t); // A fresh process resumes the persistent cursor.
    $t->responses=[['manifest',$page]]; $r->receive();
    check($r->state()['phase']==='changes' && $r->state()['cursor']==='c0','Complete manifest switches to boundary');
    check((int)qstate(20)['needs_resolution']===1,'Pending remains durable');
    $t->responses=[['changes',changes([],'c0')]]; check(!$r->receive()['more'],'An empty successful tail is allowed');
    $t->responses=[['batch',['items'=>[card(20,3)]]]]; $r->resolve();
    check((int)qstate(20)['needs_resolution']===0 && (string)qstate(20)['revision']==='3','Pending resolves explicitly');
    check(!$r->resolve(20)['more'],'Resolution loop terminates');
    $t->responses=[['changes',changes([card(10,5,'revoke')+['position'=>'4']],'c4')]]; $r->receive();
    check(qstate(10)['operation']==='revoke','Explicit revocation retained');
    $t->responses=[['changes',changes([card(10,6)+['position'=>'5']],'c5')]]; $r->receive();
    $t->responses=[['changes',changes([card(10,4,'revoke')+['position'=>'6']],'c6')]]; $r->receive();
    check(qstate(10)['operation']==='upsert' && (string)qstate(10)['revision']==='6','Old response cannot undo regrant');
    $before=$r->state();
    $t->responses=[['changes',changes([card(30,7)+['position'=>'7'],['id'=>40,'revision'=>'8','operation'=>'bad','position'=>'8']],'c8')]];
    failWith('invalid_operation',fn()=>$r->receive());
    check(!qstate(30) && $r->state()===$before,'Malformed page has no partial commit');
    $t->responses=[['changes',['items'=>[],'has_more'=>true,'next_cursor'=>'c8']]];
    failWith('empty_incomplete_page',fn()=>$r->receive());
    $t->responses=[['changes',new ProtocolException('http_401')]];
    failWith('http_401',fn()=>$r->receive());
    check($r->state()===$before,'HTTP failure never clears cursor/catalog');
    $t->responses=[['changes',new ProtocolException('invalid_cursor')]];
    failWith('invalid_cursor',fn()=>$r->receive());
    $t->responses=[['changes',new ProtocolException('cursor_expired')]];
    check($r->receive()['stage']==='recovery' && qstate(10)['operation']==='upsert','Expired history retains previous state');
    $t->responses=[['status',['protocol'=>1,'stream'=>'catalog','seeded'=>true]],['bootstrap',['total'=>1,'cursor'=>'ss0','changes_cursor'=>'cc0']]];
    $r->receive();
    $t->responses=[['manifest',['total'=>1,'items'=>[],'done'=>true,'next_cursor'=>'ss1','changes_cursor'=>'cc0']]];
    failWith('incomplete_snapshot',fn()=>$r->receive());
    check((int)qstate(10)['needs_resolution']===0,'Partial snapshot does not mark absent products');
    $t->responses=[['manifest',['total'=>1,'items'=>[card(20,9)],'done'=>true,'next_cursor'=>'ss1','changes_cursor'=>'cc0']]];
    $r->receive();
    check((int)qstate(10)['needs_resolution']===1 && qstate(10)['operation']==='upsert','Completed snapshot requests verification, not deletion');
    $t->responses=[['batch',['items'=>[]]]]; failWith('incomplete_batch',fn()=>$r->resolve());
    $t->responses=[['batch',['items'=>[['id'=>10,'status'=>'not_available']]]]]; $r->resolve();
    check((int)qstate(10)['needs_resolution']===1 && qstate(10)['operation']==='upsert','Unordered not_available cannot authorize deletion');
    $t->responses=[['batch',['items'=>[card(10,10,'revoke')]]]]; $r->resolve();
    check(qstate(10)['operation']==='revoke' && $r->summary()['unresolved']===0,'Ordered batch revoke resolves absence');
    check($r->summary()['applied_to_shop']===false,'Receipt never claims application');
    $other=new CatalogReceiver($db,'other',$t);$other->register(2,'https://fixture.invalid/v2',$fingerprint);
    check($other->summary()['tracked']===0,'Connections isolated');
    check(!isset($db->schema->getTableSchema($prefix.'shop_gpd_catalog_state')->columns['json']),'No copied JSON table');
    // Another worker commits while the API request is in flight.
    $t->responses=[['changes',function()use($db){
        $db->createCommand()->update('{{%shop_gpd_connection}}',['cursor'=>'raced'],['id'=>'test'])->execute();
        return changes([card(999,99)+['position'=>'99']],'loser');
    }]];
    failWith('concurrent_receiver',fn()=>$r->receive());
    check(!qstate(999) && $r->state()['cursor']==='raced','Stale network response cannot overwrite another commit');
}
failWith('invalid_connection_configuration',fn()=>new \skeeks\cms\shop\gpd\CatalogTransport('http://fixture.invalid/v2','fixture'));
failWith('invalid_connection_configuration',fn()=>new \skeeks\cms\shop\gpd\CatalogTransport('https://user:password@fixture.invalid/v2','fixture'));
failWith('invalid_connection_configuration',fn()=>new \skeeks\cms\shop\gpd\CatalogTransport('https://fixture.invalid/v2',''));
$component=new \skeeks\cms\shop\gpd\ReceiverComponent();
failWith('connection_disabled_or_wrong_site',fn()=>$component->receiver('missing',1));
$component->connections=['configured'=>['enabled'=>true,'siteId'=>2,'url'=>'https://fixture.invalid/v2']];
failWith('connection_disabled_or_wrong_site',fn()=>$component->receiver('configured',1));
Yii::$app->set('skeeksSuppliersApi',new class extends yii\base\Component {public $api_key='fixture-only';public $api_url='https://fixture.invalid/v1';});
$component->receiver('configured',2);
check(Yii::$app->skeeksSuppliersApi->api_url==='https://fixture.invalid/v1','New receiver does not change v1 component');
check($component->connectionIdForSite(2)==='configured','Empty payload resolves the existing pilot configuration');
failWith('connection_disabled_or_wrong_site',fn()=>$component->receiverForSite(2,'another'));
$component->connections['duplicate']=['enabled'=>true,'siteId'=>2];
failWith('ambiguous_site_connection',fn()=>$component->connectionIdForSite(2));
$flat=new \skeeks\cms\shop\gpd\ReceiverComponent(['enabled'=>true,'siteId'=>3,'url'=>'https://fixture.invalid/v2']);
check($flat->connectionIdForSite(3)==='site-3','Fresh configuration needs no named connection');
$flat->receiverForSite(3);
check($flat->receiverForSite(3)->state()['id']==='site-3','Flat configuration registers once');
failWith('connection_disabled_or_wrong_site',fn()=>$flat->receiverForSite(2));
$preserved=new CatalogReceiver($db,'pilot',$t);
$preserved->register(4,'https://fixture.invalid/v2',$fingerprint);
$db->createCommand()->update('{{%shop_gpd_connection}}',['cursor'=>'preserved-cursor','phase'=>'changes'],['id'=>'pilot'])->execute();
$flat->siteId=4;
check($flat->receiverForSite(4)->state()['cursor']==='preserved-cursor','Flattening keeps pilot identity and cursor');
check($flat->receiverForSite(4,'pilot')->state()['id']==='pilot','Already queued legacy payload remains valid');
Yii::$app->skeeksSuppliersApi->api_key='different-fixture';
failWith('connection_identity_changed',fn()=>$flat->receiverForSite(4));
Yii::$app->skeeksSuppliersApi->api_key='fixture-only';
Yii::$app->set('skeeks',new class extends yii\base\Component {public $site;public function init(){$this->site=(object)['id'=>4];}});
Yii::$app->set('gpd',new class extends yii\base\Component {public $enabled=true;public function forSite($id){return $this;}});
$automatic=new \skeeks\cms\shop\gpd\ReceiverComponent(['url'=>'https://fixture.invalid/v2']);
check($automatic->receiverForSite(4)->state()['cursor']==='preserved-cursor','Automatic settings keep existing pilot cursor');
check($automatic->referencesEnabled && $automatic->offersEnabled,'Automatic streams enabled');
failWith('connection_disabled_or_wrong_site',fn()=>$automatic->receiverForSite(3));
Yii::$app->gpd->enabled=false;
failWith('connection_disabled_or_wrong_site',fn()=>$automatic->receiverForSite(4));
Yii::$app->gpd->enabled=true;
$automatic->enabled=false;
failWith('connection_disabled_or_wrong_site',fn()=>$automatic->receiverForSite(4));
Yii::$app->clear('gpd');
Yii::$app->clear('skeeks');
// Real native handler, including cooperative cancellation and resumable chunk loop.
$transport=new FixtureTransport();
$large=new CatalogReceiver($db,'large',$transport);
$large->register(1,'https://fixture.invalid/v2',hash('sha256','fixture-only'));
$transport->responses=[['status',['protocol'=>1,'stream'=>'catalog','seeded'=>true]],['bootstrap',['total'=>10000,'cursor'=>'large0','changes_cursor'=>'tail']]];
for ($offset=0;$offset<10000;$offset+=50) {
    $items=[];for($i=$offset+1;$i<=$offset+50;$i++)$items[]=card($i,$i);
    $transport->responses[]=['manifest',['total'=>10000,'items'=>$items,'done'=>$offset===9950,'next_cursor'=>'large'.($offset+50),'changes_cursor'=>'tail']];
}
$transport->responses[]=['changes',changes([],'tail')];
Yii::$app->set('gpdReceiver',new class($large) extends yii\base\Component {
    private $service;
    public function __construct($service) {$this->service=$service;parent::__construct();}
    public function receiverForSite($site,$id=null) {if($id!==null||$site!==1)throw new RuntimeException('Wrong binding');return $this->service;}
});
$params=[];
$config=require dirname(__DIR__).'/src/config/common.php';
$definition=new \skeeks\cms\job\JobTypeDefinition($config['components']['jobRegistry']['types']['shop.gpd.catalog.receive']);
check(isset($config['components']['jobQueueFactory']['queues'][$definition->queue]),'Native lane is registered');
check($definition->idempotent && $definition->overlapPolicy==='skip','Idempotency and overlap explicit');
$context=new ReceiverFixtureContext(['run'=>(object)['cms_site_id'=>1,'progress_current'=>0],'definition'=>$definition]);
$reporter=new ReceiverFixtureReporter(['run'=>$context->run]);
$handler=$definition->createHandler();
$reporter->cancel=true;
try {$handler->run($context,$reporter);throw new RuntimeException('Cancellation ignored');}
catch(\skeeks\cms\job\exceptions\JobCancelledException $e) {check($large->state()['phase']==='bootstrap','Cancellation before receipt changes nothing');}
$reporter->cancel=false;
$requeues=0;
do {
    $repeat=false;
    $handler=$definition->createHandler();
    $handler->maxRunSeconds=0.05; // Exercise real ChunkedJobHandler continuation.
    try {$handler->run($context,$reporter);}catch(\skeeks\cms\job\exceptions\JobRequeueException $e) {$repeat=true;$requeues++;}
} while($repeat);
check($requeues>0,'Native chunk loop requeues with durable receipt');
check($large->summary()['tracked']===10000 && $large->summary()['snapshot_received']===10000,'All 10000 IDs received once');
check($reporter->result['applied_to_shop']===false && $reporter->errors===0,'Native result explicitly receipt only');
check($context->run->progress_current===10000,'Native progress reports 10000 receipts');
check(!$transport->responses,'All expected protocol requests consumed');
check(array_unique(array_filter($reporter->totals,fn($v)=>$v!==null))===[10000],'Progress total remains stable during bootstrap');
check($reporter->result['run']['cards_received']===10000,'Receipt counters survive native process continuations');
check(strpos($context->run->progress_message,'карточек 10000')!==false,'Completed bootstrap reports received cards');
$context=new ReceiverFixtureContext(['run'=>(object)['cms_site_id'=>1,'progress_current'=>0],'definition'=>$definition]);
$reporter=new ReceiverFixtureReporter(['run'=>$context->run]);
$transport->responses=[['changes',changes([],'tail')]];
$definition->createHandler()->run($context,$reporter);
check($reporter->result['run']['cards_received']===0 && $reporter->result['tracked']===10000,'New run counters do not repeat historical catalog total');
check(strpos($context->run->progress_message,'Новых изменений нет.')===0,'Empty poll has a human-readable final message');
$context=new ReceiverFixtureContext(['run'=>(object)['cms_site_id'=>1,'progress_current'=>0],'definition'=>$definition]);
$reporter=new ReceiverFixtureReporter(['run'=>$context->run]);
$transport->responses=[['changes',changes([card(1,10001,'revoke')+['position'=>'10001']],'newtail')]];
$definition->createHandler()->run($context,$reporter);
check($reporter->result['run']['exclusions_received']===1 && $reporter->result['run']['cards_received']===0,'Revocations counted separately for this run');
check(strpos($context->run->progress_message,'Товары сайта не изменялись.')!==false,'Receipt message never claims product application');
// Application acknowledgements use the same transaction as domain writes.
$db->createCommand()->createTable('fixture_shop',['id'=>'int primary key','revision'=>'bigint'])->execute();
$writer=new class implements \skeeks\cms\shop\gpd\CatalogWriterInterface {
    public $fail=false;
    public function prepare(array $item):void {}
    public function apply(array $item,array $state):array {
        Yii::$app->db->createCommand()->upsert('fixture_shop',['id'=>$item['id'],'revision'=>$item['revision']])->execute();
        if($this->fail)throw new RuntimeException('Fixture after shop write');
        return ['outcome'=>'updated','local_product_id'=>$item['id']];
    }
};
$at=new FixtureTransport();
$applier=new \skeeks\cms\shop\gpd\CatalogApplier($db,'large',$at,$writer);
$entry=['state'=>(new yii\db\Query())->from('{{%shop_gpd_catalog_state}}')->where(['connection_id'=>'large','product_id'=>2])->one(),'item'=>card(2,2)+['data'=>['id'=>2,'name'=>'Fixture']]];
$writer->fail=true;
try{$applier->apply($entry);throw new RuntimeException('Missing rollback');}catch(RuntimeException $e){check($e->getMessage()==='Fixture after shop write','Fixture fails after shop write');}
check(!(new yii\db\Query())->from('fixture_shop')->exists(),'Domain write rolls back with failed apply');
check((int)(new yii\db\Query())->select('applied_revision')->from('{{%shop_gpd_catalog_state}}')->where(['connection_id'=>'large','product_id'=>2])->scalar()===0,'Failed application leaves applied revision unchanged');
$writer->fail=false;$applier->apply($entry);
$state=(new yii\db\Query())->from('{{%shop_gpd_catalog_state}}')->where(['connection_id'=>'large','product_id'=>2])->one();
check((int)$state['applied_revision']===2 && (int)$state['local_product_id']===2,'Shop write and applied version committed');
check($applier->apply($entry)['outcome']==='unchanged','Redelivery does not write twice');
$db->createCommand()->update('{{%shop_gpd_catalog_state}}',['revision'=>5],['connection_id'=>'large','product_id'=>2])->execute();
check($applier->apply($entry)['outcome']==='pending','Receipt arriving during HTTP supersedes stale apply');
$entry['item']=['id'=>2,'status'=>'not_available'];check($applier->apply($entry)['outcome']==='pending','Not available never means delete');
$entry['item']=['id'=>2,'status'=>'pending'];check($applier->apply($entry)['outcome']==='pending','Pending cannot be acknowledged');
$at->responses=[['batch',['items'=>[]]]];failWith('incomplete_batch',fn()=>$applier->page());
$entry['item']=card(2,5)+['data'=>['id'=>999]];failWith('invalid_product_data',fn()=>$applier->apply($entry));
echo "PASS $checks receiver/native-job checks including 10000 products, two table prefixes.\n";
} finally {$db->createCommand("DROP DATABASE $name")->execute();}
