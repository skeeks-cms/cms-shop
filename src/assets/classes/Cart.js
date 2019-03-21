/*!
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 03.04.2015
 */
(function (sx, $, _) {
    sx.createNamespace('classes.shop', sx);

    /**
     * Объект корзины
     * Слушает события магазина
     * Умеет обновляться update();
     *
     * @events:
     * update
     * beforeUpdate
     */
    sx.classes.shop._Cart = sx.classes.Component.extend({

        construct: function (Shop, id, opts) {
            var self = this;
            opts = opts || {};

            if (!Shop instanceof sx.classes.shop._App) {
                throw new Error("Shop object not found");
            }

            this.id = String(id);
            this.Shop = Shop;
            this.Shop.registerCart(this);

            //this.parent.construct(opts);
            this.applyParentMethod(sx.classes.Component, 'construct', [opts]); // TODO: make a workaround for magic parent calling
        },

        _init: function () {
            var self = this;

            this.Shop.bind('change', function (e, data) {
                self.update();
            });
        },

        update: function () {
            this.trigger('beforeUpdate', {
                'Cart': this
            });
            //throw new Error("Not implemented this method");
            return this;
        },

        /**
         * @returns {*|HTMLElement}
         * @constructor
         */
        JWrapper: function () {
            return $('#' + this.id);
        }
    });

    sx.classes.shop.Cart = sx.classes.shop._Cart.extend({});

    /**
     * Корзина которая перезагружается Pjax
     *
     * @options:
     * delay - задержка обновления контейнера
     */
    sx.classes.shop.CartPjax = sx.classes.shop.Cart.extend({

        _init: function () {
            this.applyParentMethod(sx.classes.shop.Cart, '_init', []);

            var self = this;

            self.JWrapper().on('pjax:complete', function () {
                self.trigger('update', {
                    'Cart': this
                });
            });
        },

        /**
         * @returns {sx.classes.shop.CartPjax}
         */
        update: function () {
            var self = this;

            this.trigger('beforeUpdate', {
                'Cart': this
            });

            _.delay(function () {
                var selector = '';
                
                if (self.JWrapper().get(0).id) {
                    selector = "#" + self.JWrapper().get(0).id;
                } else {
                    //todo depricated в jquery > 1.8
                    selector = self.JWrapper().selector;
                }
                
                $.pjax.reload(selector, {
                    push: false
                });
            }, Number(this.get('delay', 0)));

            return this;
        },
    });

})(sx, sx.$, sx._);