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
/* @var $shopStoreProduct \skeeks\cms\shop\models\ShopStoreProduct */

//Родительский общий товар, указан если создается предложение к товару
$parent_content_element_id = null;
//Товар поставщика, из которого создается главный товар
$shopStoreProduct = @$shopStoreProduct;

//Разрешено ли менять тип товара?
$allowChangeProductType = true;
//Показывать управление ценами
$isShowPrices = true;
$isShowNdsSettings = true;
$isShowMeasureRatio = true;
$isShowMeasureQuantity = true;
$isShowMeasureCode = true;
$isShowQuantity = true;
$isAllowChangeSupplier = true;
$possibleProductTypes = \skeeks\cms\shop\models\ShopProduct::possibleProductTypes();
/**
 * @var $shopContent \skeeks\cms\shop\models\ShopContent
 */
$shopContent = \skeeks\cms\shop\models\ShopContent::find()->where(['content_id' => $contentModel->id])->one();
/*if ($shopContent->childrenContent) {
    $allowChangeProductType = true;*/

/*if ($shopProduct->shop_supplier_id) {
    $shopProduct->product_type = \skeeks\cms\shop\models\ShopProduct::TYPE_SIMPLE;
    $allowChangeProductType = false;
}*/
/*}*/


if ($model->isNewRecord) {

    if ($tree_id = \Yii::$app->request->get("tree_id")) {
        $model->tree_id = $tree_id;
    }

    //Если создаем товар модификацию
    if ($parent_content_element_id = \Yii::$app->request->get("parent_content_element_id")) {
        $allowChangeProductType = false;
    }

    //Если создается новый товар и указан товар поставщика
    if ($shopStoreProduct) {
        $allowChangeProductType = false;
        $isShowPrices = false;
        $isShowNdsSettings = false;
        $isShowMeasureQuantity = false;
        $isShowMeasureRatio = true;
        $isShowMeasureCode = true;
        $isShowQuantity = false;
    }

    \yii\helpers\ArrayHelper::remove($possibleProductTypes, \skeeks\cms\shop\models\ShopProduct::TYPE_OFFER);

} else {
    //Товар не новый уже и у него заданы товары поставщика
    /*if ($model->shopSupplierElements) {
        $allowChangeProductType = true;
        $isAllowChangeSupplier = false;
        $isShowPrices = false;
        $isShowNdsSettings = false;
        $isShowMeasureCode = true;
        $isShowMeasureRatio = true;
        $isShowQuantity = false;
        $isShowMeasureQuantity = false;

        \yii\helpers\ArrayHelper::remove($possibleProductTypes, \skeeks\cms\shop\models\ShopProduct::TYPE_OFFERS);
    }*/
}

if ($shopProduct->tradeOffers) {
    $allowChangeProductType = false;
    $shopProduct->product_type = \skeeks\cms\shop\models\ShopProduct::TYPE_OFFERS;
}

$isChangeParrentElement = false;
if ($shopProduct->product_type == \skeeks\cms\shop\models\ShopProduct::TYPE_OFFER) {
    $isChangeParrentElement = true;
}


?>

<? $fieldSet = $form->fieldSet(\Yii::t('skeeks/shop/app', 'Товарные данные')); ?>

<?
/*if ($shopStoreProduct && $model->isNewRecord) {
    $siteClass = \Yii::$app->skeeks->siteClass;
    $defaultSite = $siteClass::find()->where(['is_default' => 1])->one();
    $model->cms_site_id = $defaultSite->id;
    echo "<div style='display: none;'>" . $form->field($model, 'cms_site_id') . "</div>";
}*/
?>

<? if ($allowChangeProductType === false) : ?>
    <div style="display: none;">
        <?= $form->fieldSelect($shopProduct, 'product_type',
            \skeeks\cms\shop\models\ShopProduct::possibleProductTypes()); ?>
    </div>
<? else : ?>
    <?= $form->fieldSelect($shopProduct, 'product_type', $possibleProductTypes, [
        'options' => [
            'data-form-reload' => "true",
        ],
    ]); ?>
<? endif; ?>


