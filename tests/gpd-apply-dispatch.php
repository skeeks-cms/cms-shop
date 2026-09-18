<?php
$loader=require '/deps/autoload.php';$loader->addPsr4('skeeks\\cms\\shop\\','/shared-vendor/skeeks/cms-shop/src',true);require '/deps/yiisoft/yii2/Yii.php';
new yii\console\Application(['id'=>'apply-wake-test','basePath'=>__DIR__,'vendorPath'=>'/deps','extensions'=>[],'components'=>['db'=>['class'=>yii\db\Connection::class,'dsn'=>'sqlite::memory:']]]);
$db=Yii::$app->db;$db->createCommand('CREATE TABLE shop_gpd_connection(id TEXT PRIMARY KEY,cms_site_id INT)')->execute();$db->createCommand('CREATE TABLE shop_gpd_catalog_state(connection_id TEXT,needs_resolution INT,operation TEXT,revision INT,applied_revision INT)')->execute();
$db->createCommand()->batchInsert('shop_gpd_connection',['id','cms_site_id'],[['a',1],['b',2]])->execute();
$jobs=new class extends yii\base\Component {public $calls=[];public function push($type,$payload,$options){$this->calls[]=[$type,$payload,$options];}};Yii::$app->set('jobs',$jobs);
$gpd=new class extends yii\base\Component {public $enabled=true;public function forSite($site){return $this;}};Yii::$app->set('gpd',$gpd);$n=0;
function check($v,$m){global $n;if(!$v)throw new RuntimeException($m);++$n;}
use skeeks\cms\shop\gpd\CatalogApplyDispatch as Dispatch;
Dispatch::forSite(1);check(!$jobs->calls,'No job for empty state');
$db->createCommand()->insert('shop_gpd_catalog_state',['connection_id'=>'b','needs_resolution'=>0,'operation'=>'upsert','revision'=>1,'applied_revision'=>0])->execute();Dispatch::forSite(1);check(!$jobs->calls,'Other site ignored');
$db->createCommand()->insert('shop_gpd_catalog_state',['connection_id'=>'a','needs_resolution'=>1,'operation'=>'upsert','revision'=>1,'applied_revision'=>0])->execute();Dispatch::forSite(1);check(!$jobs->calls,'Unresolved ignored');
$db->createCommand()->update('shop_gpd_catalog_state',['needs_resolution'=>0],['connection_id'=>'a'])->execute();$t=$db->beginTransaction();Dispatch::forSite(1);check(!$jobs->calls,'No precommit dispatch');$t->commit();Dispatch::forSite(1);check(count($jobs->calls)===1&&$jobs->calls[0][2]['siteId']===1&&$jobs->calls[0][1]===[],'Committed state wakes native apply without payload');
$jobs->calls=[];$gpd->enabled=false;Dispatch::forSite(1);check(!$jobs->calls,'Disabled component preserved');$gpd->enabled=true;
$db->createCommand()->update('shop_gpd_catalog_state',['applied_revision'=>1],['connection_id'=>'a'])->execute();Dispatch::forSite(1);check(!$jobs->calls,'Applied state ignored');
$db->createCommand()->update('shop_gpd_catalog_state',['operation'=>'revoke','revision'=>2],['connection_id'=>'a'])->execute();Dispatch::forSite(1);check(count($jobs->calls)===1,'Revocation wakes apply');
echo "PASS $n native apply dispatch checks\n";
