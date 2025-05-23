<?php
/* @var $this yii\web\View */
/* @var $model \skeeks\cms\shop\models\ShopMarketplace */
/* @var $controller \skeeks\cms\backend\controllers\BackendModelController */
/* @var $action \skeeks\cms\backend\actions\BackendModelCreateAction|\skeeks\cms\backend\actions\IHasActiveForm */
$controller = $this->context;
$action = $controller->action;
$model = $action->model;

$this->render("view-css");
\skeeks\cms\backend\widgets\AjaxControllerActionsWidget::registerAssets();


?>
<?php if ($model->wbProvider) : ?>
<?php $apiResponse = $model->wbProvider->methodStatReportDetailByPeriod([
        'dateFrom' => "2023-10-25",
        'dateTo' => \Yii::$app->formatter->asDate(time(), "php:Y-m-d")
    ]); ?>

    <?php if ($apiResponse->isOk) : ?>
        <?php
        foreach((array) $apiResponse->data as $key => $row) : ?>
            <pre>
                <?php print_r($row); ?>
            </pre>
        <?php endforeach; ?>
    <?php else : ?>
        <span style="color: red;">api не работает!</span>
        <span style="color: red;"><?php echo $apiResponse->error_message; ?></span>
    <?php endif; ?>
<?php endif ?>