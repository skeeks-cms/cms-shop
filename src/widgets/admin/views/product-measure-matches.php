<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 02.03.2016
 */
/* @var $this yii\web\View */
/* @var $widget \skeeks\cms\shop\widgets\admin\ProductMeasureMatchesInputWidget */
/* @var $model \skeeks\cms\shop\models\ShopProduct */

$widget = $this->context;
$model = $widget->model;

$this->registerCss(<<<CSS
.sx-product-measure-matches-wrapper .sx-measure-base-value {
    width: 220px;
}
.sx-product-measure-matches-wrapper .sx-new-value {
    width: 160px;
}
.sx-product-measure-matches-wrapper .sx-new-measure {
    width: 40px;
    text-align: center;
}
.sx-measure-row {
    padding-top: 3px;
    padding-bottom: 3px;
}
CSS
);
?>

<?= \yii\helpers\Html::beginTag('div', $widget->options); ?>
    <div style="display: none;">
        <?= $element; ?>
    </div>

    <div class="sx-elements-wrapper">
    </div>

    <div style="display: none;">
        <div class="sx-template d-flex flex-row sx-measure-row">

            <div class="my-auto sx-measure-base-value">
                <div class="input-group">
                    <div class="input-group-prepend" style="min-width: 20px;">
                        <div class="input-group-text" style="min-width: 20px;">1</div>
                    </div>
                    <?= \yii\helpers\Html::listBox("measure", [], \yii\helpers\ArrayHelper::map(
                        \skeeks\cms\measure\models\CmsMeasure::find()
                            ->orderBy(['priority' => SORT_ASC])
                            ->andWhere(['!=', 'code', $model->measure_code])
                            ->all(),
                        'code',
                        'asShortText'
                    ), [
                        'class' => 'form-control',
                        'size'  => '1',
                    ]); ?>
                </div>

            </div>
            <div class="my-auto " style="width: 20px; text-align: center;">
                =
            </div>
            <div class="my-auto sx-new-value">
                <div class="input-group">
                    <input type="number" class="form-control" name="value" step="0.0000001">
                    <div class="input-group-append">
                        <span class="input-group-text"><?= $model->measure->symbol; ?></span>
                    </div>
                </div>
            </div>
            <div class="my-auto sx-new-measure">
                <button class="btn btn-xs sx-remove-row-btn"><i class="fas fa-remove"></i></button>
            </div>
        </div>
    </div>

    <button class="btn btn-secondary btn-xs sx-btn-add-row">
        <i class="fas fa-plus"></i> <?= \Yii::t('skeeks/cms', 'Add') ?>
    </button>


<?
\yii\jui\Sortable::widget();

$jsOptions = \yii\helpers\Json::encode($widget->clientOptions);

$this->registerCss(<<<CSS
.sx-elements-wrapper .row
{
    margin-top: 0px;
    margin-bottom: 10px;
    cursor: move;
}
CSS
);

$this->registerJs(<<<JS
(function(sx, $, _)
{
    sx.classes.FormMeasureMatches = sx.classes.Component.extend({
    
        _init: function()
        {
            var self = this;
            
            self.on("innerUpdate", function() {
                self._updateValue(); 
            });
        },
        
        _updateValue: function() {
            
            var self = this;
            
            var value = "";
            var valueObject = {};
            
            $(".sx-measure-row", self.getJElementsWrapper()).each(function() {
                
                var measure = String($("select", $(this)).val());
                var value = Number($("input", $(this)).val());
                
                valueObject[measure] = value;
            });
            
            if (_.size(valueObject)) {
                value = JSON.stringify(valueObject);
            }
            
            self.getJTextarea().empty().append(value);
        },
        
        getJTextarea: function() {
            return $("#" + this.get("inputId"), this.getJWrapper());
        },
        
        getJTemplate: function() {
            return $(".sx-template", this.getJWrapper());
        },
        
        
        getJWrapper: function() {
            return $('#' + this.get('id'));
        },
        
        /**
        * 
        * @returns {*|jQuery|HTMLElement}
        */
        getJElementsWrapper: function() {
            return $(".sx-elements-wrapper", this.getJWrapper());
        },
        
        _onDomReady: function()
        {
            this.counter = 0;
            var self = this;
            
            //Удалить строку
            self.getJWrapper().on("change", "input, select", function() {
                self.trigger("innerUpdate");
                return false;
            });
            
            self.getJWrapper().on("click", ".sx-remove-row-btn", function() {
                var jMeasureRow = $(this).closest(".sx-measure-row");
                console.log(jMeasureRow);
                jMeasureRow.slideUp().remove();
                self.trigger("innerUpdate");
                return false;
            });
            
            //Добавить строку
            self.getJWrapper().on('click', '.sx-btn-add-row', function() {
                self._createRow();
                self.trigger("innerUpdate");
                return false;
            });
            
            
            var jElementsWrapper = $('.sx-elements-wrapper', self.getJWrapper());
            jElementsWrapper.sortable({
                /*cursor: "move",
                handle: ".sx-btn-move",*/
                forceHelperSize: true,
                forcePlaceholderSize: true,
                opacity: 0.5,
                placeholder: "ui-state-highlight",
            });
            
            
            var value = self.getJTextarea().text();
            if (value) {
                var jsonObject = JSON.parse(value);
                _.each(jsonObject, function(val, key) {
                    self._createRow(key, val);
                });
            }
        },
        
        
        /**
        * Добавление строки
        * @param data
        * @private
        */
        _createRow: function(key, val) {
            var self = this;
            
            var jRow = self.getJTemplate().clone();
            
            jRow.removeClass("sx-template");
            jRow.appendTo($('.sx-elements-wrapper', self.getJWrapper()));

            if (key) {
                $("select", jRow).val(key);
            }
            
            if (val) {
                $("input", jRow).val(val);
            }
            
            this.counter = this.counter + 1;
        }
    });
    new sx.classes.FormMeasureMatches({$jsOptions});
})(sx, sx.$, sx._);
JS
); ?>
<?= \yii\helpers\Html::endTag('div'); ?>