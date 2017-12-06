<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link https://skeeks.com/
 * @copyright (c) 2010 SkeekS
 * @date 13.11.2017
 */
/* @var $this yii\web\View */
/* @var $widget \yv\widgets\filters\ProductFilterWidget */
/* @var $handler \yv\widgets\filters\V3pSortFiltersHandler */
/* @var $form \yii\widgets\ActiveForm */
/* @var $code string */
$widget = $this->context;

$id = \yii\helpers\Html::getInputId($handler, 'value');

$this->registerJs(<<<JS
$("#{$id}").on('change', function() {
    if ($(this).is(":checked")) {
        $("[data-value=sx-availability]").val(1);
    } else {
        $("[data-value=sx-availability]").val(0);
    }
    
    $("[data-value=sx-availability]").change();
});
JS
);

?>

<?= $form->field($handler, 'value')->checkbox(); ?>
