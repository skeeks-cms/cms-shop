<?php
/* @var $this yii\web\View */
/* @var $model \skeeks\cms\shop\models\ShopCasheboxShift */
/* @var $controller \skeeks\cms\backend\controllers\BackendModelController */
/* @var $action \skeeks\cms\backend\actions\BackendModelCreateAction|\skeeks\cms\backend\actions\IHasActiveForm */
$controller = $this->context;
$action = $controller->action;
$model = $action->model;
$this->render("@skeeks/cms/shop/views/admin-shop-store-doc-move/view-css");
?>

<div class="row">
    <div class="col-12">
        <h5>Данные смены</h5>
    </div>
</div>

<div class="sx-properties-wrapper sx-columns-1" style="max-width: 700px;">
    <ul class="sx-properties sx-bg-secondary" style="padding: 10px;">
        <li>
            <span class="sx-properties--name">
                Магазин
            </span>
            <span class="sx-properties--value">
                <?php echo $model->shopCashebox->shopStore->name; ?>
            </span>
        </li>
        <li>
            <span class="sx-properties--name">
                Касса
            </span>
            <span class="sx-properties--value">
                <?php echo $model->shopCashebox->name; ?>
            </span>
        </li>
        <li>
            <span class="sx-properties--name">
                Открыта
            </span>
            <span class="sx-properties--value">
                <?php echo \Yii::$app->formatter->asDatetime($model->created_at); ?>
            </span>
        </li>
        <li>
            <span class="sx-properties--name">
                Закрыта
            </span>
            <span class="sx-properties--value">
                <?php echo $model->closed_at ? \Yii::$app->formatter->asDatetime($model->closed_at) : "Сейчас в работе"; ?>
            </span>
        </li>
        <li>
            <span class="sx-properties--name">
                Кассир
            </span>
            <span class="sx-properties--value">
                <?php echo \skeeks\cms\widgets\admin\CmsUserViewWidget::widget(['cmsUser' => $model->createdBy]); ?>
            </span>
        </li>


    </ul>
</div>

<div class="row" style="margin-top: 15px;">
    <div class="col-12">
        <h5>Продажи</h5>
    </div>
</div>

<div class="sx-properties-wrapper sx-columns-1" style="max-width: 700px;">
    <ul class="sx-properties sx-bg-secondary" style="padding: 10px;">

        <li>
            <span class="sx-properties--name">
                Количество продаж
            </span>
            <span class="sx-properties--value">
                <?php echo $model->getShopPayments()->andWhere(['is_debit' => 1])->count(); ?>
            </span>
        </li>


        <li>
            <span class="sx-properties--name">
                Сумма продаж
            </span>
            <span class="sx-properties--value">
                <?php echo new \skeeks\cms\money\Money((string) ($model->getTotalCash() + $model->getTotalCard()), \Yii::$app->money->currency_code); ?>
            </span>
        </li>

        <li>
            <span class="sx-properties--name">
                Наличные
            </span>
            <span class="sx-properties--value">
                <?php echo new \skeeks\cms\money\Money((string) $model->getTotalCash(), \Yii::$app->money->currency_code); ?>
            </span>
        </li>

        <li>
            <span class="sx-properties--name">
                Безналичные
            </span>
            <span class="sx-properties--value">
                <?php echo new \skeeks\cms\money\Money((string) $model->getTotalCard(), \Yii::$app->money->currency_code); ?>
            </span>
        </li>
    </ul>
</div>

<div class="row" style="margin-top: 15px;">
    <div class="col-12">
        <h5>Возвраты</h5>
    </div>
</div>

<div class="sx-properties-wrapper sx-columns-1" style="max-width: 700px;">
    <ul class="sx-properties sx-bg-secondary" style="padding: 10px;">

        <li>
            <span class="sx-properties--name">
                Количество возвратов
            </span>
            <span class="sx-properties--value">
                <?php echo $model->getShopPayments()->andWhere(['is_debit' => 0])->count(); ?>
            </span>
        </li>


        <li>
            <span class="sx-properties--name">
                Сумма возвратов
            </span>
            <span class="sx-properties--value">
                <?php echo new \skeeks\cms\money\Money((string) ($model->getTotalReturnCard() + $model->getTotalReturnCash()), \Yii::$app->money->currency_code); ?>
            </span>
        </li>

        <li>
            <span class="sx-properties--name">
                Наличные
            </span>
            <span class="sx-properties--value">
                <?php echo new \skeeks\cms\money\Money((string) $model->getTotalReturnCash(), \Yii::$app->money->currency_code); ?>
            </span>
        </li>

        <li>
            <span class="sx-properties--name">
                Безналичные
            </span>
            <span class="sx-properties--value">
                <?php echo new \skeeks\cms\money\Money((string) $model->getTotalReturnCard(), \Yii::$app->money->currency_code); ?>
            </span>
        </li>
    </ul>
</div>
<div class="row" style="margin-top: 15px;">
    <div class="col-12">
        <h5>Итог</h5>
    </div>
</div>

<div class="sx-properties-wrapper sx-columns-1" style="max-width: 700px;">
    <ul class="sx-properties sx-bg-secondary" style="padding: 10px;">

        <li>
            <span class="sx-properties--name">
                Выручка с учтом возвратов
            </span>
            <span class="sx-properties--value">
                <b><?php echo new \skeeks\cms\money\Money((string) ($model->getTotalCard() + $model->getTotalCash() - $model->getTotalReturnCard() - $model->getTotalReturnCash()), \Yii::$app->money->currency_code); ?></b>
            </span>
        </li>

    </ul>
</div>
