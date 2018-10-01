<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

/* @var $this yii\web\View */
/* @var $model \skeeks\cms\shop\models\ShopOrder */
$statusDate = \Yii::$app->formatter->asDatetime($model->canceled_at);
?>

<? if ($model->canceled_at) : ?>
    <a href="#" data-toggle="modal" data-target="#sx-close-order" class="sx-dashed" style="color: red;">Да</a>
    <small>(<?= $statusDate ?>)</small>
    <p><br/>
        <?= $model->reason_canceled; ?>
    </p>
<? else : ?>
<a href="#" data-toggle="modal" data-target="#sx-close-order" class="sx-dashed">Нет<a>
<? endif; ?>



