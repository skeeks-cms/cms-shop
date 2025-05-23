<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */
/* @var $this yii\web\View */
/* @var $model \skeeks\cms\shop\models\ShopBill */
$this->registerCss(<<<CSS
.wrapper {
    background: #f5f5f5;
}
CSS
);
?>
<div class="container content">
    <div class="row">
        <div class="col-md-12" style="border: 1px solid #e4e4e4; padding: 27px; background: white; margin-top: 20px; margin-bottom: 20px;">

            <h1 style="text-align: center; margin-bottom: 15px;">
                <b>Счет №<?= $model->id; ?> от <?= \Yii::$app->formatter->asDate($model->created_at); ?></b>
            </h1>
            <div class="sx-data" style="margin-bottom: 15px;">

                <?php if($model->cmsUser) : ?>
                    <p>Клиент: <b><?= $model->cmsUser->shortDisplayName; ?></b></p>
                <?php endif; ?>

                <?php if($model->company) : ?>
                    <p>Компания: <b><?= $model->company->asText; ?></b></p>
                <?php endif; ?>

                <? if ($model->shopOrder) : ?>
                    <p>Заказ: <b><a href="<?= $model->shopOrder->url; ?>" data-pjax="0">№<?= $model->shopOrder->id; ?></a></b></p>
                <? endif; ?>

                <p>Комментарий: <b><?= $model->description; ?></b></p>

                <p>
                    К оплате: <?= \yii\helpers\Html::tag('b', (string)$model->money); ?>
                </p>
                <p>
                    Тип оплаты: <?= \yii\helpers\Html::tag('b', (string)$model->shopPaySystem->name); ?>
                </p>
            </div>
            <? if ($model->closed_at) : ?>
                <div class="" style="color: red; font-weight: bold;">Счет не оплачен и отменен</div>
            <? elseif ($model->paid_at) : ?>
                <div class="" style="color: green; font-weight: bold;">Счет оплачен</div>
            <? else: ?>
                <a href="<?= \yii\helpers\Url::to(['/shop/shop-bill/go', 'code' => $model->code]) ?>" class="btn btn-primary btn-lg">Оплатить</a>
            <? endif; ?>
        </div>
    </div>
</div>