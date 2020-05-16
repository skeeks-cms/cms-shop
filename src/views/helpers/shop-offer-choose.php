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
.sx-choose-property-group .sx-disabled-btn-option {
    border-color: silver;
    color: silver;
    
}

.btn-select-option {
    margin-bottom: 5px;
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

                    <? foreach ((array)\yii\helpers\ArrayHelper::getValue($data, 'options') as $key => $value): ?>
                        <?
                        $isChecked = false;
                        $isDisabled = false;
                        $cssClass = 'u-btn-outline-darkgray';
                        if ($helper->chooseModel->{$code} == $key) {
                            $isChecked = true;
                            $cssClass = 'u-btn-primary';
                        }
                        if (in_array($key, $disabled)) {
                            $isDisabled = true;
                            $cssClass = "sx-disabled-btn-option";
                        }

                        ?>
                        <button class="btn btn-select-option <?= $cssClass; ?>" data-value="<?= $key; ?>" data-disabled="<?= (int)$isDisabled; ?>">
                            <? if ($isChecked) : ?>
                                <i class="fas fa-check"></i>
                            <? endif; ?>
                            <?= $value; ?>
                        </button>
                    <? endforeach; ?>
                </div>
            <? endif; ?>


        <? endforeach; ?>
    <? endif; ?>
</div>
<div class="sx-offer-choose">

    <? if (!$helper->is_offers_properties) : ?>
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
