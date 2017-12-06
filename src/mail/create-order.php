<?php

use skeeks\cms\mail\helpers\Html;

/* @var $this yii\web\View */
/* @var $order \skeeks\cms\shop\models\ShopOrder */
$url = $order->getPublicUrl();
$order->refresh();
?>

<?= Html::beginTag('h1'); ?>
<?= \Yii::t('skeeks/shop/app', 'New order'); ?> #<?= $order->id; ?> <?= \Yii::t('skeeks/shop/app',
    'in site'); ?> <?= \Yii::$app->cms->appName ?>
<?= Html::endTag('h1'); ?>

<?= Html::tag('hr'); ?>

<div style="text-align: left;">
    <?= Html::beginTag('h2'); ?>
        Заказ:
    <?= Html::endTag('h2'); ?>
    <?= Html::beginTag('p'); ?>

    <?=
    \yii\grid\GridView::widget([
        'dataProvider' => new \yii\data\ArrayDataProvider([
            'allModels' => $order->shopBaskets,
            'pagination' => [
                'pageSize' => 200,
                'pageSizeLimit' => [1, 200],
            ],
        ]),
        'layout' => "{items}",
        'columns' =>
            [
                /*[
                    'class' => \yii\grid\SerialColumn::className()
                ],*/

                [
                    'class' => \yii\grid\DataColumn::className(),
                    'format' => 'raw',
                    'value' => function (\skeeks\cms\shop\models\ShopBasket $shopBasket) {
                        if ($shopBasket->image) {
                            return Html::img($shopBasket->image->absoluteSrc, ['width' => 80]);
                        }
                    }
                ],
                [
                    'class' => \yii\grid\DataColumn::className(),
                    'attribute' => 'name',
                    'format' => 'raw',
                    'value' => function (\skeeks\cms\shop\models\ShopBasket $shopBasket) {
                        if ($shopBasket->url) {
                            return Html::a($shopBasket->name, $shopBasket->absoluteUrl, [
                                'target' => '_blank',
                                'titla' => "Смотреть на сайте",
                                'data-pjax' => 0
                            ]);
                        } else {
                            return $shopBasket->name;
                        }

                    }
                ],

                [
                    'class' => \yii\grid\DataColumn::className(),
                    'attribute' => 'quantity',
                    'value' => function (\skeeks\cms\shop\models\ShopBasket $shopBasket) {
                        return $shopBasket->quantity . " " . $shopBasket->measure_name;
                    }
                ],

                [
                    'class' => \yii\grid\DataColumn::className(),
                    'label' => \Yii::t('skeeks/shop/app', 'Price'),
                    'attribute' => 'price',
                    'format' => 'raw',
                    'value' => function (\skeeks\cms\shop\models\ShopBasket $shopBasket) {
                        if ($shopBasket->discount_value) {
                            return "<span style='text-decoration: line-through;'>" . \Yii::$app->money->intlFormatter()->format($shopBasket->moneyOriginal) . "</span><br />" . Html::tag('small',
                                    $shopBasket->notes) . "<br />" . \Yii::$app->money->intlFormatter()->format($shopBasket->money) . "<br />" . Html::tag('small',
                                    \Yii::t('skeeks/shop/app', 'Discount') . ": " . $shopBasket->discount_value);
                        } else {
                            return \Yii::$app->money->intlFormatter()->format($shopBasket->money) . "<br />" . Html::tag('small',
                                    $shopBasket->notes);
                        }

                    }
                ],
                [
                    'class' => \yii\grid\DataColumn::className(),
                    'label' => \Yii::t('skeeks/shop/app', 'Sum'),
                    'attribute' => 'price',
                    'format' => 'raw',
                    'value' => function (\skeeks\cms\shop\models\ShopBasket $shopBasket) {
                        return \Yii::$app->money->intlFormatter()->format($shopBasket->money->multiply($shopBasket->quantity));
                    }
                ],
            ]
    ])
    ?>

    <?= Html::endTag('p'); ?>


    <?= Html::beginTag('h2'); ?>
        Покупатель:
    <?= Html::endTag('h2'); ?>
    <?=
    \yii\widgets\DetailView::widget([
        'model' => $order->buyer->relatedPropertiesModel,
        'attributes' => $order->buyer->relatedPropertiesModel->attributes()
    ]);
    ?>

    <? if ($order->pay_system_id) : ?>
        <?= Html::beginTag('h2'); ?>
        Оплата:
        <?= Html::endTag('h2'); ?>

        <?=
        \yii\widgets\DetailView::widget([
            'model' => $order,
            'attributes' => [
                [
                    'attribute' => 'pay_system_id',
                    'value' => $order->paySystem->name
                ],
                [
                    'attribute' => 'payed',
                    'label' => 'Статус оплаты',
                    'value' => $order->payed == 'Y' ? "оплачен" : "не оплачен"
                ],
            ]
        ]);
        ?>

    <? endif; ?>

    <? if ($order->delivery_id) : ?>
        <?= Html::beginTag('h2'); ?>
        Доставка:
        <?= Html::endTag('h2'); ?>

        <?=
        \yii\widgets\DetailView::widget([
            'model' => $order,
            'attributes' => [
                [
                    'attribute' => 'delivery_id',
                    'value' => $order->delivery->name
                ],
            ]
        ]);
        ?>

    <? endif; ?>


    <?= Html::beginTag('h2'); ?>
        Итого:
    <?= Html::endTag('h2'); ?>

    <?= Html::beginTag('p'); ?>
        Стоимость товаров: <?= Html::tag('b', \Yii::$app->money->intlFormatter()->format($order->basketsMoney)); ?><br/>
        Стоимость доставки: <?= Html::tag('b', \Yii::$app->money->intlFormatter()->format($order->moneyDelivery)); ?><br/>
        К оплате: <?= Html::tag('b', \Yii::$app->money->intlFormatter()->format($order->money)); ?>
    <?= Html::endTag('p'); ?>

    <?= Html::beginTag('p'); ?>
    <?= \Yii::t('skeeks/shop/app', 'The details of the order, you can track on the page'); ?>: <?= Html::a($url, $url); ?>
    <?= Html::endTag('p'); ?>
</div>
