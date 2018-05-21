<?php

use skeeks\cms\mail\helpers\Html;

/* @var $this yii\web\View */
/* @var $model \skeeks\cms\shop\models\ShopQuantityNoticeEmail */
?>

<?= Html::beginTag('h1'); ?>
<?= \Yii::t('skeeks/shop/app', 'Notify admission'); ?> #<?= $model->id; ?> <?= \Yii::t('skeeks/shop/app',
    'in site'); ?> <?= \Yii::$app->cms->appName ?>
<?= Html::endTag('h1'); ?>

<?= Html::tag('hr'); ?>

<?= Html::beginTag('h2'); ?>
<?= \Yii::t('skeeks/shop/app', 'The data at the request'); ?>:
<?= Html::endTag('h2'); ?>
<?= Html::beginTag('p'); ?>

<?= \yii\widgets\DetailView::widget([
    'model' => $model,
    'attributes' =>
        [
            [
                'attribute' => 'created_at',
                'value' => \Yii::$app->formatter->asDatetime($model->created_at)
            ],
            'email',
            'name',
        ]
]); ?>
<?= Html::endTag('p'); ?>


<?= Html::beginTag('h2'); ?>
<?= \Yii::t('skeeks/shop/app', 'Product'); ?>:
<?= Html::endTag('h2'); ?>

<?= Html::beginTag('p'); ?>
<?=
\yii\grid\GridView::widget([
    'dataProvider' => new \yii\data\ArrayDataProvider([
        'allModels' => $model->getShopProduct()->all()
    ]),
    'layout' => "{items}",
    'columns' =>
        [

            [
                'class' => \yii\grid\DataColumn::className(),
                'format' => 'raw',
                'value' => function (\skeeks\cms\shop\models\ShopProduct $shopProduct) {
                    if ($shopProduct->cmsContentElement->image) {
                        return Html::img($shopProduct->cmsContentElement->image->absoluteSrc, ['width' => 80]);
                    }
                }
            ],
            [
                'class' => \yii\grid\DataColumn::className(),
                'attribute' => 'name',
                'format' => 'raw',
                'value' => function (\skeeks\cms\shop\models\ShopProduct $shopProduct) {
                    if ($shopProduct->cmsContentElement->absoluteUrl) {
                        return Html::a($shopProduct->cmsContentElement->name,
                            $shopProduct->cmsContentElement->absoluteUrl, [
                                'target' => '_blank',
                                'titla' => "Смотреть на сайте",
                                'data-pjax' => 0
                            ]);
                    } else {
                        return $shopProduct->cmsContentElement->name;
                    }

                }
            ],

            [
                'class' => \yii\grid\DataColumn::className(),
                'label' => \Yii::t('skeeks/shop/app', 'Price'),
                'attribute' => 'price',
                'format' => 'raw',
                'value' => function (\skeeks\cms\shop\models\ShopProduct $shopProduct) {
                    return (string) $shopProduct->baseProductPrice->money . "<br />";
                }
            ],
        ]
])
?>
<?= Html::endTag('p'); ?>

<?= Html::beginTag('p'); ?>
<?= \Yii::t('skeeks/shop/app', 'The request is made from the page'); ?>: <?= Html::a($url, $url); ?>
<?= Html::endTag('p'); ?>