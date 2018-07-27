<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */
/* @var $this yii\web\View */
/* @var $widget \skeeks\cms\shop\widgets\discount\DiscountConditionsWidget */
$widget = $this->context;

\skeeks\cms\shop\widgets\discount\assets\DiscountConditionsWidgetAsset::register($this);
$js = \yii\helpers\Json::encode($widget->clientOptions);
$this->registerJs(<<<JS
new sx.classes.DiscountWidget($js);
JS
);

$value = [];
if ($widget->model->{$widget->attribute}) {
    $value = $widget->model->{$widget->attribute};
    /*$value = <<<JSON
{"type":"group","condition":"equal","rules_type":"and","rules":[{"type":"rule","condition":"equal","field":"element.tree","value":"1432"},{"type":"rule","condition":"equal","field":"element.name","value":"Спальни"},{"type":"group","condition":"equal","rules_type":"and","rules":[{"type":"rule","condition":"equal","field":"element.name","value":"Спальни"}]}]}
JSON;*/
    try {

        $value = \yii\helpers\Json::decode($value);
    } catch (\Exception $e) {
        //        $value = [];
        throw $e;
    }

}
?>

<?= \yii\helpers\Html::beginTag('div', $widget->wrapperOptions); ?>

<div style="display: none;">
    <div class="sx-element">
        <?= $element; ?>
    </div>
</div>

<div class="sx-content">
    <? if ($value) : ?>
        <?= $this->render('discount-rule', [
            'rule'   => $value,
            'widget' => $widget,
        ]); ?>
    <? else : ?>

    <? endif; ?>
</div>

<? if (!$value) : ?>
    <button class="btn btn-default sx-create-first">Добавить условие</button>
<? endif; ?>

<?= \yii\helpers\Html::endTag('div'); ?>
