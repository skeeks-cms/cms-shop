<?php
use skeeks\cms\mail\helpers\Html;

/* @var $this yii\web\View */
/* @var $model \skeeks\cms\shop\models\ShopQuantityNoticeEmail */
?>

<?= Html::beginTag('h1'); ?>
    <?= \Yii::t('skeeks/shop/app', "We've got the goods interesting you"); ?>!
<?= Html::endTag('h1'); ?>

<?= Html::tag('hr'); ?>

<?= Html::beginTag('h3'); ?>
    <?= \Yii::t('skeeks/shop/app', 'Product'); ?>:
<?= Html::endTag('h3'); ?>

<?= Html::beginTag('p'); ?>
    <?=
        \yii\grid\GridView::widget([
            'dataProvider'    => new \yii\data\ArrayDataProvider([
                'allModels' => $model->getShopProduct()->all()
            ]),
            'filterModel' => null,
            'filterUrl' => \yii\helpers\Url::to('/', true),
            'layout' => "{items}",
            'columns'   =>
            [

                [
                    'class'     => \yii\grid\DataColumn::className(),
                    'format'    => 'raw',
                    'value'     => function(\skeeks\cms\shop\models\ShopProduct $shopProduct)
                    {
                        if ($shopProduct->cmsContentElement->image)
                        {
                            return Html::a(
                                Html::img($shopProduct->cmsContentElement->image->absoluteSrc, ['width' => 80])
                                , $shopProduct->cmsContentElement->absoluteUrl, [
                                'target' => '_blank',
                                'titla' => "Смотреть на сайте",
                                'data-pjax' => 0
                            ]);
                        }
                    }
                ],
                [
                    'class' => \yii\grid\DataColumn::className(),
                    'label' => \Yii::t('skeeks/shop/app', 'Product name'),
                    'format' => 'raw',
                    'value' => function(\skeeks\cms\shop\models\ShopProduct $shopProduct)
                    {
                        if ($shopProduct->cmsContentElement->absoluteUrl)
                        {
                            return Html::a($shopProduct->cmsContentElement->name, $shopProduct->cmsContentElement->absoluteUrl, [
                                'target' => '_blank',
                                'titla' => "Смотреть на сайте",
                                'data-pjax' => 0
                            ]);
                        } else
                        {
                            return $shopProduct->cmsContentElement->name;
                        }

                    }
                ],

                [
                    'class' => \yii\grid\DataColumn::className(),
                    'attribute' => 'quantity',
                    'value' => function(\skeeks\cms\shop\models\ShopProduct $shopProduct)
                    {
                        return $shopProduct->quantity . " " . $shopProduct->measure->symbol_rus;
                    }
                ],

                [
                    'class' => \yii\grid\DataColumn::className(),
                    'label' => \Yii::t('skeeks/shop/app', 'Price'),
                    'attribute' => 'price',
                    'format' => 'raw',
                    'value' => function(\skeeks\cms\shop\models\ShopProduct $shopProduct)
                    {
                        return \Yii::$app->money->intlFormatter()->format($shopProduct->baseProductPrice->money) . "<br />" ;
                    }
                ],
            ]
        ])
    ?>
<?= Html::endTag('p'); ?>