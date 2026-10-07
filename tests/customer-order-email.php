<?php
// php tests/customer-order-email.php <vendor/autoload.php>
define('YII_ENABLE_ERROR_HANDLER', false);
require $argv[1];
require dirname($argv[1]).'/yiisoft/yii2/Yii.php';

use skeeks\cms\shop\models\ShopOrder;
use yii\helpers\Html;

function verify($condition, $message)
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

class EmailMoney
{
    public $amount;
    public function __construct($amount) { $this->amount = $amount; }
    public function __toString() { return $this->amount.' RUB'; }
    public function mul($quantity) { return new self($this->amount * $quantity); }
}

class EmailOrder extends ShopOrder
{
    public $id = 2415;
    public $shopOrderItems;
    public $shopOrderStatus;
    public $email;
    public $code = 'PRIVATE_ACCESS_CODE';
    public $comment = 'PRIVATE_ORDER_COMMENT';
    public $statusComment = 'PRIVATE_STATUS_COMMENT';
    public $delivery_address = 'PRIVATE_ADDRESS';
    public $contact_first_name = 'PRIVATE_FIRST_NAME';
    public $contact_last_name = 'PRIVATE_LAST_NAME';
    public $contact_phone = 'PRIVATE_PHONE';
    public $receiver_email = 'PRIVATE_RECEIVER_EMAIL';
    public function init() {}
    public function attributes() { return []; }
    public function getCalcMoneyItems() { return new EmailMoney(300); }
    public function getMoneyDelivery() { return new EmailMoney(50); }
    public function getMoneyDiscount() { return new EmailMoney(20); }
    public function getMoney() { return new EmailMoney(330); }
    public function getContactAttributes() { throw new RuntimeException('Contacts must not be read'); }
    public function getReceiverAttributes() { throw new RuntimeException('Receiver must not be read'); }
    public function getDeliveryHandlerCheckoutModel() { throw new RuntimeException('Delivery details must not be read'); }
}

class CapturingMailer extends yii\base\Component
{
    public $view;
    public $template;
    public $params;
    public $recipient;
    public function compose($template, $params) { $this->template = $template; $this->params = $params; return $this; }
    public function setFrom($from) { return $this; }
    public function setTo($to) { $this->recipient = $to; return $this; }
    public function setSubject($subject) { return $this; }
    public function send() { return true; }
}

$app = new yii\web\Application([
    'id' => 'customer-email-test', 'basePath' => __DIR__,
    'components' => [
        'i18n' => ['translations' => ['skeeks/shop/app' => ['class' => yii\i18n\PhpMessageSource::class, 'basePath' => dirname(__DIR__).'/src/messages']]],
        'request' => ['cookieValidationKey' => 'test', 'scriptUrl' => '/index.php', 'hostInfo' => 'https://shop.example'],
        'urlManager' => ['enablePrettyUrl' => false],
        'db' => ['class' => yii\db\Connection::class, 'dsn' => 'sqlite::memory:'],
        'cache' => ['class' => yii\caching\ArrayCache::class],
    ],
]);
$app->set('cms', (object)['adminEmail' => 'store@example.test', 'appName' => 'Магазин']);
$mailer = new CapturingMailer();
$mailer->view = new yii\web\View(['theme' => new yii\base\Theme()]);
$app->set('mailer', $mailer);
$order = new EmailOrder();
$order->shopOrderStatus = (object)['name' => 'Принят <test>', 'email_notify_description' => 'PRIVATE_STATUS_DESCRIPTION'];
$order->shopOrderItems = [(object)[
    'name' => 'Товар <script>alert(1)</script>', 'quantity' => 1.5, 'measure_name' => 'шт.',
    'moneyWithDiscount' => new EmailMoney(200), 'notes' => 'PRIVATE_ITEM_NOTES',
    'shopOrderItemProperties' => [(object)['name' => 'Имя', 'value' => 'PRIVATE_ITEM_PROPERTY']],
]];

foreach (['buyer@gmail.com', 'buyer@yandex.ru', 'buyer@custom.example'] as $email) {
    $order->email = $email;
    $order->notifyNew();
    verify($mailer->template === 'customer-order' && $mailer->recipient === $email, 'Same customer template for every mail service');
}

foreach ([false, true] as $isStatusChange) {
    $html = $mailer->view->renderFile(dirname(__DIR__).'/src/mail/customer-order.php', compact('order', 'isStatusChange'));
    verify(strpos($html, 'PRIVATE_') === false, 'No personal fields, comments, properties or access code');
    verify(strpos($html, 'buyer@') === false, 'Recipient email is absent from body');
    foreach (['2415', '1.5 шт.', '200 RUB', '300 RUB', '50 RUB', '20 RUB', '330 RUB', 'Принят &lt;test&gt;'] as $expected) {
        verify(strpos($html, $expected) !== false, 'Missing order information: '.$expected);
    }
    verify(strpos($html, Html::encode($order->shopOrderItems[0]->name)) !== false && strpos($html, '<script>') === false, 'Product text is encoded');
    verify(strpos($html, Html::encode($order->getCabinetUrl())) !== false, 'Authenticated cabinet link');
    verify(strpos($order->getCabinetUrl(), 'shop%2Fupa-order%2Fview') !== false, 'Cabinet route');
    verify(strpos($order->getCabinetUrl(), 'pk=2415') !== false, 'Order ID in cabinet link');
}

// Exercise the actual cabinet action/query using an isolated database.
$app->db->createCommand('CREATE TABLE shop_order (id INTEGER PRIMARY KEY, cms_user_id INTEGER, amount REAL, discount_amount REAL, delivery_amount REAL)')->execute();
$app->db->createCommand()->batchInsert('shop_order', ['id', 'cms_user_id', 'amount', 'discount_amount', 'delivery_amount'], [
    [2415, 10, 330, 20, 50], [2416, 20, 100, 0, 0], [2417, null, 100, 0, 0],
])->execute();
class EmailCabinetController extends skeeks\cms\shop\controllers\UpaOrderController
{
    public function init() {}
    public function render($view, $params = []) { return $params['model']->id; }
}
$controller = new EmailCabinetController('upa-order', new yii\base\Module('shop'));
$controller->action = new yii\base\Action('view', $controller);
$app->set('user', (object)['id' => 10, 'isGuest' => false]);
$app->request->setQueryParams(['pk' => 2415]);
verify((int)$controller->actionView() === 2415, 'Owner can open own order');
foreach ([2416, 9999, null] as $pk) {
    $app->request->setQueryParams(['pk' => $pk]);
    try {
        $controller->actionView();
        throw new RuntimeException('Foreign/missing order must not open');
    } catch (yii\web\NotFoundHttpException $expected) {}
}
$app->set('user', (object)['id' => null, 'isGuest' => true]);
$app->request->setQueryParams(['pk' => 2417]);
try {
    $controller->actionView();
    throw new RuntimeException('Guest must not open an unowned order');
} catch (yii\web\NotFoundHttpException $expected) {}

echo "OK: notification routing, both email variants, no personal data, totals, fractional quantity, encoding, cabinet URL and owner/foreign/missing/guest access.\n";
