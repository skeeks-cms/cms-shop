<?
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 07.04.2016
 */
/* @var $this yii\web\View */
/* @var $tinkoff skeeks\cms\shop\paySystems\TinkoffPaySystem */
/* @var $model \skeeks\cms\shop\models\ShopOrder */

$tinkoff = $model->paySystem->paySystemHandler;
$returnUrl = $model->publicUrl;
$money = $model->money->convertToCurrency("RUB");

$terminal_key = $tinkoff->terminal_key;

$payData = [
    'TerminalKey' => $tinkoff->terminal_key,
    'Amount' => $money->getAmount(),
    'OrderId' => $model->id,
    'Frame' => false,
];

$data = [];
if ($model->user)
{
    $data[] = 'Email=' . $model->user->email;
    $data[] = 'Phone=' . $model->user->phone;
    $data[] = 'Name=' . $model->user->displayName;

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

