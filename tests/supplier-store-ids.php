<?php
// php tests/supplier-store-ids.php <vendor/autoload.php>
define('YII_ENABLE_ERROR_HANDLER',false);
require $argv[1];require dirname($argv[1]).'/yiisoft/yii2/Yii.php';
require dirname(__DIR__).'/src/components/ShopComponent.php';
use skeeks\cms\shop\components\ShopComponent;
use skeeks\cms\shop\models\ShopStore;
$app=new yii\console\Application(['id'=>'supplier-ids','basePath'=>__DIR__,'components'=>[
 'db'=>['class'=>yii\db\Connection::class,'dsn'=>'sqlite::memory:'],
 'cache'=>['class'=>yii\caching\ArrayCache::class],
]]);
$app->set('skeeks',new class extends yii\base\Component {public $site;});
$app->skeeks->site=(object)['id'=>1,'shopSite'=>(object)['is_show_product_only_quantity'=>2]];
$db=$app->db;
$db->createCommand('CREATE TABLE shop_store (id INTEGER PRIMARY KEY, cms_site_id INTEGER, is_supplier INTEGER, selling_extra_charge REAL, purchase_extra_charge REAL)')->execute();
$db->createCommand()->batchInsert('shop_store',['id','cms_site_id','is_supplier'],[[10,1,0],[20,1,1],[21,1,1],[30,2,1]])->execute();
ShopStore::getTableSchema();
$loaded=0;yii\base\Event::on(ShopStore::class,ShopStore::EVENT_AFTER_FIND,function()use(&$loaded){++$loaded;});
function verify($ok,$message){if(!$ok)throw new RuntimeException($message);}
function sqlCount(){return count(array_filter(Yii::getLogger()->messages,fn($m)=>$m[1]===yii\log\Logger::LEVEL_PROFILE_BEGIN&&$m[2]==='yii\db\Command::query'));}
$shop=new ShopComponent();Yii::getLogger()->messages=[];
verify($shop->getSupplierStoreIds()===[20=>20,21=>21],'ID поставщиков сайта: '.json_encode($shop->getSupplierStoreIds()));
verify(sqlCount()===1 && $loaded===0,'Один SQL, без моделей');
$shop->getSupplierStoreIds();verify(sqlCount()===1,'Повторное чтение');
$app->skeeks->site->id=2;
verify($shop->getSupplierStoreIds()===[30=>30] && sqlCount()===2,'Смена сайта');
$app->skeeks->site->id=3;
verify($shop->getSupplierStoreIds()===[],'Пустой набор');$count=sqlCount();$shop->getSupplierStoreIds();verify(sqlCount()===$count,'Пустой набор сохранён');
$shop->setSupplierStores([(object)['id'=>99]]);
verify($shop->getSupplierStoreIds()===[99=>99],'Явный setter');
$shop->setSupplierStores([]);verify($shop->getSupplierStoreIds()===[],'Пустой setter');
$shop->setSupplierStores(null);$app->skeeks->site->id=1;
verify($shop->getSupplierStoreIds()===[20=>20,21=>21],'Сброс setter');
$models=$shop->getSupplierStores();verify(count($models)===2 && $models[0] instanceof ShopStore,'Getter полных моделей сохранён');
$models[0]->id=88;Yii::getLogger()->messages=[];
verify($shop->getSupplierStoreIds()===[88=>88,21=>21] && sqlCount()===0,'Загруженные модели имеют приоритет');
$custom=new class extends ShopComponent {public function getSupplierStores(){return [(object)['id'=>77]];}};
verify($custom->getSupplierStoreIds()===[77=>77],'Переопределение getter');

class FilterElement extends yii\db\ActiveRecord {
 public static function tableName(){return 'cms_content_element';}
 public function getShopProduct(){return $this->hasOne(FilterProduct::class,['id'=>'id']);}
}
class FilterProduct extends yii\db\ActiveRecord {
 public static function tableName(){return 'filter_product';}
 public function getShopProductOffers(){return $this->hasMany(self::class,['parent_id'=>'id']);}
}
$db->createCommand('CREATE TABLE cms_content_element (id INTEGER PRIMARY KEY)')->execute();
$db->createCommand('CREATE TABLE filter_product (id INTEGER PRIMARY KEY, parent_id INTEGER)')->execute();
$db->createCommand('CREATE TABLE shop_store_product (shop_product_id INTEGER, shop_store_id INTEGER, quantity INTEGER)')->execute();
$db->createCommand()->batchInsert('cms_content_element',['id'],[[1],[2],[3],[4],[5],[6]])->execute();
$db->createCommand()->batchInsert('filter_product',['id','parent_id'],[[1,null],[2,null],[3,null],[4,null],[5,null],[6,null],[104,4]])->execute();
$db->createCommand()->batchInsert('shop_store_product',['shop_product_id','shop_store_id','quantity'],[[1,10,1],[2,20,1],[3,20,0],[104,21,2],[5,30,10],[6,20,-2]])->execute();
foreach([[1,0,[1,2,3,4,5,6]],[1,1,[1]],[1,2,[1,2,4]],[2,2,[5]],[3,2,[]]] as [$siteId,$mode,$expected]){
 $app->skeeks->site->id=$siteId;$app->skeeks->site->shopSite->is_show_product_only_quantity=$mode;
 $shop=new ShopComponent();$query=FilterElement::find()->select('cms_content_element.id')->orderBy('cms_content_element.id');
 $shop->filterByQuantityQuery($query);
 verify(array_map('intval',$query->column())===$expected,"Фильтр site=$siteId mode=$mode");
}
echo "OK: ID без моделей, повтор/пустота/смена сайта/setters/override; фильтры остатков и предложений эквивалентны ожидаемым.\n";
