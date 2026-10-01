<?php
define('YII_ENV','dev');define('YII_DEBUG',false);
define('ROOT_DIR',$argv[1]);define('APP_DIR',ROOT_DIR.'/console');
define('APP_CONFIG_DIR',APP_DIR.'/config');define('APP_RUNTIME_DIR',APP_DIR.'/runtime');
require ROOT_DIR.'/vendor/skeeks/cms/bootstrap.php';
$config=new Yiisoft\Config\Config(new Yiisoft\Config\ConfigPaths(ROOT_DIR,'config'),null,
 [Yiisoft\Config\Modifier\RecursiveMerge::groups('console','console-'.ENV,'params','params-console-'.ENV)],'params-console-'.ENV);
$app=new yii\console\Application($config->get($config->has('console-'.ENV)?'console-'.ENV:'console'));
// Read-only runtime smoke test: php tests/gpd-reconcile-progress.php /path/to/site
use skeeks\cms\job\models\CmsJobRun;use skeeks\cms\shop\jobs\GpdReconcileProgress;
$n=0;$check=static function($ok,$label)use(&$n){if(!$ok)throw new RuntimeException($label);++$n;};
$run=new CmsJobRun(['status'=>'succeeded_with_warnings','result_json'=>json_encode(['counts'=>['products'=>['checked'=>5496,'pending'=>30,'deleted'=>1678],'collections'=>['checked'=>1524,'pending'=>1174],'brands'=>['checked'=>64,'pending'=>42]]])]);
$s=GpdReconcileProgress::collect($run);$check(count($s['rows'])===3,'three stages');$check(array_column($s['rows'],'kind')===['products','collections','brands'],'order');$check(!$s['active'],'terminal');$check($s['rows'][0]['status']==='complete','legacy completion');$check($s['rows'][0]['seconds']===null,'no invented duration');$check(str_contains($s['rows'][0]['reasons'],'не сохранена'),'legacy reason');
$run->status='running';$run->result_json=json_encode(['counts'=>['products'=>['checked'=>20,'pending'=>3,'pending_not_available'=>2,'pending_dependencies'=>1]],'stages'=>['products'=>['status'=>'running','total'=>100,'started_at'=>time()-10]]]);
$s=GpdReconcileProgress::collect($run);$check($s['active']&&$s['rows'][0]['total']===100,'active total');$check($s['rows'][1]['status']==='waiting','waiting stage');$check(str_contains($s['rows'][0]['reasons'],'Нет записи')&&str_contains($s['rows'][0]['reasons'],'справочники'),'reason breakdown');
$run->status='cancelled';$s=GpdReconcileProgress::collect($run);$check($s['rows'][0]['status']==='stopped','cancelled stage');$run->status='succeeded';$run->result_json='{"disabled":true}';$s=GpdReconcileProgress::collect($run);$check($s['rows'][0]['status']==='skipped','disabled');
$check(str_contains(GpdReconcileProgress::message('products',['checked'=>20,'deleted'=>5],100),'удалено 5'),'scheduler deleted count');
echo 'PASS '.$n.' progress checks',PHP_EOL;