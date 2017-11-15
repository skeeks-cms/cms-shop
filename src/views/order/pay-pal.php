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
$notifyUrl = \yii\helpers\Url::to(['/shop/pay-pal/ipn'], true);

/*$data = 'a:33:{s:8:"mc_gross";s:5:"68.52";s:22:"protection_eligibility";s:10:"Ineligible";s:8:"payer_id";s:13:"GB94ESEPPPB42";s:3:"tax";s:4:"0.00";s:12:"payment_date";s:25:"00:37:35 Apr 08, 2016 PDT";s:14:"payment_status";s:9:"Completed";s:7:"charset";s:6:"KOI8_R";s:10:"first_name";s:9:"Alexander";s:6:"mc_fee";s:5:"12.67";s:14:"notify_version";s:3:"3.8";s:6:"custom";s:1:"{";s:12:"payer_status";s:8:"verified";s:8:"business";s:30:"semenov-facilitator@skeeks.com";s:8:"quantity";s:1:"1";s:11:"verify_sign";s:56:"An5ns1Kso7MWUdW4ErQKJJJ4qi4-ALfkkgv3i0h-cHWSy3tgwVF8d7UG";s:11:"payer_email";s:23:"semenov-test@skeeks.com";s:6:"txn_id";s:17:"2T609443LF1147527";s:12:"payment_type";s:7:"instant";s:9:"last_name";s:7:"Semenov";s:14:"receiver_email";s:30:"semenov-facilitator@skeeks.com";s:11:"payment_fee";s:0:"";s:11:"receiver_id";s:13:"MSPKLDZ5MLELE";s:8:"txn_type";s:10:"web_accept";s:9:"item_name";s:9:"Order #19";s:11:"mc_currency";s:3:"RUB";s:11:"item_number";s:0:"";s:17:"residence_country";s:2:"RU";s:8:"test_ipn";s:1:"1";s:15:"handling_amount";s:4:"0.00";s:19:"transaction_subject";s:0:"";s:13:"payment_gross";s:0:"";s:8:"shipping";s:4:"0.00";s:12:"ipn_track_id";s:13:"e008e0a49d8a1";}';
print_r(unserialize($data));die;*/

$money = $model->money->convertToCurrency("RUB");

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
    <form action="<?php echo $payPal->payPalUrl; ?>" method="post" id="payPal">
        <input type="hidden" name="cmd" value="_xclick">
        <input type="hidden" name="business" value="<?php echo $payPal->receiverEmail; ?>">
        <input id="paypalItemName" type="hidden" name="item_name" value="Order #<?= $model->id; ?>">
        <input id="paypalQuantity" type="hidden" name="quantity" value="1">
        <input id="paypalAmmount" type="hidden" name="amount" value="<?= $money->getValue(); ?>">
        <input type="hidden" name="no_shipping" value="1">
        <input type="hidden" name="return" value="<?php echo $returnUrl; ?>">
        <input type="hidden" name="cancel_return" value="<?php echo $returnUrl; ?>">
        <input type="hidden" name="notify_url" value="<?php echo $notifyUrl; ?>">

        <input type="hidden" name="custom" value="<?php echo $model->id; ?>">

        <input type="hidden" name="currency_code" value="RUB">
        <input type="hidden" name="lc" value="US">
        <input type="hidden" name="bn" value="PP-BuyNowBF">

        <button type="submit">
            Pay Now
        </button>
    </form>
</div>
