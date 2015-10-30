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


<?= $form->fieldSet(\skeeks\cms\shop\Module::t('app', 'Main')); ?>

    <?= $form->field($model, 'baseUrl')->textInput(); ?>



    <?= $form->field($model, 'sMerchantLogin')->textInput(); ?>
    <?= $form->field($model, 'sMerchantPass1')->textInput(); ?>
    <?= $form->field($model, 'sMerchantPass2')->textInput(); ?>

<?= $form->fieldSetEnd(); ?>

<?= $form->buttonsCreateOrUpdate($model); ?>
<?php ActiveForm::end(); ?>


