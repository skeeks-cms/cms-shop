<?php
/* @var $this yii\web\View */
/* @var $model \skeeks\cms\shop\models\ShopStoreDocMove */

$this->registerCSS(<<<CSS
.sx-main-col {
    background: var(--bg-gray);
}
.sx-project-content {
    background: white;
    border-radius: 24px;
    padding: 40px 56px 48px;
    margin-bottom: 24px;
}
.sx-title-block {
    margin-bottom: 12px; 
}
.sx-back a {
    font-style: normal;
    font-weight: 400;
    font-size: 12px;
    line-height: 26px;
    color: #656464;
}

CSS
);

$models = [$model];
?>


<div class="sx-back">
    <a href="<?php echo \yii\helpers\Url::to(['index']); ?>">
        &larr;&nbsp;Вернуться назад
    </a>
</div>
<div class="sx-title-block d-flex">
    <div class="my-auto" style="width: 100%;">
        <h1>Создание движения товара «<?php echo $model->docTypeAsText; ?>»</h1>
    </div>
</div>
<div class="row">
    <div class="col-12" style="max-width: 800px;">
        <div class="sx-project-content">
            <?php $form = \skeeks\cms\base\widgets\ActiveFormAjaxSubmit::begin([
                'enableAjaxValidation' => false,
                //'validationUrl' => \skeeks\cms\helpers\UrlHelper::constructCurrent()->enableAjaxValidateForm()->toString(),
                'clientCallback'       => new \yii\web\JsExpression(<<<JS
    function (ActiveFormAjaxSubmit) {
        
        ActiveFormAjaxSubmit.on('success', function(e, response) {

            ActiveFormAjaxSubmit.AjaxQueryHandler.set("allowResponseSuccessMessage", false);
            ActiveFormAjaxSubmit.AjaxQueryHandler.set("allowResponseErrorMessage", false);
            
            $(".sx-success-result").empty().append("<div class='sx-message'>✓ " + response.message + "</div>");
            
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

            <div class="row">
                <div class="col-12">

                    <?php echo $form->field($model, 'shop_store_id')->widget(
                        \skeeks\cms\widgets\Select2::class,
                        [
                            'data'          => \yii\helpers\ArrayHelper::map(
                                \skeeks\cms\shop\models\ShopStore::find()->cmsSite()->isSupplier(false)->all(),
                                'id',
                                'name'
                            ),
                            'options'       => [
                                'placeholder' => 'Выбрать магазин',
                            ],
                            'hideSearch'    => true,
                            'pluginOptions' => [
                                'allowClear' => false,
                            ],
                        ]
                    ); ?>
                    <?php echo $form->field($model, "comment")->textarea(['rows' => 5]); ?>

                    <div style="display: none;">
                        <?php echo $form->field($model, "doc_type"); ?>
                        <?php echo $form->field($model, "is_active"); ?>
                    </div>
                </div>
            </div>

            <div class="d-flex sx-submit-wrapper">
                <button class="btn btn-primary">Далее</button>
                <div class="sx-success-result my-auto" style="width: 100%;"></div>

            </div>

            <?php echo $form->errorSummary($models); ?>

            <?php $form::end(); ?>
        </div>

    </div>
</div>
