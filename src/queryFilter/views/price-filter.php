<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link https://skeeks.com/
 * @copyright (c) 2010 SkeekS
 * @date 13.11.2017
 */
/* @var $this yii\web\View */
/* @var $handler \skeeks\cms\shop\queryFilter\PriceFiltersHandler */
/* @var $form \yii\widgets\ActiveForm */
/* @var $code string */
$widget = $this->context;
?>

<?
$min = $handler->minValue;
$max = $handler->maxValue;

$val1 = $handler->from ? $handler->from : $min;
$val2 = $handler->to ? $handler->to : $max;

$fromId = \yii\helpers\Html::getInputId($handler, 'from');
$toId = \yii\helpers\Html::getInputId($handler, 'to');

?>
<? if ($min != $max
    //&& $max > 0
) : ?>
    <div class="sx-product-filter-wrapper">
        <div class="row">
            <div class="col-md-12">
                <label>Цена</label>
            </div>

            <div class="col-md-6">
                <?= $form->field($handler, 'from')
                    ->textInput([
                        'placeholder' => $min,
                        'data-value'  => 'sx-price-from',
                    ])
                    ->label('От');
                ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($handler, 'to')
                    ->textInput([
                        'placeholder' => $max,
                        'data-value'  => 'sx-price-to',
                    ])
                    ->label('До');
                ?>
            </div>


            <div class="row">
                <div class="col-md-12" style="height: 40px;">
                    <? echo \yii\jui\Slider::widget([
                        'clientEvents'  => [
                            'change' => new \yii\web\JsExpression(<<<JS
                function( event, ui ) {
                  $("[data-value=sx-price-from]").change();
                }
JS
                            ),
                            'slide'  => new \yii\web\JsExpression(<<<JS
                function( event, ui ) {
                    $("[data-value=sx-price-from]").val(ui.values[ 0 ]);
                    $("[data-value=sx-price-to]").val(ui.values[ 1 ]);
                }
JS
                            ),
                        ],
                        'clientOptions' => [
                            'range'  => true,
                            'min'    => (float)$min,
                            'max'    => (float)$max,
                            'values' => [(float)$val1, (float)$val2],
                        ],
                    ]); ?>
                </div>
            </div>
        </div>
    </div>
<? endif; ?>

