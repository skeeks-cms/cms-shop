<?php
use skeeks\cms\mail\helpers\Html;

/* @var $this yii\web\View */
/* @var $order \skeeks\cms\shop\models\ShopOrder */
$url = \yii\helpers\Url::to(['/shop/order/view', 'id' => $order->id], true);
$order->refresh();
?>

<?= Html::beginTag('h1'); ?>
    <?= \skeeks\cms\shop\Module::t('app', 'New order'); ?> #<?= $order->id; ?> <?= \skeeks\cms\shop\Module::t('app', 'in site'); ?> <?= \Yii::$app->cms->appName ?>
<?= Html::endTag('h1'); ?>

<?= Html::beginTag('p'); ?>
    <?= \skeeks\cms\shop\Module::t('app', 'The order #{order_id} created successfully', ['order_id' => $order->id]); ?>.<br>
    К оплате: <b><?= Html::tag('b', \Yii::$app->money->intlFormatter()->format($order->money)); ?></b>
<?= Html::endTag('p'); ?>

<?= Html::beginTag('p'); ?>
    <?=
        \yii\grid\GridView::widget([
            'dataProvider'    => new \yii\data\ArrayDataProvider([
                'allModels' => $order->shopBaskets
            ]),
            'layout' => "{items}",
            'columns'   =>
            [
                /*[
                    'class' => \yii\grid\SerialColumn::className()
                ],*/

                [
                    'class'     => \yii\grid\DataColumn::className(),
                    'format'    => 'raw',
                    'value'     => function(\skeeks\cms\shop\models\ShopBasket $shopBasket)
                    {
                        $widget = new \skeeks\cms\modules\admin\widgets\AdminImagePreviewWidget([
                            'image' => $shopBasket->product->cmsContentElement->image
                        ]);
                        return $widget->run();
                    }
                ],
                [
                    'class' => \yii\grid\DataColumn::className(),
                    'attribute' => 'name',
                    'format' => 'raw',
                    'value' => function(\skeeks\cms\shop\models\ShopBasket $shopBasket)
                    {
                        if ($shopBasket->product)
                        {
                            return Html::a($shopBasket->name, $shopBasket->product->cmsContentElement->url, [
                                'target' => '_blank',
                                'titla' => "Смотреть на сайте",
                                'data-pjax' => 0
                            ]);
                        } else
                        {
                            return $shopBasket->name;
                        }

                    }
                ],

                [
                    'class' => \yii\grid\DataColumn::className(),
                    'attribute' => 'quantity',
                    'value' => function(\skeeks\cms\shop\models\ShopBasket $shopBasket)
                    {
                        return $shopBasket->quantity . " " . $shopBasket->measure_name;
                    }
                ],

                [
                    'class' => \yii\grid\DataColumn::className(),
                    'label' => \skeeks\cms\shop\Module::t('app', 'Price'),
                    'attribute' => 'price',
                    'format' => 'raw',
                    'value' => function(\skeeks\cms\shop\models\ShopBasket $shopBasket)
                    {
                        if ($shopBasket->discount_value)
                        {
                            return "<span style='text-decoration: line-through;'>" . \Yii::$app->money->intlFormatter()->format($shopBasket->moneyOriginal) . "</span><br />". Html::tag('small', $shopBasket->notes) . "<br />" . \Yii::$app->money->intlFormatter()->format($shopBasket->money) . "<br />" . Html::tag('small', \skeeks\cms\shop\Module::t('app', 'Discount').": " . $shopBasket->discount_value);
                        } else
                        {
                            return \Yii::$app->money->intlFormatter()->format($shopBasket->money) . "<br />" . Html::tag('small', $shopBasket->notes);
                        }

                    }
                ],
                [
                    'class' => \yii\grid\DataColumn::className(),
                    'label' => \skeeks\cms\shop\Module::t('app', 'Sum'),
                    'attribute' => 'price',
                    'format' => 'raw',
                    'value' => function(\skeeks\cms\shop\models\ShopBasket $shopBasket)
                    {
                        return \Yii::$app->money->intlFormatter()->format($shopBasket->money->multiply($shopBasket->quantity));
                    }
                ],
            ]
        ])
    ?>
<?= Html::endTag('p'); ?>

<?= Html::beginTag('p'); ?>
    <?= \skeeks\cms\shop\Module::t('app', 'The details of the order, you can track on the page'); ?>: <?= Html::a($url, $url); ?>
<?= Html::endTag('p'); ?>