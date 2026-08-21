<?php

use skeeks\cms\shop\models\ShopPayment;

$autoloadCandidates = [
    dirname(__DIR__, 3).'/autoload.php',
    dirname(__DIR__).'/vendor/autoload.php',
    getcwd().'/vendor/autoload.php',
];

foreach ($autoloadCandidates as $autoload) {
    if (is_file($autoload)) {
        require $autoload;
        break;
    }
}

if (!class_exists(ShopPayment::class)) {
    throw new RuntimeException('Composer autoload.php was not found.');
}

function paymentBillAmountPrecisionExpect($condition, $message)
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

paymentBillAmountPrecisionExpect(
    ShopPayment::isAmountEnoughForBill(19999.64, 19999.643),
    'A fraction of a kopeck must not leave a visually fully paid bill unpaid.'
);
paymentBillAmountPrecisionExpect(
    !ShopPayment::isAmountEnoughForBill(19999.63, 19999.643),
    'A real one-kopeck shortage must leave the bill unpaid.'
);
paymentBillAmountPrecisionExpect(
    ShopPayment::isAmountEnoughForBill(20000, 19999.643),
    'An overpayment must cover the bill.'
);

echo "CMS shop payment/bill amount precision contract: OK\n";
