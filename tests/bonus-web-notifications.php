<?php

$transactionSource = file_get_contents(__DIR__.'/../src/models/ShopBonusTransaction.php');
$notifySource = file_get_contents(__DIR__.'/../../cms/src/models/CmsWebNotify.php');

$checks = [
    'only new credit transactions notify the beneficiary' => strpos($transactionSource, 'if (!$insert || $this->is_debit || !$this->cms_user_id)') !== false,
    'bonus notification is assigned to the beneficiary' => strpos($transactionSource, '$notify->cms_user_id = (int)$this->cms_user_id;') !== false,
    'bonus notification contains the credited amount' => strpos($transactionSource, "'Вам начислены бонусы: '") !== false,
    'bonus notification links to the shared partner ledger' => strpos($transactionSource, '/shop/upa-partner-bonus') !== false,
    'credit transaction has client-facing bonus wording' => strpos($transactionSource, 'Начисление бонусов №') !== false,
    'debit transaction has client-facing bonus wording' => strpos($transactionSource, 'Списание бонусов №') !== false,
    'URL-only notifications render an actionable title' => strpos($notifySource, 'if (!$model && $url)') !== false,
];

foreach ($checks as $message => $passed) {
    if (!$passed) {
        fwrite(STDERR, "FAILED: {$message}\n");
        exit(1);
    }
}

echo "bonus-web-notifications: OK\n";
