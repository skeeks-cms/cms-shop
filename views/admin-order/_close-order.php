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
$statusDate = \Yii::$app->formatter->asDatetime($model->canceled_at);
?>

<? if ($model->canceled == 'Y') : ?>
    <a href="#sx-close-order" class="sx-dashed sx-fancybox" style="color: red;">Да</a>
    <small>(<?= $statusDate ?>)</small>
    <p><br />
        <?= $model->reason_canceled; ?>
    </p>
<? else : ?>
    <a href="#sx-close-order" class="sx-dashed sx-fancybox">Нет<a>
<? endif; ?>

