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
?>
<div class="sx-hidden-filters">
    <?= $form->field($handler, 'f')->textInput([
        'data-value' => 'sx-price-from',
    ]) ?>
    <?= $form->field($handler, 't')->textInput([
        'data-value' => 'sx-price-from',
    ]) ?>
</div>
