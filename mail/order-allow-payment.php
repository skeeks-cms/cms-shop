<?php
use skeeks\cms\mail\helpers\Html;

/* @var $this yii\web\View */
/* @var $order \skeeks\cms\shop\models\ShopOrder */
$url = \yii\helpers\Url::to(['/shop/order/view', 'id' => $order->id], true);
$urlPay = \yii\helpers\Url::to(['/shop/order/pay', 'id' => $order->id], true);
?>

<?= Html::beginTag('h1'); ?>
    Разрешение оплаты по заказу #<?= $order->id; ?> на сайте <?= \Yii::$app->cms->appName ?>
<?= Html::endTag('h1'); ?>

<?= Html::beginTag('p'); ?>
    Ваш заказ на сайте: <?= Html::a(\Yii::$app->name, \yii\helpers\Url::home(true)) ?> проверен модератором, и утвержден, теперь вы можете его оплатить.<br>
<?= Html::endTag('p'); ?>

<?= Html::beginTag('p'); ?>
    Подробные данные по заказу вы можете посмотреть в <?= Html::a("карточке заказа", $url); ?>.
<?= Html::endTag('p'); ?>

<?= Html::beginTag('p'); ?>
    Для оплаты: <?= Html::a("перейти к оплате заказа", $urlPay); ?>.
<?= Html::endTag('p'); ?>