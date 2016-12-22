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
        'searchModel'   => $searchModel,
        'dataProvider'  => $dataProvider
    ]); ?>

    <?= \skeeks\cms\modules\admin\widgets\GridViewStandart::widget([
        'dataProvider'      => $dataProvider,
        'filterModel'       => $searchModel,
        'pjax'              => $pjax,
        'adminController'   => \Yii::$app->controller,
        'columns'           =>
        [
            [
                'class' => \skeeks\cms\grid\CreatedAtColumn::className(),
            ],

            'email',

            [
                'class' => \yii\grid\DataColumn::className(),
                'format' => 'raw',
                'label' => \Yii::t('skeeks/shop/app', 'Good'),
                'value' => function(\skeeks\cms\shop\models\ShopQuantityNoticeEmail $shopQuantityNoticeEmail)
                {
                    if ($shopQuantityNoticeEmail->shopProduct)
                    {
                        return (new \skeeks\cms\modules\admin\widgets\AdminImagePreviewWidget([
                            'image' => $shopQuantityNoticeEmail->shopProduct->cmsContentElement->image,
                            'maxWidth' => "25px"
                        ]))->run() . " " . \yii\helpers\Html::a($shopQuantityNoticeEmail->shopProduct->cmsContentElement->name, $shopQuantityNoticeEmail->shopProduct->cmsContentElement->url, [
                            'target' => "_blank",
                            'data-pjax' => 0,
                        ] ) . "<br /><small>" . \Yii::t('skeeks/shop/app', 'In stock') . ": " . $shopQuantityNoticeEmail->shopProduct->quantity . "</small>";
                    }

                    return null;
                },
            ],

            'name',

            [
                'class' => \skeeks\cms\grid\BooleanColumn::class,
                'attribute' => 'is_notified',
                'trueValue' => true,
                'falseValue' => false,
            ],

            [
                'class' => \skeeks\cms\grid\DateTimeColumnData::class,
                'attribute' => 'notified_at',
            ],

            [
                'class' => \yii\grid\DataColumn::className(),
                'format' => 'raw',
                'label' => \Yii::t('skeeks/shop/app', 'User'),
                'value' => function(\skeeks\cms\shop\models\ShopQuantityNoticeEmail $shopQuantityNoticeEmail)
                {
                    return ( $shopQuantityNoticeEmail->shopFuser && $shopQuantityNoticeEmail->shopFuser->user ? ( new \skeeks\cms\shop\widgets\AdminBuyerUserWidget(['user' => $shopQuantityNoticeEmail->shopFuser->user]) )->run() : \Yii::t('skeeks/shop/app', 'Not authorized') );
                },
            ],
        ]
    ]); ?>

<? $pjax::end(); ?>
