<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 27.05.2015
 */
/* @var $this yii\web\View */
/* @var $contentType \skeeks\cms\models\CmsContentType */
/* @var $model \skeeks\cms\shop\cmsWidgets\filters\ShopProductFiltersWidget */

$result = [];
if ($contentTypes = \skeeks\cms\models\CmsContentType::find()->all())
{
    foreach ($contentTypes as $contentType)
    {
        $result[$contentType->name] = \yii\helpers\ArrayHelper::map($contentType->cmsContents, 'id', 'name');
    }
}
?>
<?= $form->fieldSet(\skeeks\cms\shop\Module::t('app', 'Showing')); ?>
    <?= $form->field($model, 'viewFile')->textInput(); ?>
<?= $form->fieldSetEnd(); ?>

<?= $form->fieldSet(\skeeks\cms\shop\Module::t('app', 'Data source')); ?>
    <?= $form->fieldSelect($model, 'content_id', $result); ?>

    <?/*= $form->fieldSelectMulti($model, 'searchModelAttributes', [
        'image' => \skeeks\cms\shop\Module::t('app', 'Filter by photo'),
        'hasQuantity' => \skeeks\cms\shop\Module::t('app', 'Filter by availability')
    ]); */?>

    <?= $form->field($model, 'searchModelAttributes')->dropDownList([
        'image' => \skeeks\cms\shop\Module::t('app', 'Filter by photo'),
        'hasQuantity' => \skeeks\cms\shop\Module::t('app', 'Filter by availability')
    ], [
'multiple' => true,
'size' => 4
]); ?>

    <? if ($model->cmsContent) : ?>
        <?= $form->fieldSelectMulti($model, 'realatedProperties', \yii\helpers\ArrayHelper::map($model->cmsContent->cmsContentProperties, 'code', 'name')); ?>

        <? if ($model->shopContent) : ?>
            <?= $form->fieldSelectMulti($model, 'offerRelatedProperties', \yii\helpers\ArrayHelper::map($model->shopContent->offerContent->cmsContentProperties, 'code', 'name')); ?>
        <? endif ; ?>

    <? else: ?>
        Дополнительные свойства появятся после сохранения настроек
    <? endif; ?>


    <?= $form->fieldSelect($model, 'type_price_id', \yii\helpers\ArrayHelper::map(
        \skeeks\cms\shop\models\ShopTypePrice::find()->all(), 'id', 'name'
    ), [
        'allowDeselect' => true
    ]); ?>

<?= $form->fieldSetEnd(); ?>



