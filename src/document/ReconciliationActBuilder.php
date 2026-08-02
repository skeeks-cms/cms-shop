<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 */

namespace skeeks\cms\shop\document;

use skeeks\cms\models\CmsCompany;
use skeeks\cms\models\CmsContractor;
use skeeks\cms\shop\models\ShopDocument;
use skeeks\cms\shop\models\ShopPayment;
use yii\base\InvalidArgumentException;
use yii\helpers\ArrayHelper;

/**
 * Builds and freezes a reconciliation statement from current accounting data.
 *
 * A positive balance means that the counterparty owes our organization.
 * A negative balance means that our organization owes the counterparty.
 */
class ReconciliationActBuilder
{
    const DATA_VERSION = 1;

    /**
     * @param CmsCompany $company
     * @param CmsContractor $ourContractor
     * @param CmsContractor $counterparty
     * @param int $periodStart
     * @param int $periodEnd
     * @param string $currencyCode
     * @return ShopDocument
     */
    public function createDocument(
        CmsCompany $company,
        CmsContractor $ourContractor,
        CmsContractor $counterparty,
        $periodStart,
        $periodEnd,
        $currencyCode = 'RUB'
    ) {
        $data = $this->build(
            $ourContractor,
            $counterparty,
            $periodStart,
            $periodEnd,
            $currencyCode,
            $company->id
        );

        $document = new ShopDocument();
        $document->loadDefaultValues();
        $document->type = ShopDocument::TYPE_RECONCILIATION_ACT;
        $document->status = ShopDocument::STATUS_ISSUED;
        $document->issued_at = strtotime('today');
        $document->cms_company_id = $company->id;
        $document->seller_contractor_id = $ourContractor->id;
        $document->buyer_contractor_id = $counterparty->id;
        $document->currency_code = strtoupper((string)$currencyCode);
        $document->amount = 0;
        $document->discount_amount = 0;
        $document->description = 'Сверка взаимных расчетов за период с '
            .date('d.m.Y', $periodStart).' по '.date('d.m.Y', $periodEnd);
        $document->document_data = [
            'reconciliation_act' => $data,
        ];

        if (!$document->save()) {
            throw new InvalidArgumentException(implode('; ', $document->getFirstErrors()));
        }

        return $document;
    }

    /**
     * @param CmsContractor $ourContractor
     * @param CmsContractor $counterparty
     * @param int $periodStart
     * @param int $periodEnd
     * @param string $currencyCode
     * @param int|null $companyId Used only for old payments without filled parties.
     * @return array
     */
    public function build(
        CmsContractor $ourContractor,
        CmsContractor $counterparty,
        $periodStart,
        $periodEnd,
        $currencyCode = 'RUB',
        $companyId = null
    ) {
        $periodStart = (int)$periodStart;
        $periodEnd = (int)$periodEnd;
        if (!$periodStart || !$periodEnd || $periodStart > $periodEnd) {
            throw new InvalidArgumentException('Некорректный период акта сверки');
        }
        if ((int)$ourContractor->id === (int)$counterparty->id) {
            throw new InvalidArgumentException('Для акта сверки нужны два разных контрагента');
        }

        $currencyCode = strtoupper(trim((string)$currencyCode)) ?: 'RUB';
        $allOperations = array_merge(
            $this->documentOperations($ourContractor, $counterparty, $currencyCode, $periodEnd),
            $this->paymentOperations($ourContractor, $counterparty, $currencyCode, $periodEnd, $companyId)
        );

        usort($allOperations, function (array $a, array $b) {
            $left = [$a['timestamp'], $a['sort_order'], $a['source_id']];
            $right = [$b['timestamp'], $b['sort_order'], $b['source_id']];
            return $left <=> $right;
        });

        $openingBalance = 0.0;
        $turnoverDebit = 0.0;
        $turnoverCredit = 0.0;
        $operations = [];
        $documentIds = [];
        $paymentIds = [];

        foreach ($allOperations as $operation) {
            $delta = (float)$operation['debit'] - (float)$operation['credit'];
            if ($operation['source_type'] === 'document') {
                $documentIds[] = (int)$operation['source_id'];
            } else {
                $paymentIds[] = (int)$operation['source_id'];
            }

            if ($operation['timestamp'] < $periodStart) {
                $openingBalance += $delta;
                continue;
            }

            $turnoverDebit += (float)$operation['debit'];
            $turnoverCredit += (float)$operation['credit'];
            unset($operation['sort_order']);
            $operations[] = $operation;
        }

        $openingBalance = round($openingBalance, 2);
        $turnoverDebit = round($turnoverDebit, 2);
        $turnoverCredit = round($turnoverCredit, 2);
        $closingBalance = round($openingBalance + $turnoverDebit - $turnoverCredit, 2);

        return [
            'version'             => self::DATA_VERSION,
            'generated_at'        => time(),
            'period_start'        => date('Y-m-d', $periodStart),
            'period_end'          => date('Y-m-d', $periodEnd),
            'currency_code'       => $currencyCode,
            'opening_balance'     => $openingBalance,
            'opening_debit'       => $openingBalance > 0 ? $openingBalance : 0,
            'opening_credit'      => $openingBalance < 0 ? abs($openingBalance) : 0,
            'turnover_debit'      => $turnoverDebit,
            'turnover_credit'     => $turnoverCredit,
            'closing_balance'     => $closingBalance,
            'closing_debit'       => $closingBalance > 0 ? $closingBalance : 0,
            'closing_credit'      => $closingBalance < 0 ? abs($closingBalance) : 0,
            'operations'          => $operations,
            'counterparty_data'   => [],
            'source_document_ids' => array_values(array_unique($documentIds)),
            'source_payment_ids'  => array_values(array_unique($paymentIds)),
        ];
    }

