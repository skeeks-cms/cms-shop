<?php
// php checkout-contacts.php vendor/autoload.php [staged directory]
define('YII_ENABLE_ERROR_HANDLER', false);
require $argv[1];
require dirname($argv[1]).'/yiisoft/yii2/Yii.php';
if (isset($argv[2])) {
    require $argv[2].'/ShopOrder.php';
    require $argv[2].'/CartController.php';
}
use skeeks\cms\shop\models\ShopOrder;
function check($ok, $message) { if (!$ok) throw new RuntimeException($message); }
class ContactOrder extends ShopOrder {
    public $profile;
    public $saved = false;
    public function init() {}
    public function attributes() { return ['id','cms_user_id','contact_first_name','contact_last_name','contact_email','contact_phone','is_created','delivery_amount','amount','currency_code']; }
    public function rules() {
        return array_values(array_filter(parent::rules(), function($rule) {
            return count(array_diff((array)$rule[0], ['contact_first_name','contact_last_name','contact_email','contact_phone'])) === 0;
        }));
    }
    public function getCmsUser() { return $this->profile; }
    public function getOrderRequiredFields() { return $this->scenario === self::SCENARIO_BUY_ONE_CLICK ? ['phone'] : ['first_name','email','phone']; }
    public function refreshDeliveryCalculation($force = false) { return true; }
    public function recalculate() { return $this; }
    public function getDeliveryHandlerCheckoutModel() { return null; }
    public function getHasReceiver() { return false; }
    public function getUrl($params = [], $scheme = true) { return '/test-finish'; }
    public function save($runValidation = true, $attributeNames = null) {
        if ($runValidation && !$this->validate()) return false;
        $this->saved=true;
        if ($this->is_created) $this->notifyNew();
        return true;
    }
}
class ContactMailer extends yii\base\Component {
    public $view;
    public $recipient;
    public $sent = 0;
    public $template;
    public function compose($template, $params) { $this->template=$template; return $this; }
    public function setFrom($value) { return $this; }
    public function setTo($value) { $this->recipient=$value; return $this; }
    public function setSubject($value) { return $this; }
    public function send() { ++$this->sent; return true; }
}
class ContactTransaction {
    public $active=true;
    public function commit() {$this->active=false;}
    public function rollBack() {$this->active=false;}
}
class ContactConnection extends yii\db\Connection {
    public $transaction;
    public function beginTransaction($isolationLevel=null) {return $this->transaction=new ContactTransaction();}
    public function getTransaction() {return $this->transaction && $this->transaction->active ? $this->transaction : null;}
}
$app=new yii\web\Application(['id'=>'checkout-contacts-test','basePath'=>__DIR__, 'components'=>[
 'db'=>['class'=>ContactConnection::class],
 'request'=>['cookieValidationKey'=>'test','scriptUrl'=>'/index.php','hostInfo'=>'https://shop.example'],
 'session'=>['class'=>yii\web\Session::class,'useCookies'=>false],
 'i18n'=>['translations'=>['skeeks/shop/app'=>['class'=>yii\i18n\PhpMessageSource::class,'basePath'=>dirname($argv[1]).'/skeeks/cms-shop/src/messages']]],
]]);
$app->set('cms',(object)['adminEmail'=>'store@example.invalid','appName'=>'Магазин']);
$app->set('money',(object)['currencyCode'=>'RUB']);
$app->set('skeeks',(object)['site'=>(object)['id'=>1,'shopSite'=>(object)['order_min_price'=>0]]]);
$app->set('user',(object)['id'=>10,'isGuest'=>false]);
$mailer=new ContactMailer(['view'=>new yii\web\View(['theme'=>new yii\base\Theme()])]);
$app->set('mailer',$mailer);
function orderFixture($complete=false) {
    $o=new ContactOrder(); $o->id=123; $o->cms_user_id=10;
    $o->profile=(object)['phone'=>'+7 999 123-45-67','first_name'=>$complete?'Из профиля':'','last_name'=>'','email'=>$complete?'profile@example.invalid':''];
    $o->contact_phone=$o->profile->phone; $o->delivery_amount=0; $o->currency_code='RUB';
    return $o;
}
$o=orderFixture(); $o->scenario=ShopOrder::SCENARIO_CHECKOUT; $o->applyCheckoutContacts();
check(!$o->validate() && $o->hasErrors('contact_first_name') && $o->hasErrors('contact_email'),'Authenticated incomplete profile cannot checkout');
$o->scenario=ShopOrder::SCENARIO_DEFAULT;
check($o->validate(),'Administration/default validation unchanged');
$o->scenario=ShopOrder::SCENARIO_BUY_ONE_CLICK;
check($o->validate(),'Phone-only one-click unchanged');
$o=orderFixture(true); $o->applyCheckoutContacts();
check($o->contact_first_name==='Из профиля' && $o->contact_email==='profile@example.invalid','Profile fills omitted contacts');
$o->applyCheckoutContacts(['contact_first_name'=>' Покупатель ','contact_email'=>'buyer@example.invalid','cms_user_id'=>999]);
$o->applyCheckoutContacts();
check($o->contact_first_name==='Покупатель' && $o->contact_email==='buyer@example.invalid' && $o->cms_user_id===10,'Order values preserved; unrelated fields ignored');
check($o->profile->email==='profile@example.invalid','Account unchanged');
$o->applyCheckoutContacts(['contact_email'=>'']); $o->scenario=ShopOrder::SCENARIO_CHECKOUT;
check(!$o->validate() && $o->hasErrors('contact_email'),'Explicit blank does not restore profile value');
try { $o->applyCheckoutContacts(['contact_email'=>['bad']]); throw new RuntimeException('Array accepted'); } catch (InvalidArgumentException $expected) {}

