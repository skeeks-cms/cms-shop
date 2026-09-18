<?php
// Run against an explicitly authorized Yii test site; fixtures roll back.
if (empty($argv[1])) throw new RuntimeException('Pass a Yii bootstrap path.');
require $argv[1];
$app=Yii::$app;$db=$app->db;
use skeeks\cms\shop\gpd\CatalogApplier;
use skeeks\cms\shop\gpd\CatalogTransportInterface;
use skeeks\cms\shop\gpd\ShopCatalogWriter;
use skeeks\cms\shop\gpd\ShopReferenceWriter;
use skeeks\cms\shop\gpd\ReferencePendingException;
class NoReferenceNetwork implements CatalogTransportInterface {public function request(string $method,string $endpoint,array $data=[]):array{throw new RuntimeException('Unexpected HTTP');}}
$settings=new \skeeks\cms\shop\components\GpdComponent();$api=$app->skeeksSuppliersApi;
$writer=new ShopReferenceWriter(1,'reference-fixture',new ShopCatalogWriter(1,$settings,$api,static function(){}));
$applier=new CatalogApplier($db,'reference-fixture',new NoReferenceNetwork(),$writer,'references');
$checks=0;
function check($v,$m){global $checks;if(!$v)throw new RuntimeException($m);++$checks;}
function applyReference(int $id,string $kind,int $source,array $payload,int $revision):array {
    global $db,$applier;
    $key=['connection_id'=>'reference-fixture','product_id'=>$id];
    $values=['revision'=>$revision,'product_revision'=>$revision,'operation'=>'upsert','needs_resolution'=>0,'updated_at'=>time()];
    $db->createCommand()->upsert('{{%shop_gpd_reference_state}}',$key+$values,$values)->execute();
    $state=(new yii\db\Query())->from('{{%shop_gpd_reference_state}}')->where($key)->one();
    return $applier->apply(['state'=>$state,'item'=>['id'=>$id,'revision'=>(string)$revision,'product_revision'=>(string)$revision,'operation'=>'upsert','data'=>['id'=>$id,'kind'=>$kind,'source_id'=>$source,'payload'=>['id'=>$source]+$payload]]]);
}
$t=$db->beginTransaction();
try {
 $db->createCommand()->insert('{{%shop_gpd_reference_connection}}',['id'=>'reference-fixture','cms_site_id'=>1,'source_url'=>'https://fixture.invalid','credential_fingerprint'=>str_repeat('x',64),'updated_at'=>time()])->execute();
 $id=2000000111;$image=['id'=>2000000111,'src'=>'/gpd-category-fixture.webp','width'=>1,'height'=>1];
 $data=['name'=>'Category fixture','description_full'=>'Original description','image'=>$image,'has_collections'=>1];
 check(applyReference($id,'categories',$id,$data,1)['outcome']==='created','new category');
 $m=ShopReferenceWriter::find(1,'categories',$id);check((int)$m->is_sx_info_update===1,'new enabled default');
 $data['name']='Updated category';check(applyReference($id,'categories',$id,$data,2)['outcome']==='updated','enabled updated');
 $m->refresh();check($m->name==='Updated category','new name applied');$imageId=$m->image_id;
 $m->is_sx_info_update=0;$m->name='Local name';$m->description_full='Local description';$m->save(false);
 $data['name']='Remote overwrite';$data['description_full']='Remote overwrite';$data['image']=null;$data['has_collections']=0;
 check(applyReference($id,'categories',$id,$data,3)['outcome']==='protected','disabled protected');$m->refresh();
 check($m->name==='Local name'&&$m->description_full==='Local description'&&$m->image_id===$imageId&&(int)$m->shop_has_collections===1,'all protected fields retained');
 check(ShopReferenceWriter::required(1,'reference-fixture','categories',$id)->id===$m->id,'protected category usable by products');
 $state=(new yii\db\Query())->from('{{%shop_gpd_reference_state}}')->where(['connection_id'=>'reference-fixture','product_id'=>$id])->one();check((int)$state['applied_revision']===3,'protected version acknowledged');
 $m->is_sx_info_update=1;$m->save(false);check(applyReference($id,'categories',$id,$data,4)['outcome']==='updated','reenabled applies next version');$m->refresh();check($m->name==='Remote overwrite'&&!$m->image_id,'reenabled fields and image removal');
 $m->is_sx_info_update=0;$m->save(false);
 $legacy=new \skeeks\cms\shop\console\controllers\SkeeksSuppliersController('fixture',$app);
 $method=new ReflectionMethod($legacy,'_updateTree');$method->setAccessible(true);
 check($method->invoke($legacy,['id'=>$id,'name'=>'Legacy overwrite'],$m)===false,'legacy skips protected');$m->refresh();check($m->name==='Remote overwrite','legacy preserves name');
 $m->is_sx_info_update=2;check(!$m->validate(['is_sx_info_update']),'invalid flag rejected');
 echo "PASS $checks category model checks; fixture changes rolled back\n";
}finally{$t->rollBack();}