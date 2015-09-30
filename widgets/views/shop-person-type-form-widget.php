<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 25.09.2015
 */
/* @var $this yii\web\View */
/* @var $widget \skeeks\cms\shop\widgets\ShopPersonTypeFormWidget */

$payLink = \yii\helpers\Url::to('shop/cart/payment');
?>



<?php $form = \skeeks\cms\base\widgets\ActiveFormAjaxSubmit::begin([
        'validationUrl'     => \skeeks\cms\helpers\UrlHelper::construct('shop/cart/shop-person-type-validate')->toString(),
        'action'            => \skeeks\cms\helpers\UrlHelper::construct('shop/cart/shop-person-type-submit')->toString(),

        'afterValidateCallback'                     => new \yii\web\JsExpression(<<<JS
            function(jForm, ajax)
            {
                var handler = new sx.classes.AjaxHandlerStandartRespose(ajax, {
                    'blockerSelector' : '#' + jForm.attr('id'),
                    'enableBlocker' : true,
                });

                handler.bind('success', function(response)
                {
                    window.location.href = '{$payLink}';
                });
            }
JS
),
    ]);
?>

<?
    echo \yii\helpers\Html::hiddenInput("shop_person_type_id",   $widget->shopPersonType->id);
?>

<?
    if ($widget->shopBuyer && !$widget->shopBuyer->isNewRecord)
    {
        echo \yii\helpers\Html::hiddenInput("shop_buyer_id",   $widget->shopBuyer->id);
    }

?>

<? if ($properties = $widget->shopBuyer->relatedProperties) : ?>
    <? foreach ($properties as $property) : ?>
        <?= $property->renderActiveForm($form, $widget->shopBuyer); ?>
    <? endforeach; ?>
<? endif; ?>

<?= \yii\helpers\Html::submitButton("" . \Yii::t('app', "Отправить"), [
    'class' => "btn btn-primary",
]); ?>

<?php \skeeks\cms\base\widgets\ActiveFormAjaxSubmit::end(); ?>