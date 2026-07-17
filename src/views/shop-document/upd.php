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

$noSignature = @$noSignature;
$documentData = (array)$model->document_data;
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
$pickContractorName = function ($values, $fallback = '-') {
    $incompleteName = '';

    foreach ((array)$values as $value) {
        $value = trim((string)$value);
        if ($value === '' || $value === '-') {
            continue;
        }

        if (preg_match('/^(?:ИП|ООО|АО|ПАО|ОАО|ЗАО|НКО|АНО|самозанятый|физическое лицо)\.?$/ui', $value)) {
            $incompleteName = $incompleteName ?: $value;
            continue;
        }

        return $value;
    }

    return $incompleteName ?: $fallback;
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
$ruDate = function ($timestamp) {
    $months = [
        1  => 'января',
        2  => 'февраля',
        3  => 'марта',
        4  => 'апреля',
        5  => 'мая',
        6  => 'июня',
        7  => 'июля',
        8  => 'августа',
        9  => 'сентября',
        10 => 'октября',
        11 => 'ноября',
        12 => 'декабря',
    ];

    return '«'.date('d', $timestamp).'» '.$months[(int)date('n', $timestamp)].' '.date('Y', $timestamp).' г.';
};
$innKpp = function ($inn, $kpp, $empty = '-') use ($pick) {
    $inn = $pick($inn, '');
    $kpp = $pick($kpp, '');

    if (!$inn && !$kpp) {
        return $empty;
    }

    return $inn.($kpp ? '/'.$kpp : '');
};
$subjectInnKpp = function ($inn, $kpp) use ($pick) {
    $inn = $pick($inn, '');
    $kpp = $pick($kpp, '');
    return $inn || $kpp ? $inn.'/'.$kpp : '-';
};

$sellerContractor = $model->sellerContractor;
if (!$sellerContractor && $sourceBill) {
    $sellerContractor = $sourceBill->receiverContractor;
}
if (!$sellerContractor) {
    $sellerContractor = CmsContractor::find()->our()->one();
}
$isSellerLegal = $sellerContractor && $sellerContractor->contractor_type === CmsContractor::TYPE_LEGAL;
$sellerSignature = !$noSignature && $sellerContractor ? $sellerContractor->directorSignature : null;
$sellerStamp = !$noSignature && $sellerContractor ? $sellerContractor->stamp : null;

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

$sellerName = $pickContractorName([
    $model->sellerFullName,
    $model->sellerName,
    $sellerContractor ? $sellerContractor->full_name : null,
    $sellerContractor ? $sellerContractor->asShortText : null,
]);
$sellerAddress = $pick([
    $model->sellerAddress,
    $sellerContractor ? $sellerContractor->address : null,
]);
$sellerInn = $pick([
    $model->sellerInn,
    $sellerContractor ? $sellerContractor->inn : null,
], '');
$sellerKpp = $pick([
    $model->sellerKpp,
    $sellerContractor ? $sellerContractor->kpp : null,
], '');
$sellerOgrn = $pick([
    $model->sellerOgrn,
    $sellerContractor ? $sellerContractor->ogrn : null,
], '');
$sellerRegistrationDate = ShopDocument::normalizeDocumentDateValue($pick([
    $model->sellerRegistrationDate,
    $sellerContractor && $sellerContractor->hasAttribute('registration_date') ? $sellerContractor->getAttribute('registration_date') : null,
], ''));

$buyerName = $pickContractorName([
    $model->buyerFullName,
    $model->buyerName,
    $buyerContractor ? $buyerContractor->full_name : null,
    $buyerContractor ? $buyerContractor->asShortText : null,
    $sourceBill && $sourceBill->company ? $sourceBill->company->name : null,
]);
$buyerAddress = $pick([
    $model->buyerAddress,
    $buyerContractor ? $buyerContractor->address : null,
]);
$buyerInn = $pick([
    $model->buyerInn,
    $buyerContractor ? $buyerContractor->inn : null,
], '');
$buyerKpp = $pick([
    $model->buyerKpp,
    $buyerContractor ? $buyerContractor->kpp : null,
], '');

$paymentDocument = $pick([
    ShopDocument::formatPaymentDocuments($model->resolvedPaymentDocuments('upd')),
], '№ - от -');
$advanceDocument = ShopDocument::formatNumberDateDocuments(
    ShopDocument::normalizeNumberDateDocuments(
        ArrayHelper::getValue($documentData, 'upd.advance_documents', []),
        ArrayHelper::getValue($documentData, 'upd.advance_document', '')
    )
);
$stateContract = $pick([
    ArrayHelper::getValue($documentData, 'upd.state_contract_identifier'),
    ArrayHelper::getValue($documentData, 'state_contract_identifier'),
], '-');
$baseDocument = $pick([
    ShopDocument::formatBaseDocument($model->resolvedBaseDocument('upd')),
    ArrayHelper::getValue($documentData, 'upd.base_document'),
    $model->description,
], '-');
$transportInfo = $pick(ArrayHelper::getValue($documentData, 'upd.transport_info'), '-');
$sellerOtherInfo = $pick(ArrayHelper::getValue($documentData, 'upd.seller_other_info'), '');
$buyerOtherInfo = $pick(ArrayHelper::getValue($documentData, 'upd.buyer_other_info'), '');
$shippingDocument = $pick(
    ArrayHelper::getValue($documentData, 'upd.shipping_document'),
    'Универсальный передаточный документ № '.$number.' от '.$date
);
$updStatus = $pick(ArrayHelper::getValue($documentData, 'upd.status'), '2');
$currencyName = $model->currency_code == 'RUB' ? 'Российский рубль, 643' : $model->currency_code;
$shipper = $pick(ArrayHelper::getValue($documentData, 'upd.shipper'), $sellerName.', '.$sellerAddress);
$consignee = $pick(ArrayHelper::getValue($documentData, 'upd.consignee'), $buyerName.', '.$buyerAddress);
$sellerSubject = $sellerName.', ИНН/КПП '.$subjectInnKpp($sellerInn, $sellerKpp);
$buyerSubject = $buyerName.', ИНН/КПП '.$subjectInnKpp($buyerInn, $buyerKpp);
$logoSrc = $pick(ArrayHelper::getValue($documentData, 'logo_src'), '');
if (!$logoSrc && $sellerContractor && $sellerContractor->cmsImage) {
    $logoSrc = $sellerContractor->cmsImage->absoluteSrc;
}
if (!$logoSrc && \Yii::$app->has('skeeks') && \Yii::$app->skeeks->site && \Yii::$app->skeeks->site->image) {
    $logoSrc = \Yii::$app->skeeks->site->image->absoluteSrc;
}
?>

<section class="sx-upd-page sx-upd-page-1">
    <table class="sx-upd-head-block">
        <tr>
            <td class="sx-upd-side">
                <div class="sx-upd-side-title">Универсальный<br>передаточный<br>документ</div>
                <div class="sx-upd-status-label">Статус:</div>
                <div class="sx-upd-status-box"><?= Html::encode($updStatus); ?></div>
                <div class="sx-upd-status-help">1 - счёт-фактура<br>и передаточный<br>документ (акт)<br>2 - передаточный<br>документ (акт)</div>
            </td>
            <td class="sx-upd-head-main">
                <table class="sx-upd-title-row">
                    <tr>
                        <td>
                            <table class="sx-upd-invoice-lines">
                                <tr>
                                    <td>Счёт фактура №</td>
                                    <td class="sx-upd-fill sx-upd-fill-number"><?= Html::encode($number); ?></td>
                                    <td>от</td>
                                    <td class="sx-upd-fill sx-upd-fill-date"><?= Html::encode($date); ?></td>
                                    <td>(1)</td>
                                </tr>
                                <tr>
                                    <td>Исправление №</td>
                                    <td class="sx-upd-fill sx-upd-fill-number">-</td>
                                    <td>от</td>
                                    <td class="sx-upd-fill sx-upd-fill-date">-</td>
                                    <td>(1а)</td>
                                </tr>
                            </table>
                        </td>
                        <td class="sx-upd-appendix">
                            Приложение № 1<br>
                            к постановлению Правительства Российской Федерации от 26 декабря 2011 г. № 1137<br>
                            (в ред. постановлений Правительства Российской Федерации от 25 мая 2017 г. № 625,<br>
                            от 19 августа 2017 г. № 981, от 2 апреля 2021 г. № 534, от 16 августа 2024 г. № 1096,<br>
                            от 23 января 2026 г. № 26)
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
                    <tr><td class="sx-upd-label">Документ об отгрузке</td><td class="sx-upd-line-value"><?= $e($shippingDocument); ?></td><td class="sx-upd-code">(5а)</td></tr>
                    <tr class="sx-upd-long-row">
                        <td class="sx-upd-long-line" colspan="2">
                            К счету-фактуре (счетам-фактурам), выставленному (выставленным) при получении оплаты, частичной оплаты или иных платежей в счет предстоящих поставок товаров (выполнения работ, оказания услуг), передачи имущественных прав
                            <span><?= Html::encode($advanceDocument ?: '№ _________ от _________, исправление № _________ от _________'); ?></span>
                        </td>
                        <td class="sx-upd-code">(5б)</td>
                    </tr>
                    <tr><td class="sx-upd-label sx-upd-bold">Покупатель</td><td class="sx-upd-line-value sx-upd-bold"><?= $e($buyerName); ?></td><td class="sx-upd-code">(6)</td></tr>
                    <tr><td class="sx-upd-label">Адрес</td><td class="sx-upd-line-value"><?= $e($buyerAddress); ?></td><td class="sx-upd-code">(6а)</td></tr>
                    <tr><td class="sx-upd-label">ИНН/КПП покупателя</td><td class="sx-upd-line-value"><?= $e($innKpp($buyerInn, $buyerKpp)); ?></td><td class="sx-upd-code">(6б)</td></tr>
                    <tr><td class="sx-upd-label">Валюта: наименование, код</td><td class="sx-upd-line-value"><?= $e($currencyName); ?></td><td class="sx-upd-code">(7)</td></tr>
                    <tr><td class="sx-upd-label">Идентификатор государственного контракта, договора (соглашения) (при наличии)</td><td class="sx-upd-line-value"><?= $e($stateContract); ?></td><td class="sx-upd-code">(8)</td></tr>
                </table>
            </td>
        </tr>
    </table>

    <table class="sx-upd-products">
        <colgroup>
            <col style="width:5mm">
            <col style="width:17mm">
            <col style="width:19mm">
            <col style="width:9mm">
            <col style="width:7mm">
            <col style="width:12mm">
            <col style="width:12mm">
            <col style="width:13mm">
            <col style="width:17mm">
            <col style="width:10mm">
            <col style="width:12mm">
            <col style="width:11mm">
            <col style="width:16mm">
            <col style="width:10mm">
            <col style="width:14mm">
            <col style="width:28mm">
            <col style="width:9mm">
            <col style="width:20mm">
            <col style="width:22mm">
            <col style="width:22mm">
        </colgroup>
        <thead>
            <tr>
                <th rowspan="2">N<br>п/п</th>
                <th rowspan="2">Код товара/<br>работ, услуг</th>
                <th rowspan="2">Наименование товара (описание выполненных работ, оказанных услуг), имущественного права</th>
                <th rowspan="2">Код вида товара</th>
                <th colspan="2">Единица измерения</th>
                <th rowspan="2">Количест-<br>во (объём)</th>
                <th rowspan="2">Цена (тариф) за единицу измере-<br>ния</th>
                <th rowspan="2">Стоимость товаров (работ, услуг), имущест-<br>венных прав без налога - всего</th>
                <th rowspan="2">В том числе сумма акциза</th>
                <th rowspan="2">Налого-<br>вая ставка</th>
                <th rowspan="2">Сумма налога, предъяв-<br>ляемая покупа-<br>телю</th>
                <th rowspan="2">Стоимость товаров (работ, услуг), имущест-<br>венных прав с налогом - всего</th>
                <th colspan="2">Страна происхождения товара</th>
                <th rowspan="2">Регистрационный номер декларации на товары или регистрационный номер партии товара, подлежащего прослеживаемости</th>
                <th colspan="2">Количественная единица измерения товара, используемая в целях осуществления прослеживаемости</th>
                <th rowspan="2">Количество товара, подлежащего прослежива-<br>емости, в количествен-<br>ной единице измерения товара, используемой в целях осуществления прослежива-<br>емости</th>
                <th rowspan="2">Стоимость товара, подлежащего прослежива-<br>емости, без налога на добавленную стоимость, в рублях</th>
            </tr>
            <tr>
                <th>Код</th>
                <th>Условное обозна-<br>чение (нацио-<br>нальное)</th>
                <th>Цифро-<br>вой код</th>
                <th>Краткое наимено-<br>вание</th>
                <th>Код</th>
                <th>Условное обозначение (национальное)</th>
            </tr>
            <tr class="sx-upd-products-codes">
                <th>1</th><th>А</th><th>1а</th><th>1б</th><th>2</th><th>2а</th><th>3</th><th>4</th><th>5</th><th>6</th><th>7</th><th>8</th><th>9</th><th>10</th><th>10а</th><th>11</th><th>12</th><th>12а</th><th>13</th><th>14</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $index => $item) : ?>
                <?php $vatName = $pick($item->vat_name, 'Без НДС'); ?>
                <?php $unitPrice = $item->unitPriceAfterDiscount; ?>
                <tr class="sx-upd-product-row">
                    <td><?= $index + 1; ?></td>
                    <td></td>
                    <td><?= Html::encode($item->name); ?></td>
                    <td>-</td>
                    <td><?= Html::encode($measureCode($item->measure_name)); ?></td>
                    <td><?= $e($item->measure_name); ?></td>
                    <td class="sx-upd-num"><?= Html::encode($quantity($item->quantity)); ?></td>
                    <td class="sx-upd-num"><?= Html::encode($money($unitPrice)); ?></td>
                    <td class="sx-upd-num"><?= Html::encode($money($item->amount)); ?></td>
                    <td>-</td>
                    <td><?= Html::encode($vatName); ?></td>
                    <td>-</td>
                    <td class="sx-upd-num"><?= Html::encode($money($item->amount)); ?></td>
                    <td>-</td>
                    <td>-</td>
                    <td>-</td>
                    <td>-</td>
                    <td>-</td>
                    <td>-</td>
                    <td>-</td>
                </tr>
            <?php endforeach; ?>
            <tr class="sx-upd-total">
                <td></td>
                <td colspan="7">Всего к оплате (9)</td>
                <td class="sx-upd-num"><?= Html::encode($money($model->amount)); ?></td>
                <td colspan="2">X</td>
                <td class="sx-upd-num">0,00</td>
                <td class="sx-upd-num"><?= Html::encode($money($model->amount)); ?></td>
                <td colspan="7"></td>
            </tr>
        </tbody>
    </table>

    <table class="sx-upd-footbar">
        <tr>
            <td class="sx-upd-footbar-logo">
                <?php if ($logoSrc) : ?>
                    <img class="sx-upd-logo" src="<?= Html::encode($logoSrc); ?>" />
                <?php endif; ?>
            </td>
            <td class="sx-upd-footer">УПД(<?= Html::encode($updStatus); ?>)№<?= Html::encode($number); ?> от <?= Html::encode($date); ?></td>
            <td class="sx-upd-page-number">1/2</td>
        </tr>
    </table>
