<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model \skeeks\cms\shop\models\ShopOrder */
?>

<? if ($model->allow_payment == 'Y') : ?>
    <a href="#sx-allow-payment" class="sx-dashed sx-fancybox">Да<a>
<? else : ?>
    <a href="#sx-allow-payment" class="sx-dashed sx-fancybox">Нет<a>
<? endif; ?>

