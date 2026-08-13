<?php

$controller = file_get_contents(dirname(__DIR__).'/src/controllers/AdminPaymentController.php');

function paymentListMediaExpect($condition, $message)
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

paymentListMediaExpect(strpos($controller, 'BackendEntityMedia::widget') !== false, 'Payment list has no canonical entity media.');
paymentListMediaExpect(strpos($controller, "'icon'  => 'credit-card'") !== false, 'Payment list has no semantic fallback icon.');
paymentListMediaExpect(substr_count($controller, "'class' => 'sx-preview-card__related'") >= 2, 'Payment client links are still rendered as primary bold text.');

echo "Payment list media contract: OK\n";
