<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 02.06.2015
 */
/* @var $this yii\web\View */
/* @var $searchModel \skeeks\cms\models\Search */
/* @var $dataProvider yii\data\ActiveDataProvider */
?>

<? $pjax = \skeeks\cms\modules\admin\widgets\Pjax::begin(); ?>

<?php echo $this->render('_search', [
    'searchModel'  => $searchModel,
    'dataProvider' => $dataProvider,
]); ?>

<?= \skeeks\cms\modules\admin\widgets\GridViewStandart::widget([
    'dataProvider'    => $dataProvider,
    'filterModel'     => $searchModel,
    'pjax'            => $pjax,
    'adminController' => \Yii::$app->controller,
    'columns'         =>
        [
            [
                'class' => \skeeks\cms\grid\CreatedAtColumn::className(),
                'label' => \Yii::t('skeeks/shop/app', 'Date views'),
            ],
            [
                'class'  => \yii\grid\DataColumn::className(),
                'format' => 'raw',
                'label'  => \Yii::t('skeeks/shop/app', 'User'),
                'value'  => function (\skeeks\cms\shop\models\ShopViewedProduct $shopViewedProduct) {
                    return $shopViewedProduct->shopFuser->user ? (new \skeeks\cms\shop\widgets\AdminBuyerUserWidget(['user' => $shopViewedProduct->shopFuser->user]))->run() : \Yii::t('skeeks/shop/app',
                        'Not authorized');
                },
            ],

            [
                'class'  => \yii\grid\DataColumn::className(),
                'format' => 'raw',
                'label'  => \Yii::t('skeeks/shop/app', 'Good'),
                'value'  => function (\skeeks\cms\shop\models\ShopViewedProduct $shopViewedProduct) {
                    if ($shopViewedProduct->shopProduct) {

                        return (new \skeeks\cms\modules\admin\widgets\AdminImagePreviewWidget([
                                'image'    => $shopViewedProduct->shopProduct->cmsContentElement->image,
                                'maxWidth' => "25px",
                            ]))->run()." ".\yii\helpers\Html::a($shopViewedProduct->shopProduct->cmsContentElement->name,
                                $shopViewedProduct->shopProduct->cmsContentElement->url, [
                                    'target'    => "_blank",
                                    'data-pjax' => 0,
                                ]);
                    }

                    return null;
                },
            ],

        ],
]); ?>

<? $pjax::end(); ?>
