<?php


use skeeks\cms\modules\admin\widgets\form\ActiveFormUseTab as ActiveForm;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model \skeeks\cms\models\CmsContentElement */
/* @var $relatedModel \skeeks\cms\relatedProperties\models\RelatedPropertiesModel */
/* @var $shopProduct \skeeks\cms\shop\models\ShopProduct */

/* @var $this yii\web\View */
/* @var $controller \skeeks\cms\backend\controllers\BackendModelController */
/* @var $action \skeeks\cms\backend\actions\BackendModelCreateAction|\skeeks\cms\backend\actions\IHasActiveForm */
/* @var $model \skeeks\cms\models\CmsLang */
/* @var $shopStoreProducts \skeeks\cms\shop\models\ShopStoreProduct[] */

$controller = $this->context;
$action = $controller->action;
?>

<? /*= $this->render('@skeeks/cms/views/admin-cms-content-element/_form', [
    'model' => $model
])*/ ?>

<?php

/* @var $this yii\web\View */
/* @var $model \skeeks\cms\models\CmsContentElement */
/* @var $relatedModel \skeeks\cms\relatedProperties\models\RelatedPropertiesModel */

$parent_content_element_id = null;
if ($model->isNewRecord) {
    if ($content_id = \Yii::$app->request->get("content_id")) {
        $contentModel = \skeeks\cms\models\CmsContent::findOne($content_id);
        $model->content_id = $content_id;
    }

    if ($tree_id = \Yii::$app->request->get("tree_id")) {
        $model->tree_id = $tree_id;
    }

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
    }

    if ($contentModel->parent_content_id && $model->parentContentElement) {
        $model->name = $model->parentContentElement->name;
    }
} else {
    $contentModel = $model->cmsContent;
}

/**
 * @var $shopContent \skeeks\cms\shop\models\ShopContent
 */
$shopContent = \skeeks\cms\shop\models\ShopContent::find()->where(['content_id' => $contentModel->id])->one();
?>


<?php $form = $action->beginActiveForm([
    'id'                     => 'sx-dynamic-form',
    'enableAjaxValidation'   => false,
    'enableClientValidation' => false,
]); ?>



<? if ($is_saved && @$is_create) : ?>
    <?php $this->registerJs(<<<JS
    sx.Window.openerWidgetTriggerEvent('model-create', {
        'submitBtn' : '{$submitBtn}'
    });
JS
    ); ?>

<? elseif ($is_saved) : ?>
    <?php $this->registerJs(<<<JS
sx.Window.openerWidgetTriggerEvent('model-update', {
        'submitBtn' : '{$submitBtn}'
    });
JS
    ); ?>
<? endif; ?>

<? if (@$redirect) : ?>
    <?php $this->registerJs(<<<JS
window.location.href = '{$redirect}';
console.log('window.location.href');
console.log('{$redirect}');
JS
    ); ?>
<? endif; ?>


<? $this->registerJs(<<<JS

(function(sx, $, _)
{
    sx.classes.DynamicForm = sx.classes.Component.extend({

        _onDomReady: function()
        {
            var self = this;

            $("[data-form-reload=true]").on('change', function()
            {
                self.update();
            });
        },

        update: function()
        {
            _.delay(function()
            {
                var jForm = $("#sx-dynamic-form");
                jForm.append($('<input>', {'type': 'hidden', 'name' : 'sx-not-submit', 'value': 'true'}));
                jForm.submit();
            }, 200);
        }
    });

    sx.DynamicForm = new sx.classes.DynamicForm();
})(sx, sx.$, sx._);


JS
); ?>


<?php echo $form->errorSummary([$model, $relatedModel, $shopProduct]); ?>
<div style="display: none;">
    <? if ($model->isNewRecord) : ?>
        <? if ($content_id = \Yii::$app->request->get("content_id")) : ?>
            <?= $form->field($model, 'content_id')->hiddenInput(['value' => $content_id])->label(false); ?>
        <? endif; ?>
    <? endif; ?>

    <? if ($contentModel && $contentModel->parentContent) : ?>
        <?= Html::activeHiddenInput($contentModel, 'parent_content_is_required'); ?>
    <? endif; ?>
</div>


<?= $this->render('@skeeks/cms/views/admin-cms-content-element/_form-main', [
    'form'         => $form,
    'contentModel' => $contentModel,
    'model'        => $model,
]); ?>

<?= $this->render('@skeeks/cms/views/admin-cms-content-element/_form-announce', [
    'form'         => $form,
    'contentModel' => $contentModel,
    'model'        => $model,
]); ?>

