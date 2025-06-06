<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $order \skeeks\cms\shop\models\ShopOrder */
$url = $order->getPublicUrl();
$order->refresh();
?>

<h1>
    <?= \Yii::t('skeeks/shop/app', 'New order'); ?> №<?= $order->id; ?>
</h1>

<hr/>

<p>
    Здравствуйте!
</p>

<p>
    Благодарим вас за заказ на сайте <a href="<?php echo \yii\helpers\Url::home(true); ?>"><?= \Yii::$app->cms->appName ?></a>
</p>

<div style="text-align: left;">
    <h4>
        Заказ:
    </h4>

    <?=
    \yii\grid\GridView::widget([
        'dataProvider' => new \yii\data\ArrayDataProvider([
            'allModels'  => $order->shopOrderItems,
            'pagination' => [
                'pageSize'      => 2000,
                'pageSizeLimit' => [1, 2000],
            ],
        ]),
        'layout'       => "{items}",
        'columns'      =>
            [
                /*[
                    'class' => \yii\grid\SerialColumn::class
                ],*/

                [
                    'headerOptions' => [
                        'style' => 'padding: 15px;',
                    ],

                    'contentOptions' => [
                        'style' => 'padding: 15px;',
                    ],
                    'class'          => \yii\grid\DataColumn::class,
                    'format'         => 'raw',
                    'value'          => function (\skeeks\cms\shop\models\ShopOrderItem $shopBasket) {
                        if ($shopBasket->image) {
                            return Html::img($shopBasket->image->absoluteSrc, ['width' => 80]);
                        }
                    },
                ],
                [
                    'headerOptions' => [
                        'style' => 'padding: 15px;',
                    ],

                    'contentOptions' => [
                        'style' => 'padding: 15px;',
                    ],
                    'class'          => \yii\grid\DataColumn::class,
                    'attribute'      => 'name',
                    'format'         => 'raw',
                    'value'          => function (\skeeks\cms\shop\models\ShopOrderItem $shopBasket) {
                        $result = [];
                        if ($shopBasket->url) {
                            $result[] = Html::a($shopBasket->name, $shopBasket->absoluteUrl, [
                                'target'    => '_blank',
                                'titla'     => "Смотреть на сайте",
                                'data-pjax' => 0,
                            ]);
                        } else {
                            $result[] = $shopBasket->name;
                        }

                        if ($shopBasket->shopOrderItemProperties) {
                            foreach ($shopBasket->shopOrderItemProperties as $prop) {
                                $result[] = "<small style='color: gray;'>{$prop->name}: {$prop->value}</small>";
                            }
                        }

                        return implode("<br />", $result);

                    },
                ],

                [
                    'headerOptions' => [
                        'style' => 'padding: 15px;',
                    ],

                    'contentOptions' => [
                        'style' => 'padding: 15px;',
                    ],
                    'class'          => \yii\grid\DataColumn::class,
                    'attribute'      => 'quantity',
                    'value'          => function (\skeeks\cms\shop\models\ShopOrderItem $shopBasket) {
                        return $shopBasket->quantity." ".$shopBasket->measure_name;
                    },
                ],

                [
                    'headerOptions' => [
                        'style' => 'padding: 15px;',
                    ],

                    'contentOptions' => [
                        'style' => 'padding: 15px;',
                    ],
                    'class'          => \yii\grid\DataColumn::class,
                    'label'          => \Yii::t('skeeks/shop/app', 'Price'),
                    'attribute'      => 'price',
                    'format'         => 'raw',
                    'value'          => function (\skeeks\cms\shop\models\ShopOrderItem $shopBasket) {
                        if ($shopBasket->discount_value) {
                            return "<span style='text-decoration: line-through;'>".(string)$shopBasket->money."</span><br />".Html::tag('small',
                                    $shopBasket->notes)."<br />".(string)$shopBasket->moneyWithDiscount."<br />".Html::tag('small',
                                    \Yii::t('skeeks/shop/app', 'Discount').": ".$shopBasket->discount_value);
                        } else {
                            return (string)$shopBasket->money;
                        }

                    },
                ],
                [
                    'headerOptions'  => [
                        'style' => 'padding: 15px;',
                    ],
                    'contentOptions' => [
                        'style' => 'padding: 15px;',
                    ],
                    'class'          => \yii\grid\DataColumn::class,
                    'label'          => \Yii::t('skeeks/shop/app', 'Sum'),
                    'attribute'      => 'price',
                    'format'         => 'raw',
                    'value'          => function (\skeeks\cms\shop\models\ShopOrderItem $shopBasket) {
                        $money = $shopBasket->money;
                        return (string)$money->mul($shopBasket->quantity);
                    },
                ],
            ],
    ])
    ?>

    <h4 style="margin-top: 15px;">
        Контактные данные:
    </h4>
    <?
    $contactAttributes = $order->getContactAttributes();
    $receiverAttributes = $order->getReceiverAttributes();

    echo \yii\widgets\DetailView::widget([
        'model'      => $order,
        'attributes' => $contactAttributes,
    ]);
    ?>

    <?php if ($receiverAttributes) : ?>
        <h4 style="margin-top: 15px;">
            Данные получателя:
        </h4>
        <?php echo \yii\widgets\DetailView::widget([
            'model'      => $order,
            'attributes' => $receiverAttributes,
        ]);; ?>
    <?php endif; ?>



    <? if ($order->shop_pay_system_id) : ?>
        <h4 style="margin-top: 15px;">
            Оплата:
        </h4>

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
        <h4 style="margin-top: 15px;">
            Способ получения:
        </h4>

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


        <?php if ($order->deliveryHandlerCheckoutModel && $order->deliveryHandlerCheckoutModel->getVisibleAttributes()) : ?>
            <h5 style="margin-top: 15px;">
                Детали доставки:
            </h5>
            <table>
                <? foreach ($order->deliveryHandlerCheckoutModel->getVisibleAttributes() as $code => $data) : ?>
                    <tr>
                        <td><?php echo \yii\helpers\ArrayHelper::getValue($data, 'label'); ?></td>
                        <td><?php echo \yii\helpers\ArrayHelper::getValue($data, 'value'); ?></td>
                    </tr>
                <? endforeach; ?>
            </table>
        <?php endif; ?>

        <?php if ($order->comment) : ?>
        <h5 style="margin-top: 15px;">
                Примечание к заказу:
            </h5>
        
            <table>

                <tr>
                    <th>Комментарий</th>
                    <td><?php echo $order->comment; ?></td>
                </tr>

            </table>
        <?php endif; ?>


    <? endif; ?>

    <? if ($order->shopDiscountCoupons) : ?>
        <h4 style="margin-top: 15px;">
            <?= count($order->shopDiscountCoupons) == 1 ? "Скидочный купон:" : "Скидочные купоны:"; ?>
        </h4>


        <? foreach ($order->shopDiscountCoupons as $shopDiscountCoupon) : ?>
            <b><?= $shopDiscountCoupon->coupon ?></b> -
            <? if ($shopDiscountCoupon->shopDiscount->value_type == \skeeks\cms\shop\models\ShopDiscount::VALUE_TYPE_F) : ?>
                <?= new \skeeks\cms\money\Money($shopDiscountCoupon->shopDiscount->value, $order->currency_code); ?>
            <? else: ?>
            <? endif; ?>
        <? endforeach; ?>
    <? endif; ?>


    <h4 style="margin-top: 15px;">
        Итого:
    </h4>

    <p>
        Стоимость товаров: <b><?= (string)$order->calcMoneyItems; ?></b><br/>
        <?php if ((float)$order->moneyDelivery->amount > 0) : ?>
            Стоимость доставки: <b><?= (string)$order->moneyDelivery; ?></b><br/>
        <?php endif; ?>
        <?php if ((float)$order->moneyDiscount->amount > 0) : ?>
            Скидка: <b><?= (string)$order->moneyDiscount; ?></b><br/>
        <?php endif; ?>
        К оплате: <b><?= (string)$order->money; ?></b>
    </p>

    <?php if ($order->shopOrderStatus->email_notify_description) : ?>
        <div>
            <?php echo $order->shopOrderStatus->email_notify_description; ?>
        </div>
    <?php endif; ?>

    <p style="margin-top: 15px;">
        <?= \Yii::t('skeeks/shop/app', 'The details of the order, you can track on the page'); ?>: <?= Html::a($url, $url); ?>
    </p>
</div>
