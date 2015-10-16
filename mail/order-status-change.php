<?php
use skeeks\cms\mail\helpers\Html;

/* @var $this yii\web\View */
/* @var $order \skeeks\cms\shop\models\ShopOrder */
$url = \yii\helpers\Url::to(['/shop/order/view', 'id' => $order->id], true);
?>

<?= Html::beginTag('h1'); ?>
    <?= \skeeks\cms\shop\Module::t('app', 'Changing status'); ?> #<?= $order->id; ?> <?= \skeeks\cms\shop\Module::t('app', 'Online'); ?> <?= \Yii::$app->cms->appName ?>
<?= Html::endTag('h1'); ?>

<?= Html::beginTag('p'); ?>
    <?= \skeeks\cms\shop\Module::t('app', 'The status of your order in site'); ?> <?= Html::a(\Yii::$app->name, \yii\helpers\Url::home(true)) ?> <?= \skeeks\cms\shop\Module::t('app', 'changed to'); ?>: "<?= $order->status->name; ?>" .<br>
<?= Html::endTag('p'); ?>

<?= Html::beginTag('p'); ?>
    <?= \skeeks\cms\shop\Module::t('app', 'The details of the order, you can track on the page'); ?>: <?= Html::a($url, $url); ?>
<?= Html::endTag('p'); ?>