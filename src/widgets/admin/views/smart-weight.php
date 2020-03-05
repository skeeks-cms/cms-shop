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

<?= \yii\helpers\Html::beginTag('div', $widget->options); ?>

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
            <input class="form-control">
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
            <input class="form-control">
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
            
        },
        
        
    });
    new sx.classes.SmartWeightWidget({$jsOptions});
})(sx, sx.$, sx._);
JS
); ?>
<?= \yii\helpers\Html::endTag('div'); ?>