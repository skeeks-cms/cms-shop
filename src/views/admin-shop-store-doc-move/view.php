<?php
/* @var $this yii\web\View */
/* @var $model \skeeks\cms\shop\models\ShopStoreDocMove */
/* @var $controller \skeeks\cms\backend\controllers\BackendModelController */
/* @var $action \skeeks\cms\backend\actions\BackendModelCreateAction|\skeeks\cms\backend\actions\IHasActiveForm */
$controller = $this->context;
$action = $controller->action;
$model = $action->model;

$this->render("view-css");
\skeeks\cms\backend\widgets\AjaxControllerActionsWidget::registerAssets();
?>

<div class="sx-properties-wrapper sx-columns-1" style="max-width: 700px; margin-top: 15px;">
    <ul class="sx-properties sx-bg-secondary" style="padding: 10px;">
        <li>
            <span class="sx-properties--name">
                Магазин
            </span>
            <span class="sx-properties--value">
                <?php echo $model->shopStore->name; ?>
            </span>
        </li>

        <li>
            <span class="sx-properties--name">
                Проведение документа <i class="far fa-question-circle" style="margin-left: 5px;" data-toggle="tooltip" title="" data-original-title="После проведение документа количество товаров будет зачтено"></i>
            </span>
            <span class="sx-properties--value">
                <?php if (!$model->is_active) : ?>
                    <button class="btn btn-primary sx-approve-doc">Провести документ</button>
                <?php else : ?>
                    <button class="btn btn-secondary sx-not-approve-doc">Отменить документ</button>
                <?php endif; ?>
            </span>
        </li>


        <li>
            <span class="sx-properties--name">
                Комментарий
            </span>
            <span class="sx-properties--value">



                <span class="sx-properties--value">
                            <span class="sx-fast-edit sx-fast-edit-popover"
                                  data-form="#comment-form"
                                  data-title="Комментарий"
                            >
                                <?php echo $model->comment ? $model->comment : "&nbsp;&nbsp;&nbsp;"; ?>
                            </span>

                            <div class="sx-fast-edit-form-wrapper">
                                <?php $form = \skeeks\cms\base\widgets\ActiveFormAjaxSubmit::begin([
                                    'id'             => "comment-form",
                                    'action'         => \yii\helpers\Url::to(['update-attribute', 'pk' => $model->id]),
                                    'options'        => [
                                        'class' => 'sx-fast-edit-form',
                                    ],
                                    'clientCallback' => new \yii\web\JsExpression(<<<JS
                                        function (ActiveFormAjaxSubmit) {
                                            ActiveFormAjaxSubmit.on('success', function(e, response) {
                                                window.location.reload();
                                                $(".sx-fast-edit").popover("hide");
                                            });
                                        }
JS
                                    ),
                                ]); ?>
                                <div style="min-width: 500px;">
                                    <?php echo $form->field($model, 'comment')->textarea(['rows' => 3])->label(false); ?>
                                </div>
                                    <div class="input-group-append">
                                        <button class="btn btn-primary" type="submit"><i class="fas fa-check"></i> Сохранить</button>
                                    </div>
                                <?php $form::end(); ?>
                            </div>

                        </span>

            </span>
        </li>
    </ul>

</div>

