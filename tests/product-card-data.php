<?php
// Isolated SQLite integration test: real Yii relations/SQL; no site database.
namespace {
    require $argv[1] ?? '/app/vendor/autoload.php';
    require_once dirname($argv[1] ?? '/app/vendor/autoload.php').'/yiisoft/yii2/Yii.php';
    new \yii\console\Application(['id'=>'card-data-test','basePath'=>__DIR__, 'components'=>[
        'db'=>['class'=>\yii\db\Connection::class,'dsn'=>'sqlite::memory:'],
    ]]);
}
namespace skeeks\cms\shop\models {
    class ShopStoreProduct extends \yii\db\ActiveRecord {
        public static function tableName(){return 'shop_store_product';}
    }
}
namespace {
    class Favorite extends \yii\db\ActiveRecord {public static function tableName(){return 'favorite';}}
    class Comparison extends \yii\db\ActiveRecord {public static function tableName(){return 'comparison';}}
    class CardUser extends \yii\db\ActiveRecord {
        public static function tableName(){return 'card_user';}
        public function getShopFavoriteProducts(){return $this->hasMany(Favorite::class,['shop_user_id'=>'id']);}
        public function getCmsCompareElements(){return $this->hasMany(Comparison::class,['shop_user_id'=>'id']);}
    }
    require __DIR__.'/../src/helpers/ProductCardData.php';
    use skeeks\cms\shop\helpers\ProductCardData;
    use skeeks\cms\shop\models\ShopStoreProduct;
    $db=Yii::$app->db;
    foreach([
        'CREATE TABLE card_user (id INTEGER PRIMARY KEY)',
        'CREATE TABLE favorite (id INTEGER PRIMARY KEY, shop_user_id INTEGER, shop_product_id INTEGER)',
        'CREATE TABLE comparison (id INTEGER PRIMARY KEY, shop_user_id INTEGER, cms_content_element_id INTEGER)',
        'CREATE TABLE shop_store_product (id INTEGER PRIMARY KEY, shop_product_id INTEGER, shop_store_id INTEGER, quantity NUMERIC)',
        'INSERT INTO card_user VALUES (1),(2)',
        'INSERT INTO favorite VALUES (1,1,1),(2,2,2),(3,1,99)',
        'INSERT INTO comparison VALUES (1,1,2),(2,2,1),(3,1,99)',
        'INSERT INTO shop_store_product VALUES (1,1,10,1.5),(2,1,11,-0.5),(3,1,20,100),(4,2,10,0),(5,99,10,8)'
    ] as $sql) $db->createCommand($sql)->execute();
    $user1=CardUser::findOne(1);$user2=CardUser::findOne(2);
    foreach(['shop_store_product','favorite','comparison'] as $table)$db->schema->getTableSchema($table);
    $models=[];for($id=1;$id<=26;$id++)$models[]=(object)['shopProduct'=>(object)['id'=>$id]];
    $models[]=(object)['shopProduct'=>null];$models[]=$models[0];
    $shop=(object)['cart'=>$user1,'shopUser'=>$user1,'allStores'=>[(object)['id'=>10],(object)['id'=>11]]];
    $countQueries=static function(){return count(array_filter(Yii::getLogger()->messages,static function($m){return $m[1]===\yii\log\Logger::LEVEL_PROFILE_BEGIN && $m[2]==='yii\db\Command::query';}));};
    $before=$countQueries();
    $data=ProductCardData::load($models,$shop);
    $queries=$countQueries()-$before;
    $check=static function($ok,$message){if(!$ok)throw new \RuntimeException($message);};
    $check($queries===3,'26 cards must use exactly 3 batch queries, got '.$queries);
    foreach(range(1,26) as $id){
        $rows=ShopStoreProduct::find()->where(['shop_product_id'=>$id,'shop_store_id'=>[10,11]])->all();
        $check(array_map(static function($r){return $r->attributes;},$rows)===array_map(static function($r){return $r->attributes;},$data->getStoreProducts($id)),'Stock mismatch '.$id);
        $check($data->isFavorite($id)===$user1->getShopFavoriteProducts()->andWhere(['shop_product_id'=>$id])->exists(),'Favorite mismatch '.$id);
        $check($data->isCompared($id)===$user1->getCmsCompareElements()->andWhere(['cms_content_element_id'=>$id])->exists(),'Comparison mismatch '.$id);
    }
    $check(!$data->hasProduct(99) && !$data->isFavorite(99) && !$data->isCompared(99),'Off-page products excluded');
    $check(count($data->getStoreProducts(1))===2 && $data->getStoreProducts(3)===[],'Negative/zero/absent stock preserved');
    $before=$countQueries();ProductCardData::load([],$shop);$check($countQueries()===$before,'Empty list must not query');
    $shop->cart=$user2;$shop->shopUser=$user2;
    $second=ProductCardData::load($models,$shop);
    $check(!$second->isFavorite(1) && $second->isFavorite(2) && $second->isCompared(1) && !$second->isCompared(2),'User isolation');
    $shop->cart=new CardUser();$shop->shopUser=$shop->cart;
    $guest=ProductCardData::load($models,$shop);
    $check(!$guest->isFavorite(1) && !$guest->isCompared(2),'Unsaved guest must not inherit flags');
    $shop->allStores=[];
    $unfiltered=ProductCardData::load($models,$shop);
    $check(count($unfiltered->getStoreProducts(1))===3,'Empty store-list compatibility');
    $shop->allStores=[(object)['id'=>20]];
    $otherSite=ProductCardData::load($models,$shop);
    $check(count($otherSite->getStoreProducts(1))===1 && $otherSite->getStoreProducts(2)===[],'Store scope isolation');
    $db->createCommand()->insert('favorite',['shop_user_id'=>2,'shop_product_id'=>1])->execute();
    $db->createCommand()->update('shop_store_product',['quantity'=>7],['id'=>3])->execute();
    $shop->cart=$user2;$shop->shopUser=$user2;
    $fresh=ProductCardData::load($models,$shop);
    $check($fresh->isFavorite(1) && (float)$fresh->getStoreProducts(1)[0]->quantity===7.0,'Next render sees mutations');
    echo "OK: 26 cards match 78 individual queries using 3 batch queries; user/store/guest/empty/freshness checks passed\n";
}
