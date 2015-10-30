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

    <?= $form->fieldSelect($model, 'content_id', \yii\helpers\ArrayHelper::map(
        \skeeks\cms\models\CmsContent::find()->all(), 'id', 'name'
    )); ?>

    <?= $form->fieldRadioListBoolean($model, 'yandex_export'); ?>

<?= $form->fieldSetEnd(); ?>

<?= $form->buttonsCreateOrUpdate($model); ?>
<?php ActiveForm::end(); ?>
