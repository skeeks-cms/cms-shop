<?php

$root = dirname(__DIR__).'/src';
$controllers = [
    'lead' => file_get_contents($root.'/controllers/UpaPartnerLeadController.php'),
    'bonus' => file_get_contents($root.'/controllers/UpaPartnerBonusController.php'),
    'payout' => file_get_contents($root.'/controllers/UpaPartnerPayoutController.php'),
];

foreach ($controllers as $name => $controller) {
    if (strpos($controller, 'namespace skeeks\\cms\\shop\\controllers;') === false) {
        throw new RuntimeException('Partner '.$name.' cabinet controller is not owned by cms-shop.');
    }
    if (strpos($controller, 'frontend\\') !== false || strpos($controller, 'common\\') !== false) {
        throw new RuntimeException('Partner '.$name.' cabinet controller depends on the project namespace.');
    }
}

$routes = [
    '/shop/upa-partner-lead',
    '/shop/upa-partner-bonus',
    '/shop/upa-partner-payout',
];
$allControllers = implode("\n", $controllers);
foreach ($routes as $route) {
    if (strpos($allControllers, $route) === false) {
        throw new RuntimeException('Partner cabinet misses package route '.$route.'.');
    }
}

$payoutController = $controllers['payout'];
if (strpos($payoutController, '$model->cms_site_id =') === false
    || strpos($payoutController, '$model->cms_user_id =') === false
) {
    throw new RuntimeException('Partner payout create must assign site and partner on the server.');
}

$leadController = $controllers['lead'];
if (substr_count($leadController, '->cmsSite()') < 2) {
    throw new RuntimeException('Partner lead list and direct load must be site scoped.');
}
if (strpos($leadController, 'EVENT_BEFORE_SAVE') === false
    || strpos($leadController, 'beginTransaction()') === false
    || strpos($leadController, '_creationTransaction->commit()') === false
) {
    throw new RuntimeException('Partner lead and its contacts must be created atomically.');
}

$payoutModel = file_get_contents($root.'/models/ShopPartnerPayout.php');
$siteDefaultPosition = strpos($payoutModel, "[['cms_site_id'], 'default'");
$requiredPosition = strpos($payoutModel, "[['cms_site_id', 'cms_user_id', 'value', 'requisites'], 'required'");
if ($siteDefaultPosition === false || $requiredPosition === false || $siteDefaultPosition > $requiredPosition) {
    throw new RuntimeException('Partner payout site default must run before required validation.');
}

foreach ([
    '/models/PartnerLeadContactForm.php',
    '/helpers/PartnerProgramCabinetHelper.php',
    '/views/upa-partner-lead/view.php',
    '/views/upa-partner-payout/view.php',
] as $file) {
    if (!is_file($root.$file)) {
        throw new RuntimeException('Partner cabinet package asset is missing: '.$file.'.');
    }
}

echo "Partner cabinet package boundary: ok\n";
