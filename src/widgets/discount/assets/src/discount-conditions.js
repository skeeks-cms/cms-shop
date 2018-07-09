/**
* @link https://cms.skeeks.com/
* @copyright Copyright (c) 2010 SkeekS
* @license https://cms.skeeks.com/license/
* @author Semenov Alexander <semenov@skeeks.com>
*/
(function(sx, $, _)
{
    sx.classes.DiscountWidget = sx.classes.Component.extend({

        _init: function()
        {

        },

        _onDomReady: function()
        {
            this.jWrapper = $("#" + this.get('id'));

            this.jElement = $(".sx-element textarea", this.jWrapper);
            this.jContent = $(".sx-content", this.jWrapper);

            /*this.jConditionElement = $("name=[condition]", this.jWrapper);
            this.jAndorElement = $("name=[andor]", this.jWrapper);
            this.jEqualityElement = $("name=[equality]", this.jWrapper);*/

            this.value = this.jElement.val();

            //this.jContent.append(this.renderRule());
            console.log(this.getValue(this.jContent, true));





        },

        getValue: function(jRules)
        {
            jRules = jRules || null;

            if (jRules) {
                var type = this.jContent.children('.sx-row').data('type');
                /*var type = this.jContent.children('.sx-row').data('type');*/
            }


            if (ifFirst) {
                var result = {};

                this.jContent.children('.sx-row').each(function() {

                });

            } else {
                var result = [];

                this.jContent.children('.sx-row').each(function() {

                });

            }



            //this.jContent.append(this.renderRule());
        },

        renderRule: function() {
            var jRule = $("<div>", {'class': 'sx-rule'});

            var jRuleRules = $("<div>", {'class': 'sx-rules'});
            var jRuleAdd = $("<div>", {'class': 'sx-add'}).append('Добавить условие');
            var jRuleConditions = $("<div>", {'class': 'sx-conditions'}).append('Все условия');

            jRule.append(jRuleConditions).append(jRuleRules).append(jRuleAdd);

            return jRule;
        }
    });

    sx.classes.DiscountRule = sx.classes.Component.extend({

        _init: function()
        {

        },

        _onDomReady: function()
        {
            this.jWrapper = $("#" + this.get('id'));

            this.jElement = $(".sx-element textarea", this.jWrapper);
            this.jContent = $(".sx-content", this.jWrapper);

            this.value = this.jElement.val();

            this.jContent.append(this.renderRule());
        },

    });

})(sx, sx.$, sx._);