<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */
use yii\helpers\Html;
use skeeks\cms\modules\admin\widgets\form\ActiveFormUseTab as ActiveForm;

/* @var $this yii\web\View */
?>

<?php $form = ActiveForm::begin(); ?>

<?= $form->fieldSet('Основное'); ?>

    <?= $form->fieldCheckboxBoolean($model, 'active'); ?>
    <?= $form->field($model, 'name')->textInput(); ?>

    <?= $form->fieldSelect($model, 'site_id', \yii\helpers\ArrayHelper::map(
        \skeeks\cms\models\CmsSite::find()->all(), 'id', 'name'
    )); ?>

    <?= $form->fieldSelect($model, 'value_type', \skeeks\cms\shop\models\ShopDiscount::getValueTypes()); ?>
    <?= $form->field($model, 'value')->textInput(); ?>
    <?= $form->field($model, 'max_discount')->textInput(); ?>

    <?= $form->fieldSelect($model, 'currency_code', \yii\helpers\ArrayHelper::map(
        \skeeks\modules\cms\money\models\Currency::find()->active()->all(), 'code', 'code'
    )); ?>

    <?= $form->fieldInputInt($model, 'priority'); ?>
    <?= $form->fieldCheckboxBoolean($model, 'last_discount'); ?>
    <?= $form->field($model, 'notes')->textarea(['rows' => 3]); ?>

<?= $form->fieldSetEnd(); ?>

<?= $form->fieldSet('Условия'); ?>
<?= $form->fieldSetEnd(); ?>

<?= $form->fieldSet('Ограничения'); ?>
<?= $form->fieldSetEnd(); ?>

<?= $form->fieldSet('Купоны'); ?>
<?= $form->fieldSetEnd(); ?>

<?= $form->buttonsCreateOrUpdate($model); ?>
<?php ActiveForm::end(); ?>
