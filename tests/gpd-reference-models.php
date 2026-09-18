<?php
// Real-model integration test against an explicitly authorized Yii test site.
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
    $image=['id'=>2000000101,'src'=>'/reference-fixture-2000000101.webp','width'=>1,'height'=>1];
    $brand=['name'=>'GPD reference fixture','image'=>$image];
    check(applyReference(2000000001,'brands',2000000001,$brand,1)['outcome']==='created','create brand');
    $model=ShopReferenceWriter::find(1,'brands',2000000001);check((bool)$model->logo_image_id,'brand image');
    $brand['name']='GPD renamed fixture';check(applyReference(2000000001,'brands',2000000001,$brand,2)['outcome']==='updated','rename brand');
    $model->refresh();check($model->name===$brand['name'],'renamed value');
    $model->is_sx_info_update=0;$model->name='Local protected brand';$model->save(false);
    $brand['image']=null;check(applyReference(2000000001,'brands',2000000001,$brand,3)['outcome']==='protected','brand protected');
    $model->refresh();check($model->name==='Local protected brand'&&(bool)$model->logo_image_id,'protected brand preserves name and image');
    $model->is_sx_info_update=1;$model->save(false);applyReference(2000000001,'brands',2000000001,$brand,4);$model->refresh();check(!$model->logo_image_id,'brand image removal');
    $collection=['name'=>'GPD collection fixture','brand_id'=>2000000001,'image'=>$image,'images'=>[$image]];
    check(applyReference(2000000002,'collections',2000000002,$collection,1)['outcome']==='created','create collection');
    $c=ShopReferenceWriter::find(1,'collections',2000000002);check((bool)$c->cms_image_id&&$c->getImages()->exists(),'collection media');
    $c->is_sx_info_update=0;$c->name='Local protected collection';$c->save(false);$collection['image']=null;$collection['images']=[];
    check(applyReference(2000000002,'collections',2000000002,$collection,2)['outcome']==='protected','collection protected');$c->refresh();check($c->name==='Local protected collection'&&$c->getImages()->exists(),'protected collection keeps gallery');
    $c->is_sx_info_update=1;$c->save(false);applyReference(2000000002,'collections',2000000002,$collection,3);$c->refresh();check(!$c->cms_image_id&&!$c->getImages()->exists(),'collection images removed');
    $property=['name'=>'GPD property fixture','type'=>'list','measure_code'=>null,'category_ids'=>[],'is_multiple'=>1,'enums'=>[['id'=>2000000003,'value'=>'Original','image'=>$image]]];
    check(applyReference(2000000003,'properties',2000000003,$property,1)['outcome']==='created','create property and enum');
    $multi=ShopReferenceWriter::find(1,'properties',2000000003);check((int)$multi->is_multiple===1,'multiple flag survives model save');
    $property['name']='Renamed property';$property['enums'][0]['value']='Renamed enum';$property['enums'][0]['image']=null;
    applyReference(2000000003,'properties',2000000003,$property,2);
    $p=ShopReferenceWriter::find(1,'properties',2000000003);$e=$p->getEnums()->where(['sx_id'=>2000000003])->one();check($p->name==='Renamed property'&&$e->value==='Renamed enum'&&!$e->cms_image_id,'property and enum changed, image removed');
    try {ShopReferenceWriter::required(1,'reference-fixture','brands',2000000099);throw new RuntimeException('Missing dependency accepted');}catch(ReferencePendingException $e){check(true,'missing dependency waits');}
    check(ShopReferenceWriter::required(1,'reference-fixture','brands',2000000001)->id===$model->id,'applied dependency available');
    echo "PASS $checks real-model checks; all fixture records rolled back.\n";
}finally{$t->rollBack();}
