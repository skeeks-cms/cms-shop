<?php
// php tests/shop-site-cache.php <vendor/autoload.php> <cms/tests/site-settings-cache.php>
require $argv[2];
require dirname(__DIR__).'/src/models/CmsSite.php';
use skeeks\cms\shop\models\ShopSite;
use skeeks\cms\shop\models\CmsSite as ShopCmsSite;
$db->createCommand('CREATE TABLE shop_site (id INTEGER PRIMARY KEY, is_show_prices INTEGER, order_free_shipping_from_price REAL, order_min_price REAL, show_filter_property_ids TEXT, open_filter_property_ids TEXT, order_required_fields TEXT, required_product_fields TEXT, required_brand_fields TEXT, required_collection_fields TEXT)')->execute();
$db->createCommand()->batchInsert('shop_site',['id','is_show_prices'],[[1,1],[2,0]])->execute();
ShopSite::getTableSchema();
function shopSettingsRead($id,$sql){
 $site=new ShopCmsSite();$site->id=$id;
 return measuredSettings(fn()=>$site->shopSite,$sql);
}
checkSettings(shopSettingsRead(1,1)->is_show_prices===1,'Настройки');
checkSettings(shopSettingsRead(1,0)->is_show_prices===1,'Кеш');
checkSettings(shopSettingsRead(2,1)->is_show_prices===0,'Изоляция');
$site=new ShopCmsSite();$site->id=1;
yii\caching\TagDependency::invalidate($app->cache,[$site->getCacheTag()]);
shopSettingsRead(1,1);shopSettingsRead(2,0);
$settings=ShopSite::findOne(1);$settings->is_show_prices=0;$settings->save(false);
checkSettings(shopSettingsRead(1,1)->is_show_prices===0,'Автоматический сброс');
$settings->delete();
checkSettings(shopSettingsRead(1,1)===null && shopSettingsRead(1,0)===null,'Удаление и пустой результат');
$settings=new ShopSite();$settings->id=1;$settings->is_show_prices=1;$settings->save(false);
checkSettings(shopSettingsRead(1,1)->is_show_prices===1,'Создание');
$sites=ShopCmsSite::find()->with('shopSite')->indexBy('id')->all();
checkSettings($sites[1]->shopSite->is_show_prices===1 && $sites[2]->shopSite->is_show_prices===0,'Eager loading');
$db->queryCache='otherCache';shopSettingsRead(1,1);shopSettingsRead(1,1);
echo "OK: shop_site, ручной и автоматический сброс, пустые значения, изоляция сайтов и eager loading.\n";
