<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */
/* @var $this yii\web\View */
/* @var $rule array */
/* @var $widget \skeeks\cms\shop\widgets\discount\DiscountConditionsWidget */
?>
<? if (\yii\helpers\ArrayHelper::getValue($rule, 'type') == 'group') : ?>
    <div class="sx-row sx-group" data-type="group">
        <div class="sx-conditions">
            <!--<a href="#">Все условия</a>
            <a href="#">Выполнено(ы)</a>-->

            <div class="row">
                <div class="col-md-3">
                    <?
                    echo \yii\helpers\Html::listBox('andor', null, [
                        'and' => 'Все условия',
                        'or'  => 'Любое условие',
                    ], [
                        'size'  => 1,
                        'class' => 'form-control',
                    ]);
                    ?>
                </div>

                <div class="col-md-3">
                    <?
                    echo \yii\helpers\Html::listBox('equality', null, [
                        'equal'     => 'Выполнены',
                        'not_equal' => 'Не выполнены',
                    ], [
                        'size'  => 1,
                        'class' => 'form-control',
                    ]);
                    ?>
                </div>
            </div>
        </div>
        <div class="sx-rules">
            <? if ($rules = \yii\helpers\ArrayHelper::getValue($rule, 'rules')) : ?>
                <? foreach ($rules as $rule) : ?>
                    <?= $this->render('discount-rule', [
                        'rule'   => $rule,
                        'widget' => $widget,
                    ]); ?>
                <? endforeach; ?>
            <? endif; ?>
        </div>
        <div class="sx-add">
            <a href="#">Добавить условие</a>
        </div>
    </div>
<? else: ?>
    <div class="sx-rule sx-row" data-type="rule">
        <div class="row">
            <div class="col-md-3" style="line-height: 34px;">
                <? $widget->availableConditions; ?>
                <span class="label label-info"><?= \yii\helpers\ArrayHelper::getValue($widget->allConditions, \yii\helpers\ArrayHelper::getValue($rule, 'field'), '-- Не задано --'); ?></span>
            </div>
            <!--<a href="#"><? /*= \yii\helpers\ArrayHelper::getValue($rule, 'condition'); */ ?></a>-->
            <div class="col-md-3">
                <?
                echo \yii\helpers\Html::listBox('andor', null, [
                    'and' => 'Равно',
                    'or'  => 'Не равно',
                ], [
                    'size'  => 1,
                    'class' => 'form-control',
                ]);
                ?>
            </div>
        </div>

    </div>
<? endif; ?>
