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

    <div style="display: none;">
        <div class="sx-real">
            <?= $element; ?>
        </div>
    </div>
    <div class="sx-not-real">
        <div class="input-group">
            <input class="form-control" type="number" step="1" style="max-width: 200px;">
            <?= \yii\helpers\Html::listBox("sx-not-real-select", "sm", [
                'mm'  => 'мм',
                'sm' => 'см',
                'm'  => 'м',
            ], ['size' => 1, 'class' => 'form-control', 'style' => 'max-width: 70px;']) ?>
        </div>
    </div>


<?

$jsOptions = \yii\helpers\Json::encode($widget->clientOptions);


$this->registerJs(<<<JS
(function(sx, $, _)
{
    sx.classes.SmartDimensionsShortWidget = sx.classes.Component.extend({
    
        _onDomReady: function()
        {
           var self = this;
           
            this.getNotRealInput().on("keyup change", function() {
                var notRalVal = self.getNotRealInput().val();
                var measure = self.getNotRealSelect().val();
                var realValue = 0;
                
                if (measure == 'sm') {
                    realValue = notRalVal * 10;
                } else if (measure == 'm') {
                    realValue = notRalVal * 1000;
                } else {
                    realValue = notRalVal
                }
                
                self.getRealInput().val(realValue);
            });
            
            this.getNotRealSelect().on("change", function() {
                var notRalVal = self.getNotRealInput().val();
                var measure = self.getNotRealSelect().val();
                var realValue = 0;
                
                if (measure == 'sm') {
                    realValue = notRalVal * 10;
                    self.getNotRealInput().attr("step", "0.01");
                } else if (measure == 'm') {
                    realValue = notRalVal * 1000 ;
                    self.getNotRealInput().attr("step", "0.0001");
                } else {
                    self.getNotRealInput().attr("step", "1");
                    realValue = notRalVal
                }
                
                self.getRealInput().val(realValue);
            });
            
            var startVal = this.getRealInput().val();
            if (startVal == 0) {
                self.getNotRealSelect().val("sm");
                self.getNotRealInput().attr("step", "0.01");
            } else {
                if (startVal >= 1000) {
                    var val = startVal/1000;
                    self.getNotRealInput().val(val);
                    self.getNotRealSelect().val("m");
                    self.getNotRealInput().attr("step", "0.0001");
                } else if (startVal >= 10) {
                    var val = startVal/10;
                    self.getNotRealInput().val(val);
                    self.getNotRealSelect().val("sm");
                    self.getNotRealInput().attr("step", "0.01");
                } else {
                    var val = startVal;
                    self.getNotRealInput().val(val);
                    self.getNotRealSelect().val("mm");
                    self.getNotRealInput().attr("step", "1");
                }
            }
            
            
        },
        
        getRealInput: function() {
            return $(".sx-real input", this.getJWrapper());
        },
        
        getNotRealInput: function() {
            return $(".sx-not-real input", this.getJWrapper());
        },
        
        getNotRealSelect: function() {
            return $(".sx-not-real select", this.getJWrapper());
        },
        
        getJWrapper: function() {
            return $("#" + this.get('id'));
        }
    });
    new sx.classes.SmartDimensionsShortWidget({$jsOptions});
})(sx, sx.$, sx._);
JS
); ?>
<?= \yii\helpers\Html::endTag('div'); ?>