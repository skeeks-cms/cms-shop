<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 21.12.2016
 */
/* @var $this yii\web\View */
/* @var $widget \skeeks\cms\shop\widgets\notice\NotifyProductEmailModalWidget */
$widget = $this->context;
?>
<?= $widget->form->field($widget->model, 'email')->textInput([
    'type'          => 'email',
    'placeholder'   => 'email',
]); ?>
<div style="display: none">
    <?= $widget->form->field($widget->model, 'shop_product_id'); ?>
    <button type="submit" class="btn btn-form">Отправить</button>
</div>
