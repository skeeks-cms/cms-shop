<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link https://skeeks.com/
 * @copyright (c) 2010 SkeekS
 * @date 13.11.2017
 */
/* @var $this yii\web\View */
/* @var $form \yii\widgets\ActiveForm */
/* @var $code string */
$widget = $this->context;
$id = \yii\helpers\Html::getInputId($handler, 'value');

$this->registerJs(<<<JS
$("#{$id}").on('change', function() {
    $("[data-value=sx-sort]").val($(this).val());
    $("[data-value=sx-sort]").change();
});
JS
);
?>
<?= $form->field($handler, 'value')->listBox($handler->getSortOptions(), [
    'size' => 1
]); ?>
