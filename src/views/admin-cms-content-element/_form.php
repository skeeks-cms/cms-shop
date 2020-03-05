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


//Товар поставщика, из которого создается главный товар
$shopSubproductContentElement = @$shopSubproductContentElement;

$controller = $this->context;
$action = $controller->action;
$contentModel = $controller->content;

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


        <?= $this->render('_form-shop', [
            'form'         => $form,
            'contentModel' => $contentModel,
            'model'        => $model,
            'shopProduct'        => $shopProduct,
            'productPrices'        => $productPrices,
            'shopStoreProducts'        => $shopStoreProducts,
            'shopContent'        => $shopContent,
            'shopSubproductContentElement'        => $shopSubproductContentElement,
        ]); ?>







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
    margin-right: 400px;
}

.sx-subproduct-info hr{
    margin-top: 10px;
    margin-bottom: 0px;
}
.sx-subproduct-info {
width: 400px;
position: fixed;
top: 0px;
right: 0px;
height: 100%;
overflow-y: auto;
}
.sx-info-block {
    font-size: 10px;
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
                <h5><?= $shopSubproductContentElement->name; ?>
                    <a href="https://market.yandex.ru/search?cvredirect=2&text=<?= urlencode($shopSubproductContentElement->name); ?>" title="Поиск в yandex market" target="_blank" style="color: blue" class="btn btn-xs btn-secondary">
                        <i class="fas fa-shopping-cart"></i>
                    </a>
                    <a href="https://yandex.ru/search/?lr=213&text=<?= urlencode($shopSubproductContentElement->name); ?>" title="Поиск в yandex" target="_blank" style="color: red" class="btn btn-xs btn-secondary">
                        <i class="fab fa-yandex"></i>
                    </a>
                    <a href="https://www.google.com/search?q=<?= urlencode($shopSubproductContentElement->name); ?>" title="Поиск в google" target="_blank" style="" class="btn btn-xs btn-secondary">
                        <i class="fab fa-google"></i>
                    </a>
                </h5>
            </div>
            <? if ($shopSubproductContentElement->shopProduct->shopSupplier) : ?>
                <div class="sx-info-block">
                    <p><span>Поставщик:</span> <b><?= $shopSubproductContentElement->shopProduct->shopSupplier->asText; ?></b></p>
                    <p><span>Артикул:</span> <b><?= $shopSubproductContentElement->shopProduct->supplier_external_id; ?></b></p>
                </div>
                <div class="sx-info-block">
                    <p><span>Количество:</span> <b><?= $shopSubproductContentElement->shopProduct->quantity; ?> <?= $shopSubproductContentElement->shopProduct->measure->symbol; ?></b></p>
                </div>
                <? if ($data = $shopSubproductContentElement->shopProduct->supplier_external_jsondata) : ?>
                    <hr/>
                    <div class="sx-info-block">
                        <?= \skeeks\cms\shop\widgets\admin\SupproductExternalDataWidget::widget(['shopProduct' => $shopSubproductContentElement->shopProduct]); ?>
                    </div>
                <? endif; ?>
            <? endif; ?>
        <? endif; ?>



        <? if ($shopProduct->supplier_external_jsondata) : ?>
            <div class="sx-info-block">
                <?= \skeeks\cms\shop\widgets\admin\SupproductExternalDataWidget::widget(['shopProduct' => $shopProduct]); ?>
            </div>
        <? endif; ?>




        <? if ($shopSubproductContentElement || $shopProduct->supplier_external_jsondata) : ?>
    </div>
<? endif; ?>
</div>
