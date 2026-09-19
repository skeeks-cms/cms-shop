<?php
require dirname(__DIR__).'/src/gpd/LegacyProcessGuard.php';
use skeeks\cms\shop\gpd\LegacyProcessGuard;
$root=sys_get_temp_dir().'/gpd-guard-'.bin2hex(random_bytes(5));mkdir($root);
$checks=0;
function checkGuard($root,$blocked){global $checks;try{LegacyProcessGuard::assertStopped($root);$actual=false;}catch(RuntimeException $e){$actual=true;}if($actual!==$blocked)throw new RuntimeException('Unexpected guard result');$checks++;}
try{
checkGuard($root,false);
foreach(['shop/skeeks-suppliers/update-products','shop/skeeks-suppliers/update-store-items','cmsAgent/execute'] as $route){
$p=proc_open([PHP_BINARY,'-r','sleep(30);',$route],[['file','/dev/null','r'],['file','/dev/null','w'],['file','/dev/null','w']],$pipes,$root);
try{usleep(100000);checkGuard($root,true);checkGuard(dirname($root),false);}finally{proc_terminate($p);proc_close($p);}
}
try{LegacyProcessGuard::assertStopped($root,$root.'/missing');throw new LogicException('Unknown accepted');}catch(RuntimeException $e){$checks++;}
echo "OK $checks process guard checks\n";
}finally{rmdir($root);}
