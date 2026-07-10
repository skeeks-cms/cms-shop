<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 */

namespace skeeks\cms\shop\document;

use skeeks\cms\shop\models\ShopDocument;
use skeeks\cms\shop\models\ShopDocumentItem;
use yii\helpers\ArrayHelper;

/**
 * Experimental FNS XML exporter for UTD/UPD documents.
 *
 * The output is intentionally isolated from PDF templates so it can be
 * validated and evolved against the official FNS XSD without touching UI.
 */
class FnsUpdXmlGenerator
{
    /**
     * @var ShopDocument
     */
    public $document;

    public function __construct(ShopDocument $document)
    {
        $this->document = $document;
    }

    public function generate()
    {
        $model = $this->document;
        $issuedAt = (int)($model->issued_at ?: $model->created_at ?: time());
        $number = (string)($model->number ?: $model->id);
        $documentData = (array)$model->document_data;
        $updData = (array)ArrayHelper::getValue($documentData, 'upd', []);
        $function = ArrayHelper::getValue($updData, 'status') == '1' ? 'СЧФДОП' : 'ДОП';
        $fileId = $this->fileId($function, $issuedAt);

        $dom = new \DOMDocument('1.0', 'windows-1251');
        $dom->formatOutput = true;

        $file = $dom->createElement('Файл');
        $file->setAttribute('ИдФайл', $fileId);
        $file->setAttribute('ВерсФорм', '5.03');
        $file->setAttribute('ВерсПрог', 'SkeekS CMS');
        $dom->appendChild($file);

        $document = $this->append($dom, $file, 'Документ');
        $document->setAttribute('КНД', '1115131');
        $document->setAttribute('Функция', $function);
        $document->setAttribute('ПоФактХЖ', $function == 'СЧФДОП' ? 'Документ об отгрузке товаров (выполнении работ), передаче имущественных прав, включающий счет-фактуру' : 'Документ об отгрузке товаров (выполнении работ), передаче имущественных прав');
        $document->setAttribute('НаимДокОпр', 'Универсальный передаточный документ');
        $document->setAttribute('ДатаИнфПр', date('d.m.Y'));
        $document->setAttribute('ВремИнфПр', date('H.i.s'));
        $document->setAttribute('НаимЭконСубСост', $this->short($model->sellerFullName ?: $model->sellerName, 1000));

        $invoice = $this->append($dom, $document, 'СвСчФакт');
        $invoice->setAttribute('НомерДок', $number);
        $invoice->setAttribute('ДатаДок', date('d.m.Y', $issuedAt));
        $stateContract = trim((string)ArrayHelper::getValue($updData, 'state_contract_identifier'));
        if ($stateContract !== '') {
            $invoice->setAttribute('ИдГосКон', $stateContract);
        }

        $this->appendSubject($dom, $invoice, 'СвПрод', $model->sellerFullName ?: $model->sellerName, $model->sellerInn, $model->sellerKpp, $model->sellerAddress, $model->sellerOgrn);
        $this->appendShipper($dom, $invoice, $updData, $model);
        $this->appendConsignee($dom, $invoice, $updData, $model);
        $this->appendSubject($dom, $invoice, 'СвПокуп', $model->buyerFullName ?: $model->buyerName, $model->buyerInn, $model->buyerKpp, $model->buyerAddress, $model->buyerOgrn);
        $currency = $this->append($dom, $invoice, 'ДенИзм');
        $currency->setAttribute('КодОКВ', $this->currencyCode($model->currency_code));
        $currency->setAttribute('НаимОКВ', $this->currencyName($model->currency_code));

        $table = $this->append($dom, $document, 'ТаблСчФакт');
        $totals = $this->appendItems($dom, $table, $model->documentItems);
        $total = $this->append($dom, $table, 'ВсегоОпл');
        $total->setAttribute('СтТовБезНДСВсего', $this->money($totals['withoutVat']));
        $total->setAttribute('СтТовУчНалВсего', $this->money($totals['withVat']));
        $vatTotal = $this->append($dom, $total, 'СумНалВсего');
        $this->append($dom, $vatTotal, 'БезНДС', 'без НДС');

        $transferInfo = $this->append($dom, $document, 'СвПродПер');
        $transfer = $this->append($dom, $transferInfo, 'СвПер');
        $transfer->setAttribute('СодОпер', 'Товары переданы, работы выполнены, услуги оказаны');
        $transfer->setAttribute('ДатаПер', date('d.m.Y', $issuedAt));
        $baseDocument = trim((string)ArrayHelper::getValue($updData, 'base_document', $model->description));
        if ($baseDocument !== '') {
            $base = $this->append($dom, $transfer, 'ОснПер');
            $base->setAttribute('НаимОсн', $this->short($baseDocument, 255));
        } else {
            $this->append($dom, $transfer, 'БезДокОснПер', '1');
        }
        $transportInfo = trim((string)ArrayHelper::getValue($updData, 'transport_info'));
        if ($transportInfo !== '') {
            $transfer->setAttribute('ТранГруз', $this->short($transportInfo, 1000));
        }

        $signer = $this->append($dom, $document, 'Подписант');
        $signer->setAttribute('ОблПолн', '6');
        $signer->setAttribute('Статус', $model->sellerKpp ? '1' : '2');
        $signer->setAttribute('ОснПолн', 'Должностные обязанности');
        $this->appendSignerName($dom, $signer, $model->sellerName ?: $model->sellerFullName, $model->sellerInn);

        return $dom->saveXML();
    }

