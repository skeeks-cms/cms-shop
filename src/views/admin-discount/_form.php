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

<?php $form = $action->beginActiveForm(); ?>
<?= $form->errorSummary($model); ?>
<? $fieldset = $form->fieldSet(\Yii::t('skeeks/shop/app', 'Main')); ?>

<?= $form->field($model, 'is_active')->checkbox(); ?>
<?= $form->field($model, 'name')->textInput(); ?>

<? /*= $form->field($model, 'cms_site_id')->listBox(\yii\helpers\ArrayHelper::map(
    \skeeks\cms\models\CmsSite::find()->all(), 'id', 'name'
), ['size' => 1]); */ ?>

<?= $form->field($model, 'assignment_type')->listBox(\skeeks\cms\shop\models\ShopDiscount::getAssignmentTypes(), ['size' => 1]); ?>
<?= $form->field($model, 'value_type')->listBox(\skeeks\cms\shop\models\ShopDiscount::getValueTypes(), ['size' => 1]); ?>
<?= $form->field($model, 'value')->textInput(); ?>

<?= $form->field($model, 'currency_code')->listBox(\yii\helpers\ArrayHelper::map(
    \skeeks\cms\money\models\MoneyCurrency::find()->andWhere(['is_active' => true])->all(), 'code', 'code'
), ['size' => 1]); ?>

<?= $form->field($model, 'max_discount')->textInput(); ?>

<?= $form->field($model, 'priority'); ?>
<?= $form->field($model, 'is_last')->checkbox(); ?>
<?= $form->field($model, 'notes')->textarea(['rows' => 3]); ?>

<? $fieldset::end(); ?>

<? $fieldset = $form->fieldSet(\Yii::t('skeeks/shop/app', 'Conditions')); ?>

<?= $form->field($model, 'conditions')->widget(
    \skeeks\cms\shop\widgets\discount\DiscountConditionsWidget::class,
    [
        'options' => [
            \skeeks\cms\helpers\RequestResponse::DYNAMIC_RELOAD_FIELD_ELEMENT => 'true',
        ],
    ]
); ?>

<? $fieldset::end(); ?>


<? $fieldset = $form->fieldSet(\Yii::t('skeeks/shop/app', 'Limitations')); ?>

<?= $form->field($model, 'typePrices')->checkboxList(\yii\helpers\ArrayHelper::map(
    \skeeks\cms\shop\models\ShopTypePrice::find()->cmsSite()->all(), 'id', 'name'
))->hint(\Yii::t('skeeks/shop/app', 'if nothing is selected, it means all')); ?>

<?php $this->registerCss(<<<CSS
    .sx-checkbox label
    {
        width: 100%;
    }
CSS
    ) ?>
<?=
$form->field($model, 'cmsAuthItems')->checkboxList(\yii\helpers\ArrayHelper::map(
    \Yii::$app->authManager->getAvailableRoles(), 'name', 'description'
    ),[
    'class' => 'sx-checkbox',
])->hint(\Yii::t('skeeks/shop/app', 'if nothing is selected, it means all')); ?>


<? $fieldset::end(); ?>


<?= $form->buttonsStandart($model); ?>
<?= $form->errorSummary($model); ?>
<?php $form::end(); ?>
