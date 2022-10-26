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

$backendUrl = \yii\helpers\Url::to(['auto-create']);

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
<h5>Автоматическое создание товаров</h5>

<div class="sx-bg-secondary" style="padding: 10px; max-width: 500px;">
    <?php
    $model = new \skeeks\cms\base\DynamicModel();
    $model->defineAttribute("is_active");
    $model->setAttrubuteLebel("is_active", "Показывать товары на сайте сразу?");

    $model->defineAttribute("cms_tree_id");
    $model->setAttrubuteLebel("cms_tree_id", "Раздел");

    $form = \skeeks\cms\base\widgets\ActiveFormAjaxSubmit::begin([
        'action'               => $backendUrl,
        'enableAjaxValidation' => false,
        'clientCallback'       => new \yii\web\JsExpression(<<<JS
        function (ActiveFormAjaxSubmit) {
                
            ActiveFormAjaxSubmit.on('success', function(e, response) {
    
                ActiveFormAjaxSubmit.AjaxQueryHandler.set("allowResponseSuccessMessage", false);
                ActiveFormAjaxSubmit.AjaxQueryHandler.set("allowResponseErrorMessage", false);
                
                $(".sx-success-result").empty().append("<div class='sx-message'></div>");
                
                setTimeout(function() {
                    window.location.href = $(".sx-back a").attr("href");
                }, 1000);
            });
            
            ActiveFormAjaxSubmit.on('error', function(e, response) {
                ActiveFormAjaxSubmit.AjaxQueryHandler.set("allowResponseSuccessMessage", false);
                ActiveFormAjaxSubmit.AjaxQueryHandler.set("allowResponseErrorMessage", false);
                
                $(".error-summary ul").append("<li>" +  response.message + "</li>");
                $(".error-summary").show();
            });
        }
    JS
        ),
    ]); ?>

    <?php echo $form->field($model, "is_active")->checkbox()->hint("Созданные товары будут сразу показываться на сайте?"); ?>
    <?php echo $form->field($model, "cms_tree_id")->widget(
        \skeeks\cms\widgets\formInputs\selectTree\SelectTreeInputWidget::class,
        [
            'multiple' => false,
        ]
    )->hint("Созданные товары поместить в папку"); ?>

    <div class="d-flex sx-submit-wrapper">
        <button type="submit" href="#" class="btn btn-primary"
                title='Эта кнопка запускает автоматическое создание товаров'
                data-toggle='tooltip'
        >
            Создать&nbsp;товары
        </button>
        <div class="sx-success-result my-auto" style="width: 100%;"></div>
    </div>

    <?php $form::end(); ?>
</div>

