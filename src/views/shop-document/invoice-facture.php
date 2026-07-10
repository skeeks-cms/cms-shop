<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 */

/* @var $this yii\web\View */
/* @var $model \skeeks\cms\shop\models\ShopDocument */

use skeeks\cms\models\CmsContractor;
use skeeks\cms\shop\models\ShopDocument;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

if (!@$isPdf) {
    $css = file_get_contents(\Yii::getAlias('@skeeks/cms/shop/views/shop-document/document.css'));
    $this->registerCss($css);
}

$documentData = (array)$model->document_data;
$specificData = (array)ArrayHelper::getValue($documentData, 'invoice_facture', []);
$items = $model->documentItems;
$bills = $model->bills;
$sourceBill = $bills ? reset($bills) : null;
$number = $model->number ?: $model->id;
$issuedAt = $model->issued_at ?: $model->created_at ?: time();
$date = \Yii::$app->formatter->asDate($issuedAt, 'php:d.m.Y');

$pick = function ($values, $fallback = '-') {
    foreach ((array)$values as $value) {
        $value = trim((string)$value);
        if ($value !== '' && $value !== '-') {
            return $value;
        }
    }

    return $fallback;
};
$e = function ($value, $fallback = '-') use ($pick) {
    return Html::encode($pick($value, $fallback));
};
$money = function ($value) {
    return number_format((float)$value, 2, ',', ' ');
};
$quantity = function ($value) {
    return number_format((float)$value, 1, ',', ' ');
};
$measureCode = function ($measure) {
    $measure = trim(mb_strtolower((string)$measure));
    if (in_array($measure, ['шт', 'шт.', 'штука', 'штук'], true)) {
        return '796';
    }

    return '-';
};
$innKpp = function ($inn, $kpp, $empty = '-') use ($pick) {
    $inn = $pick($inn, '');
    $kpp = $pick($kpp, '');

    if (!$inn && !$kpp) {
        return $empty;
    }

    return $inn.($kpp ? '/'.$kpp : '');
};
$nameAddress = function ($name, $address) use ($pick) {
    $name = $pick($name, '');
    $address = $pick($address, '');

    if ($name && $address) {
        return $name.', '.$address;
    }

    return $name ?: $address ?: '-';
};

$sellerContractor = $model->sellerContractor;
if (!$sellerContractor && $sourceBill) {
    $sellerContractor = $sourceBill->receiverContractor;
}
if (!$sellerContractor) {
    $sellerContractor = CmsContractor::find()->our()->one();
}

$buyerContractor = $model->buyerContractor;
if (!$buyerContractor && $sourceBill) {
    $buyerContractor = $sourceBill->senderContractor;
}
if (!$buyerContractor && $sourceBill && $sourceBill->company && $sourceBill->company->contractors) {
    $companyContractors = $sourceBill->company->contractors;
    $buyerContractor = reset($companyContractors);
}
if (!$buyerContractor && $sourceBill && $sourceBill->cmsUser && $sourceBill->cmsUser->cmsContractors) {
    $userContractors = $sourceBill->cmsUser->cmsContractors;
    $buyerContractor = reset($userContractors);
}

$sellerName = $pick([
    $model->sellerFullName,
    $model->sellerName,
    $sellerContractor ? ($sellerContractor->full_name ?: $sellerContractor->asShortText) : null,
]);
$sellerAddress = $pick([
    $model->sellerAddress,
    $sellerContractor ? $sellerContractor->address : null,
]);
$sellerInn = $pick([$model->sellerInn, $sellerContractor ? $sellerContractor->inn : null], '');
$sellerKpp = $pick([$model->sellerKpp, $sellerContractor ? $sellerContractor->kpp : null], '');
$sellerOgrn = $pick([$model->sellerOgrn, $sellerContractor ? $sellerContractor->ogrn : null], '');

$buyerName = $pick([
    $model->buyerFullName,
    $model->buyerName,
    $buyerContractor ? ($buyerContractor->full_name ?: $buyerContractor->asShortText) : null,
    $sourceBill && $sourceBill->company ? $sourceBill->company->name : null,
]);
$buyerAddress = $pick([$model->buyerAddress, $buyerContractor ? $buyerContractor->address : null]);
$buyerInn = $pick([$model->buyerInn, $buyerContractor ? $buyerContractor->inn : null], '');
$buyerKpp = $pick([$model->buyerKpp, $buyerContractor ? $buyerContractor->kpp : null], '');

