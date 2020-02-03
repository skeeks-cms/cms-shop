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

<?= $form->fieldSet(\Yii::t('skeeks/shop/app', 'General information')); ?>

<?= $form->fieldSelect($model, 'site_code', \yii\helpers\ArrayHelper::map(
    \skeeks\cms\models\CmsSite::find()->all(), 'code', 'name'
)); ?>

<?= $form->field($model, 'name')->textInput(); ?>
<?= $form->field($model, 'description')->textarea(); ?>
<?= $form->fieldRadioListBoolean($model, 'active'); ?>
<?= $form->field($model, 'base_rate')->textInput(); ?>
<?= $form->fieldInputInt($model, 'min_plan_value'); ?>

<?= $form->fieldSetEnd(); ?>

<?= $form->buttonsStandart($model); ?>
<?php ActiveForm::end(); ?>
