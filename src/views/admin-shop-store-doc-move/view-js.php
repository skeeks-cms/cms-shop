<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */
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
            
            //Убрать
            $("body").on('click', '.sx-remove-row-btn', function (e) {
                self.createAjaxRemoveOrderItem($(this).closest("tr").data("id")).execute();
                return false;
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
        /**
         * Removing the basket position
         *
         * @param order_item_id
         * @returns {*|sx.classes.AjaxQuery}
         */
        createAjaxRemoveOrderItem: function (order_item_id) {
            var self = this;
            var ajax = sx.ajax.preparePostQuery(this.get('backend-remove-item'));

            ajax.setData({
                'id': Number(order_item_id),
            });

            ajax.onSuccess(function (e, data) {
                self.trigger('removeOrderItem', {
                });
                
                $.pjax.reload("#sx-selected-proocuts");

            });

            return ajax;
        },
        
        
    });
})(sx, sx.$, sx._);

JS
);