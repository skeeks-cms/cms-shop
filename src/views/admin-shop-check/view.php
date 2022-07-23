<?php
/* @var $this yii\web\View */
/* @var $model \skeeks\cms\shop\models\ShopCloudkassa */
$controller = $this->context;
$action = $controller->action;
$model = $action->model;

/**
 * @var $model \skeeks\cms\shop\models\ShopCheck
 */

$this->registerCss(<<<CSS
.sx-view-check .sx-row {
    display: flex;
    justify-content: space-between;
}
.sx-view-check .sx-check-results {
    margin-bottom: 10px;
}
.sx-view-check .sx-postions-table {
    width: 100%;
    margin-top: 10px;
    margin-bottom: 10px;
} 
.sx-view-check .sx-postions-table td, 
.sx-view-check .sx-postions-table th 
{
    border-top: none !important;
}
.sx-view-check .sx-postions-table tbody+tbody {
    border-top: none;
}

.sx-view-check .sx-check-head-data .sx_seller_address {
    font-weight: bold;
    font-size: 18px;
}
.sx-view-check .sx-check-head-data .sx-kkm_payments_address {
    font-weight: bold;
    font-size: 18px;
}
.sx-view-check .sx-check-head-data .sx-seller_name {
    font-weight: bold;
    font-size: 20px;
}

.sx-view-check .sx-check-head-data {
    margin-top: 10px;
    margin-bottom: 10px;
    text-align: center;
}

.sx-view-check .sx-qr-wrapper {
    margin-top: 10px;
}
.sx-view-check {
    max-width: 400px;
}
.sx-chec-status {
    margin-bottom: 10px;
}
CSS
);

?>
<div class="sx-chec-status">
    <b><?php echo $model->statusAsText; ?></b>
</div>
<?php echo $this->render("@skeeks/cms/shop/views/cashier/_check", [
    'model' => $model
]) ?>