<?= $this->render('@skeeks/cms/views/admin-cms-content-element/_form-detail', [
    'form'         => $form,
    'contentModel' => $contentModel,
    'model'        => $model,
]); ?>

<?= $this->render('@skeeks/cms/views/admin-cms-content-element/_form-sections', [
    'form'         => $form,
    'contentModel' => $contentModel,
    'model'        => $model,
]); ?>

<?= $this->render('@skeeks/cms/views/admin-cms-content-element/_form-seo', [
    'form'         => $form,
    'contentModel' => $contentModel,
    'model'        => $model,
]); ?>

<?= $this->render('@skeeks/cms/views/admin-cms-content-element/_form-images', [
    'form'         => $form,
    'contentModel' => $contentModel,
    'model'        => $model,
]); ?>

<?= $this->render('@skeeks/cms/views/admin-cms-content-element/_form-additionaly', [
    'form'         => $form,
    'contentModel' => $contentModel,
    'model'        => $model,
]); ?>









<?= $form->fieldSet(\Yii::t('skeeks/shop/app', 'Prices and availability')); ?>
<? if ($shopContent->childrenContent) : ?>
    <?
    $id = Html::getInputId($shopProduct, 'product_type');
    $this->registerJs(<<<JS
function initProductType(jQuery)
{
    if (jQuery.val() == 'offers')
    {
        $('#sx-shop-product-tradeOffers').show();
        $('#sx-shop-product-simple').hide();

        $('input', $('#sx-shop-product-simple')).attr('disabled', 'disabled');
    } else if (jQuery.val() == 'simple' || jQuery.val() == 'offer')
    {
        $('.sx-offer').hide();
        
        $('#sx-shop-product-simple').show();
        $('#sx-shop-product-tradeOffers').hide();

        $('input', $('#sx-shop-product-simple')).removeAttr('disabled');
        
        if (jQuery.val() == 'offer') {
            $('.sx-offer').show();
        }
    } 
}

$('#{$id}').on("change", function()
{
    initProductType($(this));
});
initProductType($('#{$id}'));
JS
    )
    ?>

    <? if ($parent_content_element_id) : ?>
        <div style="display: none;">
            <? $shopProduct->product_type = \skeeks\cms\shop\models\ShopProduct::TYPE_OFFER; ?>
            <?= $form->fieldSelect($shopProduct, 'product_type',
                \skeeks\cms\shop\models\ShopProduct::possibleProductTypes()); ?>
        </div>
    <? else: ?>
        <?= $form->fieldSelect($shopProduct, 'product_type',
            \skeeks\cms\shop\models\ShopProduct::possibleProductTypes()); ?>
    <? endif; ?>


<? endif; ?>

