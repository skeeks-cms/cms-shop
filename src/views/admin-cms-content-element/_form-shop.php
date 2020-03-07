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

//Родительский общий товар, указан если создается предложение к товару
$parent_content_element_id = null;
//Товар поставщика, из которого создается главный товар
$shopSubproductContentElement = @$shopSubproductContentElement;

//Разрешено ли менять тип товара?
$allowChangeProductType = false;
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
if ($shopContent->childrenContent) {
    $allowChangeProductType = true;

    if ($shopProduct->shop_supplier_id) {
        $shopProduct->product_type = \skeeks\cms\shop\models\ShopProduct::TYPE_SIMPLE;
        $allowChangeProductType = false;
    }
}


if ($model->isNewRecord) {

    if ($tree_id = \Yii::$app->request->get("tree_id")) {
        $model->tree_id = $tree_id;
    }

    //Если создаем вложенный товар
    if ($parent_content_element_id = \Yii::$app->request->get("parent_content_element_id")) {
        $parent = \skeeks\cms\shop\models\ShopCmsContentElement::findOne($parent_content_element_id);

        $data = $parent->toArray();
        \yii\helpers\ArrayHelper::remove($data, 'image_id');
        \yii\helpers\ArrayHelper::remove($data, 'image_full_id');
        \yii\helpers\ArrayHelper::remove($data, 'imageIds');
        \yii\helpers\ArrayHelper::remove($data, 'fileIds');
        \yii\helpers\ArrayHelper::remove($data, 'code');
        \yii\helpers\ArrayHelper::remove($data, 'id');
        $model->setAttributes($data);
        $model->relatedPropertiesModel->setAttributes($parent->relatedPropertiesModel->toArray());
        $model->parent_content_element_id = $parent_content_element_id;

        $shopProduct->product_type = \skeeks\cms\shop\models\ShopProduct::TYPE_OFFER;
        $allowChangeProductType = false;
        $this->registerCss(<<<CSS
.field-shopcmscontentelement-tree_id,
.field-shopcmscontentelement-parent_content_element_id {
    display: none;
}
CSS
        );
    }

    if ($contentModel->parent_content_id && $model->parentContentElement) {
        $model->name = $model->parentContentElement->name;
    }

    //Если создается новый товар и указан товар поставщика
    if ($shopSubproductContentElement) {
        $allowChangeProductType = false;
        $isShowPrices = false;
        $isShowNdsSettings = false;
        $isShowMeasureQuantity = false;
        $isShowMeasureRatio = true;
        $isShowMeasureCode = false;
        $isShowQuantity = false;
    }
} else {
    //Товар не новый уже и у него заданы товары поставщика
    if ($shopProduct->shopSupplierProducts) {
        $allowChangeProductType = true;
        $isAllowChangeSupplier = false;
        $isShowPrices = false;
        $isShowNdsSettings = false;
        $isShowMeasureCode = true;
        $isShowMeasureRatio = true;
        $isShowQuantity = false;
        $isShowMeasureQuantity = false;

        \yii\helpers\ArrayHelper::remove($possibleProductTypes, \skeeks\cms\shop\models\ShopProduct::TYPE_OFFERS);
    }
}

if ($model->parent_content_element_id) {
    $this->registerCss(<<<CSS
.field-shopcmscontentelement-tree_id {
    display: none;
}
CSS
    );
}

if ($shopProduct->tradeOffers) {
    $allowChangeProductType = false;
    $shopProduct->product_type = \skeeks\cms\shop\models\ShopProduct::TYPE_OFFERS;
}

$isChangeParrentElement = false;
if ($shopContent->childrenContent) {
    if ($shopProduct->product_type == \skeeks\cms\shop\models\ShopProduct::TYPE_OFFER) {
        $isChangeParrentElement = true;
    }
}

if ($shopSubproductContentElement || !\skeeks\cms\shop\models\ShopSupplier::find()->exists()) {
    $isAllowChangeSupplier = false;
}
?>

