<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $order \skeeks\cms\shop\models\ShopOrder */
/* @var $isStatusChange bool */
$isStatusChange = $isStatusChange ?? false;
$url = $order->getCabinetUrl();
?>

<h1><?= $isStatusChange ? 'Статус заказа' : 'Новый заказ'; ?> №<?= Html::encode($order->id); ?></h1>

<p>Здравствуйте!</p>
<p><?= $isStatusChange ? 'Статус вашего заказа изменился.' : 'Благодарим вас за заказ!'; ?></p>
<p>Статус: <b><?= Html::encode($order->shopOrderStatus->name); ?></b></p>

<h4>Товары</h4>
<table cellpadding="10" cellspacing="0" style="text-align: left;">
    <thead>
    <tr>
        <th>Товар</th>
        <th>Количество</th>
        <th>Цена</th>
        <th>Сумма</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($order->shopOrderItems as $item) : ?>
        <tr>
            <td><?= Html::encode($item->name); ?></td>
            <td><?= Html::encode($item->quantity.' '.$item->measure_name); ?></td>
            <td><?= Html::encode((string)$item->moneyWithDiscount); ?></td>
            <td><?= Html::encode((string)$item->moneyWithDiscount->mul($item->quantity)); ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<h4>Итого</h4>
<p>
    Стоимость товаров: <b><?= Html::encode((string)$order->calcMoneyItems); ?></b><br>
    <?php if ((float)$order->moneyDelivery->amount > 0) : ?>
        Стоимость доставки: <b><?= Html::encode((string)$order->moneyDelivery); ?></b><br>
    <?php endif; ?>
    <?php if ((float)$order->moneyDiscount->amount > 0) : ?>
        Скидка: <b><?= Html::encode((string)$order->moneyDiscount); ?></b><br>
    <?php endif; ?>
    Общая стоимость заказа: <b><?= Html::encode((string)$order->money); ?></b>
</p>

<p><?= Html::a('Открыть заказ в личном кабинете', $url); ?></p>
<p>Данные получателя и доставки доступны в личном кабинете после входа в аккаунт, с которого оформлен заказ.</p>
