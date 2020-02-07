<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use skeeks\cms\modules\admin\widgets\form\ActiveFormUseTab as ActiveForm;

/* @var $this yii\web\View */
?>

<?php $form = ActiveForm::begin(); ?>

<?= $form->fieldSet(\Yii::t('skeeks/shop/app', 'Cumulative program')); ?>

<?= $form->field($model, 'active')->checkbox([
    'uncheck' => \skeeks\cms\components\Cms::BOOL_N,
    'value'   => \skeeks\cms\components\Cms::BOOL_Y,
]); ?>
<?= $form->field($model, 'name')->textInput(); ?>

<?= $form->fieldSelect($model, 'site_id', \yii\helpers\ArrayHelper::map(
    \skeeks\cms\models\CmsSite::find()->all(), 'id', 'name'
)); ?>


<?= $form->fieldSetEnd(); ?>

<?= $form->fieldSet(\Yii::t('skeeks/shop/app', 'Currency amount paid orders and discounts')); ?>
<?= $form->fieldSelect($model, 'currency_code', \yii\helpers\ArrayHelper::map(
    \skeeks\cms\money\models\MoneyCurrency::find()->andWhere(['is_active' => true])->all(), 'code', 'code'
)); ?>

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

<?= \Yii::t('skeeks/shop/app',
    '<b> Warning! </b> Permissions are stored in real time. Thus, these settings are independent of site or user.'); ?>

<? \yii\bootstrap\Alert::end() ?>

<?= \skeeks\cms\rbac\widgets\adminPermissionForRoles\AdminPermissionForRolesWidget::widget([
    'permissionName'        => $model->permissionName,
    'permissionDescription' => \Yii::t('skeeks/shop/app',
            'Groups of users who can benefit from discounted rates').": '{$model->name}'",
    'label'                 => \Yii::t('skeeks/shop/app', 'Groups of users who can benefit from discounted rates'),
]); ?>

<?= $form->fieldSetEnd(); ?>



<?= $form->buttonsStandart($model); ?>
<?php ActiveForm::end(); ?>
