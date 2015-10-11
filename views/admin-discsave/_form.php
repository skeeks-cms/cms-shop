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

<?= $form->fieldSet('Накопительная программа'); ?>

    <?= $form->fieldCheckboxBoolean($model, 'active'); ?>
    <?= $form->field($model, 'name')->textInput(); ?>

    <?= $form->fieldSelect($model, 'site_id', \yii\helpers\ArrayHelper::map(
        \skeeks\cms\models\CmsSite::find()->all(), 'id', 'name'
    )); ?>


<?= $form->fieldSetEnd(); ?>

<?= $form->fieldSet('Валюта сумм оплаченных заказов и скидок'); ?>
    <?= $form->fieldSelect($model, 'currency_code', \yii\helpers\ArrayHelper::map(
        \skeeks\modules\cms\money\models\Currency::find()->active()->all(), 'code', 'code'
    )); ?>

<?= $form->fieldSetEnd(); ?>

<?= $form->fieldSet('Ограничения'); ?>

    <?= $form->field($model, 'typePrices')->checkboxList(\yii\helpers\ArrayHelper::map(
        \skeeks\cms\shop\models\ShopTypePrice::find()->all(), 'id', 'name'
    ))->hint('если ничего не выделено, то подразумеваются все'); ?>


     <? \yii\bootstrap\Alert::begin([
            'options' => [
              'class' => 'alert-warning',
          ],
        ]); ?>
        <b>Внимание!</b> Права доступа сохраняются в режиме реального времени. Так же эти настройки не зависят от сайта или пользователя.
        <? \yii\bootstrap\Alert::end()?>

        <?= \skeeks\cms\widgets\rbac\PermissionForRoles::widget([
            'permissionName'            => $model->permissionName,
            'permissionDescription'     => "Группы пользователей, которые могут воспользоваться скидкой: '{$model->name}'",
            'label'                     => 'Группы пользователей, которые могут воспользоваться скидкой',
        ]); ?>

<?= $form->fieldSetEnd(); ?>



<?= $form->buttonsCreateOrUpdate($model); ?>
<?php ActiveForm::end(); ?>
