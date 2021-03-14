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
?>

<? if ($model->shopStore->is_supplier) : ?>
    <div class="col-12">
        <div style="margin-bottom: 5px;"><b style="text-transform: uppercase;">Данные товара от поставщика</b></div>
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
        <div style="margin-bottom: 5px;"><b style="text-transform: uppercase;">Дополнительные данные</b></div>
    </div>

    <div class="sx-bg-secondary" style="padding-top: 10px;">
        <div class="col-12">
            <?= \skeeks\cms\shop\widgets\admin\StoreProductExternalDataWidget::widget(['storeProduct' => $model]); ?>
        </div>
    </div>



<? endif; ?>