$shipper = $nameAddress(
    ArrayHelper::getValue($specificData, 'shipper', $sellerName),
    ArrayHelper::getValue($specificData, 'shipper_address', $sellerAddress)
);
$consignee = $nameAddress(
    ArrayHelper::getValue($specificData, 'consignee', $buyerName),
    ArrayHelper::getValue($specificData, 'consignee_address', $buyerAddress)
);
$paymentDocument = $pick(ShopDocument::formatPaymentDocuments(
    ArrayHelper::getValue($specificData, 'payment_documents', []),
    ArrayHelper::getValue($specificData, 'payment_document', '')
), '№ - от -');
$stateContract = $pick(ArrayHelper::getValue($specificData, 'state_contract_identifier'), '-');
$advanceDocument = ShopDocument::formatNumberDateDocuments(ShopDocument::normalizeNumberDateDocuments(
    ArrayHelper::getValue($specificData, 'advance_documents', []),
    ArrayHelper::getValue($specificData, 'advance_document', '')
));
$correctionNumber = $pick(ArrayHelper::getValue($specificData, 'correction_number'), '-');
$correctionDate = $pick(ArrayHelper::getValue($specificData, 'correction_date'), '-');
$shipmentDocumentsText = $pick(ArrayHelper::getValue($specificData, 'shipment_documents_text'), '');
if (!$shipmentDocumentsText) {
    $shipmentDocuments = (array)ArrayHelper::getValue($specificData, 'shipment_documents', []);
    $shipmentDocumentsRows = [];
    foreach ($shipmentDocuments as $shipmentDocument) {
        $shipmentType = $pick(ArrayHelper::getValue($shipmentDocument, 'type'), '');
        $shipmentNumber = $pick(ArrayHelper::getValue($shipmentDocument, 'number'), '');
        $shipmentDate = $pick(ArrayHelper::getValue($shipmentDocument, 'date'), '');
        $shipmentDocumentsRows[] = trim($shipmentType.' № '.$shipmentNumber.' от '.$shipmentDate);
    }
    $shipmentDocumentsText = implode("\n", array_filter($shipmentDocumentsRows));
}
if (!$shipmentDocumentsText) {
    $shipmentDocumentsText = '-';
}
$currencyName = $model->currency_code == 'RUB' ? 'Российский рубль, 643' : $model->currency_code;
?>

