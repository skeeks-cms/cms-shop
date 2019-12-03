<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 06.11.2015
 */
/* @var $this yii\web\View */
/* @var $widget \skeeks\cms\shop\widgets\admin\PropductPriceChangeAdminWidget */
?>
<? if ($widget->productPrice && $widget->productPrice->shopProductPriceChanges) : ?>
<div>
    <a href="#" data-toggle="modal" data-target="#sx-price-change-<?= $widget->id; ?>" class="btn btn-default"><i
                class="fa fa-eye"></i>
        <?= \Yii::t('skeeks/shop/app', 'Changelog'); ?></a></div>

    <? $createModal = \yii\bootstrap\Modal::begin([
        'id'     => 'sx-price-change-'.$widget->id,
        'size'   => \yii\bootstrap\Modal::SIZE_LARGE,
        'header' => '<b>'.\Yii::t('skeeks/shop/app',
                'The history of price changes').": ".(($widget->productPrice && $widget->productPrice->typePrice) ? $widget->productPrice->typePrice->name : "Базовая цена").'</b>',
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
            'query'      => $widget->productPrice->getShopProductPriceChanges(),
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

                [
                    'class' => \yii\grid\DataColumn::class,
                    'label' => \Yii::t('skeeks/shop/app', 'Price'),
                    'value' => function (\skeeks\cms\shop\models\ShopProductPriceChange $model) {
                        return (string)$model->money;
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