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

<?php $form = ActiveForm::begin(); ?>

<?= $form->fieldSet(\Yii::t('skeeks/shop/app', 'Main')); ?>

<?= $form->field($model, 'shop_discount_id')->listBox(\yii\helpers\ArrayHelper::merge([null => ''],
    \yii\helpers\ArrayHelper::map(
        \skeeks\cms\shop\models\ShopDiscount::find()->all(), 'id', 'name'
    )), ['size' => 1]); ?>

<?= $form->field($model, 'is_active')->checkbox(); ?>

<?= $form->field($model, 'active_from')->widget(
    \kartik\datecontrol\DateControl::class,
    [
        'type' => \kartik\datecontrol\DateControl::FORMAT_DATETIME,
    ]
); ?>

<?= $form->field($model, 'active_to')->widget(
    \kartik\datecontrol\DateControl::class,
    [
        'type' => \kartik\datecontrol\DateControl::FORMAT_DATETIME,
    ]
); ?>

<?= $form->field($model, 'coupon')->textInput(); ?>
<?= $form->field($model, 'description')->textInput(); ?>
<?= $form->field($model, 'max_use')->textInput(); ?>
<?= $form->field($model, 'cms_user_id')->widget(
    \skeeks\cms\backend\widgets\SelectModelDialogUserWidget::class
); ?>

<?= $form->fieldSetEnd(); ?>

<?= $form->buttonsStandart($model); ?>
<?php ActiveForm::end(); ?>
