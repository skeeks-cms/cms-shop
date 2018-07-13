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
            var self = this;

            this.jWrapper = $("#" + this.get('id'));

            this.jElement = $(".sx-element textarea", this.jWrapper);
            this.jContent = $(".sx-content", this.jWrapper);

            /*this.jConditionElement = $("name=[condition]", this.jWrapper);
            this.jAndorElement = $("name=[andor]", this.jWrapper);
            this.jEqualityElement = $("name=[equality]", this.jWrapper);*/

            this.value = this.jElement.val();

            //this.jContent.append(this.renderRule());

            $('select', this.jWrapper).on('change', function() {
                self.jElement.val(JSON.stringify(self.getValue()));

                if ($(this).data('no-update') === true) {
                    return false;
                }
                self.jElement.change();

                return false;
            });

            $('input', this.jWrapper).on('change', function() {
                self.jElement.val(JSON.stringify(self.getValue()));

                if ($(this).data('no-update') === true) {
                    return false;
                }

                self.jElement.change();

                return false;
            });

            $('.sx-remove', this.jWrapper).on('click', function() {
                $(this).closest(".sx-row").remove();
                self.jElement.val(JSON.stringify(self.getValue())).change();
                return false;
            });

            $('.sx-create-first', this.jWrapper).on('click', function() {

                self.jElement.val(JSON.stringify({'type':'group','condition':'equal','rules_type':'and'})).change();
                return false;
            });

            $('.sx-add-condition', this.jWrapper).on('click', function() {
                var val = $(this).closest(".sx-add").children().children().children("[name=condition]").val();

                $(this).closest(".sx-row");

                var jRules = $(this).closest(".sx-row").children(".sx-rules");


                var row = '';

                if (val == 'group') {
                    row = $("<div>", {'class' : 'sx-row sx-group', 'data-type': 'group'});
                } else {
                    row = $("<div>", {'class' : 'sx-row sx-rule', 'data-type': 'rule', 'data-field': val});
                }

                jRules.append(
                    row
                );

                self.jElement.val(JSON.stringify(self.getValue())).change();

                return false;
            });
        },

        getValue: function()
        {
            var self = this;
            var result = {};

            if (this.jContent.children().length) {
                result = self.getValueByJRule(this.jContent.children());
            } else {
                result = '';
            }


            return result;
        },

        getValueByJRule: function(jRule)
        {
            var self = this;
            var result = {};

            result.type = jRule.data('type');

            if (result.type == 'group') {
                result.rules_type = jRule.children('.sx-conditions').children().children(".sx-andor").children("select").val();
                result.condition = jRule.children('.sx-conditions').children().children(".sx-condition").children("select").val();

                result.rules_type = result.rules_type || 'and';
                result.condition = result.condition || 'equal';
            } else {
                result.field = jRule.children().children(".sx-field").children().data('field');
                result.condition = jRule.children().children(".sx-andor").children("select").val();
                result.value = jRule.children().children(".sx-value").find('.sx-value-element').val();

                result.condition = result.condition || 'equal';
                result.field = result.field || jRule.data('field');
            }


            var jRules = jRule.children('.sx-rules').children('.sx-row');

            if (jRules.length) {
                var rules = [];

                jRules.each(function() {
                    rules.push(
                        self.getValueByJRule($(this))
                    );
                });

                result.rules = rules;
            }

            return result;
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