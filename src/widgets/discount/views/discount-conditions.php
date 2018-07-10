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
        $value = [];
    }

    /*print_r(\yii\helpers\Json::encode([
    "type" => "group",
    "condition" => "equal",
    "rules_type" => "and",
    "rules" => [
        [
            "type" => "rule",
            "condition" => "equal",
            "field" => "element.tree",
            "value" => "1432",
        ],
        [
            "type" => "rule",
            "condition" => "equal",
            "field" => "element.name",
            "value" => "Спальни",
        ],
        [
            "type" => "group",
            "condition" => "equal",
            "rules_type" => "and",
            "rules" => [
                [
                    "type" => "rule",
                    "condition" => "equal",
                    "field" => "element.name",
                    "value" => "Спальни",
                ]
            ]
        ],
    ]
]));die;*/
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

<div class="sx-elements">
    <? /*
            echo \yii\helpers\Html::listBox('condition', null, $widget->availableConditions, [
                'size' => 1,
                'class' => 'form-control'
            ]);
        */ ?><!--
        <? /*
            echo \yii\helpers\Html::listBox('andor', null, [
                    'and' => 'Все условия',
                    'or' => 'Любое условие',
            ], [
                'size' => 1,
                'class' => 'form-control'
            ]);
        */ ?>
        --><? /*
            echo \yii\helpers\Html::listBox('equality', null, [
                    'equal' => 'Выполнены',
                    'not_equal' => 'Не выполнены',
            ], [
                'size' => 1,
                'class' => 'form-control'
            ]);
        */ ?>
</div>

<?= \yii\helpers\Html::endTag('div'); ?>
