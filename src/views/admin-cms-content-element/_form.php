<?php
use yii\helpers\Html;

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

$controller = $this->context;
$action = $controller->action;
$contentModel = $controller->content;
$parent_content_element_id = null;
$shopSubproductContentElement = @$shopSubproductContentElement;
if ($model->isNewRecord) {

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
}

/**
 * @var $shopContent \skeeks\cms\shop\models\ShopContent
 */
$shopContent = \skeeks\cms\shop\models\ShopContent::find()->where(['content_id' => $contentModel->id])->one();
?>

<div class="">
    <div class="sx-main-product-wrapper">
        <?php $form = $action->beginActiveForm(); ?>

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


        <?php echo $form->errorSummary([$model, $relatedModel, $shopProduct]); ?>
        <div style="display: none;">
            <? if ($model->isNewRecord) : ?>
                <? if ($content_id = \Yii::$app->request->get("content_id")) : ?>
                    <?= $form->field($model, 'content_id')->hiddenInput(['value' => $content_id])->label(false); ?>
                <? endif; ?>
            <? endif; ?>

            <? if ($contentModel && $contentModel->parentContent) : ?>
                <?= Html::activeHiddenInput($contentModel, 'is_parent_content_required'); ?>
            <? endif; ?>
        </div>


        <?= $this->render('@skeeks/cms/views/admin-cms-content-element/_form-main', [
            'form'         => $form,
            'contentModel' => $contentModel,
            'model'        => $model,
        ]); ?>

        <?= $this->render('@skeeks/cms/views/admin-cms-content-element/_form-images', [
            'form'         => $form,
            'contentModel' => $contentModel,
            'model'        => $model,
        ]); ?>





        <? $fieldSet = $form->fieldSet(\Yii::t('skeeks/shop/app', 'Prices and availability')); ?>

        <? if ($shopContent->childrenContent) : ?>

            <?
            if ($parent_content_element_id) {
                $shopProduct->product_type = \skeeks\cms\shop\models\ShopProduct::TYPE_OFFER;
            }

            if ($shopProduct->shop_supplier_id) {
                $shopProduct->product_type = \skeeks\cms\shop\models\ShopProduct::TYPE_SIMPLE;
            }

            ?>

            <? if ($parent_content_element_id || $shopProduct->shop_supplier_id) : ?>
                <div style="display: none;">
                    <?= $form->fieldSelect($shopProduct, 'product_type',
                        \skeeks\cms\shop\models\ShopProduct::possibleProductTypes()); ?>
                </div>
            <? else : ?>
                <?= $form->fieldSelect($shopProduct, 'product_type', \skeeks\cms\shop\models\ShopProduct::possibleProductTypes(), [
                    'options' => [
                        'data-form-reload' => "true",
                    ],
                ]); ?>
            <? endif; ?>
        <? endif; ?>

        <? if (in_array($shopProduct->product_type, [
            \skeeks\cms\shop\models\ShopProduct::TYPE_OFFER,
            \skeeks\cms\shop\models\ShopProduct::TYPE_SIMPLE,
        ])) : ?>
            <div id="sx-shop-product-simple">

                <div class="sx-offer">
                    <? if ($shopContent->childrenContent && $shopProduct->product_type == \skeeks\cms\shop\models\ShopProduct::TYPE_OFFER) : ?>
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
                </div>

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

                <?= \skeeks\cms\modules\admin\widgets\BlockTitleWidget::widget([
                    'content' => \Yii::t('skeeks/shop/app', 'Main prices'),
                ]) ?>

                <? if ($productPrices) : ?>
                    <? foreach ($productPrices as $productPrice) : ?>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-md-3 text-md-right  ">
                                    <label class="control-label"><?= $productPrice->typePrice->name; ?></label>
                                </div>
                                <div class="col-md-3">
                                    <?= Html::textInput("prices[".$productPrice->typePrice->id."][price]", $productPrice->price, [
                                        'class' => 'form-control',
                                    ]); ?>
                                </div>
                                <div class="col-md-2">
                                    <?= \skeeks\widget\chosen\Chosen::widget([
                                        'name'          => "prices[".$productPrice->typePrice->id."][currency_code]",
                                        'value'         => $productPrice->currency_code,
                                        'allowDeselect' => false,
                                        'items'         => \yii\helpers\ArrayHelper::map(
                                            \Yii::$app->money->activeCurrencies, 'code', 'code'
                                        ),
                                    ]) ?>
                                </div>
                                <div class="col-md-2">
                                    <?= \skeeks\cms\shop\widgets\admin\PropductPriceChangeAdminWidget::widget([
                                        'productPrice' => $productPrice,
                                    ]); ?>
                                </div>
                            </div>
                        </div>

                    <? endforeach; ?>

                <? endif; ?>

                <?= \skeeks\cms\modules\admin\widgets\BlockTitleWidget::widget([
                    'content' => \Yii::t('skeeks/shop/app', 'The number and account'),
                ]); ?>
                <?= $form->fieldSelect($shopProduct, 'measure_code', \Yii::$app->measure->getDataForSelect()); ?>
                <?= $form->field($shopProduct, 'measure_ratio'); ?>


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

                <?= \skeeks\cms\modules\admin\widgets\BlockTitleWidget::widget([
                    'content' => \Yii::t('skeeks/shop/app', 'Weight and size'),
                ]); ?>

                <?= $form->field($shopProduct, 'weight')->textInput([
                    'type' => 'number',
                ]); ?>
                <?= $form->field($shopProduct, 'length')->textInput([
                    'type' => 'number',
                ]); ?>

                <?= $form->field($shopProduct, 'width')->textInput([
                    'type' => 'number',
                ]); ?>
                <?= $form->field($shopProduct, 'height')->textInput([
                    'type' => 'number',
                ]); ?>


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
            </div>
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
            </div>
        <? endif; ?>





        <? $fieldSet::end(); ?>



        <? if (!$model->isNewRecord) : ?>
            <? /*= $form->fieldSet(\Yii::t('skeeks/shop/app','Additionally')); */ ?><!--
        <? /*= $form->fieldSelect($model, 'content_id', \skeeks\cms\models\CmsContent::getDataForSelect()); */ ?>
    --><? /*= $form->fieldSetEnd() */ ?>

            <? if ($model->cmsContent->is_access_check_element) : ?>
                <? $fieldSet = $form->fieldSet(\Yii::t('skeeks/shop/app', 'Access')); ?>
                <?= \skeeks\cms\rbac\widgets\adminPermissionForRoles\AdminPermissionForRolesWidget::widget([
                    'permissionName'        => $model->permissionName,
                    'permissionDescription' => \Yii::t('skeeks/shop/app', 'Access to this member').': '.$model->name,
                    'label'                 => \Yii::t('skeeks/shop/app', 'Access to this member'),
                ]); ?>
                <? $fieldSet::end(); ?>
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
                <? $fieldSet = $form->fieldSet($childContent->name); ?>

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


                <? $fieldSet::end(); ?>
            <? endforeach; ?>
        <? endif; ?>





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


        <?= $this->render('@skeeks/cms/views/admin-cms-content-element/_form-additionaly', [
            'form'         => $form,
            'contentModel' => $contentModel,
            'model'        => $model,
        ]); ?>

        <?= $form->buttonsStandart($model); ?>
        <?php echo $form->errorSummary([$model, $relatedModel, $shopProduct]); ?>
        <?php $form::end(); ?>
    </div>


    <? if ($shopSubproductContentElement || $shopProduct->supplier_external_jsondata) : ?>
