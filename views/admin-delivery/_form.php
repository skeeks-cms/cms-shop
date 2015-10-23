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

    <div class="row">
        <div class="col-md-4">
            <?= $form->field($model, 'order_price_from')->textInput(); ?>

        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'order_price_to')->textInput(); ?>
        </div>
        <div class="col-md-4">
            <?= $form->fieldSelect($model, 'order_currency_code', \yii\helpers\ArrayHelper::map(\skeeks\modules\cms\money\models\Currency::find()->active()->all(), 'code', 'code'));?>
        </div>
    </div>


    <?= $form->fieldRadioListBoolean($model, 'active'); ?>

    <div class="row">
        <div class="col-md-4">
            <?= $form->field($model, 'price')->textInput(); ?>

        </div>

        <div class="col-md-4">
            <?= $form->fieldSelect($model, 'currency_code', \yii\helpers\ArrayHelper::map(\skeeks\modules\cms\money\models\Currency::find()->active()->all(), 'code', 'code'));?>
        </div>
    </div>

    <div class="row">

        <div class="col-md-8">
            <?= $form->field($model, 'priority')->textInput(); ?>
        </div>

    </div>

    <div class="row">

        <div class="col-md-8">
            <?= $form->field($model, 'description')->textarea(); ?>
        </div>

    </div>

    <div class="row">

        <div class="col-md-8">
            <?= $form->field($model, 'store')->textInput(); ?>
        </div>

    </div>

    <div class="row">

        <div class="col-md-8">
            <?= $form->field($model, 'logo_id')->widget(
                \skeeks\cms\widgets\formInputs\StorageImage::className()
            ); ?>
        </div>

    </div>



<?= $form->fieldSetEnd(); ?>
<?= $form->fieldSet(\skeeks\cms\shop\Module::t('app', 'Payment systems')); ?>

    <?= $form->field($model, 'shopPaySystems')->checkboxList(\yii\helpers\ArrayHelper::map(
        \skeeks\cms\shop\models\ShopPaySystem::find()->active()->all(), 'id', 'name'
    ))->hint(\skeeks\cms\shop\Module::t('app', 'if nothing is selected, it means all')); ?>

<?= $form->fieldSetEnd(); ?>

<?= $form->buttonsCreateOrUpdate($model); ?>
<?php ActiveForm::end(); ?>
