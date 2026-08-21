<?php

use skeeks\cms\backend\widgets\BackendModelHeader;
use skeeks\cms\shop\models\ShopPartnerPayout;
use yii\helpers\Html;

/** @var ShopPartnerPayout $model */

$status = Html::tag('span', Html::encode($model->statusName), [
    'class' => ShopPartnerPayout::statusCssClass($model->status),
]);

echo BackendModelHeader::widget([
    'model' => $model,
    'title' => 'Заявка на вывод №'.(int)$model->id,
    'status' => $status,
    'actions' => false,
]);