<div id="sx-shop-product-simple">

    <div class="sx-offer">

        <? if ($shopContent->childrenContent) : ?>
            <?= $form->field($model, 'parent_content_element_id')->widget(
                \skeeks\cms\backend\widgets\SelectModelDialogContentElementWidget::class,
                [
                    'content_id'  => $shopContent->childrenContent->id,
                    'dialogRoute' => [
                        '/shop/admin-cms-content-element',
                        'DynamicModel' => [
                            'product_type' => [\skeeks\cms\shop\models\ShopProduct::TYPE_SIMPLE, \skeeks\cms\shop\models\ShopProduct::TYPE_OFFERS],
                        ],
                    ],
                ]
            )
                ->label('Общий товар с предложениями');
            ?>

        <? endif; ?>


    </div>

    <div class="row">
    <div class="col">
            <?= $form->fieldSelect($shopProduct, "shop_supplier_id", \yii\helpers\ArrayHelper::map(\skeeks\cms\shop\models\ShopSupplier::find()->all(), 'id', 'name'), [
                'allowDeselect' => true,
                'options' => [
                    'data-form-reload' => "true",
                ]
            ]); ?>
    </div>
    <div class="col">
        <?= $form->field($shopProduct, "supplier_external_id"); ?>
    </div>
    </div>

    <?= \skeeks\cms\modules\admin\widgets\BlockTitleWidget::widget([
        'content' => \Yii::t('skeeks/shop/app', 'Main prices'),
    ]) ?>

    <!--<div class="row">
        <div class="col-md-3">
            <? /*= $form->field($shopProduct, 'baseProductPriceValue')->textInput()
                ->label($baseProductPrice->typePrice->name); */ ?>
        </div>
        <div class="col-md-2">
            <? /*= $form->fieldSelect($shopProduct, 'baseProductPriceCurrency', \yii\helpers\ArrayHelper::map(
                \Yii::$app->money->activeCurrencies, 'code', 'code'
            ))->label(\Yii::t('skeeks/shop/app', 'Currency base price')); */ ?>
        </div>

        <div class="col-md-2">
            <label>&nbsp;</label>
            <p>
                <? /*= \skeeks\cms\shop\widgets\admin\PropductPriceChangeAdminWidget::widget([
                    'productPrice' => $shopProduct->baseProductPrice,
                ]) */ ?>
            </p>
        </div>
    </div>-->

    <? if ($productPrices) : ?>
        <? /*= \skeeks\cms\modules\admin\widgets\BlockTitleWidget::widget([
                'content' => \Yii::t('skeeks/shop/app', 'Additional costs')
            ])*/ ?>


        <? foreach ($productPrices as $productPrice) : ?>

            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="control-label"><?= $productPrice->typePrice->name; ?></label>
                        <?= Html::textInput("prices[".$productPrice->typePrice->id."][price]", $productPrice->price, [
                            'class' => 'form-control',
                        ]); ?>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label class="control-label">Валюта</label>

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

                <div class="col-md-2">
                    <label>&nbsp;</label>
                    <p>
                        <?= \skeeks\cms\shop\widgets\admin\PropductPriceChangeAdminWidget::widget([
                            'productPrice' => $productPrice,
                        ]); ?>
                    </p>
                </div>
            </div>

        <? endforeach; ?>

    <? endif; ?>

    <?= \skeeks\cms\modules\admin\widgets\BlockTitleWidget::widget([
        'content' => \Yii::t('skeeks/shop/app', 'The number and account'),
    ]); ?>

    <div class="row">
        <div class="col-md-3">
            <?= $form->fieldSelect($shopProduct, 'measure_code', \yii\helpers\ArrayHelper::map(
                \Yii::$app->measure->activeMeasures, 'code', "asText"
            )); ?>
        </div>
    </div>

    <?= $form->field($shopProduct, "quantity"); ?>
    
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
                <div class="col-md-12">
                    <h4><?= $shopSupplier->name; ?></h4>
                </div>
                <? foreach ($shopSupplier->shopStores as $shopStore) : ?>

                    <? foreach ($shopStoreProducts as $shopStoreProduct) : ?>
                        <? if ($shopStoreProduct->shop_store_id == $shopStore->id) : ?>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="control-label"><?= $shopStore->name; ?> (количество)</label>
                                        <?= Html::textInput("stores[".$shopStore->id."][quantity]", $shopStoreProduct->quantity, [
                                            'class' => 'form-control',
                                        ]); ?>
                                    </div>
                                </div>
                            </div>
                        <? endif; ?>
                    <? endforeach; ?>
                <? endforeach; ?>

            <? endforeach; ?>
        <? endif; ?>

    <? endif; ?>


    <? /*= \skeeks\cms\modules\admin\widgets\BlockTitleWidget::widget([
        'content' => \Yii::t('skeeks/shop/app', 'The number and account'),
    ]); */ ?><!--

    <div class="row">
        <div class="col-md-3">
            <? /*= $form->field($shopProduct, 'quantity')->textInput(); */ ?>
        </div>
        <div class="col-md-3">
            <? /*= $form->field($shopProduct, 'quantity_reserved')->textInput(); */ ?>
        </div>

        <div class="col-md-3">
            <? /*= $form->field($shopProduct, 'measure_ratio')->textInput(); */ ?>
        </div>

        <? /* if ($shopProduct->shopProductQuantityChanges) : */ ?>
            <div class="col-md-12" style="margin-bottom: 20px;">
                <div style="text-align: center;">
                    <? /*= \skeeks\cms\shop\widgets\admin\PropductQuantityChangeAdminWidget::widget([
                        'product' => $shopProduct,
                    ]); */ ?>
                </div>
            </div>
        <? /* endif; */ ?>

    </div>-->


    <?= \skeeks\cms\modules\admin\widgets\BlockTitleWidget::widget([
        'content' => \Yii::t('skeeks/shop/app', 'Weight and size'),
    ]); ?>

    <div class="row">
        <div class="col-md-3">
            <?= $form->field($shopProduct, 'weight')->textInput([
                'type' => 'number',
            ]); ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($shopProduct, 'length')->textInput([
                'type' => 'number',
            ]); ?>

        </div>
        <div class="col-md-3">
            <?= $form->field($shopProduct, 'width')->textInput([
                'type' => 'number',
            ]); ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($shopProduct, 'height')->textInput([
                'type' => 'number',
            ]); ?>
        </div>
    </div>


    <?= \skeeks\cms\modules\admin\widgets\BlockTitleWidget::widget([
        'content' => \Yii::t('skeeks/shop/app', 'Setting prices'),
    ]); ?>

    <div class="row">
        <div class="col-md-3">
            <?= $form->fieldSelect($shopProduct, 'vat_id', \yii\helpers\ArrayHelper::map(
                \skeeks\cms\shop\models\ShopVat::find()->all(), 'id', 'name'
            )); ?>
        </div>
        <div class="col-md-3">
            <?= $form->fieldRadioListBoolean($shopProduct, 'vat_included'); ?>
        </div>
    </div>
