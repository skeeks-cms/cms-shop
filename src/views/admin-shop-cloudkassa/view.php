<?php
/* @var $this yii\web\View */
/* @var $model \skeeks\cms\shop\models\ShopCloudkassa */
$controller = $this->context;
$action = $controller->action;
$model = $action->model;

/**
 * @var $handler \skeeks\cms\shop\cloudkassa\modulkassa\ModulkassaHandler
 */
$handler = $model->handler;
?>

<h1><?php echo $model->name; ?></h1>
<pre><?php print_r($handler->status()); ?></pre>
<pre><?php print_r($handler->getRetailPoint()); ?></pre>
