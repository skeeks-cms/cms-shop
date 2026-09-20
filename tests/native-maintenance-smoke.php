<?php
// Disposable MariaDB only. Never load application configuration.
define('YII_ENABLE_ERROR_HANDLER', false);
$vendor = (getenv('SKEEKS_APP_ROOT') ?: '/app').'/vendor';
$loader = require $vendor.'/autoload.php';
$loader->addPsr4('skeeks\\cms\\shop\\', dirname(__DIR__).'/src', true);
require $vendor.'/yiisoft/yii2/Yii.php';
$app = new yii\console\Application(['id'=>'shop-maintenance-test','basePath'=>__DIR__,'vendorPath'=>$vendor,'extensions'=>[],
    'components'=>['db'=>['class'=>yii\db\Connection::class,'dsn'=>'mysql:host=cms-native-shop-db','username'=>'root'],
        'shop'=>new class extends yii\base\Component { public $contentProducts; }]]);
$app->shop->contentProducts = (object)['id'=>1];
$db = $app->db;
$name = 'native_shop_'.bin2hex(random_bytes(6));
$db->createCommand("CREATE DATABASE $name")->execute();
$db->createCommand("USE $name")->execute();

class MaintenanceFixtureReporter extends \skeeks\cms\job\runtime\JobReporter {
    public $result = []; public $success = 0; public $advanced = 0; public $beats = 0; public $cancel = false;
    public function init() {}
    public function setStage(string $stage, ?string $message = null): void {}
    public function setTotal(?int $total): void {}
    public function advance(int $by = 1): void { $this->advanced += $by; }
    public function countSuccess(int $by = 1): void { $this->success += $by; }
    public function heartbeat(): void { ++$this->beats; }
    public function countSkipped(int $by = 1): void {}
    public function isCancelled(): bool { return $this->cancel; }
    public function setResult(array $result): void { $this->result = $result; }
}

