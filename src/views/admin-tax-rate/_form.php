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

<?= $form->fieldSelect($model, 'tax_id',
    \yii\helpers\ArrayHelper::map(\skeeks\cms\shop\models\ShopTax::find()->all(), 'id', 'name')
); ?>

<?= $form->fieldSelect($model, 'person_type_id',
    \yii\helpers\ArrayHelper::map(\skeeks\cms\shop\models\ShopPersonType::find()->all(), 'id', 'name')
); ?>


<?= $form->field($model, 'value')->textInput(); ?>
<?= $form->fieldRadioListBoolean($model, 'active'); ?>
<?= $form->fieldRadioListBoolean($model, 'is_in_price'); ?>

<?= $form->fieldInputInt($model, 'priority'); ?>

<?= $form->fieldSetEnd(); ?>

<?= $form->buttonsCreateOrUpdate($model); ?>
<?php ActiveForm::end(); ?>
