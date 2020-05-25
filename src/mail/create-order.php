<?php

use skeeks\cms\mail\helpers\Html;

/* @var $this yii\web\View */
/* @var $order \skeeks\cms\shop\models\ShopOrder */
$url = $order->getPublicUrl();
$order->refresh();
?>

<?= Html::beginTag('h1'); ?>
<?= \Yii::t('skeeks/shop/app', 'New order'); ?> №<?= $order->id; ?>
<?= Html::endTag('h1'); ?>

<?= Html::tag('hr'); ?>

<?= Html::beginTag('p'); ?>
Здравствуйте!
<?= Html::endTag('p'); ?>

<?= Html::beginTag('p'); ?>
Благодарим вас за заказ на сайте <a href="<?php echo \yii\helpers\Url::home(true);?>"><?= \Yii::$app->cms->appName ?></a>
<?= Html::endTag('p'); ?>

<div style="text-align: left;">
    <?= Html::beginTag('h4'); ?>
    Заказ:
    <?= Html::endTag('h4'); ?>
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
                        $money = $shopBasket->money;
                        return (string)$money->mul($shopBasket->quantity);
                    },
                ],
            ],
    ])
    ?>

    <?= Html::endTag('p'); ?>


    <?= Html::beginTag('h4'); ?>
    Покупатель:
    <?= Html::endTag('h4'); ?>

    <?
    $attributes = [];
    if ($order->shopBuyer->relatedPropertiesModel->toArray()) {
        foreach ($order->shopBuyer->relatedPropertiesModel->toArray() as $key => $value) {
            $attributes[] = [
                'attribute' => $key,
                'value'     => $order->shopBuyer->relatedPropertiesModel->getSmartAttribute($key),
            ];
        }
    }
    ?>
    <?=
    \yii\widgets\DetailView::widget([
        'model'      => $order->shopBuyer->relatedPropertiesModel,
        'attributes' => $attributes,
    ]);
    ?>

    <? if ($order->shop_pay_system_id) : ?>
        <?= Html::beginTag('h4'); ?>
        Оплата:
        <?= Html::endTag('h4'); ?>

        <?=
        \yii\widgets\DetailView::widget([
            'model'      => $order,
            'attributes' => [
                [
                    'attribute' => 'shop_pay_system_id',
                    'value'     => $order->shopPaySystem->name,
                ],
                [
                    'attribute' => 'payed_at',
                    'label'     => 'Статус оплаты',
                    'value'     => $order->paid_at ? "оплачен" : "не оплачен",
                ],
            ],
        ]);
        ?>

    <? endif; ?>

    <? if ($order->shop_delivery_id) : ?>
        <?= Html::beginTag('h4'); ?>
        Доставка:
        <?= Html::endTag('h4'); ?>

        <?=
        \yii\widgets\DetailView::widget([
            'model'      => $order,
            'attributes' => [
                [
                    'attribute' => 'shop_delivery_id',
                    'value'     => $order->shopDelivery->name,
                ],
            ],
        ]);
        ?>

    <? endif; ?>

    <? if ($order->shopDiscountCoupons) : ?>
        <?= Html::beginTag('h4'); ?>
        <?= count($order->shopDiscountCoupons) == 1 ? "Скидочный купон:" : "Скидочные купоны:" ;?>
        <?= Html::endTag('h4'); ?>


        <? foreach ($order->shopDiscountCoupons as $shopDiscountCoupon) : ?>
            <b><?= $shopDiscountCoupon->coupon ?></b> -
                <? if ($shopDiscountCoupon->shopDiscount->value_type == \skeeks\cms\shop\models\ShopDiscount::VALUE_TYPE_F) : ?>
                    <?= new \skeeks\cms\money\Money($shopDiscountCoupon->shopDiscount->value, $order->currency_code); ?>
                <? else: ?>
                <? endif; ?>
        <? endforeach; ?>
    <? endif; ?>


    <?= Html::beginTag('h4'); ?>
    Итого:
    <?= Html::endTag('h4'); ?>

    <?= Html::beginTag('p'); ?>
    Стоимость товаров: <?= Html::tag('b', (string)$order->calcMoneyItems); ?><br/>
    Стоимость доставки: <?= Html::tag('b', (string)$order->moneyDelivery); ?><br/>
    Скидка: <?= Html::tag('b', (string)$order->moneyDiscount); ?><br/>
    К оплате: <?= Html::tag('b', (string)$order->money); ?>
    <?= Html::endTag('p'); ?>

    <?php if ($order->shopOrderStatus->email_notify_description) : ?>
        <div>
            <?php echo $order->shopOrderStatus->email_notify_description; ?>
        </div>
    <?php endif; ?>

    <?= Html::beginTag('p'); ?>
    <?= \Yii::t('skeeks/shop/app', 'The details of the order, you can track on the page'); ?>: <?= Html::a($url, $url); ?>
    <?= Html::endTag('p'); ?>
</div>
