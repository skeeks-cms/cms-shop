<?php
/* @var $this yii\web\View */
/* @var $model \skeeks\cms\shop\models\ShopMarketplace */

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
        <h1>Подключение маркетплейса «<?php echo $model->marketplace; ?>»</h1>
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
                window.location.href = response.data.view_url;
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

                    <p>
                    <?php if($model->marketplace == \skeeks\cms\shop\models\ShopMarketplace::MARKETPLACE_WILDBERRIES) : ?>
                        Необходимые ключи находятся в <a href="https://seller.wildberries.ru/supplier-settings/access-to-api" target="_blank">разделе настроек на сайте WB</a>
                    <?php elseif ($model->marketplace == \skeeks\cms\shop\models\ShopMarketplace::MARKETPLACE_OZON) : ?>
                        Необходимые ключи находятся в <a href="https://seller.ozon.ru/app/settings/api-keys?currentTab=sellerApi" target="_blank">разделе настроек на сайте OZON</a>
                    <?php elseif ($model->marketplace == \skeeks\cms\shop\models\ShopMarketplace::MARKETPLACE_YANDEX_MARKET) : ?>
                        В разработке...
                    <?php endif; ?>
                    </p>


                    <?php echo $form->field($model, 'name'); ?>

                    <?php if($model->marketplace == \skeeks\cms\shop\models\ShopMarketplace::MARKETPLACE_WILDBERRIES) : ?>
                        <?php echo $form->field($model, 'wb_key_standart')->textarea(); ?>
                    <?php elseif ($model->marketplace == \skeeks\cms\shop\models\ShopMarketplace::MARKETPLACE_OZON) : ?>
                        <?php echo $form->field($model, 'oz_client_id')->textInput([
                            'type' => 'number',
                            'placeholder' => 'XXXXXX'
                        ]); ?>
                        <?php echo $form->field($model, 'oz_api_key')->textInput([
                            'placeholder' => 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX'
                        ]); ?>
                    <?php elseif ($model->marketplace == \skeeks\cms\shop\models\ShopMarketplace::MARKETPLACE_YANDEX_MARKET) : ?>
                        <?php /*echo $form->field($model, 'ym_company_id'); */?>
                    <?php endif; ?>

                </div>
            </div>

            <div class="d-flex sx-submit-wrapper">
                <button class="btn btn-primary">Подключить&nbsp;магазин</button>
                <div class="sx-success-result my-auto" style="width: 100%;"></div>

            </div>

            <?php echo $form->errorSummary($models); ?>

            <?php $form::end(); ?>
        </div>

    </div>
</div>
