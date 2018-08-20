/*!
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 03.04.2015
 */
(function (sx, $, _) {
    sx.createNamespace('classes.shop', sx);

    /**
     * @events:
     *
     * beforeAddProduct
     * addProduct
     *
     * beforeRemoveBasket
     * removeBasket
     *
     * beforeUpdateBasket
     * updateBasket
     *
     * breforeAddDiscountCoupon
     * addDiscountCoupon
     *
     * breforeRemoveDiscountCoupon
     * removeDiscountCoupon
     *
     * change
     *
     */
    sx.classes.shop._App = sx.classes.Component.extend({

        _init: function () {
            var self = this;
            this.carts = [];

            this.bind('removeBasket addProduct updateBasket clearCart addDiscountCoupon removeDiscountCoupon', function (e, data) {
                self.trigger('change', {
                    'Shop': this
                });
            });
        },

        /**
         * @returns {sx.classes.AjaxQuery}
         */
        ajaxQuery: function () {
            return sx.ajax.preparePostQuery('/');
        },

        /**
         * @param Cart
         */
        registerCart: function (Cart) {
            if (!Cart instanceof sx.classes.shop._Cart) {
                throw new Error("Cart object must be instanceof sx.classes.shop._Cart");
            }

            this.carts.push(Cart);
        },


        /**
         * Apply a coupon to order
         *
         * @param coupon_code
         * @returns {sx.classes.shop._App}
         */
        addDiscountCoupon: function (coupon_code) {
            this.createAjaxAddDiscountCoupon().execute();
            return this;
        },

        /**
         * Apply a coupon to order
         *
         * @param coupon_code
         * @returns {*|sx.classes.AjaxQuery}
         */
        createAjaxAddDiscountCoupon: function (coupon_code) {
            var self = this;
            var ajax = sx.ajax.preparePostQuery(this.get('backend-add-discount-coupon'));

            ajax.setData({
                'coupon_code': coupon_code,
            });

            ajax.onBeforeSend(function (e, data) {
                self.trigger('beforeAddDiscountCoupon', {
                    'coupon_code': coupon_code,
                });
            });

            ajax.onSuccess(function (e, data) {
                self.set('cartData', data.response.data);

                self.trigger('addDiscountCoupon', {
                    'coupon_code': coupon_code,
                    'response': data.response,
                });
            });

            return ajax;
        },


        /**
         * Remove coupon to order
         *
         * @param coupon_code
         * @returns {sx.classes.shop._App}
         */
        removeDiscountCoupon: function (coupon_id) {
            this.createAjaxRemoveDiscountCoupon(coupon_id).execute();
            return this;
        },

        /**
         * Remove coupon to order
         *
         * @param coupon_code
         * @returns {*|sx.classes.AjaxQuery}
         */
        createAjaxRemoveDiscountCoupon: function (coupon_id) {
            var self = this;
            var ajax = sx.ajax.preparePostQuery(this.get('backend-remove-discount-coupon'));

            ajax.setData({
                'coupon_id': coupon_id,
            });

            ajax.onBeforeSend(function (e, data) {
                self.trigger('beforeRemoveDiscountCoupon', {
                    'coupon_id': coupon_id,
                });
            });

            ajax.onSuccess(function (e, data) {
                self.set('cartData', data.response.data);

                self.trigger('removeDiscountCoupon', {
                    'coupon_id': coupon_id,
                    'response': data.response,
                });
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

            ajax.onBeforeSend(function (e, data) {
                self.trigger('beforeAddProduct', {
                    'product_id': product_id,
                    'quantity': quantity,
                });
            });

            ajax.onSuccess(function (e, data) {
                self.set('cartData', data.response.data);

                self.trigger('addProduct', {
                    'product_id': product_id,
                    'quantity': quantity,
                    'response': data.response,
                });
            });

            return ajax;
        },

        /**
         * Adding product to cart
         *
         * @param product_id
         * @param quantity
         * @param additional
         * @returns {sx.classes.shop._App}
         */
        addProduct: function (product_id, quantity, additional) {
            this.createAjaxAddProduct(product_id, quantity, additional).execute();
            return this;
        },


        /**
         * Removing the basket position
         *
         * @param basket_id
         * @returns {sx.classes.shop._App}
         */
        removeBasket: function (basket_id) {
            this.createAjaxRemoveBasket(basket_id).execute();
            return this;
        },

        /**
         * Removing the basket position
         *
         * @param basket_id
         * @returns {*|sx.classes.AjaxQuery}
         */
        createAjaxRemoveBasket: function (basket_id) {
            var self = this;
            var ajax = sx.ajax.preparePostQuery(this.get('backend-remove-basket'));

            ajax.setData({
                'basket_id': Number(basket_id),
            });

            ajax.onBeforeSend(function (e, data) {
                self.trigger('beforeRemoveBasket', {
                    'basket_id': basket_id,
                });
            });

            ajax.onSuccess(function (e, data) {
                self.set('cartData', data.response.data);

                self.trigger('removeBasket', {
                    'basket_id': basket_id,
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
        createAjaxUpdateBasket: function (basket_id, quantity, additional) {
            var self = this;
            var ajax = sx.ajax.preparePostQuery(this.get('backend-update-basket'));

            additional = additional || {};

            ajax.setData({
                'basket_id': Number(basket_id),
                'quantity': Number(quantity),
                'additional': additional,
            });

            ajax.onBeforeSend(function (e, data) {
                self.trigger('beforeUpdateBasket', {
                    'basket_id': basket_id,
                    'quantity': quantity,
                });
            });

            ajax.onSuccess(function (e, data) {
                self.set('cartData', data.response.data);

                self.trigger('updateBasket', {
                    'basket_id': basket_id,
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
         * @param quantity
         * @param additional
         * @returns {sx.classes.shop._App}
         */
        updateBasket: function (basket_id, quantity, additional) {
            this.createAjaxUpdateBasket(basket_id, quantity, additional).execute();
            return this;
        },


        /**
         * Cleaning the entire basket
         *
         * @returns {*|sx.classes.AjaxQuery}
         */
        createAjaxClearCart: function () {
            var self = this;
            var ajax = sx.ajax.preparePostQuery(this.get('backend-clear-cart'));

            ajax.onBeforeSend(function (e, data) {
                self.trigger('beforeClearCart');
            });

            ajax.onSuccess(function (e, data) {
                self.set('cartData', data.response.data);

                self.trigger('clearCart', {
                    'response': data.response,
                });
            });

            return ajax;
        },


        /**
         * Cleaning the entire basket
         *
         * @returns {sx.classes.shop._App}
         */
        clearCart: function () {
            this.createAjaxClearCart().execute();
            return this;
        },


        /**
         * Number of items in basket
         *
         * @returns {Number|number}
         */
        getCountShopBaskets: function () {
            return Number(this.get('cartData').countShopBaskets);
        },


        /**
         * TODO: is deprecated;
         *
         * Save the state of the basket data about the customer type.
         *
         * @param buyer
         */
        saveBuyer: function (buyer) {
            var self = this;

            this.trigger('beforeSaveBuyer');

            var ajax = this.ajaxQuery().setUrl(this.get('backend-update-buyer'));
            ajax.setData({
                'buyer': buyer
            });

            ajax.onSuccess(function (e, data) {
                self.trigger('saveBuyer', {
                    'response': data.response,
                });
            });

            ajax.execute();
        }
    });

    sx.classes.shop.App = sx.classes.shop._App.extend({});

})(sx, sx.$, sx._);