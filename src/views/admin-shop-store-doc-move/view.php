<?php
/* @var $this yii\web\View */
/* @var $model \skeeks\cms\shop\models\ShopStoreDocMove */
/* @var $controller \skeeks\cms\backend\controllers\BackendModelController */
/* @var $action \skeeks\cms\backend\actions\BackendModelCreateAction|\skeeks\cms\backend\actions\IHasActiveForm */
$controller = $this->context;
$action = $controller->action;
$model = $action->model;


$this->registerCSS(<<<CSS
.sx-fast-edit-value {
    padding: 5px;
}

.sx-fast-edit-form-wrapper {
    display: none;
}

.sx-fast-edit {
    cursor: pointer;
    min-width: 40px;
    border-bottom: 1px dotted;
}
.js-slide img {
     max-height: 300px;
     margin: auto;
}
.sx-stick-navigation .js-slide {
    padding: 5px;
}
.sx-stick-navigation .slick-slide {
    opacity: .6;
}
.sx-stick-navigation .slick-slide:hover {
    opacity: 1;
}
.sx-stick-navigation .js-slide {
    cursor: pointer;
    border: none;
    margin: 0 0px;
    position: relative;
}

.sx-stick-navigation {
    margin-top: 10px;
    margin-bottom: 10px;
}

.sx-stick-navigation .slick-current:before {
    border: 1px solid #d2d2d2;
    content: '';
    position: absolute;
    z-index: 2;
    top: 0;
    right: 0;
    bottom: 0;
    left: 0;
    /* border: 1px solid rgba(21,146,165,0); */
    -moz-transition: all .3s ease;
    -o-transition: all .3s ease;
    -webkit-transition: all .3s ease;
    transition: all .3s ease;
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
    max-width: 300px;
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



.sx-table td:first-child, .sx-table th:first-child {
    text-align: left;
}
.sx-table td, .sx-table th {
    border: 0;
    text-align: center;
    padding: 7px 10px;
    font-size: 13px;
    border-bottom: 1px solid #dee2e68f;
    background: white;
}


.sx-table th {
    background: #f9f9f9;
}

.sx-table-wrapper {
    border-radius: 5px;
    border-left: 1px solid #dee2e68f;
    border-right: 1px solid #dee2e68f;
    border-top: 1px solid #dee2e68f;
}
.sx-table-wrapper table {
    margin-bottom: 0;
}


.sx-info-block {
    background: #f9f9f9;
    margin-top: 10px;
    padding: 10px;
}
.sx-title {
    font-weight: bold;
    text-transform: uppercase;
    margin-bottom: 5px;
}

CSS
);

?>

<div class="sx-properties-wrapper sx-columns-1" style="max-width: 600px; margin-top: 15px;">
    <ul class="sx-properties">
        <li>
            <span class="sx-properties--name">
                Документ проведен
            </span>
            <span class="sx-properties--value">
                <?php echo \Yii::$app->formatter->asBoolean($model->is_active); ?>
            </span>
        </li>
        <li>
            <span class="sx-properties--name">
                Создан
            </span>
            <span class="sx-properties--value">
                <?php echo \Yii::$app->formatter->asDate($model->created_at); ?>
            </span>
        </li>
        <?php if($model->created_by) : ?>
            <li>
                <span class="sx-properties--name">
                    Создал
                </span>
                <span class="sx-properties--value">
                    <?php echo $model->createdBy->shortDisplayName; ?>
                </span>
            </li>
        <?php endif; ?>

        <li>
            <span class="sx-properties--name">
                Магазин
            </span>
            <span class="sx-properties--value">
                <?php echo $model->shopStore->name; ?>
            </span>
        </li>
        <?php if($model->comment) : ?>
            <li>
                <span class="sx-properties--name">
                    Комментарий
                </span>
                <span class="sx-properties--value">
                    <?php echo $model->comment; ?>
                </span>
            </li>
        <?php endif; ?>


    </ul>
</div>

<div class="row no-gutters" style="margin-top: 10px;">
    <div class="col-12">
        <div style="margin-bottom: 5px;"><b style="text-transform: uppercase;">Товары</b></div>

        <div class="sx-table-wrapper table-responsive">
            <table class="table sx-table">
                <tr>
                    <th>Наименование</th>
                    <th>Количество</th>
                    <th>Цена</th>
                    <th>Итог</th>
                </tr>
                <? foreach ($model->shopStoreProductMoves as $productMove) : ?>
                    <tr>
                        <td>
                            <?php if($productMove->shop_store_product_id && $productMove->shopStoreProduct->shopProduct) : ?>
                                <? $widget = \skeeks\cms\backend\widgets\AjaxControllerActionsWidget::begin([
                                    'controllerId' => 'shop/admin-cms-content-element',
                                    'urlParams' => [
                                        'content_id' => $productMove->shopStoreProduct->shopProduct->cmsContentElement->content_id
                                    ],
                                    'tag' => 'span',
                                    'modelId' => $productMove->shopStoreProduct->shopProduct->id,
                                    'isRunFirstActionOnClick' => true
                                ]); ?>
                                    <?php echo $productMove->product_name; ?>
                                <? $widget::end(); ?>
                            <?php else : ?>
                                <?php echo $productMove->product_name; ?>
                            <?php endif; ?>


                        </td>
                        <td>
                            <?php echo $productMove->quantity; ?>
                        </td>
                        <td><?php echo $productMove->price; ?></td>
                        <td><?php echo $productMove->price * $productMove->quantity; ?></td>
                    </tr>
                <? endforeach; ?>
            </table>
        </div>
    </div>
</div>
