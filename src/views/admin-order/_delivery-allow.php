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

<? if ($model->allow_delivery == 'Y') : ?>
    <a href="#sx-allow-delivery" class="sx-dashed sx-fancybox"><?=\Yii::t('skeeks/shop/app','Yes')?><a>
<? else : ?>
    <a href="#sx-allow-delivery" class="sx-dashed sx-fancybox"><?=\Yii::t('skeeks/shop/app','No')?><a>
<? endif; ?>

