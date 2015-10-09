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

<?= $form->fieldSet('Основное'); ?>

    <?= $form->fieldRadioListBoolean($model, 'def'); ?>
    <?= $form->field($model, 'code')->textInput(['maxlength' => 32]); ?>
    <?= $form->field($model, 'name')->textInput(['maxlength' => 255]); ?>
    <?= $form->field($model, 'description')->textarea(); ?>
    <?= $form->fieldInputInt($model, 'priority'); ?>

    <? if (!$model->isNewRecord) : ?>
        <?= \skeeks\cms\modules\admin\widgets\BlockTitleWidget::widget([
            'content' => "Доступность цены"
        ])?>

            <? \yii\bootstrap\Alert::begin([
                'options' => [
                  'class' => 'alert-warning',
              ],
            ]); ?>
            <b>Внимание!</b> Права доступа сохраняются в режиме реального времени. Так же эти настройки не зависят от сайта или пользователя.
            <? \yii\bootstrap\Alert::end()?>

            <?= \skeeks\cms\widgets\rbac\PermissionForRoles::widget([
                'permissionName'            => $model->viewPermissionName,
                'permissionDescription'     => "Права на просмотр цен: '{$model->name}'",
                'label'                     => 'Группы пользователей, имеющие права на просмотр этого типа цен',
            ]); ?>

            <?= \skeeks\cms\widgets\rbac\PermissionForRoles::widget([
                'permissionName'            => $model->buyPermissionName,
                'permissionDescription'     => "Права на покупку по цене: '{$model->name}'",
                'label'                     => 'Группы пользователей, имеющие права на покупку по этому типу цен',
            ]); ?>
    <? else : ?>
        <? \yii\bootstrap\Alert::begin([
            'options' => [
              'class' => 'alert-info',
          ],
        ]); ?>
            После сохранения можно будет настроить, кому доступен данный тип цен
        <? \yii\bootstrap\Alert::end()?>

    <? endif; ?>
<?= $form->fieldSetEnd(); ?>

<?= $form->buttonsCreateOrUpdate($model); ?>
<?php ActiveForm::end(); ?>
