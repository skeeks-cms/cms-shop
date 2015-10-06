<?php
use skeeks\cms\mail\helpers\Html;

/* @var $this yii\web\View */
/* @var $order \skeeks\cms\shop\models\ShopOrder */
$url = \yii\helpers\Url::to(['/shop/order/view', 'id' => $order->id], true);
?>

<?= Html::beginTag('h1'); ?>
    Новый заказ #<?= $order->id; ?> на сайте <?= \Yii::$app->cms->appName ?>
<?= Html::endTag('h1'); ?>

<?= Html::beginTag('p'); ?>
    Вы успешно оформили заказ на сайте <?= Html::a(\Yii::$app->name, \yii\helpers\Url::home(true)) ?>.<br>
<?= Html::endTag('p'); ?>

<?= Html::beginTag('p'); ?>
    Подробные данные по заказу, вы можете отслеживать на странице: <?= Html::a($url, $url); ?>
<?= Html::endTag('p'); ?>