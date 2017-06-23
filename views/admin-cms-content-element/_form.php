<?php


use yii\helpers\Html;
use skeeks\cms\modules\admin\widgets\form\ActiveFormUseTab as ActiveForm;
use skeeks\cms\models\Tree;
use skeeks\cms\modules\admin\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $model \skeeks\cms\models\CmsContentElement */
/* @var $relatedModel \skeeks\cms\relatedProperties\models\RelatedPropertiesModel */
/* @var $shopProduct \skeeks\cms\shop\models\ShopProduct */

/* @var $this yii\web\View */
/* @var $controller \skeeks\cms\backend\controllers\BackendModelController */
/* @var $action \skeeks\cms\backend\actions\BackendModelCreateAction|\skeeks\cms\backend\actions\IHasActiveForm */
/* @var $model \skeeks\cms\models\CmsLang */
$controller = $this->context;
$action     = $controller->action;
?>

<?/*= $this->render('@skeeks/cms/views/admin-cms-content-element/_form', [
    'model' => $model
])*/?>

<?php

/* @var $this yii\web\View */
/* @var $model \skeeks\cms\models\CmsContentElement */
/* @var $relatedModel \skeeks\cms\relatedProperties\models\RelatedPropertiesModel */

 if ($model->isNewRecord)
 {
     if ($content_id = \Yii::$app->request->get("content_id"))
     {
         $contentModel = \skeeks\cms\models\CmsContent::findOne($content_id);
         $model->content_id = $content_id;
     }

     if ($tree_id = \Yii::$app->request->get("tree_id"))
     {
         $model->tree_id = $tree_id;
     }

     if ($parent_content_element_id = \Yii::$app->request->get("parent_content_element_id"))
     {
         $model->parent_content_element_id = $parent_content_element_id;
     }

     if ($contentModel->parent_content_id && $model->parentContentElement)
     {
         $model->name = $model->parentContentElement->name;
     }
 } else
 {
     $contentModel = $model->cmsContent;
 }

/**
 * @var $shopContent \skeeks\cms\shop\models\ShopContent
 */
$shopContent = \skeeks\cms\shop\models\ShopContent::find()->where(['content_id' => $contentModel->id])->one();
?>

