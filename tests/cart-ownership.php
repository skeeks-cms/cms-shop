<?php
/**
 * Проверка исходников: публичная корзина меняет только позиции и заказ текущего покупателя.
 * Запуск: php tests/cart-ownership.php
 */

$root = dirname(__DIR__);
$controller = file_get_contents($root.'/src/controllers/CartController.php');
$shopJs = file_get_contents($root.'/src/assets/classes/Shop.js');

function shopCartOwnershipExpect($condition, $message)
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

shopCartOwnershipExpect(
    strpos($controller, "ShopOrderItem::find()->where(['id' => \$basket_id])") === false,
    'remove-basket and update-basket must not find an order item by id alone.'
);
shopCartOwnershipExpect(
    substr_count($controller, '$this->findCartItem($basket_id)') === 2
    && strpos($controller, "'shop_order_id' => \$order->id,") !== false,
    'remove-basket and update-basket must look up the item inside the current buyer cart.'
);
shopCartOwnershipExpect(
    strpos($controller, '$this->findOrderForUpdate(') !== false
    && strpos($controller, "'cms_user_id' => \\Yii::\$app->user->id,") !== false,
    'order-update must change only the current cart or an own order of the signed-in buyer.'
);
shopCartOwnershipExpect(
    strpos($controller, 'array_intersect_key($data, array_flip($this->orderUpdateAttributes))') !== false,
    'order-update must write only the whitelisted buyer fields.'
);

if (!preg_match('/public \$orderUpdateAttributes = \[(.*?)\];/s', $controller, $m)) {
    throw new RuntimeException('orderUpdateAttributes whitelist is missing.');
}
foreach (['paid_at', 'paid_amount', 'amount', 'discount_amount', 'delivery_amount', 'shop_order_status_id', 'status_code',
    'is_created', 'is_order', 'cms_user_id', 'shop_buyer_id', 'cms_site_id', 'code', 'external_id', 'receiver_cms_user_id', 'cms_user_address_id'] as $forbidden) {
    shopCartOwnershipExpect(
        strpos($m[1], "'".$forbidden."'") === false,
        "order-update whitelist must not contain {$forbidden}."
    );
}

shopCartOwnershipExpect(
    strpos($shopJs, 'if (eventData && eventData.product)') !== false,
    'The remove analytics event must fire only when an item was actually removed.'
);

echo "Shop cart ownership contract: OK\n";
