<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

/* @var $this yii\web\View */
/* @var $model \skeeks\cms\shop\models\ShopOrder */
?>

<? if ($model->allow_delivery == 'Y') : ?>
<a href="#sx-allow-delivery" class="sx-dashed"><?= \Yii::t('skeeks/shop/app', 'Yes') ?><a>
        <? else : ?>
        <a href="#" data-toggle="modal" data-target="#sx-allow-delivery" class="sx-dashed"><?= \Yii::t('skeeks/shop/app', 'No') ?><a>
                <? endif; ?>

sx-allow-delivery