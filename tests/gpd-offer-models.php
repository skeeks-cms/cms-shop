<?php
// Real-model integration test; all fixture writes roll back.
if (empty($argv[1])) throw new RuntimeException('Pass a Yii bootstrap path.');
require $argv[1];
$app=Yii::$app;$db=$app->db;
use skeeks\cms\shop\models\ShopStore;use skeeks\cms\shop\models\ShopStoreProduct;use skeeks\cms\shop\models\ShopCmsContentElement;use skeeks\cms\shop\gpd\ShopOfferWriter;use skeeks\cms\shop\gpd\ReferencePendingException;
$n=0;function check($v,$m){global $n;if(!$v)throw new RuntimeException($m);++$n;}
function saveModel($m){if(!$m->save())throw new RuntimeException(json_encode($m->errors,JSON_UNESCAPED_UNICODE));}
$t=$db->beginTransaction();
try{
 $product=ShopCmsContentElement::find()->where(['cms_site_id'=>1])->andWhere(['>','sx_id',0])->one();if(!$product)throw new RuntimeException('No pilot product');
 $base=ShopStore::find()->where(['cms_site_id'=>1])->andWhere(['>','sx_id',0])->one();if(!$base)throw new RuntimeException('No mapped warehouse');
 $store=new ShopStore();$attrs=$base->attributes;unset($attrs['id']);$store->setAttributes($attrs,false);$store->name='GPD fixture remote store';$store->external_id='gpd-fixture-remote';$store->sx_id=200000003;saveModel($store);
 $local=new ShopStore();$attrs=$base->attributes;unset($attrs['id']);$local->setAttributes($attrs,false);$local->name='GPD fixture local store';$local->external_id='gpd-fixture-local';$local->sx_id=null;$local->is_supplier=0;$local->is_sync_external=0;saveModel($local);
 $own=new ShopStoreProduct(['shop_store_id'=>$local->id,'shop_product_id'=>$product->id,'external_id'=>'local','name'=>'Local','quantity'=>123,'purchase_price'=>1,'selling_price'=>2]);saveModel($own);
 $product->is_sx_info_update=0;$product->save(false);
 $writer=new ShopOfferWriter(1,static function(){});$id=(int)$product->sx_id;
 $offer=['id'=>71,'store_id'=>200000003,'supplier_code'=>'fixture-A','supplier_name'=>'Fixture','quantity'=>12,'purchase_price'=>100,'selling_price'=>200,'is_active'=>true];
 $item=['id'=>$id,'operation'=>'upsert','data'=>['id'=>$id,'kind'=>'offers','source_id'=>$id,'payload'=>['id'=>$id,'complete'=>true,'currency'=>'RUB','offers'=>[$offer]]]];
 $writer->prepare($item);$writer->apply($item,[]);$row=ShopStoreProduct::find()->where(['shop_store_id'=>$store->id,'shop_product_id'=>$product->id])->one();
 check($row&&(float)$row->quantity===12.0,'create position despite protected description');$rowId=$row->id;
 $writer->apply($item,[]);check(ShopStoreProduct::find()->where(['shop_store_id'=>$store->id,'shop_product_id'=>$product->id])->count()==1,'repeat no duplicates');
 $item['data']['payload']['offers'][0]['supplier_code']='fixture-B';$item['data']['payload']['offers'][0]['quantity']=7;$writer->apply($item,[]);$row->refresh();check($row->id===$rowId&&$row->external_id==='fixture-B'&&(float)$row->quantity===7.0,'code rename and quantity update');
 $item['data']['payload']['offers']=[];$writer->apply($item,[]);$row->refresh();$own->refresh();check(!$row->is_active&&(float)$row->quantity===0.0,'missing position deactivated');check($own->is_active&&(float)$own->quantity===123.0,'local warehouse preserved');
 $item['data']['payload']['offers']=[$offer];$writer->apply($item,[]);$row->refresh();check($row->is_active&&(float)$row->quantity===12.0,'return restores same position');
 $writer->apply(['id'=>$id,'operation'=>'revoke'],[]);$row->refresh();check(!$row->is_active&&(float)$row->quantity===0.0,'product revoke clears GPD position');
 $bad=$item;$bad['data']['payload']['offers'][0]['store_id']=200000099;
 try{$writer->apply($bad,[]);throw new RuntimeException('unknown store accepted');}catch(ReferencePendingException $e){++$n;}
 $bad=$item;$bad['data']['payload']['complete']=false;
 try{$writer->prepare($bad);throw new RuntimeException('partial bundle accepted');}catch(\skeeks\cms\shop\gpd\ProtocolException $e){++$n;}
 echo "PASS $n real offer model checks; rolling back all fixtures\n";
}finally{$t->rollBack();}