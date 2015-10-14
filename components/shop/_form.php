<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 27.03.2015
 */
use yii\helpers\Html;
use skeeks\cms\modules\admin\widgets\form\ActiveFormUseTab as ActiveForm;

/* @var $this yii\web\View */
/* @var $model \skeeks\cms\models\WidgetConfig */
?>
<?php $form = ActiveForm::begin(); ?>


<?= $form->fieldSet(\Yii::t('skeeks/shop/app', 'Main')); ?>

    <?= $form->field($model, 'email')->textInput()->hint(\Yii::t('skeeks/shop/app', 'Email of sales department')); ?>
    <?= $form->fieldRadioListBoolean($model, 'payAfterConfirmation'); ?>

<?= $form->fieldSetEnd(); ?>

<?= $form->buttonsCreateOrUpdate($model); ?>
<?php ActiveForm::end(); ?>


