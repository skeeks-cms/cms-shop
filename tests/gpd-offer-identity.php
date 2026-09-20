<?php
namespace Fixture {
class Query {
 private $rows;private $conditions=[];
 public function __construct(array $rows){$this->rows=$rows;}
 public function where($c){$this->conditions=[$c];return $this;}
 public function andWhere($c){$this->conditions[]=$c;return $this;}
 public function limit($n){return $this;}
 public function orderBy($o){return $this;}
 public function all(){return array_values(array_filter($this->rows,function($r){
 foreach($this->conditions as $c){
  if(isset($c[0])){if($c[0]==='>'&&!($r->{$c[1]}>$c[2]))return false;if($c[0]==='not in'&&in_array($r->{$c[1]},$c[2]))return false;}
  else foreach($c as $k=>$v){if(is_array($v)?!in_array($r->$k,$v):$r->$k!=$v)return false;}
 }return true;
 }));}
 public function one(){return $this->all()[0]??null;}
}
}
namespace skeeks\cms\shop\models {
#[\AllowDynamicProperties] class ShopCmsContentElement{
 public static $rows=[];public static function find(){return new \Fixture\Query(self::$rows);}
}
#[\AllowDynamicProperties] class ShopStore{
 public static $rows=[];public static function find(){return new \Fixture\Query(self::$rows);}
}
#[\AllowDynamicProperties] class ShopStoreProduct{
 public static $rows=[];public $id,$shop_product_id,$shop_store_id,$external_id,$is_active=1,$quantity=1,$isNewRecord=false;
 public static function find(){return new \Fixture\Query(self::$rows);}
 public function getDirtyAttributes(){return ['fixture'=>1];}
 public function save(){foreach(self::$rows as $other){if($other!==$this&&$other->shop_store_id==$this->shop_store_id&&(($this->shop_product_id&&$other->shop_product_id==$this->shop_product_id)||($this->external_id&&$other->external_id==$this->external_id)))throw new \RuntimeException("Duplicate warehouse identity");}if(!$this->id){$this->id=count(self::$rows)+100;self::$rows[]=$this;}return true;}
}
}
namespace skeeks\cms\shop\gpd {
class OfferPrices{public static $calls=[];public function recalculate($site,$product){self::$calls[]=[$site,$product];}}
require dirname(__DIR__).'/src/gpd/CatalogWriterInterface.php';
require dirname(__DIR__).'/src/gpd/ShopOfferWriter.php';
}
namespace {
use skeeks\cms\shop\models\ShopCmsContentElement as Product;
use skeeks\cms\shop\models\ShopStore as Store;
use skeeks\cms\shop\models\ShopStoreProduct as Row;
use skeeks\cms\shop\gpd\ShopOfferWriter;
use skeeks\cms\shop\gpd\OfferPrices;
function model($class,$attrs){$r=new $class();foreach($attrs as $k=>$v)$r->$k=$v;return $r;}
Product::$rows=[model(Product::class,['id'=>1,'cms_site_id'=>7,'sx_id'=>100]),model(Product::class,['id'=>2,'cms_site_id'=>7,'sx_id'=>200]),model(Product::class,['id'=>3,'cms_site_id'=>8,'sx_id'=>300])];
Store::$rows=[model(Store::class,['id'=>10,'cms_site_id'=>7,'sx_id'=>20])];
$old=model(Row::class,['id'=>50,'shop_store_id'=>10,'shop_product_id'=>1,'external_id'=>'old']);
$code=model(Row::class,['id'=>51,'shop_store_id'=>10,'shop_product_id'=>null,'external_id'=>'current']);
Row::$rows=[$old,$code];
$item=['id'=>100,'operation'=>'upsert','data'=>['kind'=>'offers','source_id'=>100,'payload'=>['id'=>100,'complete'=>true,'currency'=>'RUB','offers'=>[['store_id'=>20,'supplier_code'=>'current','supplier_name'=>'Fixture','is_active'=>true,'quantity'=>5,'purchase_price'=>2,'selling_price'=>3]]]]];
$w=new ShopOfferWriter(7,static function(){});$w->apply($item,[]);
if($code->shop_product_id!==1||$old->is_active||$old->shop_product_id!==null||count(Row::$rows)!==2)throw new RuntimeException('Unbound code must be adopted, old row deactivated without deletion');
$item['id']=200;$item['data']['source_id']=200;$item['data']['payload']['id']=200;
OfferPrices::$calls=[];$w->apply($item,[]);
if($code->shop_product_id!==2||OfferPrices::$calls!==[[7,1],[7,2]])throw new RuntimeException('Reassignment must recalculate both products');
$w->apply($item,[]);
if($code->id!==51||count(Row::$rows)!==2)throw new RuntimeException('Repeat must retain identity');
$code->shop_product_id=3;
try{$w->apply($item,[]);throw new LogicException('Cross-site reassignment accepted');}catch(RuntimeException $e){}
if($code->shop_product_id!==3)throw new RuntimeException('Foreign product modified');
echo "OK offer identity: unbound adoption, reassignment, old/new prices, repeat, cross-site protection\n";
}