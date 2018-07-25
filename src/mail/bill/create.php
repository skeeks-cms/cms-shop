<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */
/**
 * Уведомление о создании счета
 */
use skeeks\cms\mail\helpers\Html;

/* @var $this yii\web\View */
/* @var $model \skeeks\cms\shop\models\ShopBill */
?>

<?= Html::beginTag('h1'); ?>
Выставлен счет №<?= $model->id; ?> от <?= \Yii::$app->formatter->asDate($model->created_at); ?> по заказу №<?= $model->shopOrder->id ?>
<?= Html::endTag('h1'); ?>
<?= Html::tag('hr'); ?>
<div style="text-align: left;">
    <?= Html::beginTag('p'); ?>
    Плательщик: <b><?= $model->shopBuyer->name; ?></b>
    <?= Html::endTag('p'); ?>

    <? if ($model->shopOrder) : ?>
        <?= Html::beginTag('p'); ?>
        Заказ: <b>№<?= $model->shopOrder->id; ?></b>
        <?= Html::endTag('p'); ?>
    <? endif; ?>

    <?= Html::beginTag('p'); ?>
    Комментарий: <b><?= $model->description; ?></b>
    <?= Html::endTag('p'); ?>

    <?= Html::beginTag('p'); ?>
        К оплате: <?= \yii\helpers\Html::tag('b', (string)$model->money); ?>
    <?= Html::endTag('p'); ?>
    <?= Html::beginTag('p'); ?>
        Тип оплаты: <?= \yii\helpers\Html::tag('b', (string)$model->shopPaySystem->name); ?>
    <?= Html::endTag('p'); ?>

    <?= Html::beginTag('p'); ?>
        <?= Html::a("Подробнее о счете", $model->getUrl(true)); ?> | <?= Html::a("Оплатить", $model->getPayUrl(true)); ?>
    <?= Html::endTag('p'); ?>
</div>
