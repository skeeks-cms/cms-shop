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

$backendUrl = \yii\helpers\Url::to(['update-all-data', 'content_id' => $context->content->id]);
$this->registerJs(<<<JS

$(".sx-update").on("click", function() {
    var jBtn = $(this);
    if (jBtn.hasClass("disabled")) {
        return false;
    }
    var Blocker = sx.block($(".sx-main-col"));
    jBtn.addClass("disabled");
    
    var AjaxQuery = sx.ajax.preparePostQuery("{$backendUrl}");
    var AjaxHandler = new sx.classes.AjaxHandlerStandartRespose(AjaxQuery);
    
    AjaxHandler.on("success", function () {
        setTimeout(function() {
            sx.notify.info("Страница сейчас будет перезагружена");
        }, 1000)
        
        setTimeout(function() {
            window.location.reload();
        }, 3000)
    });
    AjaxHandler.on("error", function () {
        Blocker.unblock();
        jBtn.removeClass("disabled");
    });
    
    AjaxQuery.execute();
    
    return false;
});

JS
);

?>
<div class="row">
    <div class="col-md-3">
        <a href="#" class="btn btn-primary sx-update"
        title='Эта кнопка запускает обновление типов товаров, рассчет количества от поставщиков и прочее'
        data-toggle='tooltip'
        >Обновить данные по товарам</a>
    </div>
</div>
