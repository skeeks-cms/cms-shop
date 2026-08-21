<?php

$view = file_get_contents(dirname(__DIR__).'/src/views/admin-payment/view.php');

function paymentCardSurfaceExpect($condition, $message)
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

paymentCardSurfaceExpect(strpos($view, 'BackendSurfaceWidget::begin') !== false, 'Payment view does not use the canonical surface widget.');
paymentCardSurfaceExpect(strpos($view, "'raised'") !== false, 'Payment view does not use the raised surface variant.');
paymentCardSurfaceExpect(strpos($view, "'bodyFlush'") !== false, 'Payment view does not use the flush surface body.');
paymentCardSurfaceExpect(strpos($view, 'sx-surface sx-payment-overview-item') !== false, 'Payment overview items are not canonical surfaces.');
paymentCardSurfaceExpect(strpos($view, 'sx-surface sx-payment-entity') !== false, 'Payment entity cards are not canonical surfaces.');
paymentCardSurfaceExpect(strpos($view, 'sx-surface sx-payment-requisite') !== false, 'Payment requisites are not canonical surfaces.');
paymentCardSurfaceExpect(strpos($view, 'sx-panel') === false, 'Payment view still contains deprecated sx-panel markup.');
paymentCardSurfaceExpect(strpos($view, 'sx-block') === false, 'Payment view still contains deprecated sx-block markup.');
paymentCardSurfaceExpect(!preg_match('/#[0-9a-f]{3,8}\b/i', $view), 'Payment view still contains hard-coded theme colors.');

echo "CMS shop payment card surface migration contract: OK\n";
