<?php


use skeeks\cms\modules\admin\widgets\form\ActiveFormUseTab as ActiveForm;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model \skeeks\cms\models\CmsContentElement */
/* @var $relatedModel \skeeks\cms\relatedProperties\models\RelatedPropertiesModel */
/* @var $shopProduct \skeeks\cms\shop\models\ShopProduct */
?>

<? /*= $this->render('@skeeks/cms/views/admin-cms-content-element/_form', [
    'model' => $model
])*/ ?>

<?php

/* @var $this yii\web\View */
/* @var $model \skeeks\cms\models\CmsContentElement */
/* @var $relatedModel \skeeks\cms\relatedProperties\models\RelatedPropertiesModel */

$contentModel = \Yii::$app->shop->storeContent;
$content_id = $contentModel->id;
$model->content_id = $content_id;


if ($model->isNewRecord) {
    if ($content_id = \Yii::$app->request->get("content_id")) {
        $contentModel = \skeeks\cms\models\CmsContent::findOne($content_id);
        $model->content_id = $content_id;
    }

    if ($tree_id = \Yii::$app->request->get("tree_id")) {
        $model->tree_id = $tree_id;
    }

    if ($parent_content_element_id = \Yii::$app->request->get("parent_content_element_id")) {
        $model->parent_content_element_id = $parent_content_element_id;
    }

    if ($contentModel->parent_content_id) {
        $model->name = $model->parentContentElement->name;
    }
} else {
    $contentModel = $contentModel;
}

?>

<?php $form = ActiveForm::begin(); ?>

<? if ($model->isNewRecord) : ?>
    <?= $form->field($model, 'content_id')->hiddenInput(['value' => $content_id])->label(false); ?>
<? endif; ?>

<? if ($contentModel && $contentModel->parentContent) : ?>
    <?= Html::activeHiddenInput($contentModel, 'parent_content_is_required'); ?>
<? endif; ?>

<?= $form->fieldSet(\Yii::t('skeeks/shop/app', 'Main')); ?>


<?= $form->fieldRadioListBoolean($model, 'active'); ?>
<div class="row">
    <div class="col-md-3">
        <?= $form->field($model, 'published_at')->widget(\kartik\datecontrol\DateControl::classname(), [
            //'displayFormat' => 'php:d-M-Y H:i:s',
            'type' => \kartik\datecontrol\DateControl::FORMAT_DATETIME,
        ]); ?>
    </div>
    <div class="col-md-3">
        <?= $form->field($model, 'published_to')->widget(\kartik\datecontrol\DateControl::classname(), [
            //'displayFormat' => 'php:d-M-Y H:i:s',
            'type' => \kartik\datecontrol\DateControl::FORMAT_DATETIME,
        ]); ?>
    </div>
</div>
<?= $form->field($model, 'name')->textInput(['maxlength' => 255]) ?>
<?= $form->field($model, 'code')->textInput(['maxlength' => 255])->hint(\Yii::t('skeeks/cms',
    "This parameter affects the address of the page")); ?>
<?= $form->fieldInputInt($model, 'priority'); ?>

<? if ($contentModel->parent_content_id) : ?>

    <?= $form->field($model, 'parent_content_element_id')->widget(
        \skeeks\cms\backend\widgets\SelectModelDialogContentElementWidget::class,
        [
            'content_id' => $contentModel->parent_content_id,
        ]
    )->label($contentModel->parentContent->name_one) ?>
<? endif; ?>

<? if ($model->relatedProperties) : ?>
    <?= \skeeks\cms\modules\admin\widgets\BlockTitleWidget::widget([
        'content' => \Yii::t('skeeks/cms', 'Additional properties'),
    ]); ?>
    <? if ($properties = $model->relatedProperties) : ?>
        <? foreach ($properties as $property) : ?>
            <?= $property->renderActiveForm($form, $model) ?>
        <? endforeach; ?>
    <? endif; ?>

<? else : ?>
    <? /*= \Yii::t('skeeks/shop/app','Additional properties are not set')*/ ?>
<? endif; ?>
<?= $form->fieldSetEnd() ?>











<?= $form->fieldSet(\Yii::t('skeeks/shop/app', 'Announcement')); ?>
<?= $form->field($model, 'image_id')->widget(
    \skeeks\cms\widgets\formInputs\StorageImage::className()
); ?>

