<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use skeeks\cms\modules\admin\widgets\form\ActiveFormUseTab as ActiveForm;

/* @var $this yii\web\View */
?>


<?php $form = ActiveForm::begin([
    'id'                   => 'sx-dynamic-form',
    'enableAjaxValidation' => false,
]); ?>

<? $this->registerJs(<<<JS

(function(sx, $, _)
{
    sx.classes.DynamicForm = sx.classes.Component.extend({

        _onDomReady: function()
        {
            var self = this;

            $("[data-form-reload=true]").on('change', function()
            {
                self.update();
            });
        },

        update: function()
        {
            _.delay(function()
            {
                var jForm = $("#sx-dynamic-form");
                jForm.append($('<input>', {'type': 'hidden', 'name' : 'sx-not-submit', 'value': 'true'}));
                jForm.submit();
            }, 200);
        }
    });

    sx.DynamicForm = new sx.classes.DynamicForm();
})(sx, sx.$, sx._);


JS
); ?>

<?= $form->fieldSet(\skeeks\cms\shop\Module::t('app', 'Main')); ?>


<?= $form->field($model, 'name')->textInput(['maxlength' => 255]); ?>
<?= $form->field($model, 'description')->textarea(); ?>
<?= $form->field($model, 'is_active')->checkbox(); ?>
<?= $form->field($model, 'personTypeIds')->checkboxList(
    \yii\helpers\ArrayHelper::map(\skeeks\cms\shop\models\ShopPersonType::find()->all(), 'id', 'name')
); ?>
<?= $form->field($model, 'priority'); ?>


<?= $form->field($model, 'component')->listBox(array_merge(['' => ' — '],
    [
        \skeeks\cms\shop\paySystems\RobokassaPaySystem::class   => (new \skeeks\cms\shop\paySystems\RobokassaPaySystem())->descriptor->name,
        \skeeks\cms\shop\paySystems\PayPalPaySystem::class      => (new \skeeks\cms\shop\paySystems\PayPalPaySystem())->descriptor->name,
        \skeeks\cms\shop\paySystems\YandexKassaPaySystem::class => (new \skeeks\cms\shop\paySystems\YandexKassaPaySystem())->descriptor->name,
        \skeeks\cms\shop\paySystems\TinkoffPaySystem::class     => (new \skeeks\cms\shop\paySystems\TinkoffPaySystem())->descriptor->name,
        \skeeks\cms\shop\paySystems\SberbankPaySystem::class    => (new \skeeks\cms\shop\paySystems\SberbankPaySystem())->descriptor->name,
    ]
), [
    'size'             => 1,
    'data-form-reload' => 'true',
]);
?>

<? if ($handler) : ?>
    <?= \skeeks\cms\modules\admin\widgets\BlockTitleWidget::widget(['content' => \Yii::t('skeeks/cms', 'Settings')]); ?>
    <?= $handler->renderConfigForm($form); ?>
<? endif; ?>


<!--<div class="row">
        <div class="col-md-6">
            <? /*= $form->fieldSelect($model, 'component', [
                \skeeks\cms\shop\Module::t('app', 'Basic payment systems')          =>
                [
                    \skeeks\cms\shop\paySystems\RobokassaPaySystem::class => (new \skeeks\cms\shop\paySystems\RobokassaPaySystem())->descriptor->name,
                    \skeeks\cms\shop\paySystems\PayPalPaySystem::class => (new \skeeks\cms\shop\paySystems\PayPalPaySystem())->descriptor->name
                ],
            ], [
                'allowDeselect' => true
            ]); */ ?>
        </div>
        <div class="col-md-6">
            <label></label>
            <? /*= $form->field($model, 'componentSettingsString')->label(false)->widget(
                \skeeks\cms\widgets\formInputs\componentSettings\ComponentSettingsWidget::class,
                [
                    'componentSelectId' => Html::getInputId($model, "component"),
                    'buttonText'        => \skeeks\cms\shop\Module::t('app', 'Settings handler'),
                    'buttonClasses'     => "sx-btn-edit btn btn-default"
                ]
            ); */ ?>
        </div>
    </div>-->


<?= $form->fieldSetEnd(); ?>

<?= $form->buttonsStandart($model); ?>
<?php ActiveForm::end(); ?>
