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

<?php /*$form = ActiveForm::begin(); */?>
<?php $form = \skeeks\cms\backend\widgets\ActiveFormBackend::begin(); ?>

<? $fieldSet = $form->fieldSet(\skeeks\cms\shop\Module::t('app', 'Main')); ?>

<?= $form->fieldSelect($model, 'site_code', \yii\helpers\ArrayHelper::map(\skeeks\cms\models\CmsSite::find()->all(), 'code', 'name')); ?>

<?= $form->field($model, 'name')->textInput(['maxlength' => 255]); ?>
<?= $form->field($model, 'code')->textInput(['maxlength' => 50]); ?>

<? $fieldSet::end(); ?>

<? $fieldSet = $form->fieldSet(\skeeks\cms\shop\Module::t('app', 'Описание')); ?>
<?= $form->field($model, 'description')->textarea()->label(false); ?>
<? $fieldSet::end(); ?>

<?= $form->errorSummary([$model]); ?>
<?= $form->buttonsStandart($model); ?>
<?php $form::end(); ?>
