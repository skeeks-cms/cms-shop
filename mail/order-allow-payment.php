<?php
use skeeks\cms\mail\helpers\Html;

/* @var $this yii\web\View */
/* @var $order \skeeks\cms\shop\models\ShopOrder */
$url = \yii\helpers\Url::to(['/shop/order/view', 'id' => $order->id], true);
$urlPay = \yii\helpers\Url::to(['/shop/order/pay', 'id' => $order->id], true);
?>

<?= Html::beginTag('h1'); ?>
    <?= skeeks\cms\shop\Module::t('app', 'Resolution on the payment for order'); ?> #<?= $order->id; ?> <?= skeeks\cms\shop\Module::t('app', 'Online'); ?> <?= \Yii::$app->cms->appName ?>
<?= Html::endTag('h1'); ?>

<?= Html::beginTag('p'); ?>
    <?= skeeks\cms\shop\Module::t('app', 'Your order in site'); ?>: <?= Html::a(\Yii::$app->name, \yii\helpers\Url::home(true)) ?> <?= skeeks\cms\shop\Module::t('app', 'approved moderation, now you can pay for it.'); ?><br>
<?= Html::endTag('p'); ?>

<?= Html::beginTag('p'); ?>
    <?= skeeks\cms\shop\Module::t('app', 'The details of the order you can view in'); ?> <?= Html::a(skeeks\cms\shop\Module::t('app', 'order card'), $url); ?>.
<?= Html::endTag('p'); ?>

<?= Html::beginTag('p'); ?>
    Для оплаты: <?= Html::a( skeeks\cms\shop\Module::t('app', 'Go to payment'), $urlPay); ?>.
<?= Html::endTag('p'); ?>