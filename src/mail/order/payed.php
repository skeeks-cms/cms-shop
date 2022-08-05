<?php

use skeeks\cms\mail\helpers\Html;

/* @var $this yii\web\View */
/* @var $order \skeeks\cms\shop\models\ShopOrder */
$url = $order->getPublicUrl();
?>

<?= Html::beginTag('h1'); ?>
<?= \Yii::t('skeeks/shop/app', 'Order successfully paid'); ?> №<?= $order->id; ?>
<?= Html::endTag('h1'); ?>

<?= Html::tag('hr'); ?>

<?= Html::beginTag('h2'); ?>
    Заказ:
<?= Html::endTag('h2'); ?>
<?= Html::beginTag('p'); ?>
<?=
\yii\grid\GridView::widget([
    'dataProvider' => new \yii\data\ArrayDataProvider([
        'allModels'  => $order->shopOrderItems,
        'pagination' => [
            'pageSize'      => 200,
            'pageSizeLimit' => [1, 200],
        ],
    ]),
    'layout'       => "{items}",
    'columns'      =>
        [
            /*[
                'class' => \yii\grid\SerialColumn::class
            ],*/

            [
                'class'  => \yii\grid\DataColumn::class,
                'format' => 'raw',
                'value'  => function (\skeeks\cms\shop\models\ShopOrderItem $shopBasket) {
                    if ($shopBasket->image) {
                        return Html::img($shopBasket->image->absoluteSrc, ['width' => 80]);
                    }
                },
            ],
            [
                'class'     => \yii\grid\DataColumn::class,
                'attribute' => 'name',
                'format'    => 'raw',
                'value'     => function (\skeeks\cms\shop\models\ShopOrderItem $shopBasket) {
                    if ($shopBasket->url) {
                        return Html::a($shopBasket->name, $shopBasket->absoluteUrl, [
                            'target'    => '_blank',
                            'titla'     => "Смотреть на сайте",
                            'data-pjax' => 0,
                        ]);
                    } else {
                        return $shopBasket->name;
                    }

                },
            ],

            [
                'class'     => \yii\grid\DataColumn::class,
                'attribute' => 'quantity',
                'value'     => function (\skeeks\cms\shop\models\ShopOrderItem $shopBasket) {
                    return $shopBasket->quantity." ".$shopBasket->measure_name;
                },
            ],

            [
                'class'     => \yii\grid\DataColumn::class,
                'label'     => \Yii::t('skeeks/shop/app', 'Price'),
                'attribute' => 'price',
                'format'    => 'raw',
                'value'     => function (\skeeks\cms\shop\models\ShopOrderItem $shopBasket) {
                    if ($shopBasket->discount_value) {
                        return "<span style='text-decoration: line-through;'>".(string)$shopBasket->moneyOriginal."</span><br />".Html::tag('small',
                                $shopBasket->notes)."<br />".(string)$shopBasket->money."<br />".Html::tag('small',
                                \Yii::t('skeeks/shop/app', 'Discount').": ".$shopBasket->discount_value);
                    } else {
                        return (string)$shopBasket->money."<br />".Html::tag('small',
                                $shopBasket->notes);
                    }

                },
            ],
            [
                'class'     => \yii\grid\DataColumn::class,
                'label'     => \Yii::t('skeeks/shop/app', 'Sum'),
                'attribute' => 'price',
                'format'    => 'raw',
                'value'     => function (\skeeks\cms\shop\models\ShopOrderItem $shopBasket) {
                    $shopBasket->money->multiply($shopBasket->quantity);
                    return (string)$shopBasket->money;
                },
            ],
        ],
])
?>
<?= Html::endTag('p'); ?>

<? if ($order->shopDiscountCoupons) : ?>
    <?= Html::beginTag('h3'); ?>
    <?= count($order->shopDiscountCoupons) == 1 ? "Скидочный купон:" : "Скидочные купоны:" ;?>
    <?= Html::endTag('h3'); ?>


    <? foreach ($order->shopDiscountCoupons as $shopDiscountCoupon) : ?>
        <b><?= $shopDiscountCoupon->coupon ?></b> -
        <? if ($shopDiscountCoupon->shopDiscount->value_type == \skeeks\cms\shop\models\ShopDiscount::VALUE_TYPE_F) : ?>
            <?= new \skeeks\cms\money\Money($shopDiscountCoupon->shopDiscount->value, $order->currency_code); ?>
        <? else: ?>
        <? endif; ?>
    <? endforeach; ?>
<? endif; ?>

<?= Html::beginTag('h2'); ?>
    Итого:
<?= Html::endTag('h2'); ?>

<?= Html::beginTag('p'); ?>
    Стоимость товаров: <?= Html::tag('b', (string)$order->basketsMoney); ?><br/>
    <?php if((float) $order->moneyDelivery->amount > 0) : ?>
        Стоимость доставки: <?= Html::tag('b', (string)$order->moneyDelivery); ?><br/>
    <?php endif; ?>

    <?php if((float) $order->moneyDiscount->amount > 0) : ?>
        Скидка: <?= Html::tag('b', (string)$order->moneyDiscount); ?><br/>
    <?php endif; ?>
    Оплачено: <?= Html::tag('b', (string)$order->money); ?>
<?= Html::endTag('p'); ?>

<?/*= Html::beginTag('h2'); */?><!--
    Покупатель:
<?/*= Html::endTag('h2'); */?>
--><?/*=
\yii\widgets\DetailView::widget([
    'model'      => $order->buyer->relatedPropertiesModel,
    'attributes' => $order->buyer->relatedPropertiesModel->attributes(),
]);
*/?>

<?= Html::beginTag('p'); ?>
<?= \Yii::t('skeeks/shop/app', 'The details of the order, you can track on the page'); ?>: <?= Html::a($url, $url); ?>
<?= Html::endTag('p'); ?>