<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 02.03.2016
 */
/* @var $this yii\web\View */
/* @var $widget \skeeks\cms\shop\widgets\admin\SmartWeightInputWidget */

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
                    <span class="input-group-text">г.</span>
                </div>
            </div>
        </div>

        <div class="my-auto sx-measure-base-value text-center" style="width: 25px;">
            =
        </div>

        <div class="my-auto sx-measure-base-value" style="width: 200px;">
            <div class="input-group">
                <input class="form-control sx-weight-kg" type="number" step="0.001">
                <div class="input-group-append">
                    <span class="input-group-text">кг.</span>
                </div>
            </div>
        </div>

        <div class="my-auto sx-measure-base-value text-center" style="width: 25px;">
            =
        </div>

        <div class="my-auto sx-measure-base-value" style="width: 200px;">
            <div class="input-group">
                <input class="form-control sx-weight-t" type="number" step="0.000001">
                <div class="input-group-append">
                    <span class="input-group-text">т.</span>
                </div>
            </div>
        </div>

    </div>


<?

$jsOptions = \yii\helpers\Json::encode($widget->clientOptions);


$this->registerJs(<<<JS
(function(sx, $, _)
{
    sx.classes.SmartWeightWidget = sx.classes.Component.extend({
    
        _onDomReady: function()
        {
            var jValue = $(".sx-value-element", this.getJWrapper());
            var jValueKg = $(".sx-weight-kg", this.getJWrapper());
            var jValueT = $(".sx-weight-t", this.getJWrapper());
            
            if (jValue.val()) {
                var kg = jValue.val() / 1000;
                var t = jValue.val() / 1000000;
                jValueKg.val(kg);
                jValueT.val(t);
            }
            
            jValue.on("keyup change", function() {
                var kg = $(this).val() / 1000;
                var t = $(this).val() / 1000000;
                
                jValueKg.val(kg);
                jValueT.val(t);
            });
            
            jValueKg.on("keyup change", function() {
                var t = $(this).val() / 1000;
                var g = $(this).val() * 1000;
                
                jValue.val(g);
                jValueT.val(t);
            });
            
            jValueT.on("keyup change", function() {
                var kg = $(this).val() * 1000;
                var g = $(this).val() * 1000000;
                
                jValue.val(g);
                jValueKg.val(kg);
            });
            
            jValueT.on("change", function() {
                jValue.trigger("change");
            });
            
            jValueKg.on("change", function() {
                jValue.trigger("change");
            });
        },
        
        getJWrapper: function() {
            return $("#" + this.get('id'));
        }
    });
    new sx.classes.SmartWeightWidget({$jsOptions});
})(sx, sx.$, sx._);
JS
); ?>
<?= \yii\helpers\Html::endTag('div'); ?>