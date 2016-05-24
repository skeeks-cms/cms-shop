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

<?= $form->fieldSet(\Yii::t('skeeks/shop/app', 'Main')); ?>

    <?= $form->fieldCheckboxBoolean($model, 'active'); ?>
    <?= $form->field($model, 'name')->textInput(); ?>

    <?= $form->fieldSelect($model, 'site_id', \yii\helpers\ArrayHelper::map(
        \skeeks\cms\models\CmsSite::find()->all(), 'id', 'name'
    )); ?>

    <?= $form->fieldSelect($model, 'value_type', \skeeks\cms\shop\models\ShopDiscount::getValueTypes()); ?>
    <?= $form->field($model, 'value')->textInput(); ?>
    <?= $form->field($model, 'max_discount')->textInput(); ?>

    <?= $form->fieldSelect($model, 'currency_code', \yii\helpers\ArrayHelper::map(
        \skeeks\modules\cms\money\models\Currency::find()->active()->all(), 'code', 'code'
    )); ?>

    <?= $form->fieldInputInt($model, 'priority'); ?>
    <?= $form->fieldCheckboxBoolean($model, 'last_discount'); ?>
    <?= $form->field($model, 'notes')->textarea(['rows' => 3]); ?>

<?= $form->fieldSetEnd(); ?>

<?= $form->fieldSet(\Yii::t('skeeks/shop/app', 'Conditions')); ?>
<?= $form->fieldSetEnd(); ?>


<?= $form->fieldSet(\Yii::t('skeeks/shop/app', 'Limitations')); ?>

    <?= $form->field($model, 'typePrices')->checkboxList(\yii\helpers\ArrayHelper::map(
        \skeeks\cms\shop\models\ShopTypePrice::find()->all(), 'id', 'name'
    ))->hint(\Yii::t('skeeks/shop/app', 'if nothing is selected, it means all')); ?>


     <? \yii\bootstrap\Alert::begin([
            'options' => [
              'class' => 'alert-warning',
          ],
        ]); ?>
        <?=  \Yii::t('skeeks/shop/app', '<b> Warning! </b> Permissions are stored in real time. Thus, these settings are independent of site or user.'); ?>
        <? \yii\bootstrap\Alert::end()?>

        <?= \skeeks\cms\widgets\rbac\PermissionForRoles::widget([
            'permissionName'            => $model->permissionName,
            'permissionDescription'     => \Yii::t('skeeks/shop/app', 'Groups of users who can benefit from discounted rates').": '{$model->name}'",
            'label'                     => \Yii::t('skeeks/shop/app', 'Groups of users who can benefit from discounted rates'),
        ]); ?>

<?= $form->fieldSetEnd(); ?>


<?= $form->fieldSet(\Yii::t('skeeks/shop/app', 'Coupons')); ?>
<?= $form->fieldSetEnd(); ?>

<?= $form->buttonsCreateOrUpdate($model); ?>
<?php ActiveForm::end(); ?>
