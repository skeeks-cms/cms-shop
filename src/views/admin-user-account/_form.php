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



    <?= $form->fieldSelect($model, 'user_id', \yii\helpers\ArrayHelper::map(
        \skeeks\cms\models\User::find()->active()->all(), 'id', 'displayName'
    )); ?>

    <?= $form->field($model, 'current_budget')->textInput(); ?>
    <?= $form->fieldSelect($model, 'currency_code', \yii\helpers\ArrayHelper::map(
        \Yii::$app->money->activeCurrencies, 'code', 'name'
    )); ?>

    <?= $form->field($model, 'notes')->textarea(); ?>

<?= $form->fieldSetEnd(); ?>

<?= $form->buttonsCreateOrUpdate($model); ?>
<?php ActiveForm::end(); ?>
