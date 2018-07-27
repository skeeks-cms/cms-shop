<?php

use skeeks\cms\mail\helpers\Html;

/* @var $this yii\web\View */
/* @var $order \skeeks\cms\shop\models\ShopOrder */
?>

<?= Html::beginTag('h1'); ?>
<?= \Yii::t('skeeks/shop/app',
    'Resolution on the payment for order'); ?> №<?= $order->id; ?>
<?= Html::endTag('h1'); ?>

<?= Html::beginTag('p'); ?>
<?= \Yii::t('skeeks/shop/app', 'Your order in site'); ?>: <?= Html::a(\Yii::$app->name,
    \yii\helpers\Url::home(true)) ?> <?= \Yii::t('skeeks/shop/app', 'approved moderation, now you can pay for it.'); ?>
    <br>
<?= Html::endTag('p'); ?>

<?= Html::beginTag('p'); ?>
<?= \Yii::t('skeeks/shop/app', 'The details of the order you can view in'); ?> <?= Html::a(\Yii::t('skeeks/shop/app',
    'order card'), $order->url); ?>.
<?= Html::endTag('p'); ?>

<?= Html::beginTag('p'); ?>
<?= Html::a(\Yii::t('skeeks/shop/app', 'Go to payment'), $order->payUrl); ?>.
<?= Html::endTag('p'); ?>