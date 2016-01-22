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
    <span style="color: green">
        <?= \Yii::$app->formatter->asBoolean( ($model->payed == \skeeks\cms\components\Cms::BOOL_Y)); ?>
    </span>
    <? if ($model->pay_voucher_num || $model->pay_voucher_at) : ?>
        <p>
            Платежный документ №<?= $model->pay_voucher_num; ?> от <?= \Yii::$app->formatter->asDatetime($model->pay_voucher_at); ?>
        </p>
    <? endif; ?>
<? else : ?>
    <?= \Yii::$app->formatter->asBoolean(false); ?>
<? endif; ?>

