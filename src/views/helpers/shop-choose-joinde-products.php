<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */
/* @var $this yii\web\View */
/* @var $helper \skeeks\cms\shop\helpers\ShopOfferChooseHelper */
?>
<?
$this->registerCss(<<<CSS
.sx-choose-property-group .form-group {
    margin-bottom: 0px;
}

.sx-choose-property-group {
    margin-bottom: 15px;
}
.sx-image-select.sx-disabled-btn-option {
    opacity: 0.5;
}
.sx-disabled-btn-option:hover {
    background: transparent!important;
}

.btn-select-option {
    cursor:pointer;
}

.sx-choose-property-group .sx-disabled-btn-option {
    border-color: silver;
    cursor: default !important;
    color: silver;
    
}

.btn-select-option {
    margin-bottom: 5px;
}
.btn-select-option.sx-image-select {
    width: 2.4rem;
    height: 2.4rem;
    padding: 0.2rem;
    border-radius: 50%;
    overflow: hidden;
    vertical-align: middle;
}
.btn-select-option.sx-image-select img {
    display: block;
    width: 100% !important;
    height: 100% !important;
    border-radius: 50% !important;
    object-fit: cover;
    line-height: normal;
}

.sx-need-select label {
    color: red;
}

CSS
);

$this->registerJs(<<<JS
$('#sx-select-offer .btn-select-option').on("click", function() {
    var value = $(this).data('value');
    if ($(this).data('disabled')) {
        sx.notify.error("Выберите другую опцию");
        return false;
    }
    var jGroup = $(this).closest(".sx-choose-property-group");
    $("input", jGroup).val(value).change();
    return false;
});

$('#sx-select-offer .sx-offer-choose select').on("change", function() {
    
    $("#sx-select-offer").submit();
    return false; 
});
$('#sx-select-offer .sx-properties-choose input').on("change", function() {
    $("#sx-select-offer .sx-offer-choose").empty();
    $("#sx-select-offer").submit();
    return false; 
});
JS
);
?>

<? $form = \yii\bootstrap\ActiveForm::begin([
    'enableClientValidation'      => false,
    'id'      => 'sx-select-offer',
    'options' => [
        'data-pjax' => 1,
    ],
]); ?>
<div class="sx-properties-choose">
    <? if ($helper->chooseFields) : ?>
        <? foreach ($helper->chooseFields as $code => $data) : ?>
            <?
            /**
             * @var $property \skeeks\cms\models\CmsContentProperty
             */
            $property = \yii\helpers\ArrayHelper::getValue($data, 'property'); 
            $disabled = \yii\helpers\ArrayHelper::getValue($data, 'disabledOptions'); ?>
            <? if ((array)\yii\helpers\ArrayHelper::getValue($data, 'options')) : ?>

                <div class="sx-choose-property-group">
                    <?= $form->field($helper->chooseModel, $code)->hiddenInput()
                        /*->listBox(
                        \yii\helpers\ArrayHelper::getValue($data, 'options'),
                        [
                            'size' => 1,
                            'options' => \yii\helpers\ArrayHelper::getValue($data, 'disabeldOptions')
                        ])*/
                        ->label(
                            \yii\helpers\ArrayHelper::getValue($data, 'label')
                        );
                    ?>

                    <? foreach ((array)\yii\helpers\ArrayHelper::getValue($data, 'options') as $key => $optionValue): ?>
                        <?
                    
                        $id = \yii\helpers\ArrayHelper::getValue($optionValue, "value");
                        $value = \yii\helpers\ArrayHelper::getValue($optionValue, "asText");

                        $image = null;
                        if ($property->is_img_offer_property && $property->property_type == \skeeks\cms\relatedProperties\PropertyType::CODE_LIST) {
                            $enum = \skeeks\cms\models\CmsContentPropertyEnum::findOne($id);
                            if ($enum) {
                                $image = $enum->cmsImage;
                            }
                        } elseif ($property->is_img_offer_property && $property->property_type == \skeeks\cms\relatedProperties\PropertyType::CODE_ELEMENT) {
                            $element = \skeeks\cms\models\CmsContentElement::findOne($id);
                            if ($element) {
                                $image = $element->image;
                            }
                        }

                        $imageSrc = \skeeks\cms\helpers\Image::getCapSrc();
                        if ($image) {
                            $preview = \Yii::$app->imaging->getPreview(
                                $image,
                                new \skeeks\cms\components\imaging\filters\Thumbnail([
                                    'w'          => 50,
                                    'h'          => 50,
                                    'm'          => \Imagine\Image\ImageInterface::THUMBNAIL_OUTBOUND,
                                    'sx_preview' => \skeeks\cms\components\storage\SkeeksSuppliersCluster::IMAGE_PREVIEW_MICRO,
                                ])
                            );
                            $imageSrc = $preview->src;
                        }
                        
                        $isChecked = false;
                        $isDisabled = false;
                        $cssClass = 'u-btn-outline-darkgray';
                        if ($helper->chooseModel->{$code} == $key) {
                            $isChecked = true;
                            $cssClass = 'btn-primary';
                        }
                        if (in_array($key, $disabled)) {
                            $isDisabled = true;
                            $cssClass = "sx-disabled-btn-option";
                        }
                        ?>
                        <?php if($property->is_img_offer_property) : ?>
                            <button type="button" class="<?= $cssClass; ?> btn-select-option sx-image-select" title="<?= \yii\helpers\Html::encode($value); ?>" data-value="<?= $key; ?>" data-disabled="<?= (int)$isDisabled; ?>">
                                
                                <img
                                    src="<?= \yii\helpers\Html::encode($imageSrc); ?>"
                                    alt="<?= \yii\helpers\Html::encode($value); ?>"
                                >
                                    
                            </button>
                        <?php else : ?>
                            <button class="btn <?= $cssClass; ?> btn-select-option" data-value="<?= $key; ?>" data-disabled="<?= (int)$isDisabled; ?>">
                                <? if ($isChecked) : ?>
                                    <!--<i class="fas fa-check"></i>-->
                                    <!--&#10003;-->
                                <? else: ?>
                                    <!--<span class="sx-no-check">&#10003;</span>-->
                                <? endif; ?>
                                <?= $value; ?>
                            </button>
                        <?php endif; ?>


                    <? endforeach; ?>
                </div>
            <? endif; ?>


        <? endforeach; ?>
    <? endif; ?>
</div>
<div class="sx-offer-choose" style="display: none;">

    <? if (!$helper->is_offers_properties && $helper->availableOffers) : ?>
        <?= $form->field($helper->chooseModel, 'offer_id')->listBox(\yii\helpers\ArrayHelper::map(
            $helper->availableOffers,
            'id',
            'asText'
        ), ['size' => 1])->label("Предложение"); ?>
    <? elseif (count($helper->availableOffers) > 1 && \Yii::$app->request->post()) : ?>
        <?= $form->field($helper->chooseModel, 'offer_id')->listBox(\yii\helpers\ArrayHelper::map(
            $helper->availableOffers,
            'id',
            'asText'
        ), ['size' => 1])->label("Предложение"); ?>
    <? endif; ?>

</div>

<? $form::end(); ?>