<? if (in_array($shopProduct->product_type, [
    \skeeks\cms\shop\models\ShopProduct::TYPE_OFFER,
    \skeeks\cms\shop\models\ShopProduct::TYPE_SIMPLE,
])) : ?>

    <? if ($isChangeParrentElement) : ?>
        <?= $form->field($shopProduct, 'offers_pid')->widget(
        /*\skeeks\cms\widgets\AjaxSelectModel::class,
        [
            'modelClass' => \skeeks\cms\shop\models\ShopCmsContentElement::class,
            'searchQuery' => function($word = '') {
                $query = \skeeks\cms\shop\models\ShopCmsContentElement::find()->cmsSite()->joinWith("shopProduct as sp");
                $query->andWhere(['sp.product_type' => \skeeks\cms\shop\models\ShopProduct::TYPE_OFFERS]);

                if ($word) {
                    $query->search($word);
                }

                return $query;
            },
        ]*/

            \skeeks\cms\backend\widgets\SelectModelDialogContentElementWidget::class,
            [
                'content_id'  => $model->content_id,
                'dialogRoute' => [
                    '/shop/admin-cms-content-element',
                    'findex' => [
                        'shop_product_type' => [\skeeks\cms\shop\models\ShopProduct::TYPE_OFFERS],
                    ],
                ],
            ]
        )->label('Товар содержащий модификации');
        ?>
    <? endif; ?>

    <? if ($isAllowChangeSupplier) : ?>
        <? /*= $form->fieldSelect($shopProduct, "shop_supplier_id", \yii\helpers\ArrayHelper::map(\skeeks\cms\shop\models\ShopSupplier::find()->all(), 'id', 'name'), [
            'allowDeselect' => true,
            'options'       => [
                'data-form-reload' => "true",
            ],
        ]); */ ?><!--
        --><? /*= $form->field($shopProduct, "supplier_external_id"); */ ?>


    <? endif; ?>


    <? if ($isShowPrices) : ?>
        <? if ($productPrices) : ?>
            <?= \skeeks\cms\modules\admin\widgets\BlockTitleWidget::widget([
                'content' => \Yii::t('skeeks/shop/app', 'Main prices'),
            ]) ?>

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

        <? /* elseif ($shopStoreProduct): */ ?><!--
        <? /* $alert = \yii\bootstrap\Alert::begin([
            'closeButton' => false,
            'options'     => [
                'class' => 'alert-default text-center',
            ],
        ]); */ ?>
        Цена по этому товару будет рассчитана автоматически.
        <? /* $alert::end(); */ ?>

    <? /* elseif ($model->shopSupplierElements) : */ ?>
        <? /* $alert = \yii\bootstrap\Alert::begin([
            'closeButton' => false,
            'options'     => [
                'class' => 'alert-default text-center',
            ],
        ]); */ ?>
        Цена по этому товару рассчитывается автоматически из данных поставщиков.
        --><? /* $alert::end(); */ ?>
    <? endif; ?>



    <?= \skeeks\cms\modules\admin\widgets\BlockTitleWidget::widget([
        'content' => \Yii::t('skeeks/shop/app', 'The number and account'),
    ]); ?>

    <div class="row no-gutters">

        <? if ($isShowMeasureCode) : ?>
            <div class="col-md-4">
                <?= $form->fieldSelect($shopProduct, 'measure_code', \yii\helpers\ArrayHelper::map(
                    \skeeks\cms\measure\models\CmsMeasure::find()->orderBy(['priority' => SORT_ASC])->all(),
                    'code',
                    'asShortText'
                ), [
                    "options" => [
                        \skeeks\cms\helpers\RequestResponse::DYNAMIC_RELOAD_FIELD_ELEMENT => "true",
                    ],
                ]); ?>
            </div>
        <? endif; ?>

        <? if ($isShowMeasureRatio) : ?>
            <div class="col-md-4">
                <?= $form->field($shopProduct, 'measure_ratio')
                    ->widget(\skeeks\cms\backend\widgets\forms\NumberInputWidget::class, [
                        'dynamicReload' => true,
                        'append'        => $shopProduct->measure ? $shopProduct->measure->symbol : "",
                        'options'       => [
                            'step' => 0.0001,
                        ],
                    ]); ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($shopProduct, 'measure_ratio_min')
                    ->widget(\skeeks\cms\backend\widgets\forms\NumberInputWidget::class, [
                        //'dynamicReload' => true,
                        'append'  => $shopProduct->measure ? $shopProduct->measure->symbol : "",
                        'options' => [
                            'step' => 0.0001,
                        ],
                    ]); ?>
            </div>
        <? endif; ?>
    </div>

    <?= $form->field($shopProduct, 'measure_matches_jsondata')->widget(
        \skeeks\cms\shop\widgets\admin\ProductMeasureMatchesInputWidget::class
    ); ?>

    <?= \skeeks\cms\modules\admin\widgets\BlockTitleWidget::widget([
        'content' => \Yii::t('skeeks/shop/app', 'Штрихкод'),
    ]); ?>

    <?= $form->field($shopProduct, 'barcodes')->label(false)->widget(
        \skeeks\cms\shop\widgets\admin\ProductBarcodesInputWidget::class
    ); ?>


    <? /* if ($isShowMeasureQuantity) : */ ?><!--

        <? /*= $form->field($shopProduct, "quantity")
            ->widget(\skeeks\cms\backend\widgets\forms\NumberInputWidget::class, [
                'options' => [
                    'step' => 0.0001,
                ],
                'append'  => $shopProduct->measure ? $shopProduct->measure->symbol : "",
            ]);
        */ ?>

    <? /* elseif ($shopStoreProduct): */ ?>
        <? /* $alert = \yii\bootstrap\Alert::begin([
            'closeButton' => false,
            'options'     => [
                'class' => 'alert-default text-center',
            ],
        ]); */ ?>
        Количество по этому товару будет рассчитано автоматически.
        <? /* $alert::end(); */ ?>
    <? /* elseif ($model->shopSupplierElements) : */ ?>
        <? /* $alert = \yii\bootstrap\Alert::begin([
            'closeButton' => false,
            'options'     => [
                'class' => 'alert-default text-center',
            ],
        ]); */ ?>
        Количество по этому товару будет рассчитано автоматически из данных поставщиков.
        <? /* $alert::end(); */ ?>
    --><? /* endif; */ ?>

    <? if ($shopStoreProducts && !$shopStoreProduct) : ?>
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
                                <? /*= \yii\helpers\Html::textInput("stores[".$shopStore->id."][quantity]", $shopStoreProduct->quantity, [
                                                'class' => 'form-control',
                                            ]); */ ?>
                            </div>
                        </div>
                    </div>
                <? endif; ?>
            <? endforeach; ?>
        <? endforeach; ?>


    <? endif; ?>

    <?= \skeeks\cms\modules\admin\widgets\BlockTitleWidget::widget([
        'content' => \Yii::t('skeeks/shop/app', 'Габариты товара за '.$shopProduct->measure_ratio." ".($shopProduct->measure ? $shopProduct->measure->symbol : "")),
    ]); ?>

    <?= $form->field($shopProduct, 'weight')->widget(
        \skeeks\cms\shop\widgets\admin\SmartWeightInputWidget::class
    ); ?>
    <?= $form->field($shopProduct, 'length')->widget(\skeeks\cms\shop\widgets\admin\SmartDimensionsInputWidget::class); ?>
    <?= $form->field($shopProduct, 'width')->widget(\skeeks\cms\shop\widgets\admin\SmartDimensionsInputWidget::class); ?>
    <?= $form->field($shopProduct, 'height')->widget(\skeeks\cms\shop\widgets\admin\SmartDimensionsInputWidget::class); ?>

    <? if ($isShowNdsSettings) : ?>

        <?= \skeeks\cms\modules\admin\widgets\BlockTitleWidget::widget([
            'content' => \Yii::t('skeeks/shop/app', 'Setting prices'),
        ]); ?>

        <?= $form->fieldSelect($shopProduct, 'vat_id', \yii\helpers\ArrayHelper::map(
            \skeeks\cms\shop\models\ShopVat::find()->all(), 'id', 'name'
        )); ?>
        <?= $form->field($shopProduct, 'vat_included')->checkbox([
            'uncheck' => \skeeks\cms\components\Cms::BOOL_N,
            'value'   => \skeeks\cms\components\Cms::BOOL_Y,
        ]); ?>
    <? endif; ?>


        <?= \skeeks\cms\modules\admin\widgets\BlockTitleWidget::widget([
            'content' => \Yii::t('skeeks/shop/app', 'Рейтинг товара'),
        ]); ?>

            
        

