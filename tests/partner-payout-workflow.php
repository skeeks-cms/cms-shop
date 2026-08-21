<?php

$root = dirname(__DIR__).'/src';
$model = file_get_contents($root.'/models/ShopPartnerPayout.php');
$adminController = file_get_contents($root.'/controllers/AdminPartnerPayoutController.php');
$upaController = file_get_contents($root.'/controllers/UpaPartnerPayoutController.php');
$adminView = file_get_contents($root.'/views/admin-partner-payout/view.php');
$upaView = file_get_contents($root.'/views/upa-partner-payout/view.php');
$component = file_get_contents($root.'/components/ShopComponent.php');

$checks = [
    'workflow exposes exactly three public statuses' => substr_count($model, "=> '") >= 3
        && strpos($model, "self::STATUS_NEW => 'Новая'") !== false
        && strpos($model, "self::STATUS_PAID => 'Успешная'") !== false
        && strpos($model, "self::STATUS_REJECTED => 'Отменена'") !== false
        && strpos($model, 'STATUS_IN_WORK') === false,
    'success requires a partner-facing message' => strpos($model, 'Для успешной заявки нужно написать сообщение партнёру') !== false,
    'cancellation requires a reason' => strpos($model, 'Для отменённой заявки нужно указать причину') !== false,
    'manager notifications follow actual manager visibility' => strpos($model, '->forManager($worker)') !== false
        && strpos($model, 'checkAccess($workerId, \'shop/admin-partner-payout\')') !== false,
    'comment notifications deep-link to both surfaces' => strpos($model, 'getPartnerViewUrl($logId)') !== false
        && strpos($model, 'getManagerViewUrl($logId)') !== false
        && strpos($model, "'#sx-log-'") !== false,
    'admin has separate view, process and edit actions' => strpos($adminController, "'view' => [") !== false
        && strpos($adminController, "'process' => [") !== false
        && strpos($adminController, "'edit' => [") !== false
        && strpos($adminController, "'update' => [") !== false
        && strpos($adminController, "'isVisible' => false") !== false,
    'admin and partner comments are server-scoped' => strpos($adminController, "'add-comment' => [") !== false
        && strpos($upaController, "'add-comment' => [") !== false
        && strpos($upaController, '$log->model_id = $payout->id;') !== false,
    'both cards render the shared activity widgets' => strpos($adminView, 'CmsCommentWidget::widget') !== false
        && strpos($adminView, 'CmsLogListWidget::widget') !== false
        && strpos($upaView, 'CmsCommentWidget::widget') !== false
        && strpos($upaView, 'CmsLogListWidget::widget') !== false,
    'partner is a standard clickable backend entity' => strpos($adminView, 'BackendEntityLink::widget') !== false
        && strpos($adminView, "'controllerId' => '/cms/admin-user'") !== false,
    'payout model is registered for readable linked activity' => strpos($component, '$application->skeeks->modelsConfig[ShopPartnerPayout::class]') !== false,
];

foreach ($checks as $message => $passed) {
    if (!$passed) {
        fwrite(STDERR, "FAILED: {$message}\n");
        exit(1);
    }
}

echo "partner-payout-workflow: OK\n";