<div class="row" style="margin-top: 10px;">

    <?php if (!$model->is_active) : ?>


        <div class="col" style="max-width: 350px;">
            <div style="margin-bottom: 5px;">
                <b style="text-transform: uppercase;">Каталог товаров</b>
            </div>
            <div class="sx-block-search">
                <input class="form-control" placeholder="Поиск товаров">
            </div>
            <div class="sx-block-products">

            </div>
        </div>


        <div class="col">

            <? $pjax = \skeeks\cms\widgets\Pjax::begin([
                'id' => 'sx-selected-proocuts',
            ]); ?>
            <div style="margin-bottom: 5px;">
                <b style="text-transform: uppercase;">Выбранные товары</b>
            </div>

            <div class="sx-table-wrapper table-responsive">
                <table class="table sx-table">
                    <tr>
                        <th>Наименование</th>
                        <th>Количество</th>
                        <th>Цена</th>
                        <th>Итог</th>
                        <th></th>
                    </tr>
                    <?
                    $totalQuantity = 0;
                    $totalPrice = 0;
                    foreach ($model->shopStoreProductMoves as $productMove) : ?>

                        <?
                        $totalQuantity = $totalQuantity + $productMove->quantity;
                        $totalPrice = $totalPrice + $productMove->price * $productMove->quantity;
                        ?>
                        <tr data-id="<?php echo $productMove->id; ?>">
                            <td>
                                <?php if ($productMove->shop_store_product_id && $productMove->shopStoreProduct->shopProduct) : ?>
                                    <? $widget = \skeeks\cms\backend\widgets\AjaxControllerActionsWidget::begin([
                                        'controllerId'            => 'shop/admin-cms-content-element',
                                        'urlParams'               => [
                                            'content_id' => $productMove->shopStoreProduct->shopProduct->cmsContentElement->content_id,
                                        ],
                                        'tag'                     => 'span',
                                        'defaultOptions'          => [
                                            'class' => 'd-flex',
                                            'style' => 'line-height: 1.1; cursor: pointer;',
                                        ],
                                        'modelId'                 => $productMove->shopStoreProduct->shopProduct->id,
                                        'isRunFirstActionOnClick' => true,
                                    ]); ?>

                                    <?
                                    $image = null;
                                    if ($product = $productMove->shopStoreProduct) {
                                        if ($product->shopProduct) {
                                            if ($product->shopProduct->cmsContentElement) {
                                                if ($product->shopProduct->cmsContentElement->mainProductImage) {
                                                    $image = $product->shopProduct->cmsContentElement->mainProductImage;
                                                }
                                            }
                                        }
                                    }
                                    ?>

                                    <?php if ($image) : ?>
                                        <span class="my-auto">
                                        <img class="my-auto" src="<?php echo \Yii::$app->imaging->thumbnailUrlOnRequest($image->src, new \skeeks\cms\components\imaging\filters\Thumbnail()); ?>"
                                             style="max-width: 30px; height: 100%;
                        width: 100%; margin-right: 5px;"/>
                                    </span>
                                    <?php endif; ?>

                                    <span class="my-auto">
                                    <?php echo $productMove->product_name; ?>
                            </span>
                                    <? $widget::end(); ?>
                                <?php else : ?>
                                    <?php echo $productMove->product_name; ?>
                                <?php endif; ?>


                            </td>
                            <td>
                                <input type="number" class="form-control sx-quantity" value="<?php echo $productMove->quantity; ?>"/>
                            </td>
                            <td><input type="number" class="form-control sx-price" value="<?php echo $productMove->price; ?>"/></td>
                            <td><?php echo $productMove->price * $productMove->quantity; ?></td>
                            <td>
                                <div class="btn sx-remove-row-btn">
                                    ×
                                </div>
                            </td>
                        </tr>
                    <? endforeach; ?>

                    <tr>
                        <td style="text-align: right;">Итого:</td>
                        <td><b><?php echo $totalQuantity; ?></b></td>
                        <td></td>
                        <td><b><?php echo $totalPrice; ?></b></td>
                        <td></td>
                    </tr>

                </table>

            </div>

            <? $pjax::end(); ?>
        </div>

    <?php else : ?>
        <div class="sx-table-wrapper table-responsive">
            <table class="table sx-table">
                <tr>
                    <th>Наименование</th>
                    <th>Количество</th>
                    <th>Цена</th>
                    <th>Итог</th>
                </tr>

                <?
                $totalQuantity = 0;
                $totalPrice = 0;
                foreach ($model->shopStoreProductMoves as $productMove) : ?>

                    <?
                    $totalQuantity = $totalQuantity + $productMove->quantity;
                    $totalPrice = $totalPrice + $productMove->price * $productMove->quantity;
                    ?>

                    <tr data-id="<?php echo $productMove->id; ?>">
                        <td>
                            <?php if ($productMove->shop_store_product_id && $productMove->shopStoreProduct->shopProduct) : ?>
                                <? $widget = \skeeks\cms\backend\widgets\AjaxControllerActionsWidget::begin([
                                    'controllerId'            => 'shop/admin-cms-content-element',
                                    'urlParams'               => [
                                        'content_id' => $productMove->shopStoreProduct->shopProduct->cmsContentElement->content_id,
                                    ],
                                    'tag'                     => 'span',
                                    'defaultOptions'          => [
                                        'class' => 'd-flex',
                                        'style' => 'line-height: 1.1; cursor: pointer;',
                                    ],
                                    'modelId'                 => $productMove->shopStoreProduct->shopProduct->id,
                                    'isRunFirstActionOnClick' => true,
                                ]); ?>

                                <?
                                $image = null;
                                if ($product = $productMove->shopStoreProduct) {
                                    if ($product->shopProduct) {
                                        if ($product->shopProduct->cmsContentElement) {
                                            if ($product->shopProduct->cmsContentElement->mainProductImage) {
                                                $image = $product->shopProduct->cmsContentElement->mainProductImage;
                                            }
                                        }
                                    }
                                }
                                ?>

                                <?php if ($image) : ?>
                                    <span class="my-auto">
                                        <img class="my-auto" src="<?php echo \Yii::$app->imaging->thumbnailUrlOnRequest($image->src, new \skeeks\cms\components\imaging\filters\Thumbnail()); ?>"
                                             style="max-width: 30px; height: 100%;
                        width: 100%; margin-right: 5px;"/>
                                    </span>
                                <?php endif; ?>

                                <span class="my-auto">
                                    <?php echo $productMove->product_name; ?>
                            </span>
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

                <tr>
                    <td style="text-align: right;">Итого:</td>
                    <td><b><?php echo $totalQuantity; ?></b></td>
                    <td></td>
                    <td><b><?php echo $totalPrice; ?></b></td>
                </tr>

            </table>
        </div>
    <?php endif; ?>
