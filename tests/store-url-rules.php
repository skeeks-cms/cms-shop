<?php
/**
 * Проверка правил URL на изолированной SQLite, без запуска сайта.
 * php tests/store-url-rules.php <vendor/autoload.php>
 */
namespace {
    require $argv[1];
    require dirname($argv[1]).'/yiisoft/yii2/Yii.php';
}
namespace skeeks\cms\shop\models {
    class StoreTestQuery extends \yii\db\ActiveQuery {
        public function cmsSite() { return $this->andWhere(['cms_site_id' => \Yii::$app->params['siteId']]); }
    }
    class ShopStore extends \yii\db\ActiveRecord {
        public static function tableName() { return 'shop_store'; }
        public static function find() { return new StoreTestQuery(static::class); }
    }
}
namespace {
    require dirname(__DIR__).'/src/cashier/CashierUrlRule.php';
    require dirname(__DIR__).'/src/store/StoreUrlRule.php';
    class TestBackend extends \yii\base\Component implements \skeeks\cms\backend\IBackendComponent {
        public $defaultRoute = '/test/index';
        public $seen = [];
        public function getMenu() { return null; }
        public static function getCurrent() { return null; }
        public function run() { $this->seen[] = \Yii::$app->shop->backendShopStore; return $this; }
    }
    function check($value, $message) { if (!$value) throw new \RuntimeException($message); }
    $app = new \yii\console\Application([
        'id'=>'store-rule-test','basePath'=>__DIR__,
        'components'=>['db'=>['class'=>\yii\db\Connection::class,'dsn'=>'sqlite::memory:']],
        'params'=>['siteId'=>1],
    ]);
    $shop = new class extends \yii\base\Component { public $backendShopStore; };
    $app->set('shop',$shop);
    $app->db->createCommand('CREATE TABLE shop_store (id INTEGER PRIMARY KEY, cms_site_id INTEGER)')->execute();
    $app->db->createCommand()->batchInsert('shop_store',['id','cms_site_id'],[[1,1],[2,1],[3,2]])->execute();
    // Прогреваем только схему: ниже считаем реальные SELECT.
    \skeeks\cms\shop\models\ShopStore::getTableSchema();
    $manager = new \yii\web\UrlManager();
    foreach ([\skeeks\cms\shop\cashier\CashierUrlRule::class, \skeeks\cms\shop\store\StoreUrlRule::class] as $class) {
        $backend = new TestBackend();
        $rule = new $class(['urlPrefix'=>'~test','controllerPrefix'=>'test','backend'=>$backend]);
        foreach ([
            ['',[],1,0,false,null],
            ['plitka-product',['__shop_store_id'=>2],1,0,false,null],
            ['~test',[],1,1,'/test/index',1],
            ['~test/test/view',['__shop_store_id'=>2],1,1,'/test/view',2],
            ['~test',[],2,1,'/test/index',3],
            ['~test',[],99,1,'/test/index',null],
        ] as [$path,$params,$site,$expectedSql,$route,$storeId]) {
            $app->params['siteId']=$site;
            $shop->backendShopStore=null;
            $backend->seen=[];
            $request=new \yii\web\Request(['cookieValidationKey'=>'test']);
            $request->setPathInfo($path);
            $request->setQueryParams($params);
            \Yii::getLogger()->messages=[];
            $result=$rule->parseRequest($manager,$request);
            $sql=0;
            foreach (\Yii::getLogger()->messages as $message) {
                if ($message[1]===\yii\log\Logger::LEVEL_PROFILE_BEGIN && $message[2]==='yii\db\Command::query') ++$sql;
            }
            check($sql===$expectedSql, "$class $path: SQL $sql, expected $expectedSql");
            if ($route===false) {
                check($result===false && !$backend->seen && $shop->backendShopStore===null,'Чужой маршрут имеет побочные эффекты');
            } else {
                check($result===[$route,$params], 'Маршрут или параметры изменились');
                check(count($backend->seen)===1,'Backend не запущен ровно один раз');
                check(($backend->seen[0]->id??null)===$storeId,'Склад не выбран до запуска backend');
            }
        }
    }
    echo "OK: 2 правила, витрина без SQL, выбор склада/сайта и запуск backend сохранены.\n";
}
