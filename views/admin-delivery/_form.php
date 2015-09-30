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

    <?= $form->field($model, 'name')->textInput(); ?>

    <?= $form->fieldSelect($model, 'site_id', \yii\helpers\ArrayHelper::map(
        \skeeks\cms\models\CmsSite::find()->all(), 'id', 'name'
    )); ?>

    <div class="row">
        <div class="col-md-4">
            <?= $form->field($model, 'period_from')->textInput(); ?>

        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'period_to')->textInput(); ?>
        </div>
        <div class="col-md-4">
            <?= $form->fieldSelect($model, 'period_type', [
                'D' => 'день',
                'H' => 'час',
                'M' => 'месяц',
            ]); ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <?= $form->field($model, 'weight_from')->textInput(); ?>

        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'weight_to')->textInput(); ?>
        </div>
    </div>


    <?= $form->fieldRadioListBoolean($model, 'active'); ?>

<?= $form->fieldSetEnd(); ?>

<?= $form->buttonsCreateOrUpdate($model); ?>
<?php ActiveForm::end(); ?>