</section>

<section class="sx-upd-page sx-upd-page-2">
    <table class="sx-upd-sign-top">
        <tr>
            <td class="sx-upd-side sx-upd-side-second">
                <div>Документ<br>составлен на 2<br>листах</div>
            </td>
            <td class="sx-upd-sign-top-main">
                <table class="sx-upd-sign-grid">
                    <tr>
                        <td class="sx-upd-sign-label">Руководитель организации<br>или иное уполномоченное лицо</td>
                        <td class="sx-upd-under sx-upd-signature-cell">
                            <?php if ($sellerSignature && $isSellerLegal) : ?>
                                <img class="sx-upd-signature-image" src="<?= Html::encode($sellerSignature->absoluteSrc); ?>" />
                            <?php endif; ?>
                        </td>
                        <td class="sx-upd-under sx-upd-fio"></td>
                        <td class="sx-upd-sign-label">Главный бухгалтер<br>или иное уполномоченное лицо</td>
                        <td class="sx-upd-under"></td>
                        <td class="sx-upd-under sx-upd-fio"></td>
                    </tr>
                    <tr class="sx-upd-hints"><td></td><td>(подпись)</td><td>(ф.И.О.)</td><td></td><td>(подпись)</td><td>(ф.И.О.)</td></tr>
                    <tr>
                        <td class="sx-upd-sign-label">Индивидуальный предприниматель<br>или иное уполномоченное лицо</td>
                        <td class="sx-upd-under sx-upd-signature-cell">
                            <?php if ($sellerSignature && !$isSellerLegal) : ?>
                                <img class="sx-upd-signature-image" src="<?= Html::encode($sellerSignature->absoluteSrc); ?>" />
                            <?php endif; ?>
                        </td>
                        <td class="sx-upd-under sx-upd-fio"></td>
                        <td></td>
                        <td colspan="2" class="sx-upd-under sx-upd-ogrn"><?= $e($sellerOgrn ? $sellerOgrn.($sellerRegistrationDate ? ' от '.date('d.m.Y', strtotime($sellerRegistrationDate)) : '') : ''); ?></td>
                    </tr>
                    <tr class="sx-upd-hints"><td></td><td>(подпись)</td><td>(ф.И.О.)</td><td></td><td colspan="2">Основной государственный регистрационный номер индивидуального предпринимателя (ОГРНИП) и дата его присвоения</td></tr>
                </table>
            </td>
        </tr>
    </table>

    <table class="sx-upd-second-lines">
        <tr>
            <td class="sx-upd-second-label">Основание передачи (сдачи)/получения (приемки)</td>
            <td class="sx-upd-second-value"><?= $e($baseDocument); ?></td>
            <td class="sx-upd-second-code">(10)</td>
        </tr>
        <tr class="sx-upd-hints"><td></td><td>(договор; доверенность и др.)</td><td></td></tr>
        <tr>
            <td class="sx-upd-second-label">Данные о транспортировке и грузе</td>
            <td class="sx-upd-second-value"><?= $e($transportInfo); ?></td>
            <td class="sx-upd-second-code">(11)</td>
        </tr>
        <tr class="sx-upd-hints"><td></td><td>(транспортная накладная, поручение экспедитору, экспедиторская / складская расписка и др. / масса нетто/брутто груза, если не приведены ссылки на транспортные документы, содержащие эти сведения)</td><td></td></tr>
    </table>

    <table class="sx-upd-transfer">
        <tr>
            <td class="sx-upd-transfer-side">
                <div class="sx-upd-transfer-title">Товар (груз) передал/услуги, результаты работ, права сдал</div>
                <table class="sx-upd-transfer-sign">
                    <tr>
                        <td class="sx-upd-under"></td>
                        <td class="sx-upd-under"></td>
                        <td class="sx-upd-under"></td>
                        <td class="sx-upd-transfer-code">(12)</td>
                    </tr>
                    <tr class="sx-upd-hints"><td>(должность)</td><td>(подпись)</td><td>(ф.И.О.)</td><td></td></tr>
                </table>
                <div class="sx-upd-date-line"><b>Дата отгрузки, передачи (сдачи)</b> <span><?= Html::encode($ruDate($issuedAt)); ?></span> <em>(13)</em></div>
                <div class="sx-upd-transfer-title">Иные сведения об отгрузке, передаче</div>
                <div class="sx-upd-under sx-upd-wide-under"><?= Html::encode($sellerOtherInfo); ?></div>
                <div class="sx-upd-hints">(ссылки на неотъемлемые приложения, сопутствующие документы, иные документы и т.п.)</div>
                <div class="sx-upd-transfer-title">Ответственный за правильность оформления факта хозяйственной жизни</div>
                <table class="sx-upd-transfer-sign">
                    <tr><td class="sx-upd-under"></td><td class="sx-upd-under"></td><td class="sx-upd-under"></td><td class="sx-upd-transfer-code">(15)</td></tr>
                    <tr class="sx-upd-hints"><td>(должность)</td><td>(подпись)</td><td>(ф.И.О.)</td><td></td></tr>
                </table>
                <div class="sx-upd-transfer-title">Наименование экономического субъекта-составителя документа (в т.ч. комиссионера/агента)</div>
                <div class="sx-upd-under sx-upd-wide-under sx-upd-center"><?= Html::encode($sellerSubject); ?></div>
                <div class="sx-upd-hints">(может не заполняться при проставлении печати в М.П., может быть указан ИНН/КПП)</div>
                <div class="sx-upd-mp">М.П.</div>
                <?php if ($sellerStamp) : ?>
                    <div class="sx-upd-stamp-wrap">
                        <img class="sx-upd-stamp" src="<?= Html::encode($sellerStamp->absoluteSrc); ?>" />
                    </div>
                <?php endif; ?>
            </td>
            <td class="sx-upd-transfer-side sx-upd-transfer-buyer">
                <div class="sx-upd-transfer-title">Товар (груз) получил/услуги, результаты работ, права принял</div>
                <table class="sx-upd-transfer-sign">
                    <tr>
                        <td class="sx-upd-under"></td>
                        <td class="sx-upd-under"></td>
                        <td class="sx-upd-under"></td>
                        <td class="sx-upd-transfer-code">(17)</td>
                    </tr>
                    <tr class="sx-upd-hints"><td>(должность)</td><td>(подпись)</td><td>(ф.И.О.)</td><td></td></tr>
                </table>
                <div class="sx-upd-date-line"><b>Дата получения (приёмки)</b> <span>«&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;»&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;20&nbsp;&nbsp;&nbsp;&nbsp;г.</span> <em>(18)</em></div>
                <div class="sx-upd-transfer-title">Иные сведения о получении, приемке</div>
                <div class="sx-upd-under sx-upd-wide-under"><?= Html::encode($buyerOtherInfo); ?></div>
                <div class="sx-upd-hints">(ссылки на неотъемлемые приложения, сопутствующие документы, иные документы и т.п.)</div>
                <div class="sx-upd-transfer-title">Ответственный за правильность оформления факта хозяйственной жизни</div>
                <table class="sx-upd-transfer-sign">
                    <tr><td class="sx-upd-under"></td><td class="sx-upd-under"></td><td class="sx-upd-under"></td><td class="sx-upd-transfer-code">(20)</td></tr>
                    <tr class="sx-upd-hints"><td>(должность)</td><td>(подпись)</td><td>(ф.И.О.)</td><td></td></tr>
                </table>
                <div class="sx-upd-transfer-title">Наименование экономического субъекта-составителя документа</div>
                <div class="sx-upd-under sx-upd-wide-under sx-upd-center"><?= Html::encode($buyerSubject); ?></div>
                <div class="sx-upd-hints">(может не заполняться при проставлении печати в М.П., может быть указан ИНН/КПП)</div>
                <div class="sx-upd-mp">М.П.</div>
            </td>
        </tr>
    </table>

    <table class="sx-upd-footbar">
        <tr>
            <td class="sx-upd-footbar-logo">
                <?php if ($logoSrc) : ?>
                    <img class="sx-upd-logo" src="<?= Html::encode($logoSrc); ?>" />
                <?php endif; ?>
            </td>
            <td class="sx-upd-footer">УПД(<?= Html::encode($updStatus); ?>)№<?= Html::encode($number); ?> от <?= Html::encode($date); ?></td>
            <td class="sx-upd-page-number">2/2</td>
        </tr>
    </table>

</section>

<?php if (!@$isPdf) : ?>
    <?= $this->render('_controls', ['model' => $model]); ?>
<?php endif; ?>
