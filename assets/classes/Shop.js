/*!
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 03.04.2015
 */
(function(sx, $, _)
{
    sx.createNamespace('classes.shop', sx);

    /**
     * @events:
     * beforeAddProduct
     * addProduct
     *
     * beforeRemoveBasket
     * removeBasket
     *
     * beforeUpdateBasket
     * updateBasket
     *
     * change
     *
     */
    sx.classes.shop._App = sx.classes.Component.extend({

        _init: function()
        {
            var self = this;
            this.carts = [];

            this.bind('removeBasket addProduct updateBasket clearCart', function(e, data)
            {
                self.trigger('change', {
                    'Shop' : this
                });
            });
        },

        /**
         * @returns {sx.classes.AjaxQuery}
         */
        ajaxQuery: function()
        {
            return sx.ajax.preparePostQuery('/');
        },

        /**
         * @param Cart
         */
        registerCart: function(Cart)
        {
            if (!Cart instanceof sx.classes.shop._Cart)
            {
                throw new Error("Cart object must be instanceof sx.classes.shop._Cart");
            }

            this.carts.push(Cart);
        },

        /**
         * Добавление продукта в корзину
         * @param product_id
         * @param quantity
         * @param data
         */
        addProduct: function(product_id, quantity, data)
        {
            var self = this;

            //TODO: реализовать
            data = data || {};

            this.trigger('beforeAddProduct', {
                'product_id'    : product_id,
                'quantity'      : quantity,
            });

            var ajax = this.ajaxQuery().setUrl(this.get('backend-add-product'));

            ajax.setData({
                'product_id'    : Number(product_id),
                'quantity'      : Number(quantity),
            });

            ajax.onSuccess(function(e, data)
            {
                self.set('cartData', data.response.data);

                self.trigger('addProduct', {
                    'product_id'    : product_id,
                    'quantity'      : quantity,
                    'response'      : data.response,
                });
            });

            ajax.execute();
        },


        removeBasket: function(basket_id)
        {
            var self = this;

            this.trigger('beforeRemoveBasket', {
                'basket_id' : basket_id,
            });

            var ajax = this.ajaxQuery().setUrl(this.get('backend-remove-basket'));

            ajax.setData({
                'basket_id' : Number(basket_id),
            });

            ajax.onSuccess(function(e, data)
            {
                self.set('cartData', data.response.data);

                self.trigger('removeBasket', {
                    'basket_id'    : basket_id,
                    'response'     : data.response,
                });
            });

            ajax.execute();
        },

        updateBasket: function(basket_id, quantity, data)
        {
            var self = this;

            //TODO
            data = data || {};

            this.trigger('beforeUpdateBasket', {
                'basket_id'     : basket_id,
                'quantity'      : quantity,
            });

            var ajax = this.ajaxQuery().setUrl(this.get('backend-update-basket'));

            ajax.setData({
                'basket_id' : Number(basket_id),
                'quantity'  : Number(quantity),
            });

            ajax.onSuccess(function(e, data)
            {
                self.set('cartData', data.response.data);

                self.trigger('updateBasket', {
                    'basket_id'     : basket_id,
                    'quantity'      : quantity,
                    'response'      : data.response,
                });
            });

            ajax.execute();
        },


        clearCart: function()
        {
            var self = this;

            this.trigger('beforeClearCart');

            var ajax = this.ajaxQuery().setUrl(this.get('backend-clear-cart'));

            ajax.onSuccess(function(e, data)
            {
                self.set('cartData', data.response.data);

                self.trigger('clearCart', {
                    'response'      : data.response,
                });
            });

            ajax.execute();
        },

        /**
         * Сохранить в состоянии корзины данные о типе покупателя.
         *
         * @param buyer
         */
        saveBuyer: function(buyer)
        {
            var self = this;

            this.trigger('beforeSaveBuyer');

            var ajax = this.ajaxQuery().setUrl(this.get('backend-update-buyer'));
            ajax.setData({
                'buyer' : buyer
            });

            ajax.onSuccess(function(e, data)
            {
                self.trigger('saveBuyer', {
                    'response'      : data.response,
                });
            });

            ajax.execute();
        },
    });

    sx.classes.shop.App = sx.classes.shop._App.extend({});

})(sx, sx.$, sx._);