    public function fileName()
    {
        return $this->fileId('DOP', (int)($this->document->issued_at ?: time())).'.xml';
    }

    protected function append(\DOMDocument $dom, \DOMElement $parent, $name, $value = null)
    {
        $node = $dom->createElement($name);
        if ($value !== null) {
            $node->appendChild($dom->createTextNode((string)$value));
        }
        $parent->appendChild($node);
        return $node;
    }

    protected function appendSubject(\DOMDocument $dom, \DOMElement $parent, $tagName, $name, $inn, $kpp, $address, $ogrn = '')
    {
        $subject = $this->append($dom, $parent, $tagName);
        $id = $this->append($dom, $subject, 'ИдСв');
        $inn = preg_replace('/\D+/', '', (string)$inn);
        $kpp = preg_replace('/\D+/', '', (string)$kpp);

        if (strlen($inn) == 12 && !$kpp) {
            $ip = $this->append($dom, $id, 'СвИП');
            $ip->setAttribute('ИННФЛ', $inn);
            if ($ogrn) {
                $ip->setAttribute('СвГосРегИП', $ogrn);
            }
            $this->appendFio($dom, $ip, $name);
        } else {
            $company = $this->append($dom, $id, 'СвЮЛУч');
            $company->setAttribute('НаимОрг', $this->short($name, 1000));
            if ($inn) {
                $company->setAttribute('ИННЮЛ', $inn);
            }
            if ($kpp) {
                $company->setAttribute('КПП', $kpp);
            }
        }

        $this->appendAddress($dom, $subject, $address);
    }

    protected function appendShipper(\DOMDocument $dom, \DOMElement $invoice, array $data, ShopDocument $model)
    {
        $shipper = $this->append($dom, $invoice, 'ГрузОт');
        $text = trim((string)ArrayHelper::getValue($data, 'shipper'));
        if ($text === '' || stripos($text, (string)$model->sellerName) !== false) {
            $this->append($dom, $shipper, 'ОнЖе', 'он же');
            return;
        }

        $this->appendSubject($dom, $shipper, 'ГрузОтпр', $text, $model->sellerInn, $model->sellerKpp, $model->sellerAddress, $model->sellerOgrn);
    }

    protected function appendConsignee(\DOMDocument $dom, \DOMElement $invoice, array $data, ShopDocument $model)
    {
        $text = trim((string)ArrayHelper::getValue($data, 'consignee'));
        $this->appendSubject($dom, $invoice, 'ГрузПолуч', $text ?: ($model->buyerFullName ?: $model->buyerName), $model->buyerInn, $model->buyerKpp, $model->buyerAddress, $model->buyerOgrn);
    }

    protected function appendAddress(\DOMDocument $dom, \DOMElement $parent, $address)
    {
        $addressNode = $this->append($dom, $parent, 'Адрес');
        $addressText = $this->append($dom, $addressNode, 'АдрИнф');
        $addressText->setAttribute('КодСтр', '643');
        $addressText->setAttribute('НаимСтран', 'Россия');
        $addressText->setAttribute('АдрТекст', $this->short($address, 1000));
    }

    protected function appendPaymentDocuments(\DOMDocument $dom, \DOMElement $invoice, array $rows)
    {
        foreach (ShopDocument::normalizePaymentDocuments($rows) as $row) {
            $number = trim((string)ArrayHelper::getValue($row, 'number'));
            $date = ShopDocument::normalizeDocumentDateValue(ArrayHelper::getValue($row, 'date'));
            if ($number === '' || $date === '') {
                continue;
            }

            $payment = $this->append($dom, $invoice, 'СвПРД');
            $payment->setAttribute('НомерПРД', $this->short($number, 30));
            $payment->setAttribute('ДатаПРД', date('d.m.Y', strtotime($date)));
        }
    }

