<?php
// Disposable MariaDB integration test; never loads a production site configuration.
// php tests/reference-activity-log.php /app/vendor/autoload.php
// Optional TEST_DB_DSN / TEST_DB_USER / TEST_DB_PASSWORD select a test server.
define('YII_ENABLE_ERROR_HANDLER', false);
require $argv[1];
require dirname($argv[1]).'/yiisoft/yii2/Yii.php';

use skeeks\cms\models\CmsLog;
use skeeks\cms\shop\models\ShopBrand;
use skeeks\cms\shop\models\ShopCollection;
use skeeks\cms\shop\gpd\ShopReferenceWriter;
use skeeks\cms\shop\gpd\ShopCatalogWriter;
use skeeks\cms\shop\components\GpdComponent;

$app = new yii\console\Application([
    'id' => 'reference-activity-test', 'basePath' => __DIR__,
    'vendorPath' => dirname($argv[1]), 'extensions' => [],
    'components' => [
        'db' => ['class' => yii\db\Connection::class,
            'dsn' => getenv('TEST_DB_DSN') ?: 'mysql:host=db',
            'username' => getenv('TEST_DB_USER') ?: 'root',
            'password' => getenv('TEST_DB_PASSWORD') ?: 'root'],
        'cache' => ['class' => yii\caching\ArrayCache::class],
        'i18n' => ['translations' => ['skeeks/*' => [
            'class' => yii\i18n\PhpMessageSource::class, 'basePath' => __DIR__,
        ]]],
    ],
]);
$app->set('cms', new class extends yii\base\Component {public $element_max_code_length = 255;});
// Exercise actual storage-row cleanup, but never touch a filesystem or network.
$app->set('storage', new class extends yii\base\Component {public function getCluster($id) {return null;}});
$app->set('skeeks', new class extends yii\base\Component {public $modelsConfig = []; public $site;});
(new skeeks\cms\shop\components\ShopComponent())->bootstrap($app);
$db = $app->db;
$database = 'reference_log_test_'.bin2hex(random_bytes(6));
$db->createCommand("CREATE DATABASE `$database`")->execute();
$db->createCommand("USE `$database`")->execute();
$checks = 0;
function verify($ok, string $message): void {
    global $checks;
    if (!$ok) throw new RuntimeException($message);
    ++$checks;
}
function saveModel($model): void {
    verify($model->save(), get_class($model).' save: '.json_encode($model->errors));
}
function lastLog($model): CmsLog {
    return $model->getLogs()->orderBy(['id' => SORT_DESC])->one();
}
function logCount(): int {return (int)CmsLog::find()->count();}

