<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 08.10.2015
 */

namespace skeeks\cms\shop\widgets\stars;

use skeeks\cms\base\InputWidget;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class StarsInputWidget extends InputWidget
{
    static public $autoIdPrefix = "StarsInputWidget";

    public $viewFile = 'stars-input';

    static public $is_register_assets = false;

    public function run()
    {
        if (self::$is_register_assets === false && !\Yii::$app->request->isPjax) {

            $this->view->registerJs(<<<JS
$("body").on("click", ".sx-stars-wrapper input[type=radio]", function() {
    
    var jWrapper = $(this).closest(".sx-stars-wrapper");
    var jValue = $(".sx-stars-value", jWrapper);
    if (jValue.attr("disabled")) {
        return false;
    }
    
    jValue.val($(this).val()).trigger("change");
    
});

$("body").on("change init", ".sx-stars-value", function() {
    var jWrapper = $(this).closest(".sx-stars-wrapper");
    var val = $(this).val();
    
    if (val) {
        var jValue = $("input[value=" + val + "]", jWrapper).prop("checked", "checked");
    } else {
        var jValue = $("input[type=radio]", jWrapper).prop("checked", false);
    }
});

$(".sx-stars-value").trigger("init");

JS
            );

            $this->view->registerCss(<<<CSS
.sx-stars {
    display: flex;
    padding-bottom: 0rem;
}
    
.rating-area .sx-from-required {
    display: none;
}

.rating-area {
    overflow: hidden;
    /* width: 265px; */
    display: inline-block;
    /*margin: 0 auto;*/
}
.rating-area:not(:checked) > input {
	display: none;
}
.rating-area:not(:checked) > label {
    float: right;
    width: 1.5rem;
    padding: 0;
    cursor: pointer;
    font-size: 1.5rem;
    line-height: 1.5rem;
    color: lightgrey;
    text-shadow: 1px 1px #bbb;
    margin-bottom: 0;
}
.rating-area:not(:checked) > label:before {
	content: '★';
}
.rating-area > input:checked ~ label {
	color: gold;
	text-shadow: 1px 1px #c60;
}
.rating-area:not(:checked) > label:hover,
.rating-area:not(:checked) > label:hover ~ label {
	color: gold;
}


.rating-area > input:checked + label:hover,
.rating-area > input:checked + label:hover ~ label,
.rating-area > input:checked ~ label:hover,
.rating-area > input:checked ~ label:hover ~ label,
.rating-area > label:hover ~ input:checked ~ label {
	color: gold;
	text-shadow: 1px 1px goldenrod;
}
.rate-area > label:active {
	position: relative;
}
CSS
            );

            self::$is_register_assets = true;
        }

        if (ArrayHelper::getValue($this->options, "disabled")) {
            Html::addCssClass($this->wrapperOptions, "sx-disabled");
        }
        Html::addCssClass($this->wrapperOptions, "sx-stars-wrapper");
        Html::addCssClass($this->options, "sx-stars-value");

        return parent::run();
    }
}