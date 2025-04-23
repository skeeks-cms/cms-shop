<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */
/**
 * @var $this yii\web\View
 * @var $context \skeeks\cms\backend\BackendAction
 */
$context = $this->context;

$backendUrl = \yii\helpers\Url::to(['quantity-zero']);

$this->registerJs(<<<JS

$(".btn-quantity-zero").on("click", function() {
    var ajaxQuery = sx.ajax.preparePostQuery("{$backendUrl}");
    
    new sx.classes.AjaxHandlerStandartRespose(ajaxQuery, {
        'blockerSelector' : 'body',
        'enableBlocker' : true,
    }).on("success", function(e, response) {
        
    });
    
    ajaxQuery.execute();
});

JS
);

?>
<div class="sx-bg-secondary" style="padding: 10px; max-width: 500px;">
    <button class="btn btn-primary btn-quantity-zero">Обнулить количество</button>
</div>

