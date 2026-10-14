<?php

$root = dirname(__DIR__);
$model = file_get_contents($root.'/src/models/ShopDocument.php');
$view = file_get_contents($root.'/src/views/shop-document/upd.php');
$xml = file_get_contents($root.'/src/document/FnsUpdXmlGenerator.php');

function shopDocumentUpdConsigneeExpect($condition, $message)
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

shopDocumentUpdConsigneeExpect(
    substr_count($model, "=> \$consigneeAddress,") === 3,
    'UPD, waybill and invoice-facture created from a bill must store the consignee address separately from its name.'
);
shopDocumentUpdConsigneeExpect(
    strpos($model, "\$buyerContractor->hasAttribute('delivery_address')") !== false
    && strpos($model, "\$consigneeAddress = \$buyerAddress;") !== false,
    'The consignee address must come from the buyer delivery address and fall back to the buyer address.'
);
shopDocumentUpdConsigneeExpect(
    strpos($view, "'upd.consignee_address'") !== false,
    'The UPD print form must render field 4 with the separate consignee address.'
);
shopDocumentUpdConsigneeExpect(
    strpos($xml, "ArrayHelper::getValue(\$data, 'consignee_address')") !== false
    && strpos($xml, "\$address = \$model->buyerAddress;") !== false,
    'The FNS XML consignee must use the separate address and fall back to the buyer address.'
);

echo "Shop document UPD consignee address contract: OK\n";
