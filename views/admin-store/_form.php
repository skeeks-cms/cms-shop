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

<?= $form->fieldSet(\skeeks\cms\shop\Module::t('app', 'Main')); ?>

    <?= $form->fieldRadioListBoolean($model, 'active'); ?>
    <?= $form->fieldRadioListBoolean($model, 'issuing_center'); ?>
    <?= $form->fieldRadioListBoolean($model, 'shipping_center'); ?>


    <?= $form->field($model, 'image_id')->widget(
        \skeeks\cms\widgets\formInputs\StorageImage::className()
    ); ?>


    <?= $form->fieldSelect($model, 'site_code', \yii\helpers\ArrayHelper::map(
        \skeeks\cms\models\CmsSite::find()->all(), 'code', 'name'
    )); ?>

    <?= $form->field($model, 'name')->textInput(); ?>

    <div class="row">

        <div class="col-md-8">
            <?= $form->field($model, 'address')->textarea(); ?>
        </div>

    </div>

    <div class="row">

        <div class="col-md-8">
            <?= $form->field($model, 'description')->textarea(); ?>
        </div>

    </div>

    <div class="row">

        <div class="col-md-8">
            <?= $form->field($model, 'phone')->textInput(); ?>
        </div>

    </div>

    <div class="row">

        <div class="col-md-8">
            <?= $form->field($model, 'schedule')->textInput(); ?>
        </div>

    </div>

    <div class="row">

        <div class="col-md-8">
            <?= $form->field($model, 'email')->textInput(); ?>
        </div>

    </div>

    <div class="row">

        <div class="col-md-4">
            <?= $form->field($model, 'gps_n')->textInput(); ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'gps_s')->textInput(); ?>
        </div>

    </div>

    <div class="row">

        <div class="col-md-8">
            <?= $form->field($model, 'xml_id')->textInput(); ?>
        </div>

    </div>

    <div class="row">

        <div class="col-md-4">
            <?= $form->field($model, 'priority')->textInput(); ?>
        </div>
    </div>

    <div class="row">

        <div class="col-md-8">
            <?= $form->field($model, 'image_id')->widget(
                \skeeks\cms\widgets\formInputs\StorageImage::className()
            ); ?>
        </div>

    </div>



<?= $form->fieldSetEnd(); ?>

<?= $form->buttonsCreateOrUpdate($model); ?>
<?php ActiveForm::end(); ?>
