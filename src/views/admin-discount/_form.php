<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */

/* @var $this yii\web\View */
/* @var $action \skeeks\cms\backend\actions\BackendModelUpdateAction */
$action = $this->context->action;
?>

<?php $form = $action->beginDynamicActiveForm(); ?>
<?= $form->errorSummary($model); ?>
<?= $form->fieldSet(\Yii::t('skeeks/shop/app', 'Main')); ?>

<?= $form->fieldCheckboxBoolean($model, 'active'); ?>
<?= $form->field($model, 'name')->textInput(); ?>

<?= $form->field($model, 'site_id')->listBox(\yii\helpers\ArrayHelper::map(
    \skeeks\cms\models\CmsSite::find()->all(), 'id', 'name'
), ['size' => 1]); ?>

<?= $form->field($model, 'assignment_type')->listBox(\skeeks\cms\shop\models\ShopDiscount::getAssignmentTypes(), ['size' => 1]); ?>
<?= $form->field($model, 'value_type')->listBox(\skeeks\cms\shop\models\ShopDiscount::getValueTypes(), ['size' => 1]); ?>
<?= $form->field($model, 'value')->textInput(); ?>

<?= $form->field($model, 'currency_code')->listBox(\yii\helpers\ArrayHelper::map(
    \skeeks\cms\money\models\MoneyCurrency::find()->andWhere(['is_active' => true])->all(), 'code', 'code'
), ['size' => 1]); ?>

<?= $form->field($model, 'max_discount')->textInput(); ?>

<?= $form->fieldInputInt($model, 'priority'); ?>
<?= $form->fieldCheckboxBoolean($model, 'last_discount'); ?>
<?= $form->field($model, 'notes')->textarea(['rows' => 3]); ?>

<?= $form->fieldSetEnd(); ?>

<?= $form->fieldSet(\Yii::t('skeeks/shop/app', 'Conditions')); ?>

<?= $form->field($model, 'conditions')->widget(
    \skeeks\cms\shop\widgets\discount\DiscountConditionsWidget::class,
    [
        'options' => [
            $action->reloadFieldParam => 'true',
        ],
    ]
); ?>

<?= $form->fieldSetEnd(); ?>


<?= $form->fieldSet(\Yii::t('skeeks/shop/app', 'Limitations')); ?>

<?= $form->field($model, 'typePrices')->checkboxList(\yii\helpers\ArrayHelper::map(
    \skeeks\cms\shop\models\ShopTypePrice::find()->all(), 'id', 'name'
))->hint(\Yii::t('skeeks/shop/app', 'if nothing is selected, it means all')); ?>


<? $alert = \yii\bootstrap\Alert::begin([
    'options' => [
        'class' => 'alert-warning',
    ],
]); ?>
<?= \Yii::t('skeeks/shop/app',
    '<b> Warning! </b> Permissions are stored in real time. Thus, these settings are independent of site or user.'); ?>
<? $alert::end() ?>

<?= \skeeks\cms\rbac\widgets\adminPermissionForRoles\AdminPermissionForRolesWidget::widget([
    'permissionName'        => $model->permissionName,
    'notClosedRoles'        => [],
    'permissionDescription' => \Yii::t('skeeks/shop/app',
            'Groups of users who can benefit from discounted rates').": '{$model->name}'",
    'label'                 => \Yii::t('skeeks/shop/app', 'Groups of users who can benefit from discounted rates'),
]); ?>

<?= $form->fieldSetEnd(); ?>


<?= $form->buttonsStandart($model); ?>
<?= $form->errorSummary($model); ?>
<?php $action->endActiveForm(); ?>
