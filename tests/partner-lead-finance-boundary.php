<?php

$root = dirname(__DIR__).'/src';
$model = file_get_contents($root.'/models/ShopPartnerLead.php');
$component = file_get_contents($root.'/components/ShopComponent.php');
$menu = file_get_contents($root.'/config/admin/menu.php');
$initialMigration = file_get_contents($root.'/migrations/m260817_200000__create_table__shop_partner_payout.php');
$rewardMigration = file_get_contents($root.'/migrations/m260818_132000__create_table__shop_partner_lead.php');
$payoutQuery = file_get_contents($root.'/models/queries/ShopPartnerPayoutQuery.php');
$bonusQuery = file_get_contents($root.'/models/queries/ShopBonusTransactionQuery.php');
$payoutController = file_get_contents($root.'/controllers/AdminPartnerPayoutController.php');
$bonusController = file_get_contents($root.'/controllers/AdminBonusTransactionController.php');
$commonConfig = file_get_contents($root.'/config/common.php');

foreach (['cms_lead_id', 'reward_value', 'shop_bonus_transaction_id'] as $attribute) {
    if (strpos($model, $attribute) === false) {
        throw new RuntimeException('Partner finance extension misses '.$attribute.'.');
    }
}
foreach (['name', 'phone', 'email', 'description', 'executor_id', 'status'] as $leadAttribute) {
    if (preg_match('/[\'\"]'.preg_quote($leadAttribute, '/').'[\'\"]\s*=>/', $model)) {
        throw new RuntimeException('ShopPartnerLead must not own generic lead attribute '.$leadAttribute.'.');
    }
}
if (strpos($component, 'CmsLead::EVENT_PARTNER_SUCCESS') === false) {
    throw new RuntimeException('Partner reward must extend the canonical CmsLead success event.');
}
if (strpos($initialMigration, 'shop_partner_payout') === false || strpos($menu, 'Вывод бонусов') === false) {
    throw new RuntimeException('Partner payout subsystem must remain installed and visible.');
}
if (strpos($rewardMigration, 'shop_partner_lead') === false || strpos($rewardMigration, 'cms_lead_id') === false) {
    throw new RuntimeException('Partner reward migration must create the one-to-one lead extension.');
}
if (strpos($menu, 'admin-partner-lead') !== false) {
    throw new RuntimeException('Generic leads must not be duplicated in the shop admin menu.');
}
foreach ([$payoutQuery, $bonusQuery] as $query) {
    if (strpos($query, 'function forManager') === false
        || strpos($query, 'CmsUser::find()') === false
        || strpos($query, '->forManager($user)') === false
    ) {
        throw new RuntimeException('Partner finance queries must follow the CRM manager scope.');
    }
}
foreach ([$payoutController, $bonusController] as $controller) {
    if (substr_count($controller, '->forManager()') < 2
        || strpos($controller, 'function getModel()') === false
    ) {
        throw new RuntimeException('Partner finance admin must scope both collections and direct records.');
    }
}
if (strpos($commonConfig, "'name' => 'shop/admin-partner-payout'") === false
    || strpos($commonConfig, '"shop/admin-partner-payout"') === false
) {
    throw new RuntimeException('Cms-shop must own and grant the partner payout permission.');
}

foreach ([
    '$lead->addSystemActivity(',
    "'Лид успешно завершён — начислено '",
    "rtrim(rtrim(number_format((float)\$reward->reward_value, 2, '.', ' '), '0'), '.')",
    'Html::encode(',
] as $activityContract) {
    if (strpos($component, $activityContract) === false) {
        throw new RuntimeException('A successful partner lead must report its actual reward: '.$activityContract);
    }
}
$rewardSavePosition = strpos($component, 'if (!$reward->save()) {');
$rewardActivityPosition = strpos($component, '$lead->addSystemActivity(');
if ($rewardSavePosition === false || $rewardActivityPosition === false || $rewardActivityPosition < $rewardSavePosition) {
    throw new RuntimeException('The success entry must follow the persisted reward and its bonus transaction.');
}
if (strpos($model, 'if (!$transaction->save()) {') === false
    || strpos($model, '$this->shop_bonus_transaction_id = $transaction->id;') === false
) {
    throw new RuntimeException('The reward row must own its ledger transaction before any activity can announce it.');
}

echo "Partner lead finance boundary: ok\n";
