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

    <?= $form->fieldSelect($model, 'cms_user_id', \yii\helpers\ArrayHelper::map(
        \skeeks\cms\models\CmsUser::find()->all(), 'id', 'displayName'
    )); ?>

    <?= $form->fieldSelect($model, 'shop_person_type_id', \yii\helpers\ArrayHelper::map(
        \skeeks\cms\shop\models\ShopPersonType::find()->all(), 'id', 'name'
    )); ?>
    <?= $form->field($model, 'name')->textInput(); ?>

<?= $form->fieldSetEnd(); ?>

<?= $form->buttonsCreateOrUpdate($model); ?>
<?php ActiveForm::end(); ?>
