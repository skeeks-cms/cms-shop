<?php
// Disposable local MariaDB; deliberately does not load a site configuration.
require '/app/vendor/autoload.php';
require '/app/vendor/yiisoft/yii2/Yii.php';
require __DIR__.'/../src/gpd/CatalogRemoval.php';
new yii\console\Application(['id'=>'removal-test','basePath'=>__DIR__,'vendorPath'=>'/app/vendor','extensions'=>[]]);
$db=new yii\db\Connection(['dsn'=>'mysql:host=db','username'=>'root','password'=>'root']);
$name='gpd_removal_test_'.bin2hex(random_bytes(5));
$db->createCommand("CREATE DATABASE $name")->execute();$db->createCommand("USE $name")->execute();
$n=0;
function check($ok,$label){global $n;if(!$ok)throw new RuntimeException($label);++$n;}
try {
 foreach(['cms_content_element'=>'id INT PRIMARY KEY,cms_site_id INT,parent_content_element_id INT NULL',
 'shop_product'=>'id INT PRIMARY KEY',
 'shop_store'=>'id INT PRIMARY KEY,sx_id INT NULL,cms_site_id INT',
 'shop_store_product'=>'id INT PRIMARY KEY,shop_product_id INT,shop_store_id INT,is_active INT,quantity DECIMAL(12,2)',
 'shop_store_product_move'=>'id INT PRIMARY KEY,shop_store_product_id INT NULL'] as $t=>$columns)$db->createCommand("CREATE TABLE $t ($columns) ENGINE=InnoDB")->execute();
 foreach(['shop_order_item','shop_bill_item','shop_document_item','shop_product_quantity_change','shop_feedback'] as $t)$db->createCommand("CREATE TABLE $t (id INT PRIMARY KEY,shop_product_id INT NULL) ENGINE=InnoDB")->execute();
 $db->createCommand('INSERT INTO cms_content_element VALUES(1,1,NULL),(2,2,NULL)')->execute();
 $db->createCommand('INSERT INTO shop_product VALUES(1),(2)')->execute();
 $db->createCommand('INSERT INTO shop_store VALUES(1,101,1),(2,NULL,1),(3,102,2)')->execute();
 $guard=new skeeks\cms\shop\gpd\CatalogRemoval($db);
 try{$guard->positions(1,1);throw new RuntimeException('transaction required');}catch(LogicException $e){++$n;}
 $tx=$db->beginTransaction();
 check($guard->positions(1,1)===[],'isolated product removable');
 check($guard->positions(2,1)===null,'other site protected');
 check($guard->positions(999,1)===null,'missing product protected');
 $db->createCommand('INSERT INTO shop_store_product VALUES(10,1,1,1,17)')->execute();
 check($guard->positions(1,1)===[10],'GPD supplier availability is removable');
 $db->createCommand('UPDATE shop_store_product SET is_active=0,quantity=0')->execute();
 check($guard->positions(1,1)===[10],'zero inactive GPD position removable');
 foreach(['shop_order_item','shop_bill_item','shop_document_item','shop_product_quantity_change','shop_feedback'] as $t){
  $db->createCommand("INSERT INTO $t VALUES(1,1)")->execute();
  check($guard->positions(1,1)===null,'protected '.$t);
  $db->createCommand("DELETE FROM $t")->execute();
 }
 $db->createCommand('INSERT INTO shop_store_product_move VALUES(1,10)')->execute();
 check($guard->positions(1,1)===null,'movement protected before SET NULL FK');
 $db->createCommand('DELETE FROM shop_store_product_move')->execute();
 foreach([2,3] as $store){$db->createCommand('UPDATE shop_store_product SET shop_store_id=:s',[':s'=>$store])->execute();check($guard->positions(1,1)===null,'local or other-site position protected');}
 $db->createCommand('UPDATE shop_store_product SET shop_store_id=1')->execute();
 $db->createCommand('INSERT INTO cms_content_element VALUES(3,1,1)')->execute();
 check($guard->positions(1,1)===null,'child protected');
 $db->createCommand('DELETE FROM cms_content_element WHERE id=3')->execute();
 $savepoint=$db->beginTransaction();
 foreach($guard->positions(1,1) as $id)$db->createCommand()->delete('shop_store_product',['id'=>$id])->execute();
 $savepoint->rollBack();
 check((int)$db->createCommand('SELECT COUNT(*) FROM shop_store_product WHERE id=10')->queryScalar()===1,'position restored on product removal failure');
 $tx->rollBack();
 // Exercise the actual writer transaction with SQL-backed model doubles.
 eval('namespace skeeks\\cms\\shop\\models; class ShopStoreProduct extends \\yii\\db\\ActiveRecord { public static function tableName(){return "shop_store_product";} }');
 Yii::$app->set('db',$db);
 $db->createCommand('INSERT INTO shop_store_product VALUES(10,1,1,0,0)')->execute();
 $writerClass=new ReflectionClass(skeeks\cms\shop\gpd\ShopCatalogWriter::class);
 $writer=$writerClass->newInstanceWithoutConstructor();
 foreach(['site'=>1,'settings'=>(object)['excludedAction'=>'delete']] as $key=>$value){$prop=$writerClass->getProperty($key);$prop->setAccessible(true);$prop->setValue($writer,$value);}
 $revoke=$writerClass->getMethod('revoke');$revoke->setAccessible(true);
 $model=new class($db) {
  public $id=1,$sx_id=101,$is_active=1,$fail=true;private $db;
  public function __construct($db){$this->db=$db;}
  public function save(){return true;}
  public function delete(){if($this->fail)throw new RuntimeException('fixture veto');$this->db->createCommand('DELETE FROM shop_product WHERE id=1')->execute();return $this->db->createCommand('DELETE FROM cms_content_element WHERE id=1')->execute();}
 };
 try{$revoke->invoke($writer,$model,['deactivated_by_gpd'=>1]);throw new LogicException('expected veto');}catch(RuntimeException $e){check($e->getMessage()==='fixture veto','writer propagates veto');}
 check((int)$db->createCommand('SELECT COUNT(*) FROM shop_store_product WHERE id=10')->queryScalar()===1,'writer rolls positions back');
 $db->createCommand('INSERT INTO shop_order_item VALUES(1,1)')->execute();
 check($revoke->invoke($writer,$model,['deactivated_by_gpd'=>1])['outcome']==='deactivated','writer retains ordered product');
 $db->createCommand('DELETE FROM shop_order_item')->execute();
 $model->fail=false;
 check($revoke->invoke($writer,$model,['deactivated_by_gpd'=>1])['outcome']==='deleted','writer deletes isolated GPD product');
 check((int)$db->createCommand('SELECT COUNT(*) FROM shop_store_product WHERE id=10')->queryScalar()===0,'writer deletes technical position');
 check((int)$db->createCommand('SELECT COUNT(*) FROM cms_content_element WHERE id=2')->queryScalar()===1,'other site intact');
 echo "OK: $n removal checks\n";
} finally {if($db->getTransaction())$db->getTransaction()->rollBack();$db->createCommand("DROP DATABASE $name")->execute();}