    protected function appendItems(\DOMDocument $dom, \DOMElement $table, $items)
    {
        $withoutVat = 0;
        $withVat = 0;
        $index = 1;

        foreach ($items as $item) {
            /** @var ShopDocumentItem $item */
            $baseAmount = $item->amountWithoutDiscount;
            $amount = (float)$item->amount;
            $withoutVat += $amount;
            $withVat += $amount;

            $row = $this->append($dom, $table, 'СведТов');
            $row->setAttribute('НомСтр', (string)$index++);
            $row->setAttribute('НаимТов', $this->short($item->name, 1000));
            $row->setAttribute('ОКЕИ_Тов', $this->measureCode($item->measure_name));
            $row->setAttribute('НаимЕдИзм', $this->measureName($item->measure_name));
            $row->setAttribute('КолТов', $this->number($item->quantity));
            $row->setAttribute('ЦенаТов', $this->money($item->price));
            $row->setAttribute('СтТовБезНДС', $this->money($amount));
            $row->setAttribute('НалСт', $this->vatRate($item->vat_name));
            $row->setAttribute('СтТовУчНал', $this->money($amount));

            $excise = $this->append($dom, $row, 'Акциз');
            $this->append($dom, $excise, 'БезАкциз', 'без акциза');
            $vat = $this->append($dom, $row, 'СумНал');
            $this->append($dom, $vat, 'БезНДС', 'без НДС');

            if ((float)$item->discount_amount > 0) {
                $info = $this->append($dom, $row, 'ДопСведТов');
                $info->setAttribute('ПрТовРаб', '3');
                $info->setAttribute('ДопПризн', 'Скидка: '.$this->money($item->discount_amount).'; сумма без скидки: '.$this->money($baseAmount));
            }
        }

        return [
            'withoutVat' => $withoutVat,
            'withVat'    => $withVat,
        ];
    }

    protected function appendSignerName(\DOMDocument $dom, \DOMElement $parent, $name, $inn = '')
    {
        $person = $this->append($dom, $parent, 'ФЛ');
        $inn = preg_replace('/\D+/', '', (string)$inn);
        if (strlen($inn) == 12) {
            $person->setAttribute('ИННФЛ', $inn);
        }
        $this->appendFio($dom, $person, $name);
    }

    protected function appendFio(\DOMDocument $dom, \DOMElement $parent, $name)
    {
        $fio = $this->splitFio($name);
        $fioNode = $this->append($dom, $parent, 'ФИО');
        $fioNode->setAttribute('Фамилия', $fio['last']);
        $fioNode->setAttribute('Имя', $fio['first']);
        if ($fio['middle'] !== '') {
            $fioNode->setAttribute('Отчество', $fio['middle']);
        }
    }

    protected function splitFio($name)
    {
        $name = trim(preg_replace('/\s+/', ' ', str_replace(['ИП ', 'Индивидуальный предприниматель '], '', (string)$name)));
        $parts = preg_split('/\s+/u', $name);
        return [
            'last'   => $this->short(ArrayHelper::getValue($parts, 0, 'Неуказано'), 60),
            'first'  => $this->short(ArrayHelper::getValue($parts, 1, 'Неуказано'), 60),
            'middle' => $this->short(ArrayHelper::getValue($parts, 2, ''), 60),
        ];
    }

    protected function fileId($function, $timestamp)
    {
        $seller = $this->participantId($this->document->sellerInn);
        $buyer = $this->participantId($this->document->buyerInn);
        return 'ON_NSCHFDOPPR_'.$seller.'_'.$buyer.'_'.date('Ymd', $timestamp).'_'.$this->document->id;
    }

    protected function participantId($inn)
    {
        $inn = preg_replace('/\D+/', '', (string)$inn);
        return $inn ?: '0000000000';
    }

    protected function currencyCode($currency)
    {
        return strtoupper((string)$currency) == 'RUB' ? '643' : strtoupper((string)$currency);
    }

    protected function currencyName($currency)
    {
        return strtoupper((string)$currency) == 'RUB' ? 'Российский рубль' : strtoupper((string)$currency);
    }

    protected function measureCode($measure)
    {
        $measure = trim(mb_strtolower((string)$measure));
        return in_array($measure, ['шт', 'шт.', 'штука', 'штук'], true) ? '796' : '796';
    }

    protected function measureName($measure)
    {
        $measure = trim((string)$measure);
        return $measure === '' ? 'шт' : $this->short($measure, 255);
    }

    protected function vatRate($vat)
    {
        $vat = trim((string)$vat);
        if (stripos($vat, '20') !== false) {
            return '20%';
        }
        if (stripos($vat, '10') !== false) {
            return '10%';
        }

        return 'без НДС';
    }

    protected function money($value)
    {
        return number_format(round((float)$value, 2), 2, '.', '');
    }

    protected function number($value)
    {
        return rtrim(rtrim(number_format((float)$value, 6, '.', ''), '0'), '.');
    }

    protected function short($value, $length)
    {
        $value = trim(preg_replace('/\s+/u', ' ', (string)$value));
        if ($value === '') {
            return '-';
        }

        return mb_substr($value, 0, $length);
    }
}
