<?php

$controller = file_get_contents(dirname(__DIR__).'/src/controllers/AdminPaymentController.php');

function paymentExternalDocumentFieldsExpect($condition, $message)
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

foreach (['document_date', 'operation_at'] as $attribute) {
    paymentExternalDocumentFieldsExpect(
        strpos(
            $controller,
            "['payment_document']['fields']['{$attribute}']['widgetConfig']['options']['disabled']"
        ) !== false,
        "External payment field {$attribute} must pass disabled through WidgetField::widgetConfig."
    );

    paymentExternalDocumentFieldsExpect(
        strpos(
            $controller,
            "['payment_document']['fields']['{$attribute}']['elementOptions']['disabled']"
        ) === false,
        "External payment field {$attribute} must not configure the unknown WidgetField::elementOptions property."
    );
}

echo "CMS shop external payment document fields contract: OK\n";