</div>

<? if ($shopProduct->supplier_external_jsondata) : ?>
<?= \skeeks\cms\modules\admin\widgets\BlockTitleWidget::widget([
        'content' => \Yii::t('skeeks/shop/app', 'Данные от поставщика'),
    ]); ?>
<pre>
    <?= print_r($shopProduct->supplier_external_jsondata, true); ?>
</pre>
<? endif; ?>

<? if ($shopContent->childrenContent) : ?>
    <div id="sx-shop-product-tradeOffers">

        <? if ($model->isNewRecord) : ?>

            <?= \yii\bootstrap\Alert::widget([
                'options' =>
                    [
                        'class' => 'alert-warning',
                    ],
                'body'    => \Yii::t('skeeks/shop/app', 'Management will be available after saving'),
            ]); ?>
        <? else: ?>

            <?= \skeeks\cms\modules\admin\widgets\RelatedModelsGrid::widget([
                'label'       => $shopContent->childrenContent->name,
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
            ]); ?>

        <? endif; ?>

    </div>
<? endif; ?>


<?= $form->fieldSetEnd() ?>



<? if (!$model->isNewRecord) : ?>
    <? /*= $form->fieldSet(\Yii::t('skeeks/shop/app','Additionally')); */ ?><!--
        <? /*= $form->fieldSelect($model, 'content_id', \skeeks\cms\models\CmsContent::getDataForSelect()); */ ?>
    --><? /*= $form->fieldSetEnd() */ ?>

    <? if ($model->cmsContent->access_check_element == "Y") : ?>
        <?= $form->fieldSet(\Yii::t('skeeks/shop/app', 'Access')); ?>
        <?= \skeeks\cms\rbac\widgets\adminPermissionForRoles\AdminPermissionForRolesWidget::widget([
            'permissionName'        => $model->permissionName,
            'permissionDescription' => \Yii::t('skeeks/shop/app', 'Access to this member').': '.$model->name,
            'label'                 => \Yii::t('skeeks/shop/app', 'Access to this member'),
        ]); ?>
        <?= $form->fieldSetEnd() ?>
    <? endif; ?>
<? endif; ?>

<? if ($shopContent->childrenContent && $model->cmsContent->getChildrenContents()->andWhere([
        '!=',
        'id',
        $shopContent->childrenContent->id,
    ])->all()
) : ?>

    <? $childContents = $model->cmsContent->getChildrenContents()->andWhere([
        '!=',
        'id',
        $shopContent->childrenContent->id,
    ])->all(); ?>

    <? foreach ($childContents as $childContent) : ?>
        <?= $form->fieldSet($childContent->name); ?>

        <? if ($model->isNewRecord) : ?>

            <?= \yii\bootstrap\Alert::widget([
                'options' =>
                    [
                        'class' => 'alert-warning',
                    ],
                'body'    => \Yii::t('skeeks/shop/app', 'Management will be available after saving'),
            ]); ?>
        <? else: ?>

            <?= \skeeks\cms\modules\admin\widgets\RelatedModelsGrid::widget([
                'label'       => $childContent->name,
                'namespace'   => md5($model->className().$childContent->id),
                'parentModel' => $model,
                'relation'    => [
                    'content_id'                => $childContent->id,
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
                    'columns' => (array)\skeeks\cms\controllers\AdminCmsContentElementController::getColumns($childContent),
                ],
            ]); ?>

        <? endif; ?>



        <?= $form->fieldSetEnd() ?>
    <? endforeach; ?>
<? endif; ?>


<?= $form->buttonsStandart($model); ?>
<?php echo $form->errorSummary([$model, $relatedModel, $shopProduct]); ?>
<?php ActiveForm::end(); ?>
