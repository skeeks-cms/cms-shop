/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */
(function (sx, $, _) {
    sx.classes.CashierApp = sx.classes.Component.extend({

        _init: function () {
            var self = this;

            this.productBlocker = null;
            this.userSearchBlocker = null;

            this.lastKeyTime = new Date().getTime();

            this.on("orderUpdate", function () {
                self.renderOrderItems();
                self.renderProductLabels();
                self.renderOrderResults();
                self.renderUserSelected();
            });
        },

        _onDomReady: function () {
            var self = this;

            self.loadProducts();

            self.getJSearch().on("focus", function () {

            });

            self.getJSearch().on("keyup", function () {
                //Не нужно сразу применять нужно чуть подождать
                self.lastKeyTime = new Date().getTime();
                self.blockProducts();
                self.updateSearchButtons();
                setTimeout(function () {
                    var newTime = new Date().getTime();
                    var delta = newTime - self.lastKeyTime;
                    if (delta >= 1000) {
                        self.loadProducts();

                    }
                }, 1000);
            });

            //Поиск пользователя
            $("body").on("keyup", ".sx-user-search", function () {
                //Не нужно сразу применять нужно чуть подождать
                self.lastKeyTime = new Date().getTime();
                self.blockUserSearch();
                setTimeout(function () {
                    var newTime = new Date().getTime();
                    var delta = newTime - self.lastKeyTime;
                    if (delta >= 1000) {
                        self.loadUsers();
                    }
                }, 1000);
            });

            //нажатие на кнопку стереть результаты поиска
            $("body").on("click", ".clear-button", function () {
                self.getJSearch().val("");
                self.blockProducts();
                self.loadProducts();
                self.updateSearchButtons();
                return false;
            });

            //Наведение на кнопку открытия смены
            $("body").on("mouseenter", ".ClosedShift button", function () {
                $(".ClosedShift").addClass("hover");
                $(".ClosedShift i").removeClass("fa-lock").addClass("fa-unlock");

            });

            $("body").on("mouseleave", ".ClosedShift button", function () {
                $(".ClosedShift").removeClass("hover");
                $(".ClosedShift i").removeClass("fa-unlock").addClass("fa-lock");
            });

            $("body").on("click", ".ClosedShift button", function () {
                $("#sx-shift-create").modal("show");
                return false;
            });

            //Меню открыть закрыть
            $("body").on("click", ".sx-menu-btn", function () {
                var jMenu = $(this).closest(".sx-menu");
                var jMenuContent = $(".sx-menu-content", jMenu);
                if (jMenuContent.hasClass("sx-opened")) {
                    jMenuContent.removeClass("sx-opened").addClass("sx-closed");
                } else {
                    jMenuContent.removeClass("sx-closed").addClass("sx-opened");
                }
                return false;
            });
            //Меню открыть закрыть
            $("body").on("click", ".sx-checkout-menu-trigger", function () {
                if ($(this).closest(".sx-checkout-btn-wrapper").hasClass("sx-lock")) {
                    return false;
                }
                var jMenuContent = $(".sx-checkout-menu");
                if (jMenuContent.hasClass("sx-opened")) {
                    jMenuContent.removeClass("sx-opened").addClass("sx-closed");
                } else {
                    jMenuContent.removeClass("sx-closed").addClass("sx-opened");
                }
                return false;
            });
            
            //Принять деньги финальное окно
            $("body").on("click", ".sx-checkout-btn", function () {
                if ($(this).closest(".sx-checkout-btn-wrapper").hasClass("sx-lock")) {
                    return false;
                }
                //Если не указан покупатель, открыть окно выбора покупателя
                if (!self.getOrder().cms_user_id) {
                    self.renderUserSearch();
                } else {
                    $("#sx-final-modal").addClass("open");
                }

                return false;
            });
            //Принять деньги финальное окно
            $("body").on("click", ".buttons .button", function () {
                var jWrapper = $(this).closest(".buttons");
                $(".button", jWrapper).removeClass("active");
                $(this).addClass("active");

                return false;
            });

            //Открыть заказ
            $("body").on("click", ".sx-create-order", function () {
                var jBlocker = sx.block("#sx-final-modal");
                jBlocker.block();

                var data = {
                    'comment': $("#sx-order-comment").val(),
                    'payment_type': $("#sx-payment-type .active").data("type"),
                }

                var ajaxQuery = self.createAjaxOrderCreate(data);
                var Handler = new sx.classes.AjaxHandlerStandartRespose(ajaxQuery);
                Handler.on("error", function() {
                    jBlocker.unblock();
                });
                Handler.on("success", function() {
                    jBlocker.unblock();
                    $("#sx-order-comment").empty();
                    $(".sx-close-modal").click();
                });

                ajaxQuery.execute();

                return false;
            });

            //Закрыть меню по клику в пустое место
            $("body").on('click', function (e) {
                if (!$(event.target).closest('.sx-menu-content').length && !$(event.target).is('.sx-menu-content')) {
                    $(".sx-menu-content").removeClass("sx-opened").addClass("sx-closed");
                }
                if (!$(event.target).closest('.sx-checkout-menu').length && !$(event.target).is('.sx-checkout-menu')) {
                    $(".sx-checkout-menu").removeClass("sx-opened").addClass("sx-closed");
                }
            });

            //Закрыть модальное окно
            $("body").on('click', ".sx-close-modal", function (e) {
                $(this).closest(".sx-modal-overlay").removeClass("open");
                return false;
            });

            //Закрыть смену
            $("body").on('click', '.sx-close-shift-btn', function (e) {
                $(".sx-menu-content").removeClass("sx-opened").addClass("sx-closed");
                $("#sx-shift-close").modal("show");
                return false;
            });

            //Добавить товар в корзину
            $("body").on('click', '.catalog-card', function (e) {
                var jCard = $(this);
                jCard.css("transform", "scale(0.95)");
                setTimeout(function() {
                    jCard.css("transform", "");
                }, 300);
                var ajaxQuery = self.createAjaxAddProduct($(this).data("id"), 1);
                var Handler = new sx.classes.AjaxHandlerStandartRespose(ajaxQuery, {
                    'allowResponseSuccessMessage' : false
                });
                ajaxQuery.execute();

                return false;
            });
            //Убрать из корзины
            $("body").on('click', '.sx-delete-order-item', function (e) {
                self.createAjaxRemoveOrderItem($(this).closest("tr").data("order_item_id")).execute();
                return false;
            });
            //Очистить всю корзину
            $("body").on('click', '.sx-clear-order-items', function (e) {
                self.createAjaxClearOrderItems().execute();
                return false;
            });

            /**
             * Обновление позиции корзины
             */
            $("body").on('change', '.sx-quantity input', function (e) {
                self.createAjaxUpdateOrderItem($(this).closest("tr").data("order_item_id"), $(this).val()).execute();
                return false;
            });


            //переход в поиск клиента
            $("body").on('click', '.sx-user-selected', function (e) {
                self.renderUserSearch();

                return false;
            });

            //Отмена поиска клиента
            $("body").on('click', '.sx-user-find-clear', function (e) {
                self.renderUserSelected();
                return false;
            });


            //Выбор пользователя
            $("body").on('click', '.sx-user-find-menu .item', function (e) {
                var ajaxQuery = self.createAjaxUpdateOrderUser($(this).data("id"));

                /*ajaxQuery.on("success", function() {
                    self.renderUserSelected();
                });*/

                ajaxQuery.execute();

                return false;
            });

            //закрыть смену
            $("body").on('click', '.sx-close-shift-btn-submit', function (e) {
                var ajaxQuery = sx.ajax.preparePostQuery(self.get("backend_close_shift"));

                var jBtn = $(this);

                var blocker = sx.block("#sx-shift-close .modal-body");
                var handler = new sx.classes.AjaxHandlerStandartRespose(ajaxQuery, {
                    'blocker': blocker
                });

                handler.on("stop", function () {
                    blocker.unblock();
                });

                handler.on("error", function () {
                    blocker.unblock();
                });

                handler.on("success", function (e, response) {
                    jBtn.closest(".modal").modal("hide");
                    setTimeout(function () {
                        window.location.reload();
                    }, 500);
                });

                ajaxQuery.execute();
                return false;
            });

            this.renderOrderItems();
            this.renderProductLabels();
            this.renderOrderResults();

        },

        /**
         * Перерисовка кнопок поискового блока
         * @returns {sx.classes.CashierApp}
         */
        renderOrderResults: function () {
            var cart = this.getOrder();

            this._updateOrderResultBlock("sx-money-items", cart.moneyItems.amount, cart.moneyItems.convertAndFormat);
            this._updateOrderResultBlock("sx-money-delivery", cart.moneyDelivery.amount, cart.moneyDelivery.convertAndFormat);
            this._updateOrderResultBlock("sx-money-vat", cart.moneyVat.amount, cart.moneyVat.convertAndFormat);
            this._updateOrderResultBlock("sx-money-discount", cart.moneyDiscount.amount, cart.moneyDiscount.convertAndFormat);
            this._updateOrderResultBlock("sx-money", cart.money.amount, cart.money.convertAndFormat);
            this._updateOrderResultBlock("sx-weight", cart.weight.value, cart.weight.convertAndFormat);
        },

        _updateOrderResultBlock: function (css_class, value, formatedValue) {
            var jBlocks = $("." + css_class);
            value = Number(value);

            jBlocks.each(function () {
                var currentValue = Number($(this).data("value"));

                if (value != currentValue) {

                    //Если значение меняется
                    var jChangeBlock = $(this);
                    jChangeBlock.empty().append(formatedValue);
                    jChangeBlock.data("value", value);

                    setTimeout(function () {
                        jChangeBlock.addClass("sx-blink-text");
                    }, 400);

                    setTimeout(function () {
                        jChangeBlock.removeClass("sx-blink-text");
                    }, 900);
                }

                var jBlock = $(this).closest(".sx-order-result-block");

                if (jBlock.length > 0) {
                    if (value > 0) {
                        jBlock.removeClass("sx-hidden");
                    } else {
                        jBlock.addClass("sx-hidden");
                        /*setTimeout(function() {
                            jBlock.addClass("sx-hidden");
                        }, 2000);*/

                    }
                }
            });

            return this;
        },

        /**
         * Перерисовка кнопок поискового блока
         * @returns {sx.classes.CashierApp}
         */
        renderProductLabels: function () {
            var self = this;

            var jCard = $(".catalog-card", self.getJProducts());
            jCard.removeClass("active");

            var items = self.getOrder().items;
            items.forEach(function (value) {
                var addLabel = $(".catalog-card[data-id=" + value.shop_product_id + "]", self.getJProducts());
                if (addLabel.length) {
                    addLabel.addClass("active");
                    $(".label", addLabel).empty().text(value.quantity);
                }
            });

            return this;
        },
        /**
         * Перерисовка кнопок поискового блока
         * @returns {sx.classes.CashierApp}
         */
        updateSearchButtons: function () {
            var self = this;

            if (self.getJSearch().val()) {
                self.getJSearchBtns().empty().append('<button type="button" class="action clear-button">очистить</button>');
            } else {
                /*self.getJSearchBtns().empty().append('<button type="button" class="action back"><i class="fa icon fa-arrow-left fa-fw"></i></button>');*/
                self.getJSearchBtns().empty().append('<div class="main-tabs"><div name="catalog" icon="cubes" class="main-tabs-item"><i class="fa icon fa-cubes fa-fw"></i></div><div name="categories" icon="tags" class="main-tabs-item"><i class="fa icon fa-tags fa-fw"></i></div><div name="groups" icon="folder" class="main-tabs-item main-tabs-item--active"><i class="fa icon fa-folder fa-fw"></i></div></div>');
            }

            return this;
        },

        /**
         * @returns {sx.classes.CashierApp}
         */
        blockProducts: function () {
            if (this.productBlocker === null) {
                this.productBlocker = new sx.classes.Blocker(".catalogList");
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
        /**
         * @returns {sx.classes.CashierApp}
         */
        blockUserSearch: function () {
            if (this.userSearchBlocker === null) {
                this.userSearchBlocker = new sx.classes.Blocker(".sx-user-find-menu");
            }
            this.userSearchBlocker.block();
            return this;
        },

        /**
         * @returns {sx.classes.CashierApp}
         */
        unblockUserSearch: function () {
            this.userSearchBlocker.unblock();
            return this;
        },

        /**
         * Состояние поиска клиента
         * @returns {sx.classes.CashierApp}
         */
        renderUserSearch: function () {
            $(".sx-user-selected-element").hide();
            $(".sx-user-find-element").show();
            $(".sx-user-search").focus();
            $(".sx-user-find-clear").css("display", "flex");
            this.loadUsers();
            return this;
        },

        /**
         * Состояние поиска клиента
         * @returns {sx.classes.CashierApp}
         */
        renderUserSelected: function () {
            $(".sx-user-selected-element").show();
            $(".sx-user-find-element").hide();
            $(".sx-user-search").val("");
            if (this.getOrder().cms_user_id) {
                $(".sx-user").empty().append(this.getOrder().cmsUser.shortDisplayName).removeClass("sx-user-not-selected");
            }
            return this;
        },

        /**
         * @returns {sx.classes.CashierApp}
         */
        renderOrderItems: function () {
            var self = this;
            if (this.getOrder().amount > 0) {
                $(".sx-checkout-btn-wrapper").removeClass("sx-lock");
            } else {
                $(".sx-checkout-btn-wrapper").addClass("sx-lock");
            }

            if (this.getOrder().items.length) {

                $(".calculation").show();


                var jEmptyItems = $(".products-sale-list-tmpl").clone();
                jEmptyItems.removeClass("products-sale-list-tmpl").addClass("products-sale-list");

                var jItemTemplate = $(".products-sale-list-item-tmpl").clone();

                $(".sx-order-items-wrapper").empty().append(jEmptyItems);
                var jTable = $(".products-sale-list", ".sx-order-items-wrapper");
                var jTableBody = $("tbody", jTable);

                jTableBody.empty();

                var items = self.getOrder().items;
                items.forEach(function (value) {
                    console.log(value);
                    var jItemTemplate = $(".products-sale-list-item-tmpl").clone();
                    jItemTemplate.removeClass("products-sale-list-item-tmpl");
                    jItemTemplate.attr("data-order_item_id", value.id);

                    $(".sx-name", jItemTemplate).append(value.name);
                    $(".sx-price", jItemTemplate).append(value.itemMoney.convertAndFormat);
                    $(".sx-quantity input", jItemTemplate).val(value.quantity);
                    $(".sx-total", jItemTemplate).append(value.totalMoney.convertAndFormat);

                    jTableBody.append(jItemTemplate);
                });

            } else {
                $(".calculation").hide();
                var jEmptyItems = $(".sx-no-order-items").clone();
                $(".sx-order-items-wrapper").empty().append(jEmptyItems);

            }

        },

        /**
         * @returns {sx.classes.CashierApp}
         */
        loadProducts: function () {
            var self = this;

            self.getJProducts().empty();

            var ajaxQuery = sx.ajax.preparePostQuery(this.get("backend_products"), {
                'q': self.getJSearch().val(),
            });

            var handler = new sx.classes.AjaxHandlerStandartRespose(ajaxQuery, {
                'enableBlocker': false
            });

            handler.on("stop", function () {
                self.unblockProducts();
            });

            handler.on("success", function (e, response) {
                self.getJProducts().empty().append(response.data.content);
                self.renderProductLabels();
            });

            ajaxQuery.execute();

            return this;
        },

        /**
         * @returns {sx.classes.CashierApp}
         */
        loadUsers: function () {
            var self = this;

            var jUserInput = $(".sx-user-search");
            var jUserFindMenu = $(".sx-user-find-menu");
            jUserFindMenu.empty();

            var ajaxQuery = sx.ajax.preparePostQuery(this.get("backend-find-users"), {
                'q': jUserInput.val(),
            });

            var handler = new sx.classes.AjaxHandlerStandartRespose(ajaxQuery, {
                'enableBlocker': false
            });

            handler.on("stop", function () {
                self.unblockUserSearch()
            });

            handler.on("success", function (e, response) {
                jUserFindMenu.empty().append(response.data.content);
            });

            ajaxQuery.execute();

            return this;
        },

        getJSearchBtns: function () {
            return $(".sx-block-search .action");
        },

        getJSearch: function () {
            return $(".sx-block-search input");
        },

        getJProducts: function () {
            return $(".catalogList");
        },

        /**
         * @param order
         * @returns {sx.classes.CashierApp}
         */
        setOrder: function (order) {
            this.set("order", order);
            this.trigger("orderUpdate");
            //Это чтобы закрыть лишние окна
            $("body").click();
            return this;
        },

        getOrder: function () {
            return this.get("order");
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
                self.setOrder(data.response.data.order);
                self.trigger('addProduct', {
                    'product_id': product_id,
                    'quantity': quantity,
                    'response': data.response,
                });
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
            var ajax = sx.ajax.preparePostQuery(this.get('backend-remove-order-item'));

            ajax.setData({
                'order_item_id': Number(order_item_id),
            });

            ajax.onSuccess(function (e, data) {
                self.setOrder(data.response.data.order);

                self.trigger('removeOrderItem', {
                    'order_item_id': order_item_id,
                    'response': data.response,
                });
            });

            return ajax;
        },
        /**
         * Removing the basket position
         *
         * @param order_item_id
         * @returns {*|sx.classes.AjaxQuery}
         */
        createAjaxClearOrderItems: function () {
            var self = this;
            var ajax = sx.ajax.preparePostQuery(this.get('backend-clear-order-items'));
            ajax.onSuccess(function (e, data) {

                self.setOrder(data.response.data.order);

                self.trigger('clearCart', {
                    'response': data.response,
                });
            });

            return ajax;
        },


        /**
         * Updating the positions of the basket, such as changing the number of
         *
         * @param basket_id
         * @returns {*|sx.classes.AjaxQuery}
         */
        createAjaxUpdateOrderItem: function (order_item_id, quantity, additional) {
            var self = this;
            var ajax = sx.ajax.preparePostQuery(this.get('backend-update-order-item'));

            additional = additional || {};

            ajax.setData({
                'order_item_id': Number(order_item_id),
                'quantity': Number(quantity),
                'additional': additional,
            });


            ajax.onSuccess(function (e, data) {
                self.setOrder(data.response.data.order);

                self.trigger('updateOrderItem', {
                    'order_item_id': order_item_id,
                    'quantity': quantity,
                    'response': data.response,
                });
            });

            return ajax;
        },

        /**
         * Updating the positions of the basket, such as changing the number of
         *
         * @param basket_id
         * @returns {*|sx.classes.AjaxQuery}
         */
        createAjaxUpdateOrderUser: function (user_id) {
            var self = this;
            var ajax = sx.ajax.preparePostQuery(this.get('backend-update-order-user'));

            ajax.setData({
                'user_id': Number(user_id),
            });


            ajax.onSuccess(function (e, data) {
                self.setOrder(data.response.data.order);

                self.trigger('updateOrderUser', {
                    'user_id': user_id,
                });
            });

            return ajax;
        },
        /**
         * Updating the positions of the basket, such as changing the number of
         *
         * @param basket_id
         * @returns {*|sx.classes.AjaxQuery}
         */
        createAjaxOrderCreate: function (data) {
            var self = this;
            var ajax = sx.ajax.preparePostQuery(this.get('backend-order-create'));

            ajax.setData(data);

            ajax.onSuccess(function (e, data) {
                self.setOrder(data.response.data.order);

                self.trigger('orderCreate');
            });

            return ajax;
        },

    });
})(sx, sx.$, sx._);