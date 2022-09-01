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

.sx-table td {
    vertical-align: baseline;
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
                Магазин
            </span>
            <span class="sx-properties--value">
                <?php echo $model->shopStore->name; ?>
            </span>
        </li>
        <?php if ($model->comment) : ?>
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

<div class="row" style="margin-top: 10px;">

    <?php if (!$model->is_active) : ?>


        <div class="col" style="max-width: 350px;">
            <div style="margin-bottom: 5px;">
                <b style="text-transform: uppercase;">Добавить товары</b>
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
                <? foreach ($model->shopStoreProductMoves as $productMove) : ?>
                    <tr>
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
                            <input type="number" class="form-control" value="<?php echo $productMove->quantity; ?>" />
                        </td>
                        <td><input type="number" class="form-control" value="<?php echo $productMove->price; ?>" /></td>
                        <td><?php echo $productMove->price * $productMove->quantity; ?></td>
                        <td>
                            <div class="btn sx-remove-row-btn">
                                ×
                            </div>
                        </td>
                    </tr>
                <? endforeach; ?>
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
                <? foreach ($model->shopStoreProductMoves as $productMove) : ?>
                    <tr>
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
/*\skeeks\assets\unify\base\UnifyHsScrollbarAsset::register($this);*/
$jsData = \yii\helpers\Json::encode([
    'backend_products'            => \yii\helpers\Url::to(['products', 'pk' => $model->id]),
    'backend-add-product'         => \yii\helpers\Url::to(['add-product', 'pk' => $model->id]),
    'backend-add-product-barcode' => \yii\helpers\Url::to(['add-product-barcode', 'pk' => $model->id]),
    'backend-remove-order-item'   => \yii\helpers\Url::to(['remove-item', 'pk' => $model->id]),
    'backend-update-order-item'   => \yii\helpers\Url::to(['update-item', 'pk' => $model->id]),
    'doc'                         => $model->toArray(),
]);
$this->registerJs(<<<JS

(function (sx, $, _) {
    sx.classes.DocMove = sx.classes.Component.extend({
        _init: function () {
            var self = this;
    
            this.productBlocker = null;
            this.lastKeyTime = new Date().getTime();
            this._initScanner();
        },
        
        _onDomReady: function () {
            var self = this;

            self.loadProducts();

            self.getJSearch().on("focus", function () {

            });

            self.getJSearch().on("keyup", function () {
                //Не нужно сразу применять нужно чуть подождать
                self.lastKeyTime = new Date().getTime();

                setTimeout(function () {
                    var newTime = new Date().getTime();
                    var delta = newTime - self.lastKeyTime;
                    if (delta >= 1000) {
                        self.loadProducts();
                    }
                }, 1000);
            });
            
            //Подгрузка следующих данных
            $("body").on('click', ".sx-block-products .sx-btn-next-page", function () {
                if ($(this).hasClass("sx-loaded")) {
                    return false;
                }
                var text = $(this).data("load-text");
                var nextPage = $(this).data("next-page");
                $(this).empty().append(text);
                $(this).closest(".sx-more").addClass("sx-loaded");
                self.loadProducts(nextPage);
            });
            
            //Добавить товар в корзину
            $("body").on('click', '.catalog-card', function (e) {
                var jCard = $(this);
                jCard.css("transform", "scale(0.95)");
                setTimeout(function () {
                    jCard.css("transform", "");
                }, 300);
                var ajaxQuery = self.createAjaxAddProduct($(this).data("id"), 1);
                var Handler = new sx.classes.AjaxHandlerStandartRespose(ajaxQuery, {
                    'allowResponseSuccessMessage': false
                });
                ajaxQuery.execute();

                return false;
            });
        },
        
        _initScanner: function () {
            var self = this;
            var code = "";
            var reading = false;

            document.addEventListener('keypress', e => {
                //usually scanners throw an 'Enter' key at the end of read
                if (e.keyCode === 13) {
                    if (code.length > 10) {

                        var ajaxQuery = self.createAjaxAddProductBarcode(code);

                        ajaxQuery.onError(function (e, data) {
                            code = "";
                        });

                        ajaxQuery.onSuccess(function (e, data) {

                            if (self.getJSearch().val() != code) {
                                self.getJSearch().val(code);
                                self.loadProducts();
                            }

                            //self.loadProducts();

                            if (data.response.data.total == 1) {

                                var q = self.createAjaxAddProduct(data.response.data.product.id, 1);
                                var Handler = new sx.classes.AjaxHandlerStandartRespose(q, {
                                    'allowResponseSuccessMessage': false
                                });
                                q.execute();

                            } else if (data.response.data.total > 1) {

                            } else {

                            }

                            code = "";
                        });

                        /// code ready to use
                        ajaxQuery.execute();
                    }
                } else {
                    code += e.key; //while this is not an 'enter' it stores the every key
                }

                //run a timeout of 200ms at the first read and clear everything
                if (!reading) {
                    reading = true;
                    setTimeout(() => {
                        code = "";
                        reading = false;
                    }, 200);  //200 works fine for me but you can adjust it
                }
            });
        },
        
        /**
         * @returns {sx.classes.CashierApp}
         */
        loadProducts: function (page = 0) {
            var self = this;

            if (page == 0) {
                self.blockProducts();
                self.getJProducts().empty();
            }


            var ajaxQuery = sx.ajax.preparePostQuery(this.get("backend_products"), {
                'q': self.getJSearch().val(),
                'page': page,
            });

            var handler = new sx.classes.AjaxHandlerStandartRespose(ajaxQuery, {
                'enableBlocker': false
            });

            handler.on("stop", function () {
                self.unblockProducts();
            });

            handler.on("success", function (e, response) {
                self.getJProducts().append(response.data.content);

                $(".sx-block-products .sx-more.sx-loaded").hide().remove();

                var maxHeight = $(window).height() - $(".sx-block-products").offset().top;
                $(".sx-block-products").css("max-height", maxHeight);
            });

            ajaxQuery.execute();

            return this;
        },
        
        /**
         * @returns {sx.classes.CashierApp}
         */
        blockProducts: function () {
            if (this.productBlocker === null) {
                this.productBlocker = new sx.classes.Blocker(".sx-block-products");
            }
            this.productBlocker.block();
            return this;
        },

        /**
         * @returns {sx.classes.CashierApp}
         */
        unblockProducts: function () {
            this.productBlocker.unblock();
            return this;
        },
        
        getJSearch: function () {
            return $(".sx-block-search input");
        },
        
        getJProducts: function () {
            return $(".sx-block-products");
        },
        /**
         * Updating the positions of the basket, such as changing the number of
         *
         * @param basket_id
         * @returns {*|sx.classes.AjaxQuery}
         */
        createAjaxAddProductBarcode: function (barcode) {
            var self = this;
            var ajax = sx.ajax.preparePostQuery(this.get('backend-add-product-barcode'));

            ajax.setData({
                'barcode': barcode
            });

            return ajax;
        },
        
        /**
         * Adding product to cart
         *
         * @param product_id
         * @param quantity
         * @param additional
         * @returns {*|sx.classes.AjaxQuery}
         */
        createAjaxAddProduct: function (product_id, quantity, additional) {
            var self = this;
            var ajax = sx.ajax.preparePostQuery(this.get('backend-add-product'));

            additional = additional || {};
            quantity = quantity || 0;

            product_id = Number(product_id);
            quantity = Number(quantity);

            if (quantity <= 0) {
                quantity = 1;
            }

            ajax.setData({
                'product_id': product_id,
                'quantity': quantity,
                'additional': additional,
            });

            ajax.onSuccess(function (e, data) {
                self.trigger('addProduct', {
                    'product_id': product_id,
                    'quantity': quantity,
                    'response': data.response,
                });
                
                $.pjax.reload("#sx-selected-proocuts");
            });

            return ajax;
        },
    });
})(sx, sx.$, sx._);

sx.DocMove = new sx.classes.DocMove({$jsData});
JS
);
?>
