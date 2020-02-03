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

<?= $form->fieldSelect($model, 'site_code', \yii\helpers\ArrayHelper::map(
    \skeeks\cms\models\CmsSite::find()->all(), 'code', 'name'
)); ?>

<?= $form->field($model, 'rate1')->textInput(); ?>
<?= $form->field($model, 'rate2')->textInput(); ?>
<?= $form->field($model, 'rate3')->textInput(); ?>
<?= $form->field($model, 'rate4')->textInput(); ?>
<?= $form->field($model, 'rate5')->textInput(); ?>

<?= $form->fieldSetEnd(); ?>

<?= $form->buttonsStandart($model); ?>
<?php ActiveForm::end(); ?>
