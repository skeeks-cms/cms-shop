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

<? if ($model->payed == 'Y') : ?>
    <a href="#sx-payment-container-close" class="btn btn-primary sx-fancybox">Изменить<a>
<? else : ?>
    <a href="#sx-payment-container" class="btn btn-primary sx-fancybox">Оплатить<a>
<? endif; ?>

