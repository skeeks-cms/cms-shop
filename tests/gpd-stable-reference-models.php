<?php
// Integration test: explicitly supplied Yii bootstrap, transactional fixtures only.
if (empty($argv[1])) throw new RuntimeException('Pass the test Yii bootstrap path.');
require $argv[1];
$db=Yii::$app->db;
use skeeks\cms\shop\gpd\StableReferenceSync;
use skeeks\cms\shop\gpd\CatalogTransportInterface;
use skeeks\cms\shop\gpd\ReferencePendingException;
use skeeks\cms\models\CmsCountry;
use skeeks\cms\measure\models\CmsMeasure;
class FixtureTransport implements CatalogTransportInterface {
 public $calls=0; public $rows=[];
 public function request(string $method,string $endpoint,array $data=[]):array {++$this->calls;return $this->rows[$endpoint]??[];}
}
$n=0;function check($value,$message){global $n;if(!$value)throw new RuntimeException($message);++$n;}
$t=$db->beginTransaction();
try{
 check(!CmsCountry::find()->where(['alpha2'=>'QZ'])->exists(),'fixture country collision');
 check(!CmsMeasure::find()->where(['code'=>'gpd-fixture'])->exists(),'fixture measure collision');
 $transport=new FixtureTransport();$transport->rows=['countries'=>[['alpha2'=>'QZ','alpha3'=>'QZZ','iso'=>'999','name'=>'Fixture country']], 'measures'=>[['code'=>'gpd-fixture','name'=>'Fixture unit','symbol'=>'fx']]];
 $images=0;$sync=new StableReferenceSync($transport,static function($image)use(&$images){++$images;return null;},static function(){});
 $sync->ensure('countries','QZ');$sync->ensure('measures','gpd-fixture');
 $country=CmsCountry::find()->where(['alpha2'=>'QZ'])->one();$measure=CmsMeasure::find()->where(['code'=>'gpd-fixture'])->one();
 check($country&&$country->name==='Fixture country','country creation');check($measure&&$measure->symbol==='fx','measure creation');
 $country->name='Local country';$country->save(false);$measure->name='Local unit';$measure->symbol='local';$measure->save(false);
 check($sync->apply('countries',['alpha2'=>'QZ','name'=>'Overwrite','image'=>['src'=>'invalid']])==='existing','country skip');
 check($sync->apply('measures',['code'=>'gpd-fixture','name'=>'Overwrite','symbol'=>'bad'])==='existing','measure skip');
 $country->refresh();$measure->refresh();check($country->name==='Local country'&&$measure->symbol==='local','local values preserved');check($images===1,'existing country image untouched');
 $sync->ensure('countries','QZ');check($transport->calls===2,'existing code no HTTP');
 try{$sync->ensure('countries','NO_SUCH_CODE');throw new RuntimeException('missing accepted');}catch(ReferencePendingException $e){++$n;}
 check($transport->calls===2,'dictionary response cached');
 $bad=new FixtureTransport();$bad->rows=['countries'=>[['alpha2'=>'AA'],['alpha2'=>'AA']]];
 try{(new StableReferenceSync($bad,static fn()=>null,static function(){}))->rows('countries');throw new RuntimeException('duplicate accepted');}catch(\skeeks\cms\shop\gpd\ProtocolException $e){++$n;}
 echo "PASS $n stable dictionary model checks; rolling back fixtures\n";
}finally{$t->rollBack();}