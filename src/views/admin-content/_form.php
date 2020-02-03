<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use skeeks\cms\modules\admin\widgets\form\ActiveFormUseTab as ActiveForm;

/* @var $this yii\web\View */
/* @var $model \skeeks\cms\shop\models\ShopContent */
$cofing = [];

/*if (!$model->isNewRecord) {
    $cofing = ['disabled' => 'disabled'];
}*/
?>

<?php $form = ActiveForm::begin(); ?>

<?= $form->fieldSet(\Yii::t('skeeks/shop/app', 'Main')); ?>

<?= $form->fieldSelect($model, 'content_id', \skeeks\cms\models\CmsContent::getDataForSelect(), $cofing); ?>

<? if (!$model->isNewRecord) : ?>
    <?= $form->fieldSelect($model, 'children_content_id', \skeeks\cms\models\CmsContent::getDataForSelect()); ?>
<? endif; ?>
<? /*= $form->fieldRadioListBoolean($model, 'yandex_export'); */ ?>

<?= $form->fieldSetEnd(); ?>

<?= $form->buttonsStandart($model); ?>
<?php ActiveForm::end(); ?>
