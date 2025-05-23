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
<?php $apiResponseWarehouses = $model->wbProvider->methodContentWarehouses(); ?>

    <?php if ($apiResponseWarehouses->isOk) : ?>
        <?php foreach($apiResponseWarehouses->data as $row) : ?>
            <pre>
                <?php print_r($row); ?>
            </pre>
        <?php endforeach; ?>
    <?php else : ?>
        <span style="color: red;">api не работает!</span>
    <?php endif; ?>
<?php endif ?>