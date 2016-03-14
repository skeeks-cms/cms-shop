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

            <?

            if ($widget->elementIds && !$widget->searchModel->price_from)
            {
                $minPrice = \skeeks\cms\shop\models\ShopProductPrice::find()
                    ->select(['price'])
                    ->indexBy('price')
                    ->andWhere(['product_id' => $widget->elementIds])
                    ->andWhere(['type_price_id' => $widget->typePrice->id])
                    ->orderBy(['price' => SORT_ASC])
                    ->asArray()
                    ->one()
                ;

                $widget->searchModel->price_from = $minPrice['price'];
            }

            if ($widget->elementIds && !$widget->searchModel->price_to)
            {
                $maxPrice = \skeeks\cms\shop\models\ShopProductPrice::find()
                    ->select(['price'])
                    ->indexBy('price')
                    ->andWhere(['product_id' => $widget->elementIds])
                    ->andWhere(['type_price_id' => $widget->typePrice->id])
                    ->orderBy(['price' => SORT_DESC])
                    ->asArray()
                    ->one()
                ;

                $widget->searchModel->price_to = $maxPrice['price'];
            }

            ?>
            <?= $form->field($widget->searchModel, "type_price_id")->hiddenInput([
                'value' => $widget->typePrice->id
            ])->label(false); ?>
            <div class="row">
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
            </div>


        <? endif; ?>

        <? if (in_array('image', $widget->searchModelAttributes)) : ?>
            <?= $form->fieldSelect($widget->searchModel, "image", [
                '' => \skeeks\cms\shop\Module::t('app', 'Does not matter'),
                'Y' => \skeeks\cms\shop\Module::t('app', 'With photo'),
                'N' => \skeeks\cms\shop\Module::t('app', 'Without photo'),
            ]); ?>
        <? endif; ?>

        <? if (in_array('hasQuantity', $widget->searchModelAttributes)) : ?>
            <?= $form->field($widget->searchModel, "hasQuantity")->checkbox()->label(\skeeks\cms\shop\Module::t('app', 'Availability')); ?>
        <? endif; ?>

    <? endif ; ?>



    <? if ($properties = $widget->searchRelatedPropertiesModel->properties) : ?>

        <? foreach ($properties as $property) : ?>
            <? if (in_array($property->code, $widget->realatedProperties)) : ?>

                <? if ($property->property_type == \skeeks\cms\relatedProperties\PropertyType::CODE_ELEMENT) : ?>

                    <?
                        $propertyType = $property->createPropertyType();

                        if ($widget->elementIds)
                        {
                            $availables = \skeeks\cms\models\CmsContentElementProperty::find()
                                ->select(['value_enum'])
                                ->indexBy('value_enum')
                                ->andWhere(['element_id' => $widget->elementIds])
                                ->andWhere(['property_id' => $property->id])
                                ->asArray()
                                ->all()
                            ;

                            $availables = array_keys($availables);
                        }

                        $options = \skeeks\cms\models\CmsContentElement::find()
                            ->active()
                            ->andWhere(['content_id' => $propertyType->content_id]);
                            if ($widget->elementIds)
                            {
                                $options->andWhere(['id' => $availables]);
                            }

                        $options = $options->select(['id', 'name'])->asArray()->all();

                        $options = \yii\helpers\ArrayHelper::map(
                            $options, 'id', 'name'
                        );

                    ?>
                    <?= $form->field($widget->searchRelatedPropertiesModel, $property->code)->checkboxList($options, ['class' => 'sx-filters-checkbox-options']); ?>

                <? elseif ($property->property_type == \skeeks\cms\relatedProperties\PropertyType::CODE_LIST) : ?>

                    <?
                        $options = $property->getEnums()->select(['id', 'value']);

                        if ($widget->elementIds)
                        {
                            $availables = \skeeks\cms\models\CmsContentElementProperty::find()
                                ->select(['value_enum'])
                                ->indexBy('value_enum')
                                ->andWhere(['element_id' => $widget->elementIds])
                                ->andWhere(['property_id' => $property->id])
                                ->asArray()
                                ->all()
                            ;

                            $availables = array_keys($availables);
                            $options->andWhere(['id' => $availables]);
                        }

                        $options = $options->asArray()->all();
                    ?>

                    <?= $form->field($widget->searchRelatedPropertiesModel, $property->code)->checkboxList(\yii\helpers\ArrayHelper::map(
                        $options, 'id', 'value'
                    ), ['class' => 'sx-filters-checkbox-options']); ?>

                <? elseif ($property->property_type == \skeeks\cms\relatedProperties\PropertyType::CODE_NUMBER) : ?>
                    <?/*= $form->field($widget->searchRelatedPropertiesModel, $property->code)->textInput(); */?>

                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($widget->searchRelatedPropertiesModel, $widget->searchRelatedPropertiesModel->getAttributeNameRangeFrom($property->code) )->textInput([
                                'placeholder' => 'от'
                            ])->label(
                                $property->name . ""
                            ); ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($widget->searchRelatedPropertiesModel, $widget->searchRelatedPropertiesModel->getAttributeNameRangeTo($property->code) )->textInput([
                                'placeholder' => 'до'
                            ])->label("&nbsp;"); ?>
                        </div>
                    </div>

                <? else : ?>

                    <? $propertiesValues = \skeeks\cms\models\CmsContentElementProperty::find()->select(['value'])->where([
                        'property_id' => $property->id,
                        'element_id'  => $widget->elementIds
                    ])->all(); ?>

                    <? if ($propertiesValues) : ?>
                        <div class="row">
                            <div class="col-md-12">

                            <?= $form->field($widget->searchRelatedPropertiesModel, $property->code)->dropDownList(
                                \yii\helpers\ArrayHelper::merge(['' => ''], \yii\helpers\ArrayHelper::map(
                                    $propertiesValues, 'value', 'value'
                                )))
                            ; ?>

                            </div>
                        </div>
                    <? endif; ?>
                <? endif; ?>

            <? endif; ?>


        <? endforeach; ?>
    <? endif; ?>



    <button class="btn btn-primary"><?=\skeeks\cms\shop\Module::t('app', 'Apply');?></button>

<? \skeeks\cms\base\widgets\ActiveForm::end(); ?>
