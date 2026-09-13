<?php
/** Isolated SQLite regression test. Run with TEST_VENDOR pointing at Composer vendor. */
define('YII_ENABLE_ERROR_HANDLER', false);
$vendor = getenv('TEST_VENDOR') ?: dirname(__DIR__, 3);
$loader = require $vendor.'/autoload.php';
require $vendor.'/yiisoft/yii2/Yii.php';
$loader->addPsr4('skeeks\\cms\\shop\\', dirname(__DIR__).'/src', true);

class AgentFixture extends yii\db\ActiveRecord {
    public static function tableName() { return '{{%cms_agent}}'; }
    public function getIsJobBased() { return (bool)$this->job_type; }
    public function getEffectiveJobType() { return $this->job_type; }
    public function getJobDedupKey() { return $this->dedup_key; }
    public function getDisplayName() { return $this->name; }
}
class RunFixture extends yii\db\ActiveRecord {
    public static function tableName() { return '{{%cms_job_run}}'; }
}
class_alias(AgentFixture::class, 'skeeks\cms\agent\models\CmsAgentModel');
class_alias(RunFixture::class, 'skeeks\cms\job\models\CmsJobRun');
class TestStore extends skeeks\cms\shop\models\ShopStore {
    public function init() {}
    public function behaviors() { return []; }
}
class RegistryFixture extends yii\base\Component {
    public function getRegistry() { return $this; }
    public function has($type) { return $type !== 'missing'; }
    public function get($type) { return (object)['permission'=>$type === 'restricted' ? 'restricted' : null]; }
}
class UserFixture extends yii\base\Component {
    public $allowed = false;
    public function can($permission) { return $this->allowed; }
}
new yii\console\Application(['id'=>'supplier-status-test','basePath'=>__DIR__,'vendorPath'=>$vendor,'extensions'=>[],
    'components'=>[
        'db'=>['class'=>yii\db\Connection::class,'dsn'=>'sqlite::memory:','tablePrefix'=>'t_'],
        'jobs'=>RegistryFixture::class, 'user'=>UserFixture::class,
    ]]);
$db = Yii::$app->db;
$db->createCommand()->createTable('{{%shop_store}}',[
    'id'=>'pk','cms_site_id'=>'integer','is_supplier'=>'integer',
])->execute();
use skeeks\cms\shop\helpers\SupplierImportStatus as Status;
$checks = 0;
function check($value, $message) { global $checks; if (!$value) { throw new RuntimeException($message); } ++$checks; }
check(!Status::available(), 'No schema: integration is unavailable');
require dirname(__DIR__).'/src/migrations/m260913_120000_add_supplier_import_agent.php';
class TestMigration extends m260913_120000_add_supplier_import_agent {
    // Yii's SQLite builder predates SQLite DROP COLUMN support.
    public function dropColumn($table, $column) {
        $this->db->createCommand('ALTER TABLE '.$this->db->quoteSql($table).' DROP COLUMN '.$this->db->quoteColumnName($column))->execute();
    }
}
$migration = new TestMigration(['db'=>$db,'compact'=>true]);
$migration->up();
$db->createCommand()->createTable('{{%cms_agent}}',[
    'id'=>'pk','cms_site_id'=>'integer','is_active'=>'integer','job_type'=>'text','dedup_key'=>'text','name'=>'text',
])->execute();
$db->schema->refresh();
check(Status::available(), 'Migration and optional agent schema available');
$db->createCommand()->createTable('{{%cms_job_run}}',[
    'id'=>'pk','cms_site_id'=>'integer','dedup_key'=>'text','job_type'=>'text','status'=>'text',
])->execute();
$db->createCommand()->batchInsert('{{%cms_agent}}',['id','cms_site_id','is_active','job_type','dedup_key','name'],[
    [1,1,1,'xml','a','First'],[2,1,0,'xml','b','Disabled'],[3,2,1,'xml','c','Other site'],
    [4,1,1,'xml','d','New'],[5,1,1,'xml','e','Success'],[6,1,1,'xml','f','Warning'],
    [7,1,1,'xml','g','Cancelled'],[8,1,1,'xml','h','Timeout'],[9,1,1,null,null,'Console'],
    [10,1,1,'missing','j','Missing registration'],[11,1,1,'restricted','k','Restricted'],
])->execute();
$stores = [[1,1,1,1],[2,1,1,null],[3,1,1,2],[4,1,1,99],[5,1,1,3],
    [6,1,1,4],[7,1,1,5],[8,1,1,6],[9,1,1,7],[10,1,1,8],[11,1,1,9],
    [12,1,1,10],[13,1,1,11],[14,2,1,3],[15,1,0,1]];