<section class="sx-invoice-page">
    <table class="sx-invoice-title-row">
        <tr>
            <td>
                <table class="sx-upd-invoice-lines">
                    <tr>
                        <td>Счет-фактура №</td>
                        <td class="sx-upd-fill sx-upd-fill-number"><?= Html::encode($number); ?></td>
                        <td>от</td>
                        <td class="sx-upd-fill sx-upd-fill-date"><?= Html::encode($date); ?></td>
                        <td>(1)</td>
                    </tr>
                    <tr>
                        <td>Исправление №</td>
                        <td class="sx-upd-fill sx-upd-fill-number"><?= Html::encode($correctionNumber); ?></td>
                        <td>от</td>
                        <td class="sx-upd-fill sx-upd-fill-date"><?= Html::encode($correctionDate); ?></td>
                        <td>(1а)</td>
                    </tr>
                </table>
            </td>
            <td class="sx-upd-appendix">
                Приложение № 1<br>
                к постановлению Правительства Российской Федерации от 26 декабря 2011 г. № 1137
            </td>
        </tr>
    </table>

    <table class="sx-upd-lines">
        <tr><td class="sx-upd-label sx-upd-bold">Продавец</td><td class="sx-upd-line-value sx-upd-bold"><?= $e($sellerName); ?></td><td class="sx-upd-code">(2)</td></tr>
        <tr><td class="sx-upd-label">Адрес</td><td class="sx-upd-line-value"><?= $e($sellerAddress); ?></td><td class="sx-upd-code">(2а)</td></tr>
        <tr><td class="sx-upd-label">ИНН/КПП продавца</td><td class="sx-upd-line-value"><?= $e($innKpp($sellerInn, $sellerKpp)); ?></td><td class="sx-upd-code">(2б)</td></tr>
        <tr><td class="sx-upd-label">Грузоотправитель и его адрес</td><td class="sx-upd-line-value"><?= $e($shipper); ?></td><td class="sx-upd-code">(3)</td></tr>
        <tr><td class="sx-upd-label">Грузополучатель и его адрес</td><td class="sx-upd-line-value"><?= $e($consignee); ?></td><td class="sx-upd-code">(4)</td></tr>
        <tr><td class="sx-upd-label">К платежно-расчетному документу</td><td class="sx-upd-line-value"><?= $e($paymentDocument); ?></td><td class="sx-upd-code">(5)</td></tr>
        <tr><td class="sx-upd-label">Документ об отгрузке</td><td class="sx-upd-line-value"><?= nl2br(Html::encode($shipmentDocumentsText)); ?></td><td class="sx-upd-code">(5а)</td></tr>
        <tr class="sx-upd-long-row">
            <td class="sx-upd-long-line" colspan="2">
                К счету-фактуре, выставленному при получении оплаты, частичной оплаты или иных платежей в счет предстоящих поставок товаров, выполнения работ, оказания услуг
                <span><?= Html::encode($advanceDocument ?: '№ _________ от _________'); ?></span>
            </td>
            <td class="sx-upd-code">(5б)</td>
        </tr>
        <tr><td class="sx-upd-label sx-upd-bold">Покупатель</td><td class="sx-upd-line-value sx-upd-bold"><?= $e($buyerName); ?></td><td class="sx-upd-code">(6)</td></tr>
        <tr><td class="sx-upd-label">Адрес</td><td class="sx-upd-line-value"><?= $e($buyerAddress); ?></td><td class="sx-upd-code">(6а)</td></tr>
        <tr><td class="sx-upd-label">ИНН/КПП покупателя</td><td class="sx-upd-line-value"><?= $e($innKpp($buyerInn, $buyerKpp)); ?></td><td class="sx-upd-code">(6б)</td></tr>
        <tr><td class="sx-upd-label">Валюта: наименование, код</td><td class="sx-upd-line-value"><?= $e($currencyName); ?></td><td class="sx-upd-code">(7)</td></tr>
        <tr><td class="sx-upd-label">Идентификатор государственного контракта, договора (соглашения) (при наличии)</td><td class="sx-upd-line-value"><?= $e($stateContract); ?></td><td class="sx-upd-code">(8)</td></tr>
    </table>

    <table class="sx-upd-products sx-invoice-products">
        <colgroup>
            <col style="width:5mm">
            <col style="width:17mm">
            <col style="width:34mm">
            <col style="width:10mm">
            <col style="width:7mm">
            <col style="width:12mm">
            <col style="width:12mm">
            <col style="width:14mm">
            <col style="width:19mm">
            <col style="width:11mm">
            <col style="width:12mm">
            <col style="width:12mm">
            <col style="width:18mm">
            <col style="width:10mm">
            <col style="width:14mm">
            <col style="width:29mm">
        </colgroup>
        <thead>
            <tr>
                <th rowspan="2">N<br>п/п</th>
                <th rowspan="2">Код товара/<br>работ, услуг</th>
                <th rowspan="2">Наименование товара (описание выполненных работ, оказанных услуг), имущественного права</th>
                <th rowspan="2">Код вида товара</th>
                <th colspan="2">Единица измерения</th>
                <th rowspan="2">Количест-<br>во (объем)</th>
                <th rowspan="2">Цена за единицу</th>
                <th rowspan="2">Стоимость без налога - всего</th>
                <th rowspan="2">Акциз</th>
                <th rowspan="2">Налоговая ставка</th>
                <th rowspan="2">Сумма налога</th>
                <th rowspan="2">Стоимость с налогом - всего</th>
                <th colspan="2">Страна происхождения товара</th>
                <th rowspan="2">Регистрационный номер декларации на товары</th>
            </tr>
            <tr>
                <th>Код</th>
                <th>Условное обозначение</th>
                <th>Код</th>
                <th>Краткое наименование</th>
            </tr>
            <tr class="sx-upd-products-codes">
                <th>1</th><th>А</th><th>1а</th><th>1б</th><th>2</th><th>2а</th><th>3</th><th>4</th><th>5</th><th>6</th><th>7</th><th>8</th><th>9</th><th>10</th><th>10а</th><th>11</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $index => $item) : ?>
                <?php $vatName = $pick($item->vat_name, 'Без НДС'); ?>
                <?php $extra = (array)$item->extra_data; ?>
                <tr class="sx-upd-product-row">
                    <td><?= $index + 1; ?></td>
                    <td><?= Html::encode(ArrayHelper::getValue($extra, 'code', '')); ?></td>
                    <td><?= Html::encode($item->name); ?></td>
                    <td>-</td>
                    <td><?= Html::encode($measureCode($item->measure_name)); ?></td>
                    <td><?= $e($item->measure_name); ?></td>
                    <td class="sx-upd-num"><?= Html::encode($quantity($item->quantity)); ?></td>
                    <td class="sx-upd-num"><?= Html::encode($money($item->price)); ?></td>
                    <td class="sx-upd-num"><?= Html::encode($money($item->amount)); ?></td>
                    <td>-</td>
                    <td><?= Html::encode($vatName); ?></td>
                    <td class="sx-upd-num">0,00</td>
                    <td class="sx-upd-num"><?= Html::encode($money($item->amount)); ?></td>
                    <td><?= Html::encode(ArrayHelper::getValue($extra, 'country_code', '-')); ?></td>
                    <td><?= Html::encode(ArrayHelper::getValue($extra, 'country_name', '-')); ?></td>
                    <td><?= Html::encode(ArrayHelper::getValue($extra, 'declaration_number', '-')); ?></td>
                </tr>
            <?php endforeach; ?>
            <tr class="sx-upd-total">
                <td></td>
                <td colspan="7">Всего к оплате</td>
                <td class="sx-upd-num"><?= Html::encode($money($model->amount)); ?></td>
                <td colspan="2">X</td>
                <td class="sx-upd-num">0,00</td>
                <td class="sx-upd-num"><?= Html::encode($money($model->amount)); ?></td>
                <td colspan="3"></td>
            </tr>
        </tbody>
    </table>

    <table class="sx-invoice-signatures">
        <tr>
            <td>Руководитель организации<br><span></span><small>(подпись)</small></td>
            <td>Главный бухгалтер<br><span></span><small>(подпись)</small></td>
            <td>Индивидуальный предприниматель<br><span></span><small>(подпись)</small></td>
            <td>ОГРНИП<br><span><?= Html::encode($sellerOgrn); ?></span></td>
        </tr>
    </table>

</section>

<?php if (!@$isPdf) : ?>
    <?= $this->render('_controls', ['model' => $model]); ?>
<?php endif; ?>
