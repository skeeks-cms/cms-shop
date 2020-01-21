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
$this->registerCss(<<<CSS
.modal-header {
    display: block;
}
CSS
);
?>
<div class="text-center">
<p class="p-25" style="margin-bottom: 25px; font-size:1.166667em;">Оставьте свой email и мы сообщим вам о поступлении товара</p>
</div>
<?= $widget->form->field($widget->model, 'email')
    ->label(false)
    ->textInput([
    'type'          => 'email',
    'placeholder'   => 'Ваш email',
]); ?>
<div class="form-group text-center">
<button type="submit" class="btn btn-primary">Отправить</button>
</div>
<div style="display: none">
    <?= $widget->form->field($widget->model, 'shop_product_id'); ?>
</div>
