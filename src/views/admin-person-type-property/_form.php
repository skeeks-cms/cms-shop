<?php

use skeeks\cms\models\Tree;
use skeeks\cms\modules\admin\widgets\form\ActiveFormUseTab as ActiveForm;

/* @var $this yii\web\View */
/* @var $model Tree */
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

<?= $form->fieldSet(\skeeks\cms\shop\Module::t('app', 'Main settings')) ?>

<?= $form->fieldRadioListBoolean($model, 'active') ?>
<?= $form->fieldRadioListBoolean($model, 'is_required') ?>

<?= $form->field($model, 'name')->textInput(['maxlength' => 255]) ?>
<?= $form->field($model, 'code')->textInput() ?>

<?= $form->field($model, 'component')->listBox(array_merge(['' => ' — '],
    \Yii::$app->cms->relatedHandlersDataForSelect), [
    'size'             => 1,
    'data-form-reload' => 'true',
])
    ->label(\Yii::t('skeeks/cms', "Property type"));
?>

<? if ($handler) : ?>
    <?= \skeeks\cms\modules\admin\widgets\BlockTitleWidget::widget(['content' => \Yii::t('skeeks/cms', 'Settings')]); ?>
    <? if ($handler instanceof \skeeks\cms\relatedProperties\propertyTypes\PropertyTypeList) : ?>
        <? $handler->enumRoute = 'shop/admin-person-type-property-enum'; ?>
    <? endif; ?>
    <?= $handler->renderConfigForm($form); ?>
<? endif; ?>



<?= $form->fieldSetEnd(); ?>

<?= $form->fieldSet(\skeeks\cms\shop\Module::t('app', 'Additionally')) ?>
<?= $form->field($model, 'hint')->textInput() ?>
<?= $form->fieldInputInt($model, 'priority') ?>


<? /*= $form->fieldRadioListBoolean($model, 'searchable') */ ?>
<? /*= $form->fieldRadioListBoolean($model, 'filtrable') */ ?>
<? /*= $form->fieldRadioListBoolean($model, 'smart_filtrable') */ ?>
<? /*= $form->fieldRadioListBoolean($model, 'with_description') */ ?>


<? if ($content_id = \Yii::$app->request->get('shop_person_type_id')) : ?>

    <?= $form->field($model, 'shop_person_type_id')->hiddenInput(['value' => $content_id])->label(false); ?>

<? else: ?>

    <?= $form->field($model, 'shop_person_type_id')->label(\skeeks\cms\shop\Module::t('app', 'Type payer'))->widget(
        \skeeks\cms\widgets\formInputs\EditedSelect::class, [
        'items'           => \yii\helpers\ArrayHelper::map(
            \skeeks\cms\shop\models\ShopPersonType::find()->all(),
            "id",
            "name"
        ),
        'controllerRoute' => 'shop/admin-shop-person-type',
    ]);
    ?>

<? endif; ?>

<?= $form->fieldSetEnd(); ?>


<?= $form->fieldSet('Связь с пользователем') ?>

<?= $form->fieldRadioListBoolean($model, 'is_user_email') ?>
<?= $form->fieldRadioListBoolean($model, 'is_user_phone') ?>
<?= $form->fieldRadioListBoolean($model, 'is_user_username') ?>
<?= $form->fieldRadioListBoolean($model, 'is_user_name') ?>

<?= $form->fieldRadioListBoolean($model, 'is_buyer_name') ?>

<?= $form->fieldSetEnd(); ?>

<?= $form->fieldSet('Связь с заказом') ?>

<?= $form->fieldRadioListBoolean($model, 'is_order_location_delivery') ?>
<?= $form->fieldRadioListBoolean($model, 'is_order_location_tax') ?>
<?= $form->fieldRadioListBoolean($model, 'is_order_postcode') ?>

<?= $form->fieldSetEnd(); ?>

<?= $form->buttonsStandart($model); ?>

<?php ActiveForm::end(); ?>




