<?
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 07.04.2016
 */
/* @var $this yii\web\View */
/* @var $tinkoff skeeks\cms\shop\paySystems\TinkoffPaySystem */
/* @var $model \skeeks\cms\shop\models\ShopBill */

$tinkoff = $model->shopPaySystem->paySystemHandler;
$returnUrl = $model->url;
$money = $model->money->convertToCurrency("RUB");

$terminal_key = $tinkoff->terminal_key;

$payData = [
    'TerminalKey' => $tinkoff->terminal_key,
    'Amount'      => $money->amount * $money->currency->getSubUnit(),
    'OrderId'     => $model->id,
    'Frame'       => false,
];

$data = [];

if ($model->shopBuyer) {
    if ($model->shopBuyer->email) {
        $data[] = 'Email='.$model->shopBuyer->email;
    }

    if ($model->shopBuyer->name) {
        $data[] = 'Name='.$model->shopBuyer->name;
    }
    //$data[] = 'Phone='.$model->buyer->phone;


    $payData['DATA'] = implode('|', $data);
}


$jsData = \yii\helpers\Json::encode($payData);

$this->registerJs(<<<JS
    doPay($jsData);
JS
);
?>
<script src="https://securepay.tinkoff.ru/html/payForm/js/tinkoff.js"></script>
<div style="text-align: center; margin: 100px; font-size: 20px;">
    Перенаправление на платежную систему...
</div>