try {
    $common = 'id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(255), code VARCHAR(255),
        created_by INT NULL, updated_by INT NULL, created_at INT NULL, updated_at INT NULL,
        is_active INT DEFAULT 1, priority INT DEFAULT 500, sx_id INT NULL,
        is_sx_info_update INT DEFAULT 1, external_id VARCHAR(255),
        description_short TEXT, description_full TEXT, seo_h1 TEXT, meta_title TEXT,
        meta_description TEXT, meta_keywords TEXT';
    $tables = [
        'shop_brand' => $common.', country_alpha2 VARCHAR(2), logo_image_id INT NULL, website_url TEXT',
        'shop_collection' => $common.', shop_brand_id INT, cms_image_id INT NULL, show_counter INT DEFAULT 0',
        'cms_country' => 'id INT PRIMARY KEY, alpha2 VARCHAR(2), name VARCHAR(255)',
        'cms_storage_file' => 'id INT PRIMARY KEY, name VARCHAR(255), cluster_id VARCHAR(64), priority INT',
        'shop_collection2image' => 'shop_collection_id INT, storage_file_id INT',
        'shop_collection_sticker' => 'id INT PRIMARY KEY, name VARCHAR(255)',
        'shop_collection2sticker' => 'shop_collection_id INT, shop_collection_sticker_id INT',
        'cms_log' => 'id INT AUTO_INCREMENT PRIMARY KEY, created_by INT NULL, updated_by INT NULL,
            created_at INT, updated_at INT, cms_company_id INT NULL, cms_user_id INT NULL,
            model_code VARCHAR(255), model_id INT, model_as_text TEXT, sub_model_code VARCHAR(255),
            sub_model_id INT NULL, sub_model_log_type VARCHAR(32), sub_model_as_text TEXT,
            data LONGTEXT, log_type VARCHAR(32), is_pinned INT, comment TEXT',
        'cms_log_file' => 'id INT PRIMARY KEY, cms_log_id INT, storage_file_id INT',
        'shop_gpd_reference_state' => 'connection_id VARCHAR(64), product_id INT, kind VARCHAR(32),
            source_id INT, operation VARCHAR(16), needs_resolution INT, revision INT, applied_revision INT',
        'cms_content_element' => 'id INT PRIMARY KEY, cms_site_id INT, sx_id INT',
        'shop_product' => 'id INT PRIMARY KEY, brand_id INT',
        'shop_product2collection' => 'shop_product_id INT, shop_collection_id INT',
        'cms_saved_filter' => 'id INT PRIMARY KEY, shop_brand_id INT, cms_site_id INT',
    ];
    foreach ($tables as $table => $columns) {
        $db->createCommand("CREATE TABLE `$table` ($columns) ENGINE=InnoDB")->execute();
    }
    $db->createCommand()->batchInsert('cms_country', ['id','alpha2','name'], [[1,'IT','Italy'],[2,'ES','Spain']])->execute();
    $db->createCommand()->batchInsert('cms_storage_file', ['id','name'], [[1,'Old logo'],[2,'New logo'],[3,'Old image'],[4,'New image']])->execute();
    $db->createCommand()->insert('shop_collection_sticker', ['id'=>1,'name'=>'New collection'])->execute();

    $brand = new ShopBrand(['name'=>'Brand A','country_alpha2'=>'IT','logo_image_id'=>1]);
    // File validation is unrelated to log integration; all save events still run.
    verify($brand->save(false), 'insert brand');
    verify(lastLog($brand)->log_type === CmsLog::LOG_TYPE_INSERT, 'brand insert log');
    verify(lastLog($brand)->model instanceof ShopBrand, 'activity resolves brand model');
    verify(strpos(lastLog($brand)->render(), 'Бренд «Brand A»') !== false, 'readable brand activity title');
    verify(lastLog($brand)->data['country_alpha2']['as_text'] === 'Italy', 'country readable on insert');
    verify(lastLog($brand)->data['logo_image_id']['as_text'] === 'Old logo', 'logo readable on insert');
    $other = new ShopBrand(['name'=>'Brand B']); saveModel($other);
    $collection = new ShopCollection(['name'=>'Collection A','shop_brand_id'=>$brand->id,'cms_image_id'=>3]);
    verify($collection->save(false), 'insert collection');
    verify(lastLog($collection)->model instanceof ShopCollection, 'activity resolves collection model');
    verify(strpos(lastLog($collection)->render(), 'Коллекция «Collection A»') !== false, 'readable collection activity title');
    verify(lastLog($collection)->data['shop_brand_id']['as_text'] === 'Brand A', 'brand readable on insert');
    verify(lastLog($collection)->data['cms_image_id']['as_text'] === 'Old image', 'image readable on insert');
    verify(!isset(lastLog($collection)->data['show_counter']), 'counter absent from insert snapshot');

    foreach ([[$brand,'logo_image_id',2,'Old logo','New logo'],[$collection,'cms_image_id',4,'Old image','New image']] as [$model,$field,$id,$old,$new]) {
        $oldId = $model->$field;
        $model->$field = $id;
        verify($model->save(false), 'replace image');
        $data = lastLog($model)->data[$field];
        verify($data['old_as_text'] === $old && $data['as_text'] === $new, 'old/new image names before cleanup');
        verify(!(new yii\db\Query())->from('cms_storage_file')->where(['id'=>$oldId])->exists(), 'old storage row actually removed');
    }
    $brand->country_alpha2 = 'ES'; saveModel($brand);
    verify(lastLog($brand)->data['country_alpha2']['old_as_text'] === 'Italy' && lastLog($brand)->data['country_alpha2']['as_text'] === 'Spain', 'old/new countries');
    $collection->brand; // Cached relation must not replace either historical name.
    $collection->shop_brand_id = $other->id; saveModel($collection);
    verify(lastLog($collection)->data['shop_brand_id']['old_as_text'] === 'Brand A' && lastLog($collection)->data['shop_brand_id']['as_text'] === 'Brand B', 'old/new brand despite cached relation');
    $collection->shopCollectionStickers = [1]; saveModel($collection);
    verify(lastLog($collection)->data['shopCollectionStickers']['as_text'] === 'New collection', 'sticker relationship saved before log');

    // Fresh instances avoid retaining an earlier explicit relation assignment.
    foreach ([ShopBrand::findOne($brand->id), ShopCollection::findOne($collection->id)] as $model) {
        $count = logCount(); saveModel($model);
        verify(logCount() === $count, 'unchanged save does not log');
        if ($model->hasAttribute('show_counter')) {
            ++$model->show_counter; saveModel($model);
            verify(logCount() === $count, 'counter-only save does not log');
        }
        $model->name .= ' renamed'; saveModel($model);
        verify(lastLog($model)->data['name']['old_value'] !== $model->name, 'rename old value');
        $model->is_active = 0; saveModel($model);
        verify((int)lastLog($model)->data['is_active']['old_value'] === 1 && (int)lastLog($model)->data['is_active']['value'] === 0, 'deactivation logged');
        foreach (['created_by','updated_by','created_at','updated_at','id'] as $field) {
            verify(!isset(lastLog($model)->data[$field]), 'technical field excluded: '.$field);
        }
        $count = logCount(); $tx = $db->beginTransaction();
        $model->name = 'Rolled back'; saveModel($model);
        verify(logCount() === $count + 1, 'log visible inside transaction');
        $nested = $db->beginTransaction();
        verify($model->delete() === 1, 'delete inside savepoint');
        verify(lastLog($model)->log_type === CmsLog::LOG_TYPE_DELETE, 'delete event');
        $nested->rollBack();
        verify($model::findOne($model->id) !== null && logCount() === $count + 1, 'savepoint restores model and removes delete log');
        $tx->rollBack();
        verify(logCount() === $count && $model::findOne($model->id)->name !== 'Rolled back', 'outer rollback removes update log');
        $tx = $db->beginTransaction();
        $newModel = new ($model::class)(['name'=>'Rolled back insert']);
        if ($newModel instanceof ShopCollection) $newModel->shop_brand_id = $other->id;
        saveModel($newModel); $newId = $newModel->id; $tx->rollBack();
        verify($model::findOne($newId) === null && logCount() === $count, 'insert rollback removes model and log');
    }

    // The real GPD writer calls these same models; no separate log calls.
    $settings = new GpdComponent(['excludedBrandAction'=>'deactivate','excludedCollectionAction'=>'deactivate']);
    $writer = new ShopReferenceWriter(1, 'test', new ShopCatalogWriter(1, $settings, new stdClass(), static function() {}), $settings);
    foreach (['brands'=>ShopBrand::class,'collections'=>ShopCollection::class] as $kind=>$class) {
        $source = $kind === 'brands' ? 101 : 102;
        $payload = ['id'=>$source,'name'=>'GPD '.$kind];
        if ($kind === 'collections') $payload['brand_id'] = 101;
        $db->createCommand()->insert('shop_gpd_reference_state', ['connection_id'=>'test','product_id'=>$source,'kind'=>$kind,'source_id'=>$source,'operation'=>'upsert','needs_resolution'=>0,'revision'=>1,'applied_revision'=>1])->execute();
        $item = ['id'=>$source,'operation'=>'upsert','data'=>['kind'=>$kind,'source_id'=>$source,'payload'=>$payload]];
        $tx = $db->beginTransaction();
        verify($writer->apply($item, [])['outcome'] === 'created', 'GPD create');
        $model = $class::find()->where(['sx_id'=>$source])->one();
        verify(lastLog($model)->log_type === 'insert', 'GPD create logged');
        $item['data']['payload']['name'] .= ' updated';
        $writer->apply($item, []);
        verify(lastLog($model)->data['name']['as_text'] === $item['data']['payload']['name'], 'GPD update logged');
        $revoke = ['id'=>$source,'operation'=>'revoke','kind'=>$kind,'source_id'=>$source];
        verify($writer->apply($revoke, [])['outcome'] === 'deactivated', 'GPD deactivate');
        verify((int)lastLog($model)->data['is_active']['value'] === 0, 'GPD deactivation logged');
        $tx->commit();
    }
    foreach (['collections'=>ShopCollection::class,'brands'=>ShopBrand::class] as $kind=>$class) {
        $source = $kind === 'brands' ? 101 : 102;
        $settings->excludedBrandAction = $settings->excludedCollectionAction = 'delete';
        $model = $class::find()->where(['sx_id'=>$source])->one();
        $count = logCount(); $tx = $db->beginTransaction();
        verify($writer->apply(['id'=>$source,'operation'=>'revoke','kind'=>$kind,'source_id'=>$source], [])['outcome'] === 'deleted', 'GPD delete');
        verify(lastLog($model)->log_type === 'delete', 'GPD delete logged');
        $tx->rollBack();
        verify($class::findOne($model->id) !== null && logCount() === $count, 'GPD rollback restores record and removes log');
        $tx = $db->beginTransaction();
        $writer->apply(['id'=>$source,'operation'=>'revoke','kind'=>$kind,'source_id'=>$source], []);
        $tx->commit();
        verify($class::findOne($model->id) === null && lastLog($model)->model_as_text !== '', 'committed deletion retains readable event');
    }
    echo "PASS: $checks reference activity checks (real models, GPD, relations, cleanup, rollback).\n";
} finally {
    if ($db->getTransaction()) $db->getTransaction()->rollBack();
    $db->createCommand("DROP DATABASE `$database`")->execute();
}
