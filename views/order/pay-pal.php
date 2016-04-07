<?
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 07.04.2016
 */
/* @var $this yii\web\View */
/* @var $payPal skeeks\cms\shop\paySystems\PayPalPaySystem */
/* @var $model \skeeks\cms\shop\models\ShopOrder */

$payPal = $model->paySystem->paySystemHandler;

$returnUrl = \yii\helpers\Url::to(['/shop/order/view', 'id' => $model->id], true);
$notifyUrl = \yii\helpers\Url::to(['/shop/order/pay-pal-notify'], true);

$money = $model->money->convertToCurrency("RUB");

$customData = ['product_id' => $model->id];
$this->registerJs(<<<JS
$(function()
{
    $('#payPal').submit();
});
JS
)
?>
<div style="text-align: center; margin: 100px; font-size: 20px;">
    Wait, is redirected to the payment system...
</div>
<div style="display: none">
    <form action="<?php echo $payPal->payNowButtonUrl; ?>" method="post" id="payPal">
        <input type="hidden" name="cmd" value="_xclick">
        <input type="hidden" name="business" value="<?php echo $payPal->receiverEmail; ?>">
        <input id="paypalItemName" type="hidden" name="item_name" value="Order #<?= $model->id; ?>">
        <input id="paypalQuantity" type="hidden" name="quantity" value="1">
        <input id="paypalAmmount" type="hidden" name="amount" value="<?= $money->getValue(); ?>">
        <input type="hidden" name="no_shipping" value="1">
        <input type="hidden" name="return" value="<?php echo $returnUrl; ?>">
        <input type="hidden" name="cancel_return" value="<?php echo $returnUrl; ?>">
        <input type="hidden" name="notify_url" value="<?php echo $notifyUrl; ?>">

        <input type="hidden" name="custom" value="<?php echo json_encode($customData);?>">

        <input type="hidden" name="currency_code" value="RUB">
        <input type="hidden" name="lc" value="US">
        <input type="hidden" name="bn" value="PP-BuyNowBF">

        <button type="submit">
            Pay Now
        </button>
     </form>
 </div>
