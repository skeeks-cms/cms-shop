<?php
define('YII_ENABLE_ERROR_HANDLER',false);
require '/app/vendor/autoload.php';require '/app/vendor/yiisoft/yii2/Yii.php';
new yii\console\Application(['id'=>'system-shop-test','basePath'=>__DIR__,'extensions'=>[],
'components'=>['db'=>['class'=>yii\db\Connection::class,'dsn'=>'sqlite::memory:']]]);
require dirname(__DIR__).'/src/migrations/m260920_223000_system_shop_jobs.php';
$db=Yii::$app->db;
$db->createCommand('CREATE TABLE cms_agent (id INTEGER PRIMARY KEY, name TEXT, job_type TEXT, job_payload TEXT, is_system INTEGER DEFAULT 0, is_running INTEGER DEFAULT 0, is_active INTEGER, agent_interval INTEGER, next_exec_at INTEGER)')->execute();
$rows=[
[1,'shop/agents/update-subproducts',null,'{}',0,0,0,301,123],
[2,'shop/notify/quantity-emails',null,'{}',0,0,1,602,456],
[3,'job:shop.gpd.catalog.receive','shop.gpd.catalog.receive','{"connection":"custom"}',0,0,1,47,789],
[4,'custom/task','custom.task','{}',0,0,1,48,790]];
$db->createCommand()->batchInsert('cms_agent',['id','name','job_type','job_payload','is_system','is_running','is_active','agent_interval','next_exec_at'],$rows)->execute();
$m=new m260920_223000_system_shop_jobs(['compact'=>true]);$m->up();
$out=(new yii\db\Query())->from('cms_agent')->indexBy('id')->all();
if($out[1]['job_type']!=='shop.update-subproducts'||$out[1]['is_active']!=0||$out[1]['agent_interval']!=301||$out[1]['next_exec_at']!=123)throw new RuntimeException('Schedule lost');
if($out[2]['job_type']!=='shop.quantity-emails'||$out[2]['is_system']!=1)throw new RuntimeException('Notifier not migrated');
if($out[3]['is_system']!=1||$out[3]['job_payload']!=='{"connection":"custom"}'||$out[3]['agent_interval']!=47)throw new RuntimeException('GPD settings lost');
if($out[4]['is_system']!=0)throw new RuntimeException('Custom schedule touched');
$m->up();
if($out!==(new yii\db\Query())->from('cms_agent')->indexBy('id')->all())throw new RuntimeException('Not repeatable');
echo "OK system schedule migration: identity, activation, cadence, payload, custom rows, repeat\n";$config=require dirname(__DIR__).'/src/config/common.php';
foreach(skeeks\cms\shop\gpd\ScheduleUpgrade::SCHEDULES as $type=>$definition){
 $schedule=$config['components']['cmsAgent']['jobs'][$type]??null;
 if(!$schedule||$schedule['jobType']!==$type||$schedule['interval']!==$definition[1])throw new RuntimeException('Missing configured GPD schedule');
}
echo "OK five GPD schedules are configuration-owned\n";
