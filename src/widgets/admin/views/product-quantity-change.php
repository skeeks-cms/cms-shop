<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 06.11.2015
 */
/* @var $this yii\web\View */
/* @var $widget \skeeks\cms\shop\widgets\admin\PropductQuantityChangeAdminWidget */
?>
<? if ($widget->product && $widget->product->shopProductQuantityChanges) : ?>
    <a href="#" data-toggle="modal" data-target="#sx-price-change-<?= $widget->id; ?>" class="btn btn-default"><i
                class="fa fa-eye"></i>
        <?= \Yii::t('skeeks/shop/app', 'Changelog'); ?></a>

    <? $createModal = \yii\bootstrap\Modal::begin([
        'id'     => 'sx-price-change-'.$widget->id,
        'size'   => \yii\bootstrap\Modal::SIZE_LARGE,
        'header' => '<b>'.\Yii::t('skeeks/shop/app', 'The history of the availability of product').'</b>',
        'footer' => '
        <button type="button" class="btn btn-default" data-dismiss="modal">'.\Yii::t('skeeks/admin', 'Close').'</button>
    ',
    ]); ?>
    <?
    \skeeks\cms\widgets\Pjax::begin([
        'enablePushState' => false,
    ]);
    ?>
    <?= \yii\grid\GridView::widget([
        'dataProvider' => new \yii\data\ActiveDataProvider([
            'query'      => $widget->product->getShopProductQuantityChanges(),
            'pagination' => [
                'pageSize'  => 10,
                'pageParam' => 'page-'.$widget->id,
            ],
        ]),
        'columns'      =>
            [
                [
                    'class' => \skeeks\cms\grid\CreatedAtColumn::class,
                    'label' => \Yii::t('skeeks/shop/app', 'Date and time changes'),
                ],

                'quantity',
                'quantity_reserved',
                'measure_ratio',

                [
                    'class'     => \yii\grid\DataColumn::class,
                    'attribute' => 'measure_id',
                    'value'     => function (\skeeks\cms\shop\models\ShopProductQuantityChange $shopProductQuantityChange) {
                        return $shopProductQuantityChange->measure->name;
                    },
                ],

                [
                    'class' => \skeeks\cms\grid\CreatedByColumn::class,
                ],
            ],
    ]); ?>

    <?
    \skeeks\cms\widgets\Pjax::end();
    ?>
    <? $createModal::end(); ?>
<? endif; ?>