<? endif; ?>

<div class="row">
            <div class="col-md-4">
                <?= $form->field($shopProduct, 'rating_value')
                    ->widget(\skeeks\cms\backend\widgets\forms\NumberInputWidget::class, [
                        'options'       => [
                            'step' => 0.0001,
                        ],
                    ]); ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($shopProduct, 'rating_count')
                    ->widget(\skeeks\cms\backend\widgets\forms\NumberInputWidget::class, [
                        //'dynamicReload' => true,
                        'append'  => "шт",
                        'options' => [
                            'step' => 1,
                        ],
                    ]); ?>
            </div>
        </div>


<? if ($shopContent->childrenContent && $shopProduct->product_type == \skeeks\cms\shop\models\ShopProduct::TYPE_OFFERS) : ?>
    <div id="row">
        <div id="sx-shop-product-tradeOffers" class="col-md-12">

            <? if ($model->isNewRecord) : ?>

                <?= \yii\bootstrap\Alert::widget([
                    'options' =>
                        [
                            'class' => 'alert-warning',
                        ],
                    'body'    => \Yii::t('skeeks/shop/app', 'Управлять предложениями можно после сохранения товара, в отдельной вкладке.'),
                ]); ?>
            <? else: ?>

                <?= \yii\bootstrap\Alert::widget([
                    'options' =>
                        [
                            'class' => 'alert-warning',
                        ],
                    'body'    => \Yii::t('skeeks/shop/app', 'Управлять предложениями можно в отдельной вкладке.'),
                ]); ?>

            <? endif; ?>

        </div>
    </div>
<? endif; ?>


<? $fieldSet::end(); ?>