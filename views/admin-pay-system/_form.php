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


    <?= $form->field($model, 'name')->textInput(['maxlength' => 255]); ?>
    <?= $form->field($model, 'description')->textarea(); ?>
    <?= $form->fieldRadioListBoolean($model, 'active'); ?>
    <?= $form->field($model, 'personTypeIds')->checkboxList(
        \yii\helpers\ArrayHelper::map(\skeeks\cms\shop\models\ShopPersonType::find()->all(), 'id', 'name')
    ); ?>
    <?= $form->fieldInputInt($model, 'priority'); ?>

<?= $form->fieldSetEnd(); ?>

<?= $form->buttonsCreateOrUpdate($model); ?>
<?php ActiveForm::end(); ?>
