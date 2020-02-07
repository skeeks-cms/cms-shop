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

<?= $form->fieldSet(\Yii::t('skeeks/shop/app', 'Main')); ?>

<?= $form->fieldSelect($model, 'site_code', \yii\helpers\ArrayHelper::map(
    \skeeks\cms\models\CmsSite::find()->all(), 'code', 'name'
)); ?>

<?= $form->fieldSelect($model, 'user_id', \yii\helpers\ArrayHelper::map(
    \skeeks\cms\models\User::find()->all(), 'id', 'displayName'
)); ?>

<?= $form->fieldSelect($model, 'affiliate_id', \yii\helpers\ArrayHelper::map(
    \skeeks\cms\shop\models\ShopAffiliate::find()->all(), 'id', 'id'
)); ?>

<?= $form->field($model, 'fix_plan')->checkbox([
    'uncheck' => \skeeks\cms\components\Cms::BOOL_N,
    'value'   => \skeeks\cms\components\Cms::BOOL_Y,
]); ?>
<?= $form->field($model, 'active')->checkbox([
    'uncheck' => \skeeks\cms\components\Cms::BOOL_N,
    'value'   => \skeeks\cms\components\Cms::BOOL_Y,
]); ?>

<?= $form->fieldSetEnd(); ?>

<?= $form->buttonsStandart($model); ?>
<?php ActiveForm::end(); ?>