</div>

<?php

$this->registerCss(<<<CSS
.sx-block-products {
max-height: 500px;
overflow: auto;
}
.catalog-card {
    font-size: 12px;
    cursor: pointer;
    padding: 10px 0;
    border-bottom: 1px solid #f1f1f1;
}
.catalog-card:hover {
    background: #f9f9f9;
}
.catalog-card .title {
    line-height: 1.1;
    color: black;
}
.catalog-card .sku {
    font-size: 12px;
    color: gray;
}
.catalog-card .stock {
    font-size: 12px;
    color: gray;
}
.catalog-card .price {
    font-size: 12px;
    color: gray;
}
.catalog-card .barcode {
    font-size: 12px;
    color: gray;
}
.sx-more {
    margin-bottom: 20px;
}
CSS
);
$this->render("view-js");
/*\skeeks\assets\unify\base\UnifyHsScrollbarAsset::register($this);*/
$jsData = \yii\helpers\Json::encode([
    'backend_products'            => \yii\helpers\Url::to(['products', 'pk' => $model->id]),
    'backend-add-product'         => \yii\helpers\Url::to(['add-product', 'pk' => $model->id]),
    'backend-add-product-barcode' => \yii\helpers\Url::to(['add-product-barcode', 'pk' => $model->id]),
    'backend-remove-order-item'   => \yii\helpers\Url::to(['remove-item', 'pk' => $model->id]),
    'backend-update-item'         => \yii\helpers\Url::to(['update-item', 'pk' => $model->id]),
    'backend-remove-item'         => \yii\helpers\Url::to(['remove-item', 'pk' => $model->id]),
    'backend-approve-doc'         => \yii\helpers\Url::to(['approve-doc', 'pk' => $model->id]),
    'backend-no-approve-doc'      => \yii\helpers\Url::to(['no-approve-doc', 'pk' => $model->id]),
    'doc'                         => $model->toArray(),
]);
$this->registerJs(<<<JS
sx.DocMove = new sx.classes.DocMove({$jsData});
JS
);
?>