$checks = 0;
$check = static function ($ok, $message) use (&$checks) { if (!$ok) { throw new RuntimeException($message); } ++$checks; };
$service = new skeeks\cms\shop\services\ScheduledMaintenance(['batchSize'=>2]);
try {
foreach (['', 'sx_'] as $prefix) {
    $db->tablePrefix = $prefix;
    $schemas = [
        'shop_order'=>'id INT PRIMARY KEY, is_created INT, created_at INT',
        'shop_user'=>'id INT PRIMARY KEY, shop_order_id INT NULL',
        'shop_product_price_change'=>'id INT PRIMARY KEY, created_at INT',
        'shop_product'=>'id INT PRIMARY KEY, offers_pid INT NULL, product_type VARCHAR(30), rating_count INT DEFAULT 0, rating_value DECIMAL(10,4) DEFAULT 0, measure_ratio DECIMAL(10,2), measure_ratio_min DECIMAL(10,2), measure_matches_jsondata TEXT, measure_code VARCHAR(20), width DECIMAL(10,2), length DECIMAL(10,2), height DECIMAL(10,2), weight DECIMAL(10,2)',
        'cms_content_element'=>'id INT PRIMARY KEY, cms_site_id INT, tree_id INT NULL, main_cce_id INT NULL',
        'shop_site'=>'id INT PRIMARY KEY, is_generate_product_rating INT DEFAULT 0, generate_min_product_rating_count INT DEFAULT 10, generate_max_product_rating_count INT DEFAULT 20, generate_min_product_rating_value INT DEFAULT 4, generate_max_product_rating_value INT DEFAULT 5',
        'shop_type_price'=>'id INT PRIMARY KEY, cms_site_id INT, is_auto INT DEFAULT 0, base_auto_shop_type_price_id INT NULL, auto_extra_charge DECIMAL(10,2), is_purchase INT DEFAULT 0, is_default INT DEFAULT 0',
        'shop_product_price'=>'id INT PRIMARY KEY AUTO_INCREMENT, product_id INT, type_price_id INT, price DECIMAL(14,2) NULL, currency_code VARCHAR(3), is_fixed INT DEFAULT 0, UNIQUE(product_id,type_price_id)',
        'shop_store'=>'id INT PRIMARY KEY, is_active INT DEFAULT 1, cms_site_id INT, is_supplier INT DEFAULT 1, is_sync_external INT DEFAULT 0, source_purchase_price VARCHAR(30), source_selling_price VARCHAR(30), purchase_extra_charge DECIMAL(10,2), selling_extra_charge DECIMAL(10,2), priority INT',
        'shop_store_product'=>'id INT PRIMARY KEY, is_active INT DEFAULT 1, shop_product_id INT, shop_store_id INT, quantity INT, purchase_price DECIMAL(14,2), selling_price DECIMAL(14,2)',
    ];
    foreach ($schemas as $table=>$schema) { $db->createCommand("CREATE TABLE $prefix$table ($schema) ENGINE=InnoDB")->execute(); }
    $insert = static function ($table, $row) use ($db,$prefix) { $db->createCommand()->insert($prefix.$table,$row)->execute(); };
    $q = static function ($table) use ($prefix) { return (new yii\db\Query())->from($prefix.$table); };
    foreach ([1,2,3] as $id) { $insert('shop_order',['id'=>$id,'is_created'=>0,'created_at'=>time()-86400*4]); }
    $insert('shop_order',['id'=>4,'is_created'=>1,'created_at'=>time()-86400*4]);
    $insert('shop_order',['id'=>5,'is_created'=>0,'created_at'=>time()]);
    $insert('shop_user',['id'=>1,'shop_order_id'=>null]);
    $insert('shop_user',['id'=>2,'shop_order_id'=>4]);
    $result=$service->deleteEmptyCarts();
    $check($result['orders_deleted']===3 && $result['carts_deleted']===1,'Delete old unsubmitted orders and unlinked carts');
    $check((int)$q('shop_order')->count()===2 && (int)$q('shop_user')->count()===1,'Preserve submitted/recent orders and linked carts');
    $check($service->deleteEmptyCarts()['processed']===0,'Repeated cart cleanup has no work');
    foreach ([10,11,12] as $id) { $insert('shop_order',['id'=>$id,'is_created'=>0,'created_at'=>time()-86400*4]); }
    try {
        $service->deleteEmptyCarts(3,static function($p){if($p['processed']===2)throw new skeeks\cms\job\exceptions\JobCancelledException('cancel');});
        throw new RuntimeException('Cancellation ignored');
    } catch (skeeks\cms\job\exceptions\JobCancelledException $e) { $check($q('shop_order')->where(['id'=>12])->exists(),'Cancellation stops before next delete batch'); }

    // A row submitted after selection remains protected by the DELETE predicate.
    $db->createCommand()->delete($prefix.'shop_order')->execute();
    foreach([21,22,23] as $id){$insert('shop_order',['id'=>$id,'is_created'=>0,'created_at'=>time()-86400*4]);}
    $service->deleteEmptyCarts(3,static function($p)use($db,$prefix){
        if($p['processed']===2){
            $db->createCommand()->update($prefix.'shop_order',['is_created'=>1],['id'=>23])->execute();
            $db->createCommand('INSERT IGNORE INTO '.$prefix.'shop_order (id,is_created,created_at) VALUES (99,0,:time)', [':time'=>time()-86400*4])->execute();
        }
    });
    $check($q('shop_order')->where(['id'=>23])->exists() && $q('shop_order')->where(['id'=>99])->exists(),
        'Submitted and newly inserted orders survive a running cleanup');
    $db->createCommand()->delete($prefix.'shop_order')->execute();
    $rows=[];for($id=1;$id<=5001;++$id){$rows[]=[$id,0,time()-86400*4];}
    $db->createCommand()->batchInsert($prefix.'shop_order',['id','is_created','created_at'],$rows)->execute();
    $capService=new skeeks\cms\shop\services\ScheduledMaintenance(['batchSize'=>1000]);
    $check($capService->deleteEmptyCarts()['orders_deleted']===5000 && (int)$q('shop_order')->count()===1,
        'Legacy 5000-order run cap is preserved');

    $insert('shop_product_price_change',['id'=>1,'created_at'=>time()-86400*40]);
    $insert('shop_product_price_change',['id'=>2,'created_at'=>time()]);
    $check($service->deletePriceChanges()['deleted']===1 && $q('shop_product_price_change')->where(['id'=>2])->exists(),'Price retention preserves recent history');

    foreach ([[1,null,'offer',10],[2,null,'simple',20],[3,2,'simple',30]] as [$id,$pid,$type,$tree]) {
        $insert('shop_product',['id'=>$id,'offers_pid'=>$pid,'product_type'=>$type]);
        $insert('cms_content_element',['id'=>$id,'cms_site_id'=>1,'tree_id'=>$tree]);
    }
    $result=$service->updateProductType();
    $check($q('shop_product')->select('product_type')->where(['id'=>1])->scalar()==='simple','Unattached product becomes simple');
    $check($q('shop_product')->select('product_type')->where(['id'=>2])->scalar()==='offers','Parent becomes offers');
    $check($q('shop_product')->select('product_type')->where(['id'=>3])->scalar()==='offer','Child becomes offer');
    $check((int)$q('cms_content_element')->select('tree_id')->where(['id'=>3])->scalar()===20,'Offer inherits parent category');
    $check($service->updateProductType()['processed']===0,'Product type update is stable');
    $insert('shop_site',['id'=>1,'is_generate_product_rating'=>1]);
    $result=$service->updateProductRating();
    $check($result['processed']===3,'Rating updates all eligible products');
    $check($service->updateProductRating()['processed']===0,'Existing ratings remain unchanged');
    $rating=$q('shop_product')->where(['id'=>1])->one();
    $check($rating['rating_count']>=10 && $rating['rating_count']<20 && $rating['rating_value']>=4 && $rating['rating_value']<5,'Rating uses configured range');

    $insert('shop_type_price',['id'=>1,'cms_site_id'=>1,'is_default'=>1]);
    $insert('shop_type_price',['id'=>2,'cms_site_id'=>1,'is_purchase'=>1]);
    $insert('shop_type_price',['id'=>3,'cms_site_id'=>1,'is_auto'=>1,'base_auto_shop_type_price_id'=>1,'auto_extra_charge'=>150]);
    $insert('shop_product_price',['product_id'=>1,'type_price_id'=>1,'price'=>100,'currency_code'=>'RUB']);
    $result=$service->updateAutoPrices();
    $price=static function($product,$type)use($q){return $q('shop_product_price')->select('price')->where(['product_id'=>$product,'type_price_id'=>$type])->scalar();};
    $check((float)$price(1,3)===150.0,'Automatic price creation retains formula');
    $db->createCommand()->update($prefix.'shop_product_price',['price'=>200],['product_id'=>1,'type_price_id'=>1])->execute();
    $service->updateAutoPrices();
    $check((float)$price(1,3)===300.0,'Automatic prices update from changed base');

    $insert('shop_store',['id'=>1,'cms_site_id'=>1,'source_purchase_price'=>'purchase_price','source_selling_price'=>'selling_price','purchase_extra_charge'=>100,'selling_extra_charge'=>100,'priority'=>10]);
    $insert('shop_store_product',['id'=>1,'shop_product_id'=>1,'shop_store_id'=>1,'quantity'=>10,'purchase_price'=>80,'selling_price'=>120]);
    $autocommit=$db->createCommand('SELECT @@autocommit')->queryScalar();
    $result=$service->updateStorePrices();
    $check((float)$price(1,2)===80.0 && (float)$price(1,1)===120.0,'Store prices create purchase and update retail prices');
    $check($result['sites']===1 && $db->createCommand('SELECT @@autocommit')->queryScalar()===$autocommit && !$db->getTransaction(),'Store update closes transaction and preserves connection autocommit');
    $db->createCommand()->update($prefix.'shop_product_price',['is_fixed'=>1,'price'=>999],['product_id'=>1,'type_price_id'=>1])->execute();
    $service->updateStorePrices();
    $check((float)$price(1,1)===999.0,'Fixed store price remains unchanged');
    $db->createCommand()->delete($prefix.'shop_product_price',['type_price_id'=>2])->execute();
    try {
        $service->updateStorePrices(null,static function($p)use($db,$check){$check(!$db->getTransaction(),"Heartbeat is outside price transaction");if($p['processed']>0)throw new skeeks\cms\job\exceptions\JobCancelledException('cancel');});
        throw new RuntimeException('Cancellation ignored');
    } catch (skeeks\cms\job\exceptions\JobCancelledException $e) {
        $check($q('shop_product_price')->where(['type_price_id'=>2])->exists() && !$db->getTransaction(),'Cancellation stops later price stages without an open transaction');
    }
    foreach([81,82,83,84] as $id){
        $insert('shop_product',['id'=>$id,'width'=>($id===81?12:99),'measure_code'=>'piece']);
        $insert('cms_content_element',['id'=>$id,'main_cce_id'=>($id===81?null:81)]);
    }
    try{
        $service->updateSubproducts(static function($p){if($p['processed']>=1)throw new skeeks\cms\job\exceptions\JobCancelledException('cancel');});
        throw new RuntimeException('No cancellation');
    }catch(skeeks\cms\job\exceptions\JobCancelledException $e){
        $check((int)$q('shop_product')->where(['width'=>99])->count()>0,'Cancellation leaves later batches untouched');
    }
    $service->updateSubproducts();
    $check((int)$q('shop_product')->where(['id'=>[82,83,84],'width'=>12])->count()===3,'Subproducts inherit dimensions in batches');
    $check($service->quantityEmails()['enabled']===false,'Disabled notifier stays disabled without mail component');
    $beats=0;
    $service->deleteEmptyCarts(3,static function($p)use(&$beats){++$beats;});
    $check($beats>0,'Cleanup emits progress checkpoints');
}

    $configParams = [];
    $params = [];
    $config = require dirname(__DIR__).'/src/config/common.php';
    foreach ($config['components']['jobRegistry']['types'] as $definition) {
        if ($definition['queue'] !== 'maintenance') continue;
        $handler = new $definition['handler']();
        $reporter = new MaintenanceFixtureReporter();
        $handler->run(new \skeeks\cms\job\runtime\JobContext(), $reporter);
        $check($reporter->beats > 0 && isset($reporter->result['processed']) &&
            $reporter->success === $reporter->advanced &&
            $reporter->success === $reporter->result['processed'], 'Native handler reports actual work: '.$definition['type']);
        $reporter = new MaintenanceFixtureReporter(); $reporter->cancel = true;
        try {
            $handler->run(new \skeeks\cms\job\runtime\JobContext(), $reporter);
            throw new RuntimeException('Native cancellation ignored');
        } catch (\skeeks\cms\job\exceptions\JobCancelledException $e) { ++$checks; }
    }
    $types = $config['components']['jobRegistry']['types'];
    $catalog = array_filter($types, static function($t){return strpos($t['type'],'shop.update-')===0;});
    $resources=[];$dedup=[];
    foreach($catalog as $type){$resources[]=($type['resourceKey'])();$dedup[]=($type['dedupKey'])();}
    $check(count(array_unique($resources))===1 && count(array_unique($dedup))===5,
        'Catalog types share a resource lock without suppressing distinct operations');

echo "PASS: $checks native shop checks\n";
} finally { $db->createCommand("DROP DATABASE $name")->execute(); }
