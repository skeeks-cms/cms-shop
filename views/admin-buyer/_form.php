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
/* @var $model \skeeks\cms\shop\models\ShopBuyer */


?>

<?php $form = ActiveForm::begin(); ?>

<?= $form->fieldSet(\skeeks\cms\shop\Module::t('app', 'Main')); ?>

    <? if (\Yii::$app->request->get('cms_user_id')) : ?>

        <? $model->cms_user_id = \Yii::$app->request->get('cms_user_id'); ?>
        <div style="display: none;">
            <?= $form->field($model, 'cms_user_id')->widget(
                \skeeks\cms\modules\admin\widgets\formInputs\SelectModelDialogUserInput::className()
            ); ?>
        </div>

    <? elseif ($model->isNewRecord) : ?>
        <?= $form->field($model, 'cms_user_id')->widget(
            \skeeks\cms\modules\admin\widgets\formInputs\SelectModelDialogUserInput::className()
        ); ?>
    <? endif; ?>


    <?= $form->fieldSelect($model, 'shop_person_type_id', \yii\helpers\ArrayHelper::map(
        \skeeks\cms\shop\models\ShopPersonType::find()->all(), 'id', 'name'
    )); ?>
    <?= $form->field($model, 'name')->textInput(); ?>

<?= $form->fieldSetEnd(); ?>

<?= $form->buttonsCreateOrUpdate($model); ?>
<?php ActiveForm::end(); ?>
