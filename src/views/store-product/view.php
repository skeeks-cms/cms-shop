<?php
/* @var $this yii\web\View */
/* @var $model \skeeks\cms\shop\models\ShopStoreProduct */
/* @var $joinModel \skeeks\cms\shop\models\ShopCmsContentElement */
/* @var $controller \skeeks\cms\backend\controllers\BackendModelController */
/* @var $action \skeeks\cms\backend\actions\BackendModelCreateAction|\skeeks\cms\backend\actions\IHasActiveForm */
$controller = $this->context;
$action = $controller->action;
$model = $action->model;

$this->registerCss(<<<CSS

.sx-product-wrapper {
    background: #fafafa;
    box-shadow: 0px 0px 6px -1px #d0d0d0;
}
.sx-product-wrapper .sx-product-image img {
    max-width: 100%;
    max-height: 150px;
}
.sx-product-wrapper .sx-product-image {
    text-align: center;
    padding: 5px;
}
.sx-product-wrapper .sx-product-info {
    line-height: 1.2;
    padding: 10px;
}
/**
 * Современное оформление свойств
 */
.sx-properties-wrapper.sx-columns-1 ul.sx-properties {
    -moz-column-count: 1;
    column-count: 1;
}

.sx-properties-wrapper.sx-columns-2 ul.sx-properties {
    -moz-column-count: 2;
    column-count: 2;
}

.sx-properties-wrapper.sx-columns-3 ul.sx-properties {
    -moz-column-count: 3;
    column-count: 3;
}

ul.sx-properties {
    -moz-column-count: 2;
    column-count: 2;
    grid-column-gap: 40px;
    -moz-column-gap: 40px;
    column-gap: 40px;
    margin: 0px;
    padding: 0px;
}

ul.sx-properties li {
    display: flex;
    align-items: baseline;
    justify-content: space-between;
    margin-bottom: 8px;
    page-break-inside: avoid;
    -moz-column-break-inside: avoid;
    break-inside: avoid;
}

ul.sx-properties .sx-properties--value {
    text-align: right;
    max-width: 200px;
    line-height: 1.4;
}

ul.sx-properties .sx-properties--name {
    color: gray;
    flex: 1;
    display: flex;
    align-items: baseline;
    white-space: nowrap;
}

ul.sx-properties .sx-properties--name:after {
    content: "";
    flex-grow: 1;
    opacity: .25;
    margin: 0 6px 0 2px;
    border-bottom: 1px dotted gray;
}
CSS
);
\skeeks\assets\unify\base\UnifyIconHsAsset::register($this);

$this->registerJs(<<<JS
$("body").on("change", "#shopstoreproduct-shop_product_id", function() {
    $(this).closest("form").submit();
})
$("body").on("click", ".sx-close-shop-product", function() {
    $("#shopstoreproduct-shop_product_id").val("").change();
    return false;
});

$("body").on("click", ".sx-search-products", function() {
    $(".sx-btn-create").click();
    return false;
});
JS
);
?>

