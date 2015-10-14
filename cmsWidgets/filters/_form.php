<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 27.05.2015
 */
/* @var $this yii\web\View */
/* @var $contentType \skeeks\cms\models\CmsContentType */
use skeeks\cms\modules\admin\widgets\form\ActiveFormUseTab as ActiveForm;

$result = [];
if ($contentTypes = \skeeks\cms\models\CmsContentType::find()->all())
{
    foreach ($contentTypes as $contentType)
    {
        $result[$contentType->name] = \yii\helpers\ArrayHelper::map($contentType->cmsContents, 'id', 'name');
    }
}
?>
<?php $form = ActiveForm::begin(); ?>
    <?= $form->fieldSet(\Yii::t('skeeks/shop/app', 'Showing')); ?>
        <?= $form->field($model, 'viewFile')->textInput(); ?>
    <?= $form->fieldSetEnd(); ?>

    <?= $form->fieldSet(\Yii::t('skeeks/shop/app', 'Data source')); ?>
        <?= $form->fieldSelect($model, 'content_id', $result); ?>

        <?= $form->fieldSelectMulti($model, 'searchModelAttributes', [
            'image' => \Yii::t('skeeks/shop/app', 'Filter by photo'),
            'hasQuantity' => \Yii::t('skeeks/shop/app', 'Filter by availability')
        ]); ?>

        <?= $form->fieldSelect($model, 'type_price_id', \yii\helpers\ArrayHelper::map(
            \skeeks\cms\shop\models\ShopTypePrice::find()->all(), 'id', 'name'
        ), [
            'allowDeselect' => true
        ]); ?>

    <?= $form->fieldSetEnd(); ?>



<?= $form->buttonsStandart($model) ?>
<?php ActiveForm::end(); ?>