    protected function documentOperations(CmsContractor $ourContractor, CmsContractor $counterparty, $currencyCode, $periodEnd)
    {
        $documents = ShopDocument::find()
            ->with('bills')
            ->andWhere(['type' => ShopDocument::closingTypes()])
            ->andWhere(['<>', 'status', ShopDocument::STATUS_CANCELED])
            ->andWhere(['currency_code' => $currencyCode])
            ->andWhere([
                'or',
                [
                    'seller_contractor_id' => $ourContractor->id,
                    'buyer_contractor_id'  => $counterparty->id,
                ],
                [
                    'seller_contractor_id' => $counterparty->id,
                    'buyer_contractor_id'  => $ourContractor->id,
                ],
            ])
            ->all();

        $result = [];
        foreach ($documents as $document) {
            $timestamp = (int)($document->issued_at ?: $document->created_at);
            if (!$timestamp || $timestamp > $periodEnd) {
                continue;
            }

            $isOurSale = (int)$document->seller_contractor_id === (int)$ourContractor->id;
            $amount = round(abs((float)$document->amount), 2);
            $description = $document->typeAsText.' №'.($document->number ?: $document->id);
            if ($document->bills) {
                $description .= ', основание — '.implode(', ', ArrayHelper::getColumn($document->bills, 'asText'));
            }

            $result[] = [
                'timestamp'   => $timestamp,
                'date'        => date('Y-m-d', $timestamp),
                'description' => $description,
                'debit'       => $isOurSale ? $amount : 0,
                'credit'      => $isOurSale ? 0 : $amount,
                'source_type' => 'document',
                'source_id'   => (int)$document->id,
                'source_code' => (string)$document->code,
                'sort_order'  => 10,
            ];
        }

        return $result;
    }

    protected function paymentOperations(CmsContractor $ourContractor, CmsContractor $counterparty, $currencyCode, $periodEnd, $companyId = null)
    {
        $fallbackCompanyId = $this->fallbackCompanyId($companyId, $counterparty);
        $conditions = [
            'or',
            [
                'sender_contractor_id'   => $counterparty->id,
                'receiver_contractor_id' => $ourContractor->id,
            ],
            [
                'sender_contractor_id'   => $ourContractor->id,
                'receiver_contractor_id' => $counterparty->id,
            ],
        ];
        if ($fallbackCompanyId) {
            $conditions[] = ['cms_company_id' => $fallbackCompanyId];
        }

        $payments = ShopPayment::find()
            ->andWhere(['currency_code' => $currencyCode])
            ->andWhere($conditions)
            ->all();

        $result = [];
        foreach ($payments as $payment) {
            $direction = $this->paymentDirection($payment, $ourContractor, $counterparty, $fallbackCompanyId);
            if (!$direction) {
                continue;
            }

            $date = $payment->documentDate();
            $timestamp = $date ? strtotime($date.' 12:00:00') : 0;
            if (!$timestamp || $timestamp > $periodEnd) {
                continue;
            }

            $amount = round(abs((float)$payment->amount), 2);
            $number = $payment->documentNumber();
            $description = 'Платеж №'.$number;
            if (trim((string)$payment->comment) !== '') {
                $description .= ': '.trim((string)$payment->comment);
            }

            $result[] = [
                'timestamp'   => $timestamp,
                'date'        => date('Y-m-d', $timestamp),
                'description' => $description,
                'debit'       => $direction === 'debit' ? $amount : 0,
                'credit'      => $direction === 'credit' ? $amount : 0,
                'source_type' => 'payment',
                'source_id'   => (int)$payment->id,
                'source_code' => '',
                'sort_order'  => 20,
            ];
        }

        return $result;
    }

    protected function fallbackCompanyId($companyId, CmsContractor $counterparty)
    {
        if (!$companyId) {
            return null;
        }
        $company = CmsCompany::findOne((int)$companyId);
        if (!$company) {
            return null;
        }

        $counterpartyIds = $company->getContractors()
            ->andWhere(['is_our' => 0])
            ->select(CmsContractor::tableName().'.id')
            ->column();

        return count($counterpartyIds) === 1 && (int)reset($counterpartyIds) === (int)$counterparty->id
            ? (int)$company->id
            : null;
    }

    protected function paymentDirection(ShopPayment $payment, CmsContractor $ourContractor, CmsContractor $counterparty, $companyId = null)
    {
        if ((int)$payment->sender_contractor_id === (int)$counterparty->id
            && (int)$payment->receiver_contractor_id === (int)$ourContractor->id) {
            return 'credit';
        }
        if ((int)$payment->sender_contractor_id === (int)$ourContractor->id
            && (int)$payment->receiver_contractor_id === (int)$counterparty->id) {
            return 'debit';
        }

        if (!$companyId || (int)$payment->cms_company_id !== (int)$companyId) {
            return null;
        }

        $allowedIds = [(int)$ourContractor->id, (int)$counterparty->id, 0];
        if (!in_array((int)$payment->sender_contractor_id, $allowedIds, true)
            || !in_array((int)$payment->receiver_contractor_id, $allowedIds, true)) {
            return null;
        }

        // is_debit is a cash-ledger direction: incoming money reduces receivables.
        return (int)$payment->is_debit === 1 ? 'credit' : 'debit';
    }
}
