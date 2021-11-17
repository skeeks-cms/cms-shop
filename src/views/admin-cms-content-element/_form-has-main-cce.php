<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */
/* @var $this yii\web\View */
/* @var $shopProduct \skeeks\cms\shop\models\ShopProduct */
/* @var $this yii\web\View */
/* @var $controller \skeeks\cms\shop\controllers\AdminCmsContentElementController */
/* @var $action \skeeks\cms\backend\actions\BackendModelCreateAction|\skeeks\cms\backend\actions\IHasActiveForm */
/* @var $model \skeeks\cms\shop\models\ShopCmsContentElement */
/* @var $shopStoreProducts \skeeks\cms\shop\models\ShopStoreProduct[] */
/* @var $relatedModel \skeeks\cms\relatedProperties\models\RelatedPropertiesModel */
/* @var $shopContent \skeeks\cms\shop\models\ShopContent */
/* @var $shopSubproductContentElement \skeeks\cms\shop\models\ShopCmsContentElement */


?>

<? $fieldSet = $form->fieldSet(\Yii::t('skeeks/shop/app', 'Товарные данные')); ?>

<? if (in_array($shopProduct->product_type, [
    \skeeks\cms\shop\models\ShopProduct::TYPE_OFFER,
    \skeeks\cms\shop\models\ShopProduct::TYPE_SIMPLE,
])) : ?>

    <?= \skeeks\cms\modules\admin\widgets\BlockTitleWidget::widget([
        'content' => \Yii::t('skeeks/shop/app', 'Main prices'),
    ]) ?>


    <? if ($productPrices) : ?>
        <? foreach ($productPrices as $productPrice) : ?>
            <div class="form-group">
                <div class="row sx-inline-row">
                    <div class="col-md-3 text-md-right my-auto">
                        <label class="control-label"><?= $productPrice->typePrice->name; ?></label>
                    </div>
                    <div class="col-md-9">
                        <div class="d-flex flex-row sx-measure-row">
                            <div class="my-auto" style="padding-right: 5px;">
                                <?= \yii\helpers\Html::textInput("prices[".$productPrice->typePrice->id."][price]", $productPrice->price, [
                                    'class' => 'form-control',
                                ]); ?>
                            </div>
                            <div class="my-auto">
                                <?= \skeeks\cms\widgets\Select::widget([
                                    'name'          => "prices[".$productPrice->typePrice->id."][currency_code]",
                                    'value'         => $productPrice->currency_code,
                                    'allowDeselect' => false,
                                    'items'         => \yii\helpers\ArrayHelper::map(
                                        \Yii::$app->money->activeCurrencies, 'code', 'code'
                                    ),
                                ]) ?>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        <? endforeach; ?>
    <? endif; ?>




    <?= \skeeks\cms\modules\admin\widgets\BlockTitleWidget::widget([
        'content' => \Yii::t('skeeks/shop/app', 'The number and account'),
    ]); ?>



    <?= $form->field($shopProduct, "quantity")
        ->widget(\skeeks\cms\backend\widgets\forms\NumberInputWidget::class, [
            'options' => [
                'step' => 0.0001,
            ],
            'append'  => $shopProduct->measure ? $shopProduct->measure->symbol : "",
        ]);
    //->label("Доступное количество " . $shopProduct->measure->symbol);
    ?>

    <?
    if ($model->cms_site_id) {
        $site_id = $model->cms_site_id;
    } else {
        $site_id = \Yii::$app->skeeks->site->id;
    }
    $shopStores = \skeeks\cms\shop\models\ShopStore::find()->where(['cms_site_id' => $site_id])->all();
    ?>

    <? foreach ($shopStores as $shopStore) : ?>
        <? foreach ($shopStoreProducts as $shopStoreProduct) : ?>
            <? if ($shopStoreProduct->shop_store_id == $shopStore->id) : ?>
                <div class="form-group">
                    <div class="row sx-inline-row">
                        <div class="col-md-3 text-md-right my-auto">
                            <label class="control-label">Склад: <?= $shopStore->name; ?></label>
                        </div>
                        <div class="col-md-9">
                            <?= \skeeks\cms\backend\widgets\forms\NumberInputWidget::widget([
                                'name'    => "stores[".$shopStore->id."][quantity]",
                                'value'   => $shopStoreProduct->quantity,
                                'options' => [
                                    'class' => 'form-control',
                                    'step'  => 0.0001,
                                ],
                                'append'  => $shopProduct->measure ? $shopProduct->measure->symbol : "",
                            ]) ?>
                        </div>
                    </div>
                </div>
            <? endif; ?>
        <? endforeach; ?>
    <? endforeach; ?>


<? endif; ?>

<? $fieldSet::end(); ?>