<? $fieldSet = $form->fieldSet(\Yii::t('skeeks/shop/app', 'Товарные данные')); ?>


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
        <?= $form->field($model, 'parent_content_element_id')->widget(
            \skeeks\cms\backend\widgets\SelectModelDialogContentElementWidget::class,
            [
                'content_id'  => $shopContent->childrenContent->id,
                'dialogRoute' => [
                    '/shop/admin-cms-content-element',
                    'findex' => [
                        'shop_product_type' => [\skeeks\cms\shop\models\ShopProduct::TYPE_SIMPLE, \skeeks\cms\shop\models\ShopProduct::TYPE_OFFERS],
                    ],
                ],
            ]
        )->label('Общий товар с предложениями');
        ?>
    <? endif; ?>

    <? if ($isAllowChangeSupplier) : ?>
        <?= $form->fieldSelect($shopProduct, "shop_supplier_id", \yii\helpers\ArrayHelper::map(\skeeks\cms\shop\models\ShopSupplier::find()->all(), 'id', 'name'), [
            'allowDeselect' => true,
            'options'       => [
                'data-form-reload' => "true",
            ],
        ]); ?>
        <?= $form->field($shopProduct, "supplier_external_id"); ?>

        <? if ($shopProduct->shop_supplier_id) : ?>
            <?= $form->field($shopProduct, 'main_pid')->widget(
                \skeeks\cms\backend\widgets\SelectModelDialogContentElementWidget::class,
                [
                    'options'     => [
                        'data-form-reload' => "true",
                    ],
                    'content_id'  => $contentModel->id,
                    'dialogRoute' => [
                        '/shop/admin-cms-content-element',
                        'w3-submit-key' => "1",
                        'findex'        => [
                            'shop_supplier_id' => [
                                'mode' => 'empty',
                            ],
                        ],
                    ],
                ]
            );
            ?>
        <? endif; ?>
    <? endif; ?>


    <?= \skeeks\cms\modules\admin\widgets\BlockTitleWidget::widget([
        'content' => \Yii::t('skeeks/shop/app', 'Main prices'),
    ]) ?>


    <? if ($isShowPrices) : ?>
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
                                    <?= \skeeks\widget\chosen\Chosen::widget([
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
    <? elseif ($shopSubproductContentElement): ?>
        <? $alert = \yii\bootstrap\Alert::begin([
            'closeButton' => false,
            'options'     => [
                'class' => 'alert-default text-center',
            ],
        ]); ?>
        Цена по этому товару будет рассчитана автоматически.
        <? $alert::end(); ?>
    <? elseif ($shopProduct->shopSupplierProducts) : ?>
        <? $alert = \yii\bootstrap\Alert::begin([
            'closeButton' => false,
            'options'     => [
                'class' => 'alert-default text-center',
            ],
        ]); ?>
        Цена по этому товару рассчитывается автоматически из данных поставщиков.
        <? $alert::end(); ?>
        <? /* foreach ($shopProduct->shopSupplierProducts as $shopSupplierProduct) : */ ?><!--
            <? /*= $shopSupplierProduct->shopSupplier */ ?>
        --><? /* endforeach; */ ?>
    <? endif; ?>



    <?= \skeeks\cms\modules\admin\widgets\BlockTitleWidget::widget([
        'content' => \Yii::t('skeeks/shop/app', 'The number and account'),
    ]); ?>

    <? if ($isShowMeasureCode) : ?>
        <?= $form->fieldSelect($shopProduct, 'measure_code', \yii\helpers\ArrayHelper::map(
                \skeeks\cms\measure\models\CmsMeasure::find()->orderBy(['priority' => SORT_ASC])->all(),
                'code',
                'asShortText'
        ), [
            "options" => [
                \skeeks\cms\helpers\RequestResponse::DYNAMIC_RELOAD_FIELD_ELEMENT => "true",
            ],
        ]); ?>
    <? endif; ?>

    <? if ($isShowMeasureRatio) : ?>
        <?= $form->field($shopProduct, 'measure_ratio')
            ->widget(\skeeks\cms\backend\widgets\forms\NumberInputWidget::class, [
                'dynamicReload' => true,
                'append'        => $shopProduct->measure->symbol,
            ]); ?>
    <? endif; ?>

    <?= $form->field($shopProduct, 'measure_matches_jsondata')->widget(
        \skeeks\cms\shop\widgets\admin\ProductMeasureMatchesInputWidget::class
    ); ?>


    <? if ($isShowMeasureQuantity) : ?>

        <?= $form->field($shopProduct, "quantity")
            ->widget(\skeeks\cms\backend\widgets\forms\NumberInputWidget::class, [
                'options' => [
                    'step' => 0.0001,
                ],
                'append'  => $shopProduct->measure->symbol,
            ]);
        //->label("Доступное количество " . $shopProduct->measure->symbol);
        ?>

    <? elseif ($shopSubproductContentElement): ?>
        <? $alert = \yii\bootstrap\Alert::begin([
            'closeButton' => false,
            'options'     => [
                'class' => 'alert-default text-center',
            ],
        ]); ?>
        Количество по этому товару будет рассчитано автоматически.
        <? $alert::end(); ?>
    <? elseif ($shopProduct->shopSupplierProducts) : ?>
        <? $alert = \yii\bootstrap\Alert::begin([
            'closeButton' => false,
            'options'     => [
                'class' => 'alert-default text-center',
            ],
        ]); ?>
        Количество по этому товару будет рассчитано автоматически из данных поставщиков.
        <? $alert::end(); ?>
        <? /* foreach ($shopProduct->shopSupplierProducts as $shopSupplierProduct) : */ ?><!--
            <? /*= $shopSupplierProduct->shopSupplier */ ?>
        --><? /* endforeach; */ ?>
    <? endif; ?>

    <? if ($shopStoreProducts && $shopProduct->shop_supplier_id) : ?>
        <?
        /**
         * @var $shopSuppliers \skeeks\cms\shop\models\ShopSupplier[]
         */
        $querySuppliers = \skeeks\cms\shop\models\ShopSupplier::find();
        $querySuppliers->andWhere(['id' => $shopProduct->shop_supplier_id]);


        $shopSuppliers = $querySuppliers->all(); ?>
        <? if ($shopSuppliers) : ?>
            <? foreach ($shopSuppliers as $shopSupplier) : ?>
                <div class="sx-supplier-quantity" style="background: #efefef;
    padding: 10px;
    margin: 10px;">
                    <div class="row">
                        <div class="col-md-3"></div>
                        <div class="col-md-9">
                            <h4><?= $shopSupplier->name; ?></h4>
                        </div>
                    </div>
                    <? foreach ($shopSupplier->shopStores as $shopStore) : ?>
                        <? foreach ($shopStoreProducts as $shopStoreProduct) : ?>
                            <? if ($shopStoreProduct->shop_store_id == $shopStore->id) : ?>
                                <div class="form-group">
                                    <div class="row sx-inline-row">
                                        <div class="col-md-3 text-md-right my-auto">
                                            <label class="control-label">Склад: <?= $shopStore->name; ?></label>
                                        </div>
                                        <div class="col-md-9">
                                            <?= \skeeks\cms\backend\widgets\forms\NumberInputWidget::widget([
                                                'name' => "stores[".$shopStore->id."][quantity]",
                                                'value' => $shopStoreProduct->quantity,
                                                'options' => [
                                                    'class' => 'form-control',
                                                    'step' => 0.0001,
                                                ],
                                                'append' => $shopProduct->measure->symbol
                                            ])?>
                                            <?/*= \yii\helpers\Html::textInput("stores[".$shopStore->id."][quantity]", $shopStoreProduct->quantity, [
                                                'class' => 'form-control',
                                            ]); */?>
                                        </div>
                                    </div>
                                </div>
                            <? endif; ?>
                        <? endforeach; ?>
                    <? endforeach; ?>
                </div>

            <? endforeach; ?>
        <? endif; ?>

    <? endif; ?>

    <?= \skeeks\cms\modules\admin\widgets\BlockTitleWidget::widget([
        'content' => \Yii::t('skeeks/shop/app', 'Вес и размеры товара за '.$shopProduct->measure_ratio." ".$shopProduct->measure->symbol),
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
<? endif; ?>


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

                <? /*= \skeeks\cms\modules\admin\widgets\RelatedModelsGrid::widget([
                            'label'       => false,
                            'parentModel' => $model,
                            'relation'    => [
                                'content_id'                => $shopContent->childrenContent->id,
                                'parent_content_element_id' => $model->id,
                            ],

                            'sort' => [
                                'defaultOrder' =>
                                    [
                                        'priority' => 'published_at',
                                    ],
                            ],

                            'controllerRoute' => '/shop/admin-cms-content-element',
                            'gridViewOptions' => [
                                'columns' => (array)\skeeks\cms\shop\controllers\AdminCmsContentElementController::getColumns($shopContent->childrenContent),
                            ],
                        ]); */ ?>

            <? endif; ?>

        </div>
    </div>
<? endif; ?>


<? $fieldSet::end(); ?>