$db->createCommand()->batchInsert('{{%shop_store}}',['id','cms_site_id','is_supplier','import_agent_id'],$stores)->execute();
$db->createCommand()->batchInsert('{{%cms_job_run}}',['id','cms_site_id','dedup_key','job_type','status'],[
    [1,1,'a','xml','succeeded'],[2,1,'a','xml','failed'],[3,1,'a','xml','queued'],
    [4,1,'a','xml','running'],[5,2,'a','xml','succeeded'],[6,1,'a','other','succeeded'],
    [7,1,'e','xml','failed'],[8,1,'e','xml','succeeded'],[9,1,'f','xml','succeeded_with_warnings'],
    [10,1,'g','xml','cancelled'],[11,1,'h','xml','timed_out'],[12,1,'k','restricted','succeeded'],
])->execute();
$status = new Status(1);
check($status->get(1)['state'] === 'error', 'Queued/running, other site/type do not hide last failure');
foreach ([2,3,4,5] as $id) { check($status->get($id)['state'] === 'none', 'Unlinked/disabled/deleted/cross-site schedule'); }
foreach ([6,9,11,12,13] as $id) { check($status->get($id)['state'] === 'unknown', 'No completed outcome/permission/registry'); }
check($status->get(7)['state'] === 'success', 'Later success clears failure');
check($status->get(8)['state'] === 'error', 'Partial result with warnings needs attention');
check($status->get(10)['state'] === 'error', 'Timed out is error');
check($status->get(3)['agent_id'] === 2, 'Disabled schedule remains linked');
check($status->get(5)['agent_id'] === null, 'No cross-site schedule disclosure');
$filter = new yii\db\Query();
$filter->select('id')->from('{{%shop_store}}')->where(['cms_site_id'=>1]);
$status->apply($filter, ['error','success']);
$ids = array_map('intval', $filter->orderBy('id')->column());
check($ids === [1,7,8,10], 'Status filter respects table prefix, status combinations and supplier scope');
$empty = new yii\db\Query();
$empty->select('id')->from('{{%shop_store}}');
$status->apply($empty, 'invalid');
check($empty->count() == 0, 'Empty matches never remove filter');
$all = new yii\db\Query();
$all->from('{{%shop_store}}');
$status->apply($all, '');
check($all->count() == count($stores), 'Empty selection preserves query');
$options = $status->options();
check(!isset($options[3]) && strpos($options[2], 'отключено') !== false, 'Selector scoped and disabled marked');
Yii::$app->user->allowed = true;
check((new Status(1))->get(13)['state'] === 'success', 'Authorized user sees result');
$store = new TestStore(['cms_site_id'=>1,'is_supplier'=>1,'import_agent_id'=>1]);
$store->validateImportAgent('import_agent_id');
check(!$store->hasErrors(), 'Same-site supplier association valid');
check((int)$store->importAgent->id === 1, 'Schedule relation resolves');
$store->import_agent_id=3;
$store->validateImportAgent('import_agent_id');
check($store->hasErrors('import_agent_id'), 'Cross-site association rejected');
$store->clearErrors(); $store->import_agent_id=1; $store->is_supplier=0;
$store->validateImportAgent('import_agent_id');
check($store->hasErrors('import_agent_id'), 'Ordinary store cannot link schedule');
$store->clearErrors(); $store->import_agent_id=null;
$store->validateImportAgent('import_agent_id');
check(!$store->hasErrors(), 'Association may be cleared');
$migration->down(); $db->schema->refresh();
check(!Status::available(), 'Migration rollback removes association');
$migration->up(); $db->schema->refresh();
check(Status::available(), 'Migration reapplies');
echo "OK: $checks checks\n";
