<?php
// Isolated database, no site bootstrap or production configuration.
define('YII_ENABLE_ERROR_HANDLER', false);
$vendor = getenv('GPD_TEST_VENDOR') ?: '/deps';
$loader = require $vendor.'/autoload.php';
$loader->addPsr4('skeeks\\cms\\shop\\', dirname(__DIR__).'/src/', true);
require $vendor.'/yiisoft/yii2/Yii.php';
new yii\console\Application(['id'=>'schedule-test','basePath'=>__DIR__,'vendorPath'=>$vendor,'extensions'=>[],
    'components'=>['db'=>['class'=>yii\db\Connection::class,
        'dsn'=>getenv('GPD_TEST_DSN') ?: 'mysql:host=gpd-receiver-db',
        'username'=>'root','password'=>getenv('GPD_TEST_PASSWORD') ?: '']]]);
use skeeks\cms\shop\gpd\ScheduleUpgrade;
$db=Yii::$app->db;
$name='gpd_upgrade_'.bin2hex(random_bytes(5));
$db->createCommand("CREATE DATABASE $name")->execute();
$db->createCommand("USE $name")->execute();
$checks=0;
function verify($condition,$message){global $checks;if(!$condition)throw new RuntimeException($message);$checks++;}
try {
foreach (['','sx_'] as $prefix) {
    $db->tablePrefix=$prefix;$db->schema->refresh();
    $db->createCommand()->createTable('{{%cms_agent}}',[
        'id'=>'pk','cms_site_id'=>'integer','name'=>'text','description'=>'text','job_type'=>'varchar(190)',
        'job_payload'=>'text','is_active'=>'integer DEFAULT 1','is_running'=>'integer DEFAULT 0',
        'is_system'=>'integer DEFAULT 1','is_period'=>'integer DEFAULT 0','agent_interval'=>'integer DEFAULT 600',
        'priority'=>'integer DEFAULT 100','last_exec_at'=>'integer','next_exec_at'=>'integer',
    ])->execute();
    foreach ([1,2,3,4] as $site) foreach(ScheduleUpgrade::LEGACY as $command){
        $active=$site===1 || ($site===3 && strpos($command,'update-store-items')!==false);
        $db->createCommand()->insert('{{%cms_agent}}',['cms_site_id'=>$site,'name'=>$command,'is_active'=>(int)$active])->execute();
    }
    $db->createCommand()->insert('{{%cms_agent}}',['cms_site_id'=>4,'name'=>'job:shop.gpd.catalog.receive',
        'job_type'=>'shop.gpd.catalog.receive','job_payload'=>'{"connection":"pilot"}','agent_interval'=>47,'is_active'=>1])->execute();
    $query=static fn($site)=>(new yii\db\Query())->from('{{%cms_agent}}')->where(['cms_site_id'=>$site])->andWhere(['not',['job_type'=>null]]);
    $enabled=[];
    $upgrade=new ScheduleUpgrade($db);
    verify($upgrade->run(function($site,$enable)use(&$enabled){if($enable)$enabled[]=$site;})===4,'All legacy sites migrated');
    verify($enabled===[1,3],'Only previously active, non-pilot sites enabled');
    verify((int)$query(1)->count()===5 && (int)$query(1)->andWhere(['is_active'=>1])->count()===5,'Active schedules replaced');
    verify((int)$query(2)->andWhere(['is_active'=>1])->count()===0,'Disabled legacy stays disabled');
    verify((int)$query(3)->andWhere(['is_active'=>1])->count()===3,'Offers-only activation preserved');
    $pilot=$query(4)->andWhere(['job_type'=>'shop.gpd.catalog.receive'])->one();
    verify($pilot['agent_interval']==47 && $pilot['job_payload']==='{"connection":"pilot"}' && $pilot['is_active']==1,'Pilot configuration retained');
    verify(!(new yii\db\Query())->from('{{%cms_agent}}')->where(['name'=>ScheduleUpgrade::LEGACY,'is_active'=>1])->exists(),'Old commands disabled');
    $before=(new yii\db\Query())->from('{{%cms_agent}}')->orderBy('id')->all();
    $upgrade->run(function($site,$enable){verify(!$enable,'Rerun does not reenable settings');});
    verify($before===(new yii\db\Query())->from('{{%cms_agent}}')->orderBy('id')->all(),'Rerun leaves schedules unchanged');
    verify((int)$query(9)->count()===0,'Sites without legacy schedules untouched');
    $db->createCommand()->insert('{{%cms_agent}}',['cms_site_id'=>5,'name'=>ScheduleUpgrade::LEGACY[0],'is_active'=>1])->execute();
    try{$upgrade->run(function($site){if($site===5)throw new RuntimeException('fixture');});throw new LogicException('Missing error');}
    catch(RuntimeException $e){verify($e->getMessage()==='fixture','Settings failure is propagated');}
    verify((int)$query(5)->count()===0,'Settings failure rolls back new schedules');
    $db->createCommand()->update('{{%cms_agent}}',['is_running'=>1],['cms_site_id'=>5])->execute();
    try{$upgrade->run(static function(){});throw new LogicException('Running legacy was accepted');}
    catch(RuntimeException $e){verify(strpos($e->getMessage(),'#5')!==false,'Running command prevents overlap');}
}
echo "OK $checks schedule migration checks\n";
} finally {$db->createCommand("DROP DATABASE $name")->execute();}