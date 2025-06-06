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
$isShowDimensions = true;
$isAllowChangeSupplier = true;
$possibleProductTypes = \skeeks\cms\shop\models\ShopProduct::possibleProductTypes();
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

}

if ($shopProduct->tradeOffers) {
    $allowChangeProductType = false;
    $shopProduct->product_type = \skeeks\cms\shop\models\ShopProduct::TYPE_OFFERS;
    $isShowPrices = false;
    $isShowDimensions = false;
    $isShowNdsSettings = false;

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

<?php /*if (YII_ENV_DEV) : */?>

    <?= $form->field($shopProduct, 'brand_id')->widget(
        \skeeks\cms\widgets\AjaxSelectModel::class,
        [
            'options' => [
                'data-form-reload' => "true",
            ],
            'modelClass' => \skeeks\cms\shop\models\ShopBrand::class,
            "ajaxUrl"    => \yii\helpers\Url::to([
                '/cms/ajax/autocomplete-brands',
            ]),

            /*'searchQuery' => function($word = '')  {
                $query = \skeeks\cms\shop\models\BrandCmsContentElement::find()->cmsSite()->contentId(\Yii::$app->shop->contentBrands->id);
                if ($word) {
                    $query->search($word);
                }
                return $query;
            },*/
        ]
    ); ?>
    <?= $form->field($shopProduct, 'brand_sku'); ?>


<?= $form->field($shopProduct, 'country_alpha2')->widget(
    \skeeks\cms\widgets\AjaxSelectModel::class,
    [
        'modelClass' => \skeeks\cms\models\CmsCountry::class,
        'modelPkAttribute' => "alpha2",
        "ajaxUrl" => \yii\helpers\Url::to([
            '/cms/ajax/autocomplete-countries',
        ]),
    ]
); ?>


<?php if ($model->cmsTree && $model->cmsTree->shop_has_collections) : ?>
    <?= $form->field($shopProduct, 'collections')->widget(
        \skeeks\cms\widgets\AjaxSelectModel::class,
        [
            'multiple' => true,
            'modelClass' => \skeeks\cms\shop\models\ShopCollection::class,
            "ajaxUrl" => \yii\helpers\Url::to([
                '/cms/ajax/autocomplete-collections',
                'brand_id' => $shopProduct->brand_id,
                'cms_site_id' => \Yii::$app->skeeks->site->id,
            ]),
        ]
    ); ?>
<?php /*endif; */?>


<?php endif; ?>

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

    <?/*= \skeeks\cms\modules\admin\widgets\BlockTitleWidget::widget([
        'content' => \Yii::t('skeeks/shop/app', 'Штрихкод'),
    ]); */?>

    <?= $form->field($shopProduct, 'barcodes')->widget(
        \skeeks\cms\shop\widgets\admin\ProductBarcodesInputWidget::class
    ); ?>

<? endif; ?>




<? if ($shopProduct->product_type == \skeeks\cms\shop\models\ShopProduct::TYPE_OFFERS) : ?>
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

<?php if ($isShowDimensions) : ?>
<? $fieldSet = $form->fieldSet('Габариты и вес товара с упаковкой за '.$shopProduct->measure_ratio." ".($shopProduct->measure ? $shopProduct->measure->symbol : "")); ?>
    <div class="row no-gutters">
        <div class="col-lg-3 col-md-12 col-12">
            <?= $form->field($shopProduct, 'weight')->widget(
                \skeeks\cms\shop\widgets\admin\SmartWeightShortInputWidget::class
            ); ?>
        </div>
        <div class="col-lg-3 col-md-4 col-12">
            <?= $form->field($shopProduct, 'length')->widget(\skeeks\cms\shop\widgets\admin\SmartDimensionsShortInputWidget::class); ?>
        </div>
        <div class="col-lg-3 col-md-4 col-12">
            <?= $form->field($shopProduct, 'width')->widget(\skeeks\cms\shop\widgets\admin\SmartDimensionsShortInputWidget::class); ?>
        </div>
        <div class="col-lg-3 col-md-4 col-12">
            <?= $form->field($shopProduct, 'height')->widget(\skeeks\cms\shop\widgets\admin\SmartDimensionsShortInputWidget::class); ?>
        </div>
    </div>
<? $fieldSet::end(); ?>
<?php endif; ?>

<?php if (($isShowPrices && $productPrices) || $isShowNdsSettings) : ?>
    <? $fieldSet = $form->fieldSet(\Yii::t('skeeks/shop/app', 'Настройка цен')); ?>

    <? if ($isShowPrices && $productPrices) : ?>


        <div class="row no-gutters">
            <? foreach ($productPrices as $productPrice) : ?>

                <div class="col-md-3 col-12">
                    <div class="form-group">
                        <label class="control-label"><?= $productPrice->typePrice->name; ?></label>
                        <div class="d-flex flex-row sx-measure-row">
                            <div class="input-group">
                                <?= \yii\helpers\Html::textInput("prices[".$productPrice->typePrice->id."][price]", $productPrice->price, [
                                    'class' => 'form-control',
                                ]); ?>
                                <?= \yii\helpers\Html::listBox("prices[".$productPrice->typePrice->id."][currency_code]", $productPrice->currency_code, \yii\helpers\ArrayHelper::map(
                                    \Yii::$app->money->activeCurrencies, 'code', 'code'
                                ), ['size' => 1, 'class' => 'form-control', 'style' => 'max-width: 80px;']) ?>

                            </div>
                        </div>
                    </div>
                </div>


            <? endforeach; ?>
        </div>


    <? endif; ?>

    <? if ($isShowNdsSettings) : ?>
        <div class="row">
            <div class="col-md-4 col-12">
                <?= $form->fieldSelect($shopProduct, 'vat_id', \yii\helpers\ArrayHelper::map(
                    \skeeks\cms\shop\models\ShopVat::find()->all(), 'id', 'name'
                )); ?>
            </div>
        </div>
        <?= $form->field($shopProduct, 'vat_included')->checkbox([
            'uncheck' => \skeeks\cms\components\Cms::BOOL_N,
            'value'   => \skeeks\cms\components\Cms::BOOL_Y,
        ]); ?>
    <? endif; ?>

    <? $fieldSet::end(); ?>

<? endif; ?>



<? $fieldSet = $form->fieldSet('Срок годности и службы, гарантия.'); ?>
    <div class="row no-gutters">
        <div class="col-lg-3 col-md-12 col-12">
            <?= $form->field($shopProduct, 'expiration_time')->widget(
                \skeeks\cms\shop\widgets\admin\SmartExpirationTimeInputWidget::class
            ); ?>
        </div>
        <div class="col-12">
            <?= $form->field($shopProduct, 'expiration_time_comment')->textarea([
                'rows' => 5
            ]); ?>
        </div>

        <div class="col-lg-3 col-md-12 col-12">
            <?= $form->field($shopProduct, 'service_life_time')->widget(
                \skeeks\cms\shop\widgets\admin\SmartExpirationTimeInputWidget::class
            ); ?>
        </div>
        <div class="col-12">
            <?= $form->field($shopProduct, 'service_life_time_comment')->textarea([
                'rows' => 5
            ]); ?>
        </div>

        <div class="col-lg-3 col-md-12 col-12">
            <?= $form->field($shopProduct, 'warranty_time')->widget(
                \skeeks\cms\shop\widgets\admin\SmartExpirationTimeInputWidget::class
            ); ?>
        </div>
        <div class="col-12">
            <?= $form->field($shopProduct, 'warranty_time_comment')->textarea([
                'rows' => 5
            ]); ?>
        </div>
    </div>
<? $fieldSet::end(); ?>



