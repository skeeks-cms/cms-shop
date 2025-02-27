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
/* @var $shopSubproductContentElement \skeeks\cms\shop\models\ShopCmsContentElement */
/* @var $shopStoreProduct \skeeks\cms\shop\models\ShopStoreProduct */


//Товар поставщика, из которого создается главный товар
//$shopSubproductContentElement = @$shopSubproductContentElement;
$shopStoreProduct = @$shopStoreProduct;

$controller = $this->context;
$action = $controller->action;
$contentModel = $controller->content;

/*$shopContent = \skeeks\cms\shop\models\ShopContent::find()->where(['content_id' => $contentModel->id])->one();*/

if ($model->isNewRecord) {

    /*if ($shopSubproductContentElement) {
        $siteClass = \Yii::$app->skeeks->siteClass;
        $defaultSite = $siteClass::find()->where(['is_default' => 1])->one();
        $model->cms_site_id = $defaultSite->id;
    }*/

    if ($tree_id = \Yii::$app->request->get("tree_id")) {
        $model->tree_id = $tree_id;
    }
}
?>
<div class="">
    <div class="sx-main-product-wrapper">


        <?php $pjax = \skeeks\cms\widgets\Pjax::begin(); ?>
        <?php $form = $action->beginActiveForm(); ?>



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
                'form'                         => $form,
                'contentModel'                 => $contentModel,
                'model'                        => $model,
                'shopProduct'                  => $shopProduct,
                'productPrices'                => $productPrices,
                'shopStoreProducts'            => $shopStoreProducts,
                //'shopSubproductContentElement' => $shopSubproductContentElement,
                'shopStoreProduct' => $shopStoreProduct,
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

            <? if ($model->cmsContent->getChildrenContents()
                /*->andWhere([
                    '!=',
                    'id',
                    $shopContent->childrenContent->id,
                ])*/
                ->all()
            ) : ?>

                <? $childContents = $model->cmsContent->getChildrenContents()
                    /*->andWhere([
                        '!=',
                        'id',
                        $shopContent->childrenContent->id,
                    ])*/
                    ->all(); ?>

                <? foreach ($childContents as $childContent) : ?>
                    <? $fieldSet = $form->fieldSet($childContent->name); ?>

                    <? if ($model->isNewRecord) : ?>
                        <div class="col-12" style="margin-top: 1rem;">
                            <p>Управление будет доступно после сохранения</p>
                        </div>
                    <? else: ?>

                        <div class="col-12" style="margin-top: 1rem;">
                            <?= \skeeks\cms\modules\admin\widgets\RelatedModelsGrid::widget([
                                'label'       => "", //$childContent->name,
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

                                'controllerRoute' => '/cms/admin-cms-content-element',
                                'gridViewOptions' => [
                                    'columns' => (array)\skeeks\cms\controllers\AdminCmsContentElementController::getColumns($childContent),
                                ],
                            ]); ?>
                        </div>
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


<? $fieldSet = $form->fieldSet(\Yii::t('skeeks/shop/app', 'Рейтинг товара'), ['isOpen' => false]); ?>

<div class="col-12" style="margin-top: 20px;">
    <? $widget = \yii\bootstrap\Alert::begin([
        'closeButton' => false,
        'options'     => [
            'class' => 'alert-default',
        ],
    ]); ?>
    Рейтинг товара рассчитывается автоматически исходя из отзывов по товару.
    <? $widget = \yii\bootstrap\Alert::end(); ?>
</div>
<div class="row">
    <div class="col-md-4">
        <?= $form->field($shopProduct, 'rating_value')
            ->widget(\skeeks\cms\backend\widgets\forms\NumberInputWidget::class, [
                'options' => [
                    'step'     => 0.0001,
                    'disabled' => 'disabled',
                ],
            ]); ?>
    </div>
    <div class="col-md-4">
        <?= $form->field($shopProduct, 'rating_count')
            ->widget(\skeeks\cms\backend\widgets\forms\NumberInputWidget::class, [
                //'dynamicReload' => true,
                'append'  => "шт",
                'options' => [
                    'step'     => 1,
                    'disabled' => 'disabled',
                ],
            ]); ?>
    </div>
