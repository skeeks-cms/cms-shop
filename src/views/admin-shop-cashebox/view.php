<?php
/* @var $this yii\web\View */
/* @var $model \skeeks\cms\shop\models\ShopCashebox */
/* @var $controller \skeeks\cms\backend\controllers\BackendModelController */
/* @var $action \skeeks\cms\backend\actions\BackendModelCreateAction|\skeeks\cms\backend\actions\IHasActiveForm */
$controller = $this->context;
$action = $controller->action;
$model = $action->model;
$this->render("@skeeks/cms/shop/views/admin-shop-store-doc-move/view-css");
?>

<div class="row">
    <div class="col-12">
        <h5>Данные кассы</h5>
    </div>
</div>

<div class="sx-properties-wrapper sx-columns-1" style="max-width: 700px;">
    <ul class="sx-properties sx-bg-secondary" style="padding: 10px;">
        <li>
            <span class="sx-properties--name">
                Магазин
            </span>
            <span class="sx-properties--value">
                <?php echo $model->shopStore->name; ?>
            </span>
        </li>


        <li>
            <span class="sx-properties--name">
                Количество платежей
            </span>
            <span class="sx-properties--value">
                <?php echo $model->getShopPayments()->andWhere(['shop_store_payment_type' => \skeeks\cms\shop\models\ShopPayment::STORE_PAYMENT_TYPE_CASH])->count(); ?>
            </span>
        </li>

        <li>
            <span class="sx-properties--name">
                Баланс наличных
            </span>
            <span class="sx-properties--value">
                <b><?php echo $model->balanceCashMoney; ?></b>
            </span>
        </li>
    </ul>
</div>

<!--<div class="row" style="margin-top: 15px;">
    <div class="col-6">
        <h5>Движение денег</h5>
    </div>
</div>
В разработке...
-->