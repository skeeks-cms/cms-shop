<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 14.10.2016
 */
/* @var $this yii\web\View */
/* @var $widget \skeeks\cms\shop\widgets\checkout\ShopNoAuthCheckoutWidget */
$widget     = $this->context;
$shopFuser  = $widget->shopFuser;

?>
<?= \yii\helpers\Html::beginTag('div', $widget->options); ?>
    <? if ($widget->shopIsReady) : ?>

    <?php $form = \yii\bootstrap\ActiveForm::begin([
        'id'                                            => 'sx-checkout-form',
        'enableAjaxValidation'                          => false,
        'enableClientValidation'                        => false,
        'options'                        =>
        [
            'data-pjax' => 'true'
        ]
    ]); ?>
    <? $this->registerJs(<<<JS

(function(sx, $, _)
{
    sx.classes.Export = sx.classes.Component.extend({

        _onDomReady: function()
        {
            var self = this;

            $("[data-form-reload=true]").on('change', function()
            {
                self.update();
            });

            $("[data-form-reload=true] input[type=radio]").on('change', function()
            {
                self.update();
            });

        },

        update: function()
        {
            _.delay(function()
            {
                var jForm = $("#sx-checkout-form");
                jForm.append($('<input>', {'type': 'hidden', 'name' : 'sx-not-submit', 'value': 'true'}));
                jForm.submit();
            }, 200);
        }
    });

    sx.Export = new sx.classes.Export();
})(sx, sx.$, sx._);


JS
); ?>

            <?= $form->field($shopFuser, 'person_type_id')->radioList(
                \yii\helpers\ArrayHelper::map(\Yii::$app->shop->shopPersonTypes, 'id', 'name'),
                [
                    'data-form-reload' => 'true'
                ]
            )->label(false); ?>

            <? foreach ($widget->shopBuyer->relatedProperties as $property) : ?>
                <?= $property->renderActiveForm($form, $widget->shopBuyer)?>
            <? endforeach; ?>

            <? if ($widget->shopFuser->paySystems) : ?>
                <?= $form->field($widget->shopFuser, 'pay_system_id')->label('Способ оплаты')->radioList(
                    \yii\helpers\ArrayHelper::map($widget->shopFuser->paySystems, 'id', 'name'),
                    [
                        'data-form-reload' => 'true'
                    ]
                ); ?>
            <? endif; ?>

            <?= $form->field($widget->shopFuser, 'delivery_id')->label('Способ оплаты')->radioList(
                \yii\helpers\ArrayHelper::map(\skeeks\cms\shop\models\ShopDelivery::find()->active()->all(), 'id', 'name'),
                [
                    'data-form-reload' => 'true'
                ]
            ); ?>

            <?=
                \yii\helpers\Html::button('Отправить', [
                    'class' => 'btn btn-primary',
                    'type' => 'submit',
                ])
            ?>
        <? $form::end(); ?>
    <? else : ?>
        Магазин не настроен
    <? endif; ?>
<?= \yii\helpers\Html::endTag('div'); ?>