<?php $form = $action->beginActiveForm([
    'id'                                            => 'sx-dynamic-form',
    'enableAjaxValidation'                          => false,
    'enableClientValidation'                        => false,
]); ?>

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
        'form'              => $form,
        'contentModel'      => $contentModel,
        'model'             => $model,
    ]); ?>

    <?= $this->render('@skeeks/cms/views/admin-cms-content-element/_form-announce', [
        'form'              => $form,
        'contentModel'      => $contentModel,
        'model'             => $model,
    ]); ?>

    <?= $this->render('@skeeks/cms/views/admin-cms-content-element/_form-detail', [
        'form'              => $form,
        'contentModel'      => $contentModel,
        'model'             => $model,
    ]); ?>

    <?= $this->render('@skeeks/cms/views/admin-cms-content-element/_form-sections', [
        'form'              => $form,
        'contentModel'      => $contentModel,
        'model'             => $model,
    ]); ?>

    <?= $this->render('@skeeks/cms/views/admin-cms-content-element/_form-seo', [
        'form'              => $form,
        'contentModel'      => $contentModel,
        'model'             => $model,
    ]); ?>

    <?= $this->render('@skeeks/cms/views/admin-cms-content-element/_form-images', [
        'form'              => $form,
        'contentModel'      => $contentModel,
        'model'             => $model,
    ]); ?>

    <?= $this->render('@skeeks/cms/views/admin-cms-content-element/_form-additionaly', [
        'form'              => $form,
        'contentModel'      => $contentModel,
        'model'             => $model,
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
    } else
    {
        $('#sx-shop-product-simple').show();
        $('#sx-shop-product-tradeOffers').hide();

        $('input', $('#sx-shop-product-simple')).removeAttr('disabled');
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
        <?= $form->fieldSelect($shopProduct, 'product_type', \skeeks\cms\shop\models\ShopProduct::possibleProductTypes()); ?>
    <? endif; ?>

    <div id="sx-shop-product-simple">

        <?= \skeeks\cms\modules\admin\widgets\BlockTitleWidget::widget([
                'content' => \Yii::t('skeeks/shop/app', 'Main prices')
            ])?>

        <div class="row">
            <div class="col-md-3">
                <?= $form->field($shopProduct, 'purchasing_price')->textInput(); ?>
            </div>
            <div class="col-md-2">
                <?= $form->fieldSelect($shopProduct, 'purchasing_currency', \yii\helpers\ArrayHelper::map(
                    \Yii::$app->money->activeCurrencies, 'code', 'code'
                )); ?>
            </div>
        </div>


        <div class="row">
            <div class="col-md-3">
                <?= $form->field($shopProduct, 'baseProductPriceValue')->textInput()
                    ->label(\Yii::t('skeeks/shop/app', 'Base price')." (".\Yii::t('skeeks/shop/app', 'Price type')." «{$baseProductPrice->typePrice->name}»)"); ?>
            </div>
            <div class="col-md-2">
                <?= $form->fieldSelect($shopProduct, 'baseProductPriceCurrency', \yii\helpers\ArrayHelper::map(
                    \Yii::$app->money->activeCurrencies, 'code', 'code'
                ))->label(\Yii::t('skeeks/shop/app', 'Currency base price')); ?>
            </div>

            <div class="col-md-2">
                <label>&nbsp;</label>
                <p>
                    <?= \skeeks\cms\shop\widgets\admin\PropductPriceChangeAdminWidget::widget([
                        'productPrice' => $shopProduct->baseProductPrice
                    ])?>
                </p>
            </div>
        </div>

        <? if ($productPrices) : ?>
            <?/*= \skeeks\cms\modules\admin\widgets\BlockTitleWidget::widget([
                'content' => \Yii::t('skeeks/shop/app', 'Additional costs')
            ])*/?>

            <hr />

            <? foreach ($productPrices as $productPrice) : ?>

                <div class="row">
                    <div class="col-md-3">
                        <label><?= $productPrice->typePrice->name; ?></label>
                        <?= Html::textInput("prices[" . $productPrice->typePrice->id . "][price]", $productPrice->price, [
                            'class' => 'form-control'
                        ]); ?>
                    </div>
                    <div class="col-md-2">
                        <label>Валюта</label>

                        <?= \skeeks\widget\chosen\Chosen::widget([
                            'name' => "prices[" . $productPrice->typePrice->id . "][currency_code]",
                            'value' => $productPrice->currency_code,
                            'allowDeselect' => false,
                            'items' => \yii\helpers\ArrayHelper::map(
                                \Yii::$app->money->activeCurrencies, 'code', 'code'
                            )
                        ])?>
                    </div>

                    <div class="col-md-2">
                        <label>&nbsp;</label>
                        <p>
                            <?= \skeeks\cms\shop\widgets\admin\PropductPriceChangeAdminWidget::widget([
                                'productPrice' => $productPrice
                            ]); ?>
                        </p>
                    </div>
                </div>

            <? endforeach; ?>

        <? endif; ?>

        <?= \skeeks\cms\modules\admin\widgets\BlockTitleWidget::widget([
            'content' => \Yii::t('skeeks/shop/app', 'The number and account')
        ]); ?>

        <div class="row">
            <div class="col-md-3">
                <?= $form->field($shopProduct, 'quantity')->textInput(); ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($shopProduct, 'quantity_reserved')->textInput(); ?>
            </div>
            <div class="col-md-3">
                <?= $form->fieldSelect($shopProduct, 'measure_id', \yii\helpers\ArrayHelper::map(
                    \skeeks\cms\measure\models\Measure::find()->all(), 'id', 'name'
                )); ?>
            </div>
            <div class="col-md-3">
                    <?= $form->field($shopProduct, 'measure_ratio')->textInput(); ?>
            </div>

            <? if ($shopProduct->shopProductQuantityChanges) : ?>
                <div class="col-md-12" style="margin-bottom: 20px;">
                    <div style="text-align: center;">
                        <?= \skeeks\cms\shop\widgets\admin\PropductQuantityChangeAdminWidget::widget([
                            'product' => $shopProduct
                        ]); ?>
                    </div>
                </div>
            <? endif; ?>

        </div>


        <?= \skeeks\cms\modules\admin\widgets\BlockTitleWidget::widget([
            'content' => \Yii::t('skeeks/shop/app', 'Weight and size')
        ]); ?>

        <div class="row">
            <div class="col-md-3">
                <?= $form->field($shopProduct, 'weight')->textInput([
                    'type' => 'number'
                ]); ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($shopProduct, 'length')->textInput([
                    'type' => 'number'
                ]); ?>

            </div>
            <div class="col-md-3">
                <?= $form->field($shopProduct, 'width')->textInput([
                    'type' => 'number'
                ]); ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($shopProduct, 'height')->textInput([
                    'type' => 'number'
                ]); ?>
            </div>
        </div>


        <?= \skeeks\cms\modules\admin\widgets\BlockTitleWidget::widget([
            'content' => \Yii::t('skeeks/shop/app', 'Setting prices')
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




        <?= \skeeks\cms\modules\admin\widgets\BlockTitleWidget::widget([
            'content' => \Yii::t('skeeks/shop/app', 'Options')
        ]); ?>

            <?= $form->fieldRadioListBoolean($shopProduct, 'quantity_trace'); ?>
            <?= $form->fieldRadioListBoolean($shopProduct, 'can_buy_zero'); ?>
            <?= $form->fieldRadioListBoolean($shopProduct, 'negative_amount_trace'); ?>
            <?= $form->fieldRadioListBoolean($shopProduct, 'subscribe'); ?>
        </div>

<? if ($shopContent->childrenContent) : ?>
    <div id="sx-shop-product-tradeOffers">

        <? if ($model->isNewRecord) : ?>

            <?= \yii\bootstrap\Alert::widget([
                'options' =>
                [
                    'class' => 'alert-warning'
                ],
                'body' => \Yii::t('skeeks/shop/app', 'Management will be available after saving')
            ]); ?>
        <? else:  ?>

            <?= \skeeks\cms\modules\admin\widgets\RelatedModelsGrid::widget([
                'label'             => $shopContent->childrenContent->name,
                'parentModel'       => $model,
                'relation'          => [
                    'content_id'                    => $shopContent->childrenContent->id,
                    'parent_content_element_id'     => $model->id
                ],

                'sort'              => [
                    'defaultOrder' =>
                    [
                        'priority' => 'published_at'
                    ]
                ],

                'controllerRoute'   => 'shop/admin-cms-content-element',
                'gridViewOptions'   => [
                    'columns' => (array) \skeeks\cms\shop\controllers\AdminCmsContentElementController::getColumns($shopContent->childrenContent)
                ],
            ]); ?>

        <? endif; ?>

    </div>
<? endif; ?>


<?= $form->fieldSetEnd() ?>




<? if (!$model->isNewRecord) : ?>
    <?/*= $form->fieldSet(\Yii::t('skeeks/shop/app','Additionally')); */?><!--
        <?/*= $form->fieldSelect($model, 'content_id', \skeeks\cms\models\CmsContent::getDataForSelect()); */?>
    --><?/*= $form->fieldSetEnd() */?>

    <? if ($model->cmsContent->access_check_element == "Y") : ?>
        <?= $form->fieldSet(\Yii::t('skeeks/shop/app','Access')); ?>
            <?= \skeeks\cms\rbac\widgets\adminPermissionForRoles\AdminPermissionForRolesWidget::widget([
                'permissionName'                => $model->permissionName,
                'permissionDescription'         => \Yii::t('skeeks/shop/app','Access to this member') . ': ' . $model->name,
                'label'                         => \Yii::t('skeeks/shop/app','Access to this member'),
            ]); ?>
        <?= $form->fieldSetEnd() ?>
    <? endif; ?>
<? endif; ?>

<? if ($shopContent->childrenContent && $model->cmsContent->getChildrenContents()->andWhere(['!=', 'id', $shopContent->childrenContent->id])->all() ) : ?>

    <? $childContents = $model->cmsContent->getChildrenContents()->andWhere(['!=', 'id', $shopContent->childrenContent->id])->all(); ?>

    <? foreach($childContents as $childContent) : ?>
        <?= $form->fieldSet($childContent->name); ?>

            <? if ($model->isNewRecord) : ?>

                <?= \yii\bootstrap\Alert::widget([
                    'options' =>
                    [
                        'class' => 'alert-warning'
                    ],
                    'body' => \Yii::t('skeeks/shop/app', 'Management will be available after saving')
                ]); ?>
            <? else:  ?>

                <?= \skeeks\cms\modules\admin\widgets\RelatedModelsGrid::widget([
                    'label'             => $childContent->name,
                    'namespace'         => md5($model->className() . $childContent->id),
                    'parentModel'       => $model,
                    'relation'          => [
                        'content_id'                    => $childContent->id,
                        'parent_content_element_id'     => $model->id
                    ],

                    'sort'              => [
                        'defaultOrder' =>
                        [
                            'priority' => 'published_at'
                        ]
                    ],

                    'controllerRoute'   => 'shop/admin-cms-content-element',
                    'gridViewOptions'   => [
                        'columns' => (array) \skeeks\cms\controllers\AdminCmsContentElementController::getColumns($childContent)
                    ],
                ]); ?>

            <? endif; ?>



        <?= $form->fieldSetEnd() ?>
    <? endforeach; ?>
<? endif; ?>




<?= $form->buttonsStandart($model); ?>

    <?php echo $form->errorSummary([$model, $relatedModel, $shopProduct]); ?>
<?php ActiveForm::end(); ?>