function checkout($order,$contacts) {
 global $app;
 $request=new class extends yii\web\Request { public function getIsAjax(){return true;} public function getIsPost(){return true;} };
 $request->setBodyParams(['contacts'=>$contacts]); $app->set('request',$request);
 $shopUser=new class extends yii\base\Component { public $shopOrder; public $shop_order_id=123; public function save(){return true;} };
 $shopUser->shopOrder=$order; $app->set('shop',(object)['shopUser'=>$shopUser]);
 $controller=new skeeks\cms\shop\controllers\CartController('cart',new yii\base\Module('shop'));
 $response=$controller->actionOrderCheckout();
 // Existing validation-return branches leave the transaction to request cleanup.
 if($tx=$app->db->getTransaction()) $tx->rollBack();
 return $response;
}
$o=orderFixture();
$r=checkout($o,['contact_first_name'=>'Тест','contact_last_name'=>'Покупатель','contact_email'=>'buyer@example.invalid','contact_phone'=>'+7 999 123-45-67']);
check($r->success && $o->saved && $o->is_created,'Actual checkout action creates order with submitted contacts');
check($o->contact_first_name==='Тест' && $o->contact_last_name==='Покупатель' && $o->contact_email==='buyer@example.invalid','Checkout does not overwrite contacts from empty profile');
check($mailer->sent===1 && $mailer->recipient==='buyer@example.invalid' && $mailer->template==='customer-order','Customer notification dispatched to entered email exactly once');
$o=orderFixture(); $r=checkout($o,[]);
check(!$r->success && !$o->saved && $mailer->sent===1,'Missing contacts block order and notification');
$o=orderFixture(); $r=checkout($o,['contact_first_name'=>'Тест','contact_email'=>'invalid','contact_phone'=>'+7 999 123-45-67']);
check(!$r->success && !$o->saved && $mailer->sent===1,'Malformed email blocks checkout');
echo "OK: actual checkout action, contact preservation, required/invalid fields, profile fallback, account isolation, one-click compatibility, notification recipient and single dispatch. No real orders or email sent.\n";
