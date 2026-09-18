<?php
// Real Yii HTTP request formatting with an in-memory wire transport, no site config.
define('YII_ENABLE_ERROR_HANDLER',false);
$loader=require '/deps/autoload.php';
$loader->addPsr4('skeeks\\cms\\shop\\',dirname(__DIR__).'/src',true);
require '/deps/yiisoft/yii2/Yii.php';
new yii\console\Application(['id'=>'gpd-wire-test','basePath'=>__DIR__,'vendorPath'=>'/deps','extensions'=>[]]);
$mock=new yii\httpclient\MockTransport();
$client=new yii\httpclient\Client(['transport'=>$mock]);
$transport=new skeeks\cms\shop\gpd\CatalogTransport('https://fixture.invalid/v2','fixture-key',$client);
$checks=0;
$check=function($v,$message)use(&$checks){if(!$v)throw new RuntimeException($message);$checks++;};
foreach(['manifest','changes'] as $endpoint){
 $mock->appendResponse(new yii\httpclient\Response(['headers'=>['http-code'=>200],'content'=>'{"items":[]}']));
 $transport->request('GET',$endpoint,['cursor'=>'opaque+/=?','limit'=>50]);
 $r=$mock->flushRequests()[0];$r->prepare();parse_str(parse_url($r->getFullUrl(),PHP_URL_QUERY),$query);
 $check($query===['cursor'=>'opaque+/=?','limit'=>'50'],'GET cursor and limit must be query parameters');
 $check($r->getContent()==='','GET must have no JSON body');
 $check($r->getHeaders()->get('Authorization')==='fixture-key','Existing credential used as header');
 $check($r->getOptions()['maxRedirects']===0,'Redirects disabled');
}
$mock->appendResponse(new yii\httpclient\Response(['headers'=>['http-code'=>200],'content'=>'{"items":[]}']));
$transport->request('POST','batch',['ids'=>[10,20]]);
$r=$mock->flushRequests()[0];$r->prepare();
$check(json_decode($r->getContent(),true)===['ids'=>[10,20]],'POST retains JSON body');
$check(parse_url($r->getFullUrl(),PHP_URL_QUERY)===null,'POST has no query data');
$referenceTransport=new skeeks\cms\shop\gpd\CatalogTransport('https://fixture.invalid/v2','fixture-key',$client,'references');
$mock->appendResponse(new yii\httpclient\Response(['headers'=>['http-code'=>200],'content'=>'{"items":[]}']));
$referenceTransport->request('GET','changes',['cursor'=>'reference-cursor']);
$r=$mock->flushRequests()[0];$r->prepare();
$check(parse_url($r->getFullUrl(),PHP_URL_PATH)==='/v2/references/changes','Separate reference route');
$check($r->getContent()==='','Reference GET has empty body');
$stable=new skeeks\cms\shop\gpd\CatalogTransport('https://fixture.invalid/v2','fixture-key',$client,'dictionaries');
foreach(['countries','measures'] as $endpoint) {
    $mock->appendResponse(new yii\httpclient\Response(['headers'=>['http-code'=>200],'content'=>'[]']));
    $check($stable->request('GET',$endpoint)===[],'Empty stable dictionary is valid');
    $r=$mock->flushRequests()[0];$r->prepare();
    $check(parse_url($r->getFullUrl(),PHP_URL_PATH)==='/v2/'.$endpoint,'Stable dictionary uses direct v2 URL');
    $check($r->getContent()==='','Stable dictionary GET body empty');
}
try {$stable->request('POST','countries');throw new RuntimeException('POST accepted');}
catch(skeeks\cms\shop\gpd\ProtocolException $e){$check($e->reason==='invalid_request','Stable dictionary is read only');}
$offers=new skeeks\cms\shop\gpd\CatalogTransport('https://fixture.invalid/v2','fixture-key',$client,'offers');
$mock->appendResponse(new yii\httpclient\Response(['headers'=>['http-code'=>200],'content'=>'{"items":[]}']));
$offers->request('GET','changes',['cursor'=>'offer-cursor']);$r=$mock->flushRequests()[0];$r->prepare();
$check(parse_url($r->getFullUrl(),PHP_URL_PATH)==='/v2/offers/changes','Offers use separate v2 route');
$check($r->getContent()==='','Offers GET body empty');
echo "PASS $checks HTTP wire checks.\n";
