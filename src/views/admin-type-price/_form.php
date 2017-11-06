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
/* @var $model \skeeks\cms\shop\models\ShopTypePrice*/
?>

<?php $form = ActiveForm::begin(); ?>

<?= $form->fieldSet(\skeeks\cms\shop\Module::t('app', 'Main')); ?>

    <?= $form->fieldRadioListBoolean($model, 'def'); ?>
    <?= $form->field($model, 'code')->textInput(['maxlength' => 32]); ?>
    <?= $form->field($model, 'name')->textInput(['maxlength' => 255]); ?>
    <?= $form->field($model, 'description')->textarea(); ?>
    <?= $form->fieldInputInt($model, 'priority'); ?>

    <? if (!$model->isNewRecord) : ?>
        <?= \skeeks\cms\modules\admin\widgets\BlockTitleWidget::widget([
            'content' => \skeeks\cms\shop\Module::t('app', 'Affordable prices')
        ])?>

            <? \yii\bootstrap\Alert::begin([
                'options' => [
                  'class' => 'alert-warning',
              ],
            ]); ?>
            <?= \skeeks\cms\shop\Module::t('app', '<b> Warning! </b> Permissions are stored in real time. Thus, these settings are independent of site or user.'); ?>
            <? \yii\bootstrap\Alert::end()?>

            <?= \skeeks\cms\rbac\widgets\adminPermissionForRoles\AdminPermissionForRolesWidget::widget([
                'permissionName'            => $model->viewPermissionName,
                'permissionDescription'     => \skeeks\cms\shop\Module::t('app', 'Rights to see the prices')." '{$model->name}'",
                'label'                     => \skeeks\cms\shop\Module::t('app', 'User Groups that have permission to view this type of price'),
            ]); ?>

            <?= \skeeks\cms\rbac\widgets\adminPermissionForRoles\AdminPermissionForRolesWidget::widget([
                'permissionName'            => $model->buyPermissionName,
                'permissionDescription'     => \skeeks\cms\shop\Module::t('app', 'The right to buy at a price').": '{$model->name}'",
                'label'                     => \skeeks\cms\shop\Module::t('app', 'Group of users who have the right to purchase on this type of price'),
            ]); ?>
    <? else : ?>
        <? \yii\bootstrap\Alert::begin([
            'options' => [
              'class' => 'alert-info',
          ],
        ]); ?>
        <?= \skeeks\cms\shop\Module::t('app', 'After saving can be set up to whom this type available price'); ?>

        <? \yii\bootstrap\Alert::end()?>

    <? endif; ?>
<?= $form->fieldSetEnd(); ?>

<?= $form->buttonsCreateOrUpdate($model); ?>
<?php ActiveForm::end(); ?>
