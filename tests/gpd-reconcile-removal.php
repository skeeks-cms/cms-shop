<?php
define('YII_ENABLE_ERROR_HANDLER',false);
$loader=require '/deps/autoload.php';require '/deps/yiisoft/yii2/Yii.php';
$loader->addPsr4('skeeks\\cms\\shop\\', '/shared-vendor/skeeks/cms-shop/src',true);
new yii\console\Application(['id'=>'reconcile-test','basePath'=>__DIR__,'vendorPath'=>'/deps','extensions'=>[],
 'components'=>['db'=>['class'=>yii\db\Connection::class,'dsn'=>'mysql:host=gpd-receiver-db','username'=>'root']]]);
eval('namespace skeeks\\cms\\shop\\models; class ShopBrand extends \\yii\\db\\ActiveRecord { public static function tableName(){return "shop_brand";} } class ShopCollection extends \\yii\\db\\ActiveRecord { public static function tableName(){return "shop_collection";} } class ShopProduct extends \\yii\\db\\ActiveRecord { public static function tableName(){return "shop_product";} }');
eval('namespace skeeks\\cms\\models; class CmsSavedFilter extends \\yii\\db\\ActiveRecord { public static $veto=false;public static function tableName(){return "cms_saved_filter";}public function beforeDelete(){return !self::$veto;} }');
$db=Yii::$app->db;$name='gpd_reconcile_'.bin2hex(random_bytes(5));$db->createCommand("CREATE DATABASE $name")->execute();$db->createCommand("USE $name")->execute();
use skeeks\cms\shop\gpd\CatalogReceiver;use skeeks\cms\shop\gpd\ShopReferenceWriter;use skeeks\cms\shop\gpd\CatalogReconciler;use skeeks\cms\shop\gpd\CatalogTransportInterface;use skeeks\cms\shop\gpd\CatalogWriterInterface;
class AuditWire implements CatalogTransportInterface {
 public $items=[];public $bad=false;
 public function request(string $method,string $endpoint,array $data=[]):array {
  $items=[];foreach($data['ids']??$data['references'] as $key){$id=is_array($key)?$key['source_id']:$key;$item=$this->items[$id]??['id'=>$id,'status'=>'not_available'];if(is_array($key))$item+=$key;$items[]=$item;}
  if($this->bad)array_pop($items);return ['items'=>$items];
 }
}
class ProductAuditWriter implements CatalogWriterInterface {
 public function prepare(array $item):void{}
 public function apply(array $item,array $state):array {return ['outcome'=>'kept'];}
}
$n=0;function check($ok,$message){global $n;if(!$ok)throw new RuntimeException($message);++$n;}
try {
 foreach(['m260917_030000_gpd_catalog_receiver','m260917_120000_gpd_catalog_application','m260917_180000_gpd_reference_receiver'] as $class){require '/shared-vendor/skeeks/cms-shop/src/migrations/'.$class.'.php';$m=new $class();$m->up();}
 foreach(['shop_brand'=>'id INT PRIMARY KEY,sx_id INT,name VARCHAR(100),is_active INT',
  'shop_collection'=>'id INT PRIMARY KEY,sx_id INT,name VARCHAR(100),is_active INT,shop_brand_id INT',
  'cms_content_element'=>'id INT PRIMARY KEY,sx_id INT,cms_site_id INT,active CHAR(1),parent_content_element_id INT NULL',
  'shop_product'=>'id INT PRIMARY KEY,brand_id INT NULL,updated_at INT NULL',
  'shop_product2collection'=>'shop_product_id INT,shop_collection_id INT',
  'cms_saved_filter'=>'id INT PRIMARY KEY,shop_brand_id INT,cms_site_id INT',
  'shop_order_item'=>'id INT PRIMARY KEY,shop_product_id INT'] as $table=>$columns)$db->createCommand("CREATE TABLE $table ($columns) ENGINE=InnoDB")->execute();
 $db->createCommand('CREATE TABLE shop_store (id INT PRIMARY KEY,sx_id INT NULL,cms_site_id INT) ENGINE=InnoDB')->execute();
 $db->createCommand('CREATE TABLE shop_store_product (id INT PRIMARY KEY,shop_product_id INT,shop_store_id INT,is_active INT,quantity INT) ENGINE=InnoDB')->execute();
 $db->createCommand('CREATE TABLE shop_store_product_move (id INT PRIMARY KEY,shop_store_product_id INT) ENGINE=InnoDB')->execute();
 $db->createCommand('INSERT INTO shop_brand VALUES(1,101,"Brand",1)')->execute();
 $db->createCommand('INSERT INTO shop_collection VALUES(2,102,"Collection",1,1)')->execute();
 $db->createCommand('INSERT INTO cms_content_element VALUES(3,103,1,"Y",NULL),(4,104,2,"Y",NULL)')->execute();
 $db->createCommand('INSERT INTO shop_product VALUES(3,1,NULL),(4,NULL,NULL)')->execute();
 $db->createCommand('INSERT INTO shop_product2collection VALUES(3,2)')->execute();
 $db->createCommand('INSERT INTO cms_saved_filter VALUES(5,1,1)')->execute();
 $db->createCommand('INSERT INTO shop_order_item VALUES(6,4)')->execute();
 $settings=(new ReflectionClass(skeeks\cms\shop\components\GpdComponent::class))->newInstanceWithoutConstructor();
 check($settings->excludedBrandAction==='keep'&&$settings->excludedCollectionAction==='keep','new defaults keep');
 $media=(new ReflectionClass(skeeks\cms\shop\gpd\ShopCatalogWriter::class))->newInstanceWithoutConstructor();
 $writer=new ShopReferenceWriter(1,'test',$media,$settings);
 $wire=new AuditWire();$receiver=new CatalogReceiver($db,'test',$wire);$receiver->register(1,'https://fixture.invalid/v2',str_repeat('a',64));
 $refs=new CatalogReceiver($db,'test',$wire,'references');$refs->register(1,'https://fixture.invalid/v2',str_repeat('a',64));
 $brand=['id'=>10,'revision'=>'1','operation'=>'revoke','kind'=>'brands','source_id'=>101];
 $collection=['id'=>20,'revision'=>'1','operation'=>'revoke','kind'=>'collections','source_id'=>102];
 $apply=function($item)use($refs,$writer,$db){$refs->acceptBatch([$item['id']],[$item],true);$state=(new yii\db\Query())->from('shop_gpd_reference_state')->where(['product_id'=>$item['id']])->one();return $db->transaction(fn()=>$writer->apply($item,$state));};
 check($apply($brand)['outcome']==='kept','default revoke keeps brand');
 $db->createCommand('UPDATE shop_gpd_reference_state SET applied_revision=1 WHERE product_id=10')->execute();
 $settings->excludedCollectionAction='delete';check($apply($collection)['outcome']==='protected','linked collection safe delete protected');
 check((int)$db->createCommand('SELECT COUNT(*) FROM shop_product2collection')->queryScalar()===1,'safe mode preserves links');
 $settings->excludedCollectionAction='delete_related';check($apply($collection)['outcome']==='protected','linked collection retained');
 $db->createCommand('DELETE FROM shop_product2collection WHERE shop_collection_id=2')->execute();
 check($apply($collection)['outcome']==='deleted','empty collection removed');
 check((int)$db->createCommand('SELECT applied_revision FROM shop_gpd_reference_state WHERE product_id=10')->queryScalar()===0,'collection removal schedules parent revoke again');
 check((int)$db->createCommand('SELECT COUNT(*) FROM shop_product2collection')->queryScalar()===0,'technical collection link removed');
 $settings->excludedBrandAction='delete';check($apply($brand)['outcome']==='protected','linked brand protected');
 $db->createCommand('UPDATE shop_product SET brand_id=NULL WHERE id=3')->execute();
 check($apply($brand)['outcome']==='deactivated','empty brand with filter safe delete falls back');
 $settings->excludedBrandAction='delete_related';skeeks\cms\models\CmsSavedFilter::$veto=true;
 try{$apply($brand);throw new LogicException('expected filter veto');}catch(RuntimeException $e){check($e->getMessage()==='Удаление сохранённого фильтра отменено.','filter veto propagated');}
 check((int)$db->createCommand('SELECT COUNT(*) FROM shop_brand')->queryScalar()===1,'failed removal rolls brand back');
 check((int)$db->createCommand('SELECT COUNT(*) FROM cms_saved_filter WHERE id=5')->queryScalar()===1,'failed removal retains filter');
 skeeks\cms\models\CmsSavedFilter::$veto=false;
 check($apply($brand)['outcome']==='deleted','related brand removed');
 check((int)$db->createCommand('SELECT COUNT(*) FROM cms_saved_filter')->queryScalar()===0,'brand saved filter removed');
 check((int)$db->createCommand('SELECT COUNT(*) FROM shop_product')->queryScalar()===2,'products retained');
 check((int)$db->createCommand('SELECT COUNT(*) FROM shop_order_item')->queryScalar()===1,'order retained');
 check($db->createCommand('SELECT brand_id FROM shop_product WHERE id=3')->queryScalar()===null,'product detached from removed brand');
 $wire->items=[103=>['id'=>103,'revision'=>'7','operation'=>'revoke'],104=>['id'=>104,'revision'=>'8','operation'=>'revoke']];
 $audit=new CatalogReconciler(1,'test',$wire,$wire,new ProductAuditWriter(),$writer);
 $page=$audit->page('products',0,$audit->upper('products'),static function(){});
 check($page['counts']['checked']===1,'audit scoped to actual site');
 check((int)$db->createCommand('SELECT COUNT(*) FROM shop_gpd_catalog_state WHERE product_id=103')->queryScalar()===1,'untracked legacy product registered');
 check((int)$db->createCommand('SELECT COUNT(*) FROM shop_gpd_catalog_state WHERE product_id=104')->queryScalar()===0,'other site not registered');
 check($receiver->state()['cursor']===null&&$receiver->state()['phase']==='bootstrap','audit leaves stream cursor untouched');
 $wire->bad=true;
 try{$audit->page('products',0,3,static function(){});throw new LogicException('expected incomplete');}catch(skeeks\cms\shop\gpd\ProtocolException $e){check($e->reason==='incomplete_batch','incomplete response rejected');}
 $wire->bad=false;$wire->items=[103=>['id'=>103,'status'=>'not_available']];
 $page=$audit->page('products',0,3,static function(){});check($page['counts']['pending']===1,'unordered absence not deleted');
 $db->createCommand('UPDATE shop_gpd_catalog_state SET applied_revision=7 WHERE product_id=103')->execute();
 $wire->items=[103=>['id'=>103,'revision'=>'7','operation'=>'revoke']];
 $page=$audit->page('products',0,3,static function(){});check($page['counts']['kept']===1,'confirmed revoke revisited under current policy');
 $db->createCommand('INSERT INTO shop_brand VALUES(7,107,"Legacy",1),(8,108,"Other site",1)')->execute();
 $db->createCommand('UPDATE shop_product SET brand_id=8 WHERE id=4')->execute();
 $settings->excludedBrandAction='delete_related';
 $wire->items=[107=>['id'=>70,'revision'=>'9','operation'=>'revoke'],108=>['id'=>80,'revision'=>'10','operation'=>'revoke']];
 $page=$audit->page('brands',0,$audit->upper('brands'),static function(){});
 check($page['counts']['deleted']===1&&$page['counts']['protected']===1,'legacy reference resolve removes only this site reference');
 check((int)$db->createCommand('SELECT COUNT(*) FROM shop_brand WHERE id=8 AND is_active=1')->queryScalar()===1,'other site brand activity retained');
 $db->createCommand('INSERT INTO shop_collection VALUES(9,109,"Unknown",1,NULL)')->execute();$wire->items=[];
 $page=$audit->page('collections',0,$audit->upper('collections'),static function(){});
 check($page['counts']['pending']===1&&(int)$db->createCommand('SELECT is_active FROM shop_collection WHERE id=9')->queryScalar()===1,'unknown legacy collection is preserved');
 $db->createCommand('INSERT INTO shop_brand VALUES(10,110,"Manual products brand",1)')->execute();
 $db->createCommand('INSERT INTO cms_content_element VALUES(11,NULL,1,"Y",NULL)')->execute();
 $db->createCommand('INSERT INTO shop_product VALUES(11,10,NULL)')->execute();
 $db->createCommand('INSERT INTO shop_product2collection VALUES(11,9)')->execute();
 foreach(['deactivate','delete','delete_related'] as $policy) {
  $settings->excludedBrandAction=$settings->excludedCollectionAction=$policy;
  $wire->items=[110=>['id'=>100,'revision'=>'11','operation'=>'revoke'],109=>['id'=>90,'revision'=>'12','operation'=>'revoke']];
  $page=$audit->page('brands',8,10,static function(){});check($page['counts']['protected']===1,'manual product protects brand in '.$policy);
  $page=$audit->page('collections',0,9,static function(){});check($page['counts']['protected']===1,'manual product protects collection in '.$policy);
 }
 check((int)$db->createCommand('SELECT is_active FROM shop_brand WHERE id=10')->queryScalar()===1&&(int)$db->createCommand('SELECT is_active FROM shop_collection WHERE id=9')->queryScalar()===1,'manual references remain active');
 check((int)$db->createCommand('SELECT brand_id FROM shop_product WHERE id=11')->queryScalar()===10&&(int)$db->createCommand('SELECT COUNT(*) FROM shop_product2collection WHERE shop_product_id=11')->queryScalar()===1,'manual product links retained');
 $db->createCommand('UPDATE shop_collection SET shop_brand_id=10 WHERE id=9')->execute();
 $db->createCommand('UPDATE shop_product SET brand_id=NULL WHERE id=11')->execute();
 $page=$audit->page('brands',8,10,static function(){});check($page['counts']['protected']===1,'manual product in brand collection also protects brand');
 $db->createCommand('UPDATE cms_content_element SET sx_id=111 WHERE id=11')->execute();
 $db->createCommand('UPDATE shop_product SET brand_id=10 WHERE id=11')->execute();
 $db->createCommand('INSERT INTO shop_store VALUES(1,NULL,1),(2,222,1)')->execute();
 $db->createCommand('INSERT INTO shop_store_product VALUES(1,11,1,0,0)')->execute();
 foreach(['deactivate','delete','delete_related'] as $policy) {
  $settings->excludedBrandAction=$settings->excludedCollectionAction=$policy;
  $page=$audit->page('brands',8,10,static function(){});check($page['counts']['protected']===1,'local stock protects brand in '.$policy);
  $page=$audit->page('collections',0,9,static function(){});check($page['counts']['protected']===1,'local stock protects collection in '.$policy);
 }
 $db->createCommand('UPDATE shop_store_product SET shop_store_id=2')->execute();
 $db->createCommand('INSERT INTO shop_store_product_move VALUES(1,1)')->execute();
 $page=$audit->page('brands',8,10,static function(){});check($page['counts']['protected']===1,'stock movement protects brand even on GPD store');
 $page=$audit->page('collections',0,9,static function(){});check($page['counts']['protected']===1,'stock movement protects collection');
 echo "PASS $n reconciliation/removal checks\n";
}finally{if($db->getTransaction())$db->getTransaction()->rollBack();$db->createCommand("DROP DATABASE $name")->execute();}
