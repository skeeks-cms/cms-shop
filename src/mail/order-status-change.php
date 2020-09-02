<?php

use skeeks\cms\mail\helpers\Html;

/* @var $this yii\web\View */
/* @var $order \skeeks\cms\shop\models\ShopOrder */
$url = $order->url;
?>

<?= Html::beginTag('h1'); ?>
    Заказ №<?= $order->id; ?> — <?= $order->shopOrderStatus->name; ?>
<?= Html::endTag('h1'); ?>

<?= Html::beginTag('p'); ?>
<?= \Yii::t('skeeks/shop/app', 'The status of your order in site'); ?> <?= Html::a(\Yii::$app->name,
    \yii\helpers\Url::home(true)) ?> <?= \Yii::t('skeeks/shop/app', 'changed to'); ?>: "<?= $order->status->name; ?>" .
    <br>
<?= Html::endTag('p'); ?>

<?= Html::beginTag('h4'); ?>
Детали заказа
<?= Html::endTag('h4'); ?>

<?= Html::beginTag('p'); ?>
Стоимость товаров: <?= Html::tag('b', (string)$order->calcMoneyItems); ?><br/>
<?php if ((float)$order->moneyDelivery->amount > 0) : ?>
    Стоимость доставки: <?= Html::tag('b', (string)$order->moneyDelivery); ?><br/>
<?php endif; ?>
<?php if ((float)$order->moneyDiscount->amount > 0) : ?>
    Скидка: <?= Html::tag('b', (string)$order->moneyDiscount); ?><br/>
<?php endif; ?>
Общая стоимость заказа: <?= Html::tag('b', (string)$order->money); ?>
<?= Html::endTag('p'); ?>


<?php if ($order->statusComment) : ?>
    <div style="font-weight: bold;">
        <?php echo $order->statusComment; ?>
    </div>
<?php endif; ?>

<?php if ($order->shopOrderStatus->email_notify_description) : ?>
    <div>
        <?php echo $order->shopOrderStatus->getEmailNotifyDescriptionFormated($order); ?>
    </div>
<?php endif; ?>



<?= Html::beginTag('p'); ?>
<?= \Yii::t('skeeks/shop/app', 'The details of the order, you can track on the page'); ?>: <?= Html::a($url, $url); ?>
<?= Html::endTag('p'); ?>