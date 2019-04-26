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
$money = $model->money->convertToCurrency("RUB");

$name = null;
$email = null;
$phone = null;
if ($model->shopBuyer) {
    if ($model->shopBuyer->email) {
        $email = $model->shopBuyer->email;
    }

    if ($model->shopBuyer->name) {
        $name = $model->shopBuyer->name;
    }
}
$this->registerJs(<<<JS
    $("#tinkoffPay").click();
JS
);
?>
<script src="https://securepay.tinkoff.ru/html/payForm/js/tinkoff_v2.js"></script>

<div style="display: none;">
    <form name="TinkoffPayForm" onsubmit="pay(this); return false;">
        <input class="tinkoffPayRow" type="hidden" name="terminalkey" value="<?= $tinkoff->terminal_key; ?>">
        <input class="tinkoffPayRow" type="hidden" name="frame" value="false">
        <input class="tinkoffPayRow" type="hidden" name="language" value="ru">
        <input class="tinkoffPayRow" type="text" placeholder="Сумма заказа" name="amount" value="<?= $money->amount * $money->currency->getSubUnit(); ?>" required>
        <input class="tinkoffPayRow" type="text" placeholder="Номер заказа" name="order" value="<?= $model->id; ?>">
        <input class="tinkoffPayRow" type="text" placeholder="Описание заказа" name="description" value="<?= $model->description; ?>">

        <? if ($name) : ?>
            <input class="tinkoffPayRow" type="text" placeholder="ФИО плательщика" name="name" value="<?= $name; ?>">
        <? endif; ?>
        <? if ($email) : ?>
            <input class="tinkoffPayRow" type="text" placeholder="E-mail" name="email" value="<?= $email; ?>">
        <? endif; ?>
        <? if ($phone) : ?>
            <input class="tinkoffPayRow" type="text" placeholder="Контактный телефон" name="phone" value="<?= $model->senderCrmContractor->phone; ?>">
        <? endif; ?>
        <input class="tinkoffPayRow" type="submit" id="tinkoffPay" value="Оплатить">
    </form>
</div>

<div style="text-align: center; margin: 100px; font-size: 20px;">
    Перенаправление на платежную систему...
</div>

