<?php

$model = file_get_contents(dirname(__DIR__).'/src/models/ShopDocument.php');

function shopDocumentEditableAddressExpect($condition, $message)
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

shopDocumentEditableAddressExpect(
    strpos($model, "isAttributeChanged('seller_contractor_id', false)") !== false,
    'Seller contractor ID must be compared by value so an HTML string ID does not overwrite an edited address snapshot.'
);
shopDocumentEditableAddressExpect(
    strpos($model, "isAttributeChanged('buyer_contractor_id', false)") !== false,
    'Buyer contractor ID must be compared by value so an HTML string ID does not overwrite an edited address snapshot.'
);

echo "Shop document editable address snapshot contract: OK\n";