<?= $form->field($model, 'description_short')->widget(
    \skeeks\cms\widgets\formInputs\comboText\ComboTextInputWidget::className(),
    [
        'modelAttributeSaveType' => 'description_short_type',
    ]);
?>

<?= $form->fieldSetEnd() ?>

<?= $form->fieldSet(\Yii::t('skeeks/shop/app', 'In detal')); ?>

<?= $form->field($model, 'image_full_id')->widget(
    \skeeks\cms\widgets\formInputs\StorageImage::className()
); ?>

<?= $form->field($model, 'description_full')->widget(
    \skeeks\cms\widgets\formInputs\comboText\ComboTextInputWidget::className(),
    [
        'modelAttributeSaveType' => 'description_full_type',
    ]);
?>

<?= $form->fieldSetEnd() ?>

<?= $form->fieldSet(\Yii::t('skeeks/shop/app', 'Sections')); ?>


<? if ($contentModel->root_tree_id) : ?>

    <? if ($contentModel->is_allow_change_tree == \skeeks\cms\components\Cms::BOOL_Y) : ?>
        <?= $form->fieldSelect($model, 'tree_id',
            \skeeks\cms\helpers\TreeOptions::getAllMultiOptions($contentModel->root_tree_id), [
                'allowDeselect' => true,
            ]
        );
        ?>
    <? endif; ?>

    <?= $form->fieldSelectMulti($model, 'treeIds',
        \skeeks\cms\helpers\TreeOptions::getAllMultiOptions($contentModel->root_tree_id));
    ?>

<? else : ?>
    <?
    $mode = \skeeks\cms\widgets\formInputs\selectTree\SelectTree::MOD_COMBO;
    if ($contentModel->is_allow_change_tree != \skeeks\cms\components\Cms::BOOL_Y) {
        $mode = \skeeks\cms\widgets\formInputs\selectTree\SelectTree::MOD_MULTI;
    }
    ?>
    <?= $form->field($model, 'treeIds')->label(\Yii::t('skeeks/shop/app', 'Sections of the site'))->widget(
        \skeeks\cms\widgets\formInputs\selectTree\SelectTree::className(),
        [
            "attributeMulti" => "treeIds",
            "mode"           => $mode,
        ])->hint(\Yii::t('skeeks/shop/app', 'Specify sections of the site, which would like to see this publication'));
    ?>
<? endif; ?>



<?= $form->fieldSetEnd() ?>



<?= $form->fieldSet(\Yii::t('skeeks/shop/app', 'SEO')); ?>
<?= $form->field($model, 'meta_title')->textarea(); ?>
<?= $form->field($model, 'meta_description')->textarea(); ?>
<?= $form->field($model, 'meta_keywords')->textarea(); ?>
<?= $form->fieldSetEnd() ?>


<?= $form->fieldSet(\Yii::t('skeeks/shop/app', 'Images/Files')); ?>

<?= $form->field($model, 'images')->widget(
    \skeeks\cms\widgets\formInputs\ModelStorageFiles::className()
); ?>

<?= $form->field($model, 'files')->widget(
    \skeeks\cms\widgets\formInputs\ModelStorageFiles::className()
); ?>

<?= $form->fieldSetEnd() ?>






<? if (!$model->isNewRecord) : ?>
    <? /*= $form->fieldSet(\Yii::t('skeeks/shop/app','Additionally')); */ ?><!--
        <? /*= $form->fieldSelect($model, 'content_id', \skeeks\cms\models\CmsContent::getDataForSelect()); */ ?>
    --><? /*= $form->fieldSetEnd() */ ?>

    <? if ($model->cmsContent->access_check_element == "Y") : ?>
        <?= $form->fieldSet(\Yii::t('skeeks/shop/app', 'Access')); ?>
        <?= \skeeks\cms\rbac\widgets\adminPermissionForRoles\AdminPermissionForRolesWidget::widget([
            'permissionName'        => $model->permissionName,
            'permissionDescription' => \Yii::t('skeeks/shop/app', 'Access to this member').': '.$model->name,
            'label'                 => \Yii::t('skeeks/shop/app', 'Access to this member'),
        ]); ?>
        <?= $form->fieldSetEnd() ?>
    <? endif; ?>
<? endif; ?>


<?= $form->buttonsStandart($model); ?>
<?php ActiveForm::end(); ?>
