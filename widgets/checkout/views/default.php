<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 14.10.2016
 */
/* @var $this yii\web\View */
/* @var $widget \skeeks\cms\shop\widgets\checkout\ShopCheckoutWidget */
$widget = $this->context;
?>

<?= \yii\helpers\Html::tag('div', $widget->options); ?>

<?= \yii\helpers\Html::endTag('div'); ?>
