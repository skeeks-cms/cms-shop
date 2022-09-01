<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 02.03.2016
 */
/* @var $this yii\web\View */
/* @var $widget \skeeks\cms\shop\widgets\admin\ProductbarcodeMatchesInputWidget */
/* @var $model \skeeks\cms\shop\models\ShopProduct */

$widget = $this->context;
$model = $widget->model;

$this->registerCss(<<<CSS

.sx-product-barcodes-wrapper .sx-new-barcode {
    width: 40px;
    text-align: center;
}
.sx-barcode-row {
    padding-top: 3px;
    padding-bottom: 3px;
}
.sx-barcode-row .sx-remove-row-btn {
    cursor: pointer;
    font-size: 16px;
    color: black;
    opacity: 0.5;
}
.sx-barcode-row .sx-remove-row-btn:hover {
    opacity: 0.8;
}

.popover {
    max-width: none;
}
CSS
);
?>

<?= \yii\helpers\Html::beginTag('div', $widget->options); ?>
    <div style="display: none;">
        <input type="text" class="form-control" name="<?php echo \yii\helpers\Html::getInputName($widget->model, $widget->attribute); ?>" style="min-width: 200px;">
    </div>

    <div class="sx-elements-wrapper">
    </div>

    <div style="display: none;">
        <div class="sx-template d-flex flex-row sx-barcode-row">

            <div class="my-auto sx-barcode-base-value">
                <div class="input-group" style="min-width: 280px;">
                    <!--<div class="input-group-prepend" style="min-width: 20px;">
                        <div class="input-group-text" style="min-width: 20px;">тип</div>
                    </div>-->
                    <?= \yii\helpers\Html::listBox('barcode_type', \skeeks\cms\shop\models\ShopProductBarcode::TYPE_EAN13, \skeeks\cms\shop\models\ShopProductBarcode::getBarcodeTypes(), [
                        'class' => 'form-control',
                        'size'  => '1',
                        'style'  => 'width: 80px; max-width: 80px;',
                    ]); ?>

                    <input type="text" class="form-control" name="value" style="min-width: 200px;">
                </div>

            </div>
            <div class="my-auto sx-new-barcode">
                <div class="sx-remove-row-btn">
                    ×
                </div>
            </div>
        </div>
    </div>

    <button class="btn btn-secondary sx-btn-add-row">
        <i class="fas fa-plus"></i> <?= \Yii::t('skeeks/cms', 'Add') ?>
    </button>


<?
\yii\jui\Sortable::widget();

$jsOptions = \yii\helpers\Json::encode(\yii\helpers\ArrayHelper::merge($widget->clientOptions, [
        'value' => $widget->model->{$widget->attribute},
        'attributename' => \yii\helpers\Html::getInputName($widget->model, $widget->attribute)
]));

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
    sx.classes.FormBarcodes = sx.classes.Component.extend({
    
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
            
            $(".sx-barcode-row", self.getJElementsWrapper()).each(function() {
                
                var barcode = String($("select", $(this)).val());
                var value = Number($("input", $(this)).val());
                
                valueObject[barcode] = value;
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
                var jbarcodeRow = $(this).closest(".sx-barcode-row");
                console.log(jbarcodeRow);
                jbarcodeRow.slideUp().remove();
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
            
            var value = self.get("value");
            if (value) {
                _.each(value, function(barcodeData, key) {
                    self._createRow(barcodeData.barcode_type, barcodeData.value);
                });
            }
        },
        
        
        /**
        * Добавление строки
        * @param data
        * @private
        */
        _createRow: function(type, val) {
            var self = this;
            
            var jRow = self.getJTemplate().clone();
            
            jRow.removeClass("sx-template");
            jRow.appendTo($('.sx-elements-wrapper', self.getJWrapper()));

            $("select", jRow).attr("name", this.get('attributename') + "[" + this.counter + "][barcode_type]");
            $("input", jRow).attr("name", this.get('attributename') + "[" + this.counter + "][value]");

            if (type) {
                $("select", jRow).val(type);
            }
            
            if (val) {
                $("input", jRow).val(val);
            }
            
            this.counter = this.counter + 1;
        }
    });
    new sx.classes.FormBarcodes({$jsOptions});
})(sx, sx.$, sx._);
JS
); ?>
<?= \yii\helpers\Html::endTag('div'); ?>