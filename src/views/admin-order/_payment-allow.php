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

<? if ($model->is_allowed_payment) : ?>
<a href="#" data-toggle="modal" data-target="#sx-allow-payment"  class="sx-dashed">Да<a>
        <? else : ?>
        <a href="#" data-toggle="modal" data-target="#sx-allow-payment"  class="sx-dashed">Нет<a>
                <? endif; ?>