<?
$this->registerCss(<<<CSS
.sx-main-col {
    margin-right: 300px;
}

.sx-subproduct-info hr{
    margin-top: 10px;
    margin-bottom: 0px;
}
.sx-subproduct-info {
width: 300px;
position: fixed;
top: 0px;
right: 0px;
height: 100%;
overflow-y: auto;
}
.sx-info-block {
    padding: 10px 10px 0px 10px;
}
.sx-info-block span {
    color: gray;
}
.sx-info-block h5 {
    margin-bottom: 0px;
}
.sx-info-block p {
    margin-bottom: 0px;
}
CSS
);
?>
    <div class="sx-subproduct-info g-bg-gray-light-v8">
        <? endif; ?>


        <? if ($shopSubproductContentElement) : ?>

            <div class="sx-info-block">
                <h5><?= $shopSubproductContentElement->name; ?></h5>
            </div>
            <? if ($shopSubproductContentElement->shopProduct->shopSupplier) : ?>
                <div class="sx-info-block">
                    <p><span>Производитель:</span> <b><?= $shopSubproductContentElement->shopProduct->shopSupplier->asText; ?></b></p>
                    <p><span>Артикул:</span> <b><?= $shopSubproductContentElement->shopProduct->supplier_external_id; ?></b></p>
                </div>
                <div class="sx-info-block">
                    <p><span>Количество:</span> <b><?= $shopSubproductContentElement->shopProduct->quantity; ?> <?= $shopSubproductContentElement->shopProduct->measure->symbol; ?></b></p>
                </div>
                <? if ($data = $shopSubproductContentElement->shopProduct->supplier_external_jsondata) : ?>
                    <hr/>
                    <div class="sx-info-block">
                        <?= $this->render("_external_data", [
                            'shopProduct' => $shopSubproductContentElement->shopProduct,
                        ]); ?>
                    </div>
                <? endif; ?>
            <? endif; ?>
        <? endif; ?>



        <? if ($shopProduct->supplier_external_jsondata) : ?>
            <div class="sx-info-block">
                <?= $this->render("_external_data", [
                    'shopProduct' => $shopProduct,
                ]); ?>
            </div>
        <? endif; ?>




        <? if ($shopSubproductContentElement || $shopProduct->supplier_external_jsondata) : ?>
    </div>
<? endif; ?>
</div>
