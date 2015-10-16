<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 13.10.2015
 */
/* @var $this yii\web\View */
/* @var $widget \skeeks\cms\shop\cmsWidgets\filters\ShopProductFiltersWidget */
?>

<?
$this->registerJs(<<<JS
(function(sx, $, _)
{
    sx.classes.FiltersForm = sx.classes.Component.extend({

        _init: function()
        {

        },

        _onDomReady: function()
        {
            var self = this;
            this.JqueryForm = $("#sx-filters-form");

            $("input, checkbox, select", this.JqueryForm).on("change", function()
            {
                self.JqueryForm.submit();
            });
        },

        _onWindowReady: function()
        {}
    });

    new sx.classes.FiltersForm();
})(sx, sx.$, sx._);
JS
)
?>
<? $form = \skeeks\cms\base\widgets\ActiveForm::begin([
    'options' =>
    [
        'id' => 'sx-filters-form',
        'data-pjax' => '1'
    ],
    'method' => 'get',
    'action' => "/" . \Yii::$app->request->getPathInfo(),
]); ?>

    <? if ($widget->searchModel) : ?>

        <? if ($widget->typePrice) : ?>
            <?= $form->field($widget->searchModel, "type_price_id")->hiddenInput([
                'value' => $widget->typePrice->id
            ])->label(false); ?>
            <div class="col-md-6">
                <?= $form->field($widget->searchModel, "price_from")->textInput([
                    'placeholder' => \Yii::$app->money->currencyCode
                ]); ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($widget->searchModel, "price_to")->textInput([
                    'placeholder' => \Yii::$app->money->currencyCode
                ]); ?>
            </div>


        <? endif; ?>

        <? if (in_array('image', $widget->searchModelAttributes)) : ?>
            <?= $form->fieldSelect($widget->searchModel, "image", [
                '' => \Yii::t('skeeks/shop/app', 'Does not matter'),
                'Y' => \Yii::t('skeeks/shop/app', 'With photo'),
                'N' => \Yii::t('skeeks/shop/app', 'Without photo'),
            ]); ?>
        <? endif; ?>

        <? if (in_array('hasQuantity', $widget->searchModelAttributes)) : ?>
            <?= $form->field($widget->searchModel, "hasQuantity")->checkbox()->label(\Yii::t('skeeks/shop/app', 'Availability')); ?>
        <? endif; ?>

    <? endif ; ?>



    <? if ($properties = $widget->searchRelatedPropertiesModel->properties) : ?>

        <? foreach ($properties as $property) : ?>
            <? if (in_array($property->code, $widget->realatedProperties)) : ?>

                <? if ($property->property_type == \skeeks\cms\relatedProperties\PropertyType::CODE_ELEMENT) : ?>

                    <?
                        $propertyType = $property->createPropertyType();
                        $options = \skeeks\cms\models\CmsContentElement::find()->active()->andWhere([
                            'content_id' => $propertyType->content_id
                        ])->all();

                        $options = \yii\helpers\ArrayHelper::map(
                            $options, 'id', 'name'
                        );

                    ?>
                    <?= $form->field($widget->searchRelatedPropertiesModel, $property->code)->checkboxList($options); ?>

                <? elseif ($property->property_type == \skeeks\cms\relatedProperties\PropertyType::CODE_LIST) : ?>
                    <?= $form->field($widget->searchRelatedPropertiesModel, $property->code)->checkboxList(\yii\helpers\ArrayHelper::map(
                        $property->enums, 'id', 'value'
                    )); ?>
                <? else : ?>
                    <?= $property->property_type; ?>
                <? endif; ?>

            <? endif; ?>


        <? endforeach; ?>
    <? endif; ?>



    <button class="btn btn-primary"><?=\Yii::t('skeeks/shop/app', 'Apply');?></button>

<? \skeeks\cms\base\widgets\ActiveForm::end(); ?>