<?/* if ($model->shopStore->is_supplier) : */?>
    <div style="margin-bottom: 20px;">
        <div class="col-12">
            <div style="margin-bottom: 10px;">
                <i class="far fa-question-circle" data-toggle="tooltip" title="Офорленная карточка которую видят покупатели на сайте"></i>
                <b style="text-transform: uppercase;">Оформленная карточка</b></div>
        </div>

        <div class="col-12" style="display: none;">
            <?
            $form = \skeeks\cms\base\widgets\ActiveFormAjaxSubmit::begin([
                'action'                 => \yii\helpers\Url::to(['save-main', 'pk' => $model->id]),
                'enableAjaxValidation'   => false,
                'options'                => [
                    'class' => 'd-flex',
                ],
                'enableClientValidation' => false,
                'clientCallback'         => new \yii\web\JsExpression(<<<JS
                    function (ActiveFormAjaxSubmit) {
                        ActiveFormAjaxSubmit.on('success', function(e, response) {
                            window.location.reload();
                        });
                    }
JS
                ),
            ]);
            ?>

            <div>
                <?
                echo $form->field($model, 'shop_product_id')->widget(
                    \skeeks\cms\backend\widgets\SelectModelDialogContentElementWidget::class,
                    [
                        'content_id'  => \Yii::$app->shop->contentProducts->id,
                        'name'        => "sx-main-product",
                        'dialogRoute' => [
                            '/shop/admin-cms-content-element',
                            \skeeks\cms\backend\helpers\BackendUrlHelper::BACKEND_PARAM_NAME => [
                                'sx-to-main' => "true",
                            ],
                            'w3-submit-key'                                                  => "1",
                            'findex'                                                         => [
                                'shop_supplier_id' => [
                                    'mode' => 'empty',
                                ],
                            ],
                        ],
                    ]
                )->label(false);
                ?>
            </div>
            <div>
                <button type="submit" class="btn btn-primary">Сохранить</button>
            </div>
            <? $form::end(); ?>
        </div>

        <? if ($model->shopProduct) : ?>

            <div class="sx-bg-secondary" style="padding: 10px 0;">
                <div class="col-3">
                    <div class="sx-product-wrapper">
                        <div class="sx-product-image">
                            <img src="<?php echo $model->shopProduct->cmsContentElement->mainProductImage ? $model->shopProduct->cmsContentElement->mainProductImage->src : \skeeks\cms\helpers\Image::getCapSrc(); ?>"/>
                        </div>
                        <div class="sx-product-info">
                            <?
                            \skeeks\cms\backend\widgets\AjaxControllerActionsWidget::begin([
                                'controllerId' => "/shop/admin-cms-content-element",
                                'modelId'      => $model->shopProduct->id,
                                'tag'          => 'span',
                                'options'      => [
                                    'style' => ' text-align: left;',
                                    'class' => '',
                                ],
                            ]);
                            ?>
                            <i class="fas fa-link" title="Связан" data-toggle="tooltip" style="margin-left: 5px; color: green;"></i>
                            <? echo $model->shopProduct->cmsContentElement->productName; ?>
                            <?php \skeeks\cms\backend\widgets\AjaxControllerActionsWidget::end(); ?>


                        </div>
                        <button data-toggle="tooltip" title="Отменить связь с товаром" class="btn btn-light sx-close-shop-product" style="    position: absolute;right: 15px;
    top: 0;">
                                <i class="hs-icon hs-icon-close"></i></button>
                    </div>

                </div>
            </div>
        <? else: ?>
            <div class="col-12">
                <div class="d-flex">
                <div class="" style="margin-right: 20px;">
                    <a href="#" class="btn btn-xxl btn-primary sx-search-products"><i class="fa fa-search"></i> Найти товар</a>
                </div>
                <div class="">
                    <a href="<?php
                    $url = \yii\helpers\Url::to([
                        '/shop/admin-cms-content-element/create',
                        'content_id'       => \Yii::$app->shop->contentProducts->id,
                        'store_product_id' => $model->id,
                    ], true);
                    echo $url;
                    ?>" data-pjax='0' class="btn btn-xxl btn-primary"><i class="fa fa-plus"></i> Создать товар</a>
                </div>
                </div>
            </div>
        <? endif; ?>
    </div>

    <div class="col-12">
        <div style="margin-bottom: 5px;">
            <i class="far fa-question-circle" data-toggle="tooltip" title="Основные данные полученные от поставщика"></i>
            <b style="text-transform: uppercase;">Данные товара от поставщика</b></div>
    </div>


    <div class="sx-bg-secondary" style="padding-top: 10px;">
        <div class="col-12">


            <div class="sx-properties-wrapper sx-columns-1" style="max-width: 450px;">
                <ul class="sx-properties">
                    <li>
                            <span class="sx-properties--name">
                                Поставщик
                            </span>
                        <span class="sx-properties--value">
                            <?php if ($model->shopStore->cmsImage) : ?>
                                <img src="<?php echo $model->shopStore->cmsImage->src; ?>" style="max-width: 20px; max-height: 20px; "/>
                            <?php endif; ?>

                            <?php echo $model->shopStore->name; ?>
                            </span>
                    </li>
                    <?php if ($model->name) : ?>
                        <li>
                            <span class="sx-properties--name">
                                Название у поставщика
                            </span>
                            <span class="sx-properties--value">
                            <?php echo $model->name; ?>
                            </span>
                        </li>
                    <?php endif; ?>
                    <?php if ($model->external_id) : ?>
                        <li>
                            <span class="sx-properties--name">
                                Код поставщика
                            </span>
                            <span class="sx-properties--value">
                            <?php echo $model->external_id; ?>
                            </span>
                        </li>
                    <?php endif; ?>

                    <?php if ($model->quantity) : ?>
                        <li>
                            <span class="sx-properties--name">
                                Количество
                            </span>
                            <span class="sx-properties--value">
                            <?php echo $model->quantity; ?>
                            </span>
                        </li>
                    <?php endif; ?>

                    <?php if ($model->purchase_price) : ?>
                        <li>
                            <span class="sx-properties--name">
                                Закупочная цена
                            </span>
                            <span class="sx-properties--value">
                            <?php echo $model->purchase_price; ?>
                            </span>
                        </li>
                    <?php endif; ?>
                    <?php if ($model->selling_price) : ?>
                        <li>
                            <span class="sx-properties--name">
                                Розничная цена
                            </span>
                            <span class="sx-properties--value">
                            <?php echo $model->selling_price; ?>
                            </span>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>


    <div class="col-12" style="margin-top: 20px;">
        <div style="margin-bottom: 5px;">
            <i class="far fa-question-circle" data-toggle="tooltip" title="Дополнительные данные, которые предоставил поставщик по этому товару"></i>
            <b style="text-transform: uppercase;">Дополнительные данные</b></div>
    </div>


    <div class="sx-bg-secondary" style="padding-top: 10px;">
        <div class="col-12">
            <?= \skeeks\cms\shop\widgets\admin\StoreProductExternalDataWidget::widget(['storeProduct' => $model]); ?>
        </div>
    </div>


<?/* endif; */?>
