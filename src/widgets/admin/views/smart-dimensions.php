<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 02.03.2016
 */
/* @var $this yii\web\View */
/* @var $widget \skeeks\cms\shop\widgets\admin\SmartDimensionsInputWidget */

$widget = $this->context;
$model = $widget->model;

$this->registerCss(<<<CSS
.sx-smart-weight-wrapper {
    padding-top: 3px;
    padding-bottom: 3px;
}
CSS
);
?>

<?= \yii\helpers\Html::beginTag('div', $widget->wrapperOptions); ?>

    <div class="d-flex flex-row sx-measure-row">
        <div class="my-auto sx-measure-base-value" style="width: 200px;">
            <div class="input-group">
                <?= $element; ?>
                <div class="input-group-append">
                    <span class="input-group-text">мм</span>
                </div>
            </div>
        </div>

        <div class="my-auto sx-measure-base-value text-center" style="width: 25px;">
            =
        </div>

        <div class="my-auto sx-measure-base-value" style="width: 200px;">
            <div class="input-group">
                <input class="form-control sx-dim-sm" type="number" step="0.1">
                <div class="input-group-append">
                    <span class="input-group-text">см</span>
                </div>
            </div>
        </div>

        <div class="my-auto sx-measure-base-value text-center" style="width: 25px;">
            =
        </div>

        <div class="my-auto sx-measure-base-value" style="width: 200px;">
            <div class="input-group">
                <input class="form-control sx-dim-m" type="number" step="0.001">
                <div class="input-group-append">
                    <span class="input-group-text">м</span>
                </div>
            </div>
        </div>

    </div>


<?

$jsOptions = \yii\helpers\Json::encode($widget->clientOptions);


$this->registerJs(<<<JS
(function(sx, $, _)
{
    sx.classes.SmartDimensonsWidget = sx.classes.Component.extend({
    
        _onDomReady: function()
        {
            var jValue = $(".sx-value-element", this.getJWrapper());
            var jValueSm = $(".sx-dim-sm", this.getJWrapper());
            var jValueM = $(".sx-dim-m", this.getJWrapper());
            
            if (jValue.val()) {
                var sm = jValue.val() / 10;
                var m = jValue.val() / 1000;
                jValueSm.val(sm);
                jValueM.val(m);
            }
            
            jValue.on("keyup change", function() {
                var sm = $(this).val() / 10;
                var m = $(this).val() / 1000;
                
                jValueSm.val(sm);
                jValueM.val(m);
            });
            
            jValueSm.on("keyup change", function() {
                var m = $(this).val() / 100;
                var mm = $(this).val() * 10;
                
                jValue.val(mm);
                jValueM.val(m);
            });
            
            jValueM.on("keyup change", function() {
                var sm = $(this).val() * 100;
                var mm = $(this).val() * 1000;
                
                jValue.val(mm);
                jValueSm.val(sm);
            });
            
            jValueM.on("change", function() {
                jValue.trigger("change");
            });
            
            jValueSm.on("change", function() {
                jValue.trigger("change");
            });
        },
        
        getJWrapper: function() {
            return $("#" + this.get('id'));
        }
    });
    new sx.classes.SmartDimensonsWidget({$jsOptions});
})(sx, sx.$, sx._);
JS
); ?>
<?= \yii\helpers\Html::endTag('div'); ?>