<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use skeeks\cms\modules\admin\widgets\form\ActiveFormUseTab as ActiveForm;

/* @var $this yii\web\View */
/* @var $model \skeeks\cms\shop\models\ShopBuyer */


?>

<?php $form = ActiveForm::begin([
    'id'                     => 'sx-dynamic-form',
    'enableAjaxValidation'   => false,
    'enableClientValidation' => false,
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
                return false;
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

<?= $form->fieldSet(\Yii::t('skeeks/shop/app', 'Main')); ?>

<? if (\Yii::$app->request->get('cms_user_id')) : ?>

    <? $model->cms_user_id = \Yii::$app->request->get('cms_user_id'); ?>
    <div style="display: none;">
        <?= $form->field($model, 'cms_user_id')->widget(
            \skeeks\cms\backend\widgets\SelectModelDialogUserWidget::class
        ); ?>
    </div>

<? elseif ($model->isNewRecord) : ?>
    <?= $form->field($model, 'cms_user_id')->widget(
        \skeeks\cms\backend\widgets\SelectModelDialogUserWidget::class
    ); ?>
<? endif; ?>

<?= $form->field($model, 'name')->textInput(); ?>

<?= $form->field($model, 'shop_person_type_id')->listBox(\yii\helpers\ArrayHelper::merge(['' => ' — '],
    \yii\helpers\ArrayHelper::map(
        \skeeks\cms\shop\models\ShopPersonType::find()->all(), 'id', 'name'
    )), [
    'size'             => 1,
    'data-form-reload' => 'true',
]); ?>

<? if ($model->relatedProperties) : ?>
    <?= \skeeks\cms\modules\admin\widgets\BlockTitleWidget::widget([
        'content' => \Yii::t('skeeks/cms', 'Settings'),
    ]); ?>
    <? if ($properties = $model->relatedProperties) : ?>
        <? foreach ($properties as $property) : ?>
            <?= $property->renderActiveForm($form) ?>
        <? endforeach; ?>
    <? endif; ?>

<? else : ?>
    <? /*= \Yii::t('skeeks/cms','Additional properties are not set')*/ ?>
<? endif; ?>


<?= $form->fieldSetEnd(); ?>

<?= $form->buttonsCreateOrUpdate($model); ?>
<?php ActiveForm::end(); ?>
