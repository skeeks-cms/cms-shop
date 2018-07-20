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
                <div class="col-md-3 sx-andor">
                    <?
                    echo \yii\helpers\Html::listBox('andor', \yii\helpers\ArrayHelper::getValue($rule, 'rules_type'), [
                        'and' => 'Все условия',
                        'or'  => 'Любое условие',
                    ], [
                        'size'  => 1,
                        'class' => 'form-control',
                    ]);
                    ?>
                </div>

                <div class="col-md-3 sx-condition">
                    <?
                    echo \yii\helpers\Html::listBox('condition', \yii\helpers\ArrayHelper::getValue($rule, 'condition'), [
                        'equal'     => 'Выполнены',
                        'not_equal' => 'Не выполнены',
                    ], [
                        'size'  => 1,
                        'class' => 'form-control',
                    ]);
                    ?>
                </div>
                <div class="col-md-6 sx-remove-wrapper">
                    <a href="#" class="btn btn-xs sx-remove pull-right" title="Удалить группу условий">
                        <i class="glyphicon glyphicon-remove"></i>
                    </a>
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
            <div class="row">
                <div class="col-md-3">
                    <?
                        echo \yii\helpers\Html::listBox('condition', \yii\helpers\ArrayHelper::getValue($rule, 'condition'), $widget->availableConditions, [
                            'size' => 1,
                            'class' => 'form-control',
                            'data-no-update' => 'true'
                        ]);
                    ?>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-default sx-add-condition">Добавить условие</button>
                </div>


            </div>

        </div>
    </div>
<? else: ?>
    <div class="sx-rule sx-row" data-type="rule">
        <div class="row">
            <div class="col-md-3 sx-field" style="line-height: 34px;">
                <? $widget->availableConditions; ?>
                <span class="label label-info" data-field="<?= \yii\helpers\ArrayHelper::getValue($rule, 'field'); ?>">
                    <?= \yii\helpers\ArrayHelper::getValue($widget->allConditions, \yii\helpers\ArrayHelper::getValue($rule, 'field'), '-- Не задано -- ' . \yii\helpers\ArrayHelper::getValue($rule, 'field')); ?>
                </span>
            </div>
            <!--<a href="#"><? /*= \yii\helpers\ArrayHelper::getValue($rule, 'condition'); */ ?></a>-->
            <div class="col-md-3 sx-andor">
                <?
                echo \yii\helpers\Html::listBox('andor', \yii\helpers\ArrayHelper::getValue($rule, 'condition'), [
                    'and' => 'Равно',
                    'or'  => 'Не равно',
                ], [
                    'size'  => 1,
                    'class' => 'form-control',
                ]);
                ?>
            </div>

            <div class="col-md-5 sx-value">

                <? if (in_array(\yii\helpers\ArrayHelper::getValue($rule, 'field'), ['element.tree_id', 'element.treeIds'])) : ?>
                    <?
                        echo \skeeks\cms\backend\widgets\SelectModelDialogTreeWidget::widget([
                            'name' => 'value',
                            'multiple' => true,
                            'value' => \yii\helpers\ArrayHelper::getValue($rule, 'value'),
                            'options' => [
                                'data-no-update' => 'true',
                                'class' => 'sx-value-element'
                            ]
                        ]);
                    ?>
                <? elseif (in_array(\yii\helpers\ArrayHelper::getValue($rule, 'field'), ['element.tree_id', 'element.treeIds'])) : ?>
                    <?
                        echo \skeeks\cms\backend\widgets\SelectModelDialogTreeWidget::widget([
                            'name' => 'value',
                            'multiple' => true,
                            'value' => \yii\helpers\ArrayHelper::getValue($rule, 'value'),
                            'options' => [
                                'data-no-update' => 'true',
                                'class' => 'sx-value-element'
                            ]
                        ]);
                    ?>
                <? else : ?>
                    <?
                    $value = \yii\helpers\ArrayHelper::getValue($rule, 'value');
                    if (is_string($value)) {
                        echo \yii\helpers\Html::textInput('value', \yii\helpers\ArrayHelper::getValue($rule, 'value'), [
                            'size'  => 1,
                            'class' => 'form-control sx-value-element',
                            'data-no-update' => 'true'
                        ]);
                    } else {
                        print_r($value);
                    }
                    /*
                        echo \yii\helpers\Html::textInput('value', \yii\helpers\ArrayHelper::getValue($rule, 'value'), [
                            'size'  => 1,
                            'class' => 'form-control sx-value-element',
                            'data-no-update' => 'true'
                        ]);
                    */?>
                <? endif; ?>

            </div>

            <div class="col-md-1 sx-remove-wrapper">
                <a href="#" class="btn btn-xs sx-remove pull-right" title="Удалить условие">
                    <i class="glyphicon glyphicon-remove"></i>
                </a>
            </div>
        </div>

    </div>
<? endif; ?>
