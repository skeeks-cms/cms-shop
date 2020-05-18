/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */
(function (sx, $, _) {
    sx.classes.ProductList = sx.classes.Component.extend({

        _init: function () {

        },

        _onDomReady: function () {
            $("body").on("click", ".sx-offers-trigger", function() {
                var jTd = $(this).closest("td");
                var jTr = $(this).closest("tr");
                var jBtn = $(".sx-controller-actions-td .sx-btn-ajax-actions", jTr);
                jBtn.trigger("goAction", {"action" : "offers"});

                var jOffers = $(".sx-offers-wrapper", jTd);
                var jAllWrappers = $(".sx-hidden-wrapper", jTd);

                jAllWrappers.slideUp();
                jAllWrappers.removeClass("sx-opened");

                if (jOffers.hasClass("sx-opened")) {
                    jOffers.slideUp();
                    jOffers.removeClass("sx-opened");
                } else {
                    jOffers.slideDown();
                    jOffers.addClass("sx-opened");
                }
                return false;
            });
            
            $("body").on("click", ".sx-supplier-trigger", function() {
                var jTd = $(this).closest("td");
                var jOffers = $(".sx-supplier-offers-wrapper", jTd);
                var jAllWrappers = $(".sx-hidden-wrapper", jTd);

                jAllWrappers.slideUp();
                jAllWrappers.removeClass("sx-opened");

                if (jOffers.hasClass("sx-opened")) {
                    jOffers.slideUp();
                    jOffers.removeClass("sx-opened");
                } else {
                    jOffers.slideDown();
                    jOffers.addClass("sx-opened");
                }
                return false;
            });
            $("body").on("click", ".sx-seller-trigger", function() {
                var jTd = $(this).closest("td");
                var jOffers = $(".sx-seller-offers-wrapper", jTd);
                var jAllWrappers = $(".sx-hidden-wrapper", jTd);

                jAllWrappers.slideUp();
                jAllWrappers.removeClass("sx-opened");

                if (jOffers.hasClass("sx-opened")) {
                    jOffers.slideUp();
                    jOffers.removeClass("sx-opened");
                } else {
                    jOffers.slideDown();
                    jOffers.addClass("sx-opened");
                }
                return false;
            });
        },

        _onWindowReady: function () {
        }
    });
})(sx, sx.$, sx._);