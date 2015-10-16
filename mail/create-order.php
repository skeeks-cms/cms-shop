<?php
use skeeks\cms\mail\helpers\Html;

/* @var $this yii\web\View */
/* @var $order \skeeks\cms\shop\models\ShopOrder */
$url = \yii\helpers\Url::to(['/shop/order/view', 'id' => $order->id], true);
?>

<?= Html::beginTag('h1'); ?>
    <?= \skeeks\cms\shop\Module::t('app', 'New order'); ?> #<?= $order->id; ?> <?= \skeeks\cms\shop\Module::t('app', 'in site'); ?> <?= \Yii::$app->cms->appName ?>
<?= Html::endTag('h1'); ?>

<?= Html::beginTag('p'); ?>
    <?= \skeeks\cms\shop\Module::t('app', 'The order # {order_id} created successfully', ['order_id' => $order->id]); ?>.<br>
<?= Html::endTag('p'); ?>

<?= Html::beginTag('p'); ?>
    <?= \skeeks\cms\shop\Module::t('app', 'The details of the order, you can track on the page'); ?>: <?= Html::a($url, $url); ?>
<?= Html::endTag('p'); ?>