</div>

<? $fieldSet::end(); ?>

        
        <?= $form->buttonsStandart($model, $action->buttons); ?>
        <?php echo $form->errorSummary([$model, $relatedModel, $shopProduct]); ?>
        <?php $form::end(); ?>
        <?php $pjax::end(); ?>
    </div>


    <? if ($shopStoreProduct || $shopProduct->supplier_external_jsondata && 1 == 2) : ?>
<?
$this->registerJs(<<<JS
$(".sx-propery-row").click(
    function() {
        var formCode = $(this).data('form-code');
        var jElement = $("." + formCode);
        console.log("." + formCode);
        console.log(jElement);
        
        if(jElement.length) { // проверяем существование
            $('html').animate({ 
                scrollTop: jElement.offset().top // прокручиваем страницу к требуемому элементу
            }, 500 // скорость прокрутки
            );
        }
            
    },
);
$(".sx-propery-row").hover(
    function() {
        var formCode = $(this).data('form-code');
        $("." + formCode).addClass("sx-hover");
    },
    function() {
        var formCode = $(this).data('form-code');
        $("." + formCode).removeClass("sx-hover");
    }
);
JS
);

$this->registerCss(<<<CSS
.sx-hover {
background: #d9fbd9;
}
.sx-main-col {
    padding-right: 400px !important;
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

@media (max-width: 1300px) {
    .sx-subproduct-info {
        width: 300px;
    }
    .sx-main-col {
        padding-right: 300px !important;
    }
}
@media (max-width: 1100px) {
    .sx-subproduct-info {
        width: 200px;
    }
    .sx-main-col {
        padding-right: 200px !important;
    }
}
CSS
);
?>
    <div class="sx-subproduct-info js-scrollbar sx-bg-secondary">
        <? endif; ?>


        <? if ($shopStoreProduct) : ?>

            <div class="sx-info-block">
                <h5><?= $shopStoreProduct->name; ?>
                    <a href="https://market.yandex.ru/search?cvredirect=2&text=<?= urlencode($shopStoreProduct->name); ?>" title="Поиск в yandex market" target="_blank" style="color: blue"
                       class="btn btn-xs btn-default">
                        <i class="fas fa-shopping-cart"></i>
                    </a>
                    <a href="https://yandex.ru/search/?lr=213&text=<?= urlencode($shopStoreProduct->name); ?>" title="Поиск в yandex" target="_blank" style="color: red" class="btn btn-xs btn-default">
                        <i class="fab fa-yandex"></i>
                    </a>
                    <a href="https://www.google.com/search?q=<?= urlencode($shopStoreProduct->name); ?>" title="Поиск в google" target="_blank" style="" class="btn btn-xs btn-default">
                        <i class="fab fa-google"></i>
                    </a>
                </h5>
            </div>

            <hr/>
            <div class="sx-info-block ">
                <?
                \skeeks\assets\unify\base\UnifyHsScrollbarAsset::register($this);
                ?>
                <?= \skeeks\cms\shop\widgets\admin\StoreProductExternalDataWidget::widget([
                    'storeProduct' => $shopStoreProduct
                ]); ?>
            </div>

        <? endif; ?>


        <?/* if ($shopProduct->supplier_external_jsondata) : */?><!--
            <div class="sx-info-block">
                <?/*= \skeeks\cms\shop\widgets\admin\SubProductExternalDataWidget::widget(['shopProduct' => $shopProduct]); */?>
            </div>
        --><?/* endif; */?>




        <? if ($shopStoreProduct || $shopProduct->supplier_external_jsondata) : ?>
    </div>
<? endif; ?>
</div>
