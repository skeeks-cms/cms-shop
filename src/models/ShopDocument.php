<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 */

namespace skeeks\cms\shop\models;

use skeeks\cms\behaviors\CmsLogBehavior;
use skeeks\cms\behaviors\RelationalBehavior;
use skeeks\cms\models\behaviors\HasJsonFieldsBehavior;
use skeeks\cms\models\behaviors\traits\HasLogTrait;
use skeeks\cms\models\CmsCompany;
use skeeks\cms\models\CmsContractor;
use skeeks\cms\models\CmsDeal;
use skeeks\cms\models\CmsUser;
use skeeks\cms\money\models\MoneyCurrency;
use skeeks\cms\money\Money;
use skeeks\cms\shop\models\queries\ShopDocumentQuery;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/**
 * Accounting document: act, UPD and future closing/primary documents.
 *
 * @property int                 $id
 * @property int|null            $created_by
 * @property int|null            $updated_by
 * @property int|null            $created_at
 * @property int|null            $updated_at
 * @property string              $type
 * @property string              $status
 * @property string|null         $number
 * @property int|null            $issued_at
 * @property int|null            $cms_company_id
 * @property int|null            $cms_user_id
 * @property int|null            $seller_contractor_id
 * @property int|null            $buyer_contractor_id
 * @property string|null         $seller_contractor_type
 * @property string|null         $seller_contractor_name
 * @property string|null         $seller_contractor_full_name
 * @property string|null         $seller_contractor_inn
 * @property string|null         $seller_contractor_kpp
 * @property string|null         $seller_contractor_ogrn
 * @property string|null         $seller_contractor_registration_date
 * @property string|null         $seller_contractor_address
 * @property string|null         $seller_contractor_mailing_postcode
 * @property string|null         $buyer_contractor_type
 * @property string|null         $buyer_contractor_name
 * @property string|null         $buyer_contractor_full_name
 * @property string|null         $buyer_contractor_inn
 * @property string|null         $buyer_contractor_kpp
 * @property string|null         $buyer_contractor_ogrn
 * @property string|null         $buyer_contractor_registration_date
 * @property string|null         $buyer_contractor_address
 * @property string|null         $buyer_contractor_mailing_postcode
 * @property string              $amount
 * @property string              $discount_amount
 * @property string              $currency_code
 * @property string|null         $description
 * @property string|null         $comment_before
 * @property string|null         $comment_after
 * @property string|null         $canceled_reason
 * @property array|null          $document_data
 * @property string              $code
 * @property string|null         $external_id
 * @property string|null         $external_name
 * @property array|null          $external_data
 *
 * @property CmsCompany          $company
 * @property CmsUser             $cmsUser
 * @property CmsUser             $createdBy
 * @property CmsContractor       $sellerContractor
 * @property CmsContractor       $buyerContractor
 * @property ShopDocumentItem[]  $documentItems
 * @property ShopBill[]          $bills
 * @property CmsDeal[]           $deals
 * @property MoneyCurrency       $currencyCode
 * @property Money               $money
 * @property string              $typeAsText
 * @property string              $statusAsText
 * @property array               $statusColors
 * @property string              $statusIcon
 * @property bool                $isEditable
 * @property string              $asFullText
 */
class ShopDocument extends \skeeks\cms\base\ActiveRecord
{
    use HasLogTrait;

    const TYPE_ACT = 'act';
    const TYPE_UPD = 'upd';
    const TYPE_INVOICE_FACTURE = 'invoice_facture';
    const TYPE_WAYBILL = 'waybill';
    const TYPE_RECONCILIATION_ACT = 'reconciliation_act';

    const STATUS_ISSUED = 'issued';
    const STATUS_SENT = 'sent';
    const STATUS_SIGNED = 'signed';
    const STATUS_CANCELED = 'canceled';

    public $documentItemsData = null;
    public $isSnapshotPrepared = false;

    public static function tableName()
    {
        return '{{%shop_document}}';
    }

    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            RelationalBehavior::class => [
                'class' => RelationalBehavior::class,
                'relationNames' => [
                    'bills',
                    'deals',
                ],
            ],
            HasJsonFieldsBehavior::class => [
                'class'  => HasJsonFieldsBehavior::class,
                'fields' => ['external_data', 'document_data'],
            ],
            CmsLogBehavior::class => [
                'class' => CmsLogBehavior::class,
            ],
        ]);
    }

    public function init()
    {
        $this->on(self::EVENT_AFTER_FIND, [$this, 'afterFindDocument']);
        $this->on(self::EVENT_BEFORE_VALIDATE, [$this, 'normalizeIssuedAt']);
        $this->on(self::EVENT_BEFORE_VALIDATE, [$this, 'ensureSnapshotData']);
        $this->on(self::EVENT_BEFORE_VALIDATE, [$this, 'normalizeDocumentData']);
        $this->on(self::EVENT_BEFORE_VALIDATE, [$this, 'normalizeDocumentItems']);
        $this->on(self::EVENT_AFTER_INSERT, [$this, 'fillNumberAfterInsert']);
        $this->on(self::EVENT_AFTER_INSERT, [$this, 'saveDocumentItems']);
        $this->on(self::EVENT_AFTER_INSERT, [$this, 'syncBillRelationsFromDocumentItems']);
        $this->on(self::EVENT_AFTER_UPDATE, [$this, 'saveDocumentItems']);
        $this->on(self::EVENT_AFTER_UPDATE, [$this, 'syncBillRelationsFromDocumentItems']);

        parent::init();
    }

    public function afterFindDocument($event = null)
    {
        $this->amount = (float)$this->amount;
        $this->discount_amount = (float)$this->discount_amount;
    }

    public function beforeDelete()
    {
        if (!$this->isEditable) {
            $this->addError('status', 'Отправленные, подписанные и отмененные документы нельзя удалять');
            return false;
        }

        return parent::beforeDelete();
    }

    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'issued_at', 'cms_company_id', 'cms_user_id', 'seller_contractor_id', 'buyer_contractor_id'], 'integer'],
            [['type', 'status'], 'required'],
            [['type', 'status'], 'string', 'max' => 32],
            [['number'], 'string', 'max' => 64],
            [['description', 'comment_before', 'comment_after', 'canceled_reason'], 'string'],
            [['external_id', 'external_name'], 'default', 'value' => null],
            [['external_id', 'external_name'], 'string'],
            [['external_data', 'document_data'], 'safe'],
            [['amount', 'discount_amount'], 'number'],
            [['amount', 'discount_amount'], 'default', 'value' => 0],
            [['currency_code'], 'string', 'max' => 3],
            [['currency_code'], 'default', 'value' => \Yii::$app->money->currencyCode],
            [['code'], 'string', 'max' => 255],
            [['code'], 'unique'],
            [['code'], 'default', 'value' => \Yii::$app->security->generateRandomString()],
            [['type'], 'default', 'value' => self::TYPE_ACT],
            [['status'], 'default', 'value' => self::STATUS_ISSUED],
            [['issued_at'], 'default', 'value' => strtotime('today')],
            [['documentItemsData'], 'safe'],
            [['bills', 'deals'], 'safe'],

            [
                [
                    'seller_contractor_name',
                    'seller_contractor_full_name',
                    'seller_contractor_address',
                    'buyer_contractor_name',
                    'buyer_contractor_full_name',
                    'buyer_contractor_address',
                ],
                'string',
                'max' => 255,
            ],
            [
                [
                    'seller_contractor_type',
                    'seller_contractor_inn',
                    'seller_contractor_kpp',
                    'seller_contractor_ogrn',
                    'seller_contractor_registration_date',
                    'seller_contractor_mailing_postcode',
                    'buyer_contractor_type',
                    'buyer_contractor_inn',
                    'buyer_contractor_kpp',
                    'buyer_contractor_ogrn',
                    'buyer_contractor_registration_date',
                    'buyer_contractor_mailing_postcode',
                ],
                'string',
                'max' => 32,
            ],

            [['cms_company_id'], 'exist', 'skipOnError' => true, 'targetClass' => CmsCompany::class, 'targetAttribute' => ['cms_company_id' => 'id']],
            [['cms_user_id'], 'exist', 'skipOnError' => true, 'targetClass' => CmsUser::class, 'targetAttribute' => ['cms_user_id' => 'id']],
            [['seller_contractor_id'], 'exist', 'skipOnError' => true, 'targetClass' => CmsContractor::class, 'targetAttribute' => ['seller_contractor_id' => 'id']],
            [['buyer_contractor_id'], 'exist', 'skipOnError' => true, 'targetClass' => CmsContractor::class, 'targetAttribute' => ['buyer_contractor_id' => 'id']],
            [['currency_code'], 'exist', 'skipOnError' => true, 'targetClass' => MoneyCurrency::class, 'targetAttribute' => ['currency_code' => 'code']],
        ]);
    }

    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'id'                                 => Yii::t('skeeks/shop/app', 'ID'),
            'type'                               => Yii::t('skeeks/shop/app', 'Тип документа'),
            'status'                             => Yii::t('skeeks/shop/app', 'Статус'),
            'number'                             => Yii::t('skeeks/shop/app', 'Номер'),
            'issued_at'                          => Yii::t('skeeks/shop/app', 'Дата документа'),
            'cms_company_id'                     => Yii::t('skeeks/shop/app', 'Компания'),
            'cms_user_id'                        => Yii::t('skeeks/shop/app', 'Клиент'),
            'seller_contractor_id'               => Yii::t('skeeks/shop/app', 'Продавец / исполнитель'),
            'buyer_contractor_id'                => Yii::t('skeeks/shop/app', 'Покупатель / заказчик'),
            'seller_contractor_name'             => Yii::t('skeeks/shop/app', 'Продавец на момент создания документа'),
            'buyer_contractor_name'              => Yii::t('skeeks/shop/app', 'Покупатель на момент создания документа'),
            'amount'                             => Yii::t('skeeks/shop/app', 'Сумма'),
            'discount_amount'                    => Yii::t('skeeks/shop/app', 'Скидка'),
            'currency_code'                      => Yii::t('skeeks/shop/app', 'Валюта'),
            'description'                        => Yii::t('skeeks/shop/app', 'Основание или комментарий'),
            'comment_before'                     => Yii::t('skeeks/shop/app', 'Комментарий перед таблицей'),
            'comment_after'                      => Yii::t('skeeks/shop/app', 'Комментарий после таблицы'),
            'canceled_reason'                    => Yii::t('skeeks/shop/app', 'Причина отмены'),
            'document_data'                       => Yii::t('skeeks/shop/app', 'Специфичные данные документа'),
            'code'                               => Yii::t('skeeks/shop/app', 'Уникальный код документа'),
            'external_id'                        => Yii::t('skeeks/shop/app', 'Идентификатор внешней системы'),
            'external_name'                      => Yii::t('skeeks/shop/app', 'Внешняя система'),
            'external_data'                      => Yii::t('skeeks/shop/app', 'Данные внешней системы'),
            'bills'                              => Yii::t('skeeks/shop/app', 'Счета'),
            'deals'                              => Yii::t('skeeks/shop/app', 'Сделки'),
        ]);
    }

    public static function optionsForType()
    {
        return [
            self::TYPE_ACT                => 'Акт',
            self::TYPE_UPD                => 'УПД',
            self::TYPE_INVOICE_FACTURE    => 'Счет-фактура',
            self::TYPE_WAYBILL            => 'Накладная',
            self::TYPE_RECONCILIATION_ACT => 'Акт сверки',
        ];
    }

    public static function optionsForStatus()
    {
        return [
            self::STATUS_ISSUED   => 'Выставлен',
            self::STATUS_SENT     => 'Отправлен',
            self::STATUS_SIGNED   => 'Подписан',
            self::STATUS_CANCELED => 'Отменен',
        ];
    }

    public static function optionsForStatusColors()
    {
        return [
            self::STATUS_ISSUED => [
                'text'       => '#1d5f82',
                'background' => '#eaf4fb',
                'border'     => '#cfe4f5',
            ],
            self::STATUS_SENT => [
                'text'       => '#806000',
                'background' => '#fff7df',
                'border'     => '#f0dfa7',
            ],
            self::STATUS_SIGNED => [
                'text'       => '#18703a',
                'background' => '#eaf7ef',
                'border'     => '#cce8d6',
            ],
            self::STATUS_CANCELED => [
                'text'       => '#a51d1d',
                'background' => '#fdecec',
                'border'     => '#f3caca',
            ],
        ];
    }

    public function getStatusColors()
    {
        return (array)ArrayHelper::getValue(
            static::optionsForStatusColors(),
            $this->status,
            static::optionsForStatusColors()[self::STATUS_ISSUED]
        );
    }

    public static function optionsForStatusIcons()
    {
        return [
            self::STATUS_ISSUED   => 'fas fa-file-alt',
            self::STATUS_SENT     => 'fas fa-paper-plane',
            self::STATUS_SIGNED   => 'fas fa-check',
            self::STATUS_CANCELED => 'fas fa-times',
        ];
    }

    public function getStatusIcon()
    {
        return (string)ArrayHelper::getValue(
            static::optionsForStatusIcons(),
            $this->status,
            static::optionsForStatusIcons()[self::STATUS_ISSUED]
        );
    }

    public static function lockedStatuses()
    {
        return [
            self::STATUS_SENT,
            self::STATUS_SIGNED,
            self::STATUS_CANCELED,
        ];
    }

    public function getIsEditable()
    {
        return $this->isNewRecord || !in_array($this->status, static::lockedStatuses(), true);
    }

    public static function closingTypes()
    {
        return [
            self::TYPE_ACT,
            self::TYPE_UPD,
            self::TYPE_WAYBILL,
        ];
    }

    public function getTypeAsText()
    {
        return (string)ArrayHelper::getValue(static::optionsForType(), $this->type, $this->type);
    }

    public function getStatusAsText()
    {
        return (string)ArrayHelper::getValue(static::optionsForStatus(), $this->status, $this->status);
    }

    public function normalizeIssuedAt($event = null)
    {
        if ($this->issued_at === '' || $this->issued_at === null) {
            $this->issued_at = strtotime('today');
            return;
        }

        if (is_numeric($this->issued_at)) {
            $this->issued_at = (int)$this->issued_at;
            return;
        }

        $value = trim((string)$this->issued_at);
        if (preg_match('/^(\d{1,2})\.(\d{1,2})\.(\d{4})$/', $value, $matches)) {
            $this->issued_at = mktime(0, 0, 0, (int)$matches[2], (int)$matches[1], (int)$matches[3]);
            return;
        }

        if (preg_match('/^(\d{1,2})-(\d{1,2})-(\d{4})$/', $value, $matches)) {
            $this->issued_at = mktime(0, 0, 0, (int)$matches[2], (int)$matches[1], (int)$matches[3]);
            return;
        }

        $time = strtotime($value);
        $this->issued_at = $time ? $time : null;
    }

    public function ensureSnapshotData($event = null)
    {
        if ($this->isSnapshotPrepared) {
            return;
        }

        if ($this->seller_contractor_id && ($this->isNewRecord || $this->isAttributeChanged('seller_contractor_id') || !$this->seller_contractor_name)) {
            $this->fillContractorSnapshot('seller', $this->sellerContractor);
        }

        if ($this->buyer_contractor_id && ($this->isNewRecord || $this->isAttributeChanged('buyer_contractor_id') || !$this->buyer_contractor_name)) {
            $this->fillContractorSnapshot('buyer', $this->buyerContractor);
        }
    }

    protected function fillContractorSnapshot($prefix, CmsContractor $contractor = null)
    {
        if (!$contractor) {
            return;
        }

        $this->{$prefix.'_contractor_type'} = $contractor->contractor_type;
        $this->{$prefix.'_contractor_name'} = $contractor->asShortText;
        $this->{$prefix.'_contractor_full_name'} = $contractor->full_name ?: $contractor->asShortText;
        $this->{$prefix.'_contractor_inn'} = $contractor->inn;
        $this->{$prefix.'_contractor_kpp'} = $contractor->kpp;
        $this->{$prefix.'_contractor_ogrn'} = $contractor->ogrn;
        $this->{$prefix.'_contractor_registration_date'} = $contractor->registration_date;
        $this->{$prefix.'_contractor_address'} = $contractor->address;
        $this->{$prefix.'_contractor_mailing_postcode'} = $contractor->mailing_postcode;
    }

    protected function fillMissingContractorSnapshot($prefix, CmsContractor $contractor = null)
    {
        if (!$contractor) {
            return;
        }

        $values = [
            'contractor_type'             => $contractor->contractor_type,
            'contractor_name'             => $contractor->asShortText,
            'contractor_full_name'        => $contractor->full_name ?: $contractor->asShortText,
            'contractor_inn'              => $contractor->inn,
            'contractor_kpp'              => $contractor->kpp,
            'contractor_ogrn'             => $contractor->ogrn,
            'contractor_registration_date'=> $contractor->registration_date,
            'contractor_address'          => $contractor->address,
            'contractor_mailing_postcode' => $contractor->mailing_postcode,
        ];

        foreach ($values as $suffix => $value) {
            $attribute = $prefix.'_'.$suffix;
            if (trim((string)$this->{$attribute}) === '' && trim((string)$value) !== '') {
                $this->{$attribute} = $value;
            }
        }
    }

    public static function createFromBill(ShopBill $bill, $type = self::TYPE_ACT)
    {
        $sellerContractor = $bill->receiverContractor ?: static::defaultSellerContractor();
        $buyerContractor = $bill->senderContractor ?: static::defaultBuyerContractor($bill);

        $model = new static();
        $model->type = static::normalizeType($type);
        $model->issued_at = strtotime('today');
        $model->cms_company_id = $bill->cms_company_id;
        $model->cms_user_id = $bill->cms_user_id;
        $model->seller_contractor_id = $sellerContractor ? $sellerContractor->id : $bill->receiver_contractor_id;
        $model->buyer_contractor_id = $buyerContractor ? $buyerContractor->id : $bill->sender_contractor_id;
        $model->amount = $bill->amount;
        $model->discount_amount = $bill->discount_amount;
        $model->currency_code = $bill->currency_code;
        $model->description = $bill->description;
        $model->comment_after = $model->defaultCommentAfter();
        $model->bills = [$bill->id];
        $model->deals = ArrayHelper::getColumn($bill->deals, 'id');

        $model->copyBillContractorSnapshot('seller', $bill, 'receiver');
        $model->copyBillContractorSnapshot('buyer', $bill, 'sender');
        $model->fillMissingContractorSnapshot('seller', $sellerContractor);
        $model->fillMissingContractorSnapshot('buyer', $buyerContractor);
        $model->document_data = $model->documentDataFromBill($bill, $sellerContractor);

        $items = [];
        foreach ($bill->printableBillItems as $item) {
            if (method_exists($item, 'asArray')) {
                $row = $item->asArray();
            } else {
                $row = [
                    'shop_product_id' => $item->shop_product_id,
                    'name'            => $item->name,
                    'measure_name'    => $item->measure_name,
                    'quantity'        => $item->quantity,
                    'price'           => $item->price,
                    'amount'          => $item->amount,
                    'discount_amount' => $item->discount_amount,
                    'discount_value'  => $item->discount_value,
                    'discount_name'   => $item->discount_name,
                    'currency_code'   => $item->currency_code,
                    'vat_name'        => $item->vat_name,
                ];
            }

            $row['source_shop_bill_id'] = $item->shop_bill_id ?: $bill->id;
            $row['source_shop_bill_item_id'] = $item->id ?: null;
            if (empty($row['extra_data']) && !empty($row['shop_product_id'])) {
                $product = ShopProduct::findOne((int)$row['shop_product_id']);
                $row['extra_data'] = $model->documentItemExtraDataFromProduct($product);
            }
            unset($row['id']);

            $items[] = $row;
        }
        $model->documentItemsData = $items;
        $model->isSnapshotPrepared = true;

        return $model;
    }

    public static function normalizeType($type)
    {
        $type = trim((string)$type);
        return array_key_exists($type, static::optionsForType()) ? $type : self::TYPE_ACT;
    }

    protected function copyBillContractorSnapshot($documentPrefix, ShopBill $bill, $billPrefix)
    {
        $this->{$documentPrefix.'_contractor_type'} = (string)$bill->{$billPrefix.'_contractor_type'};
        $this->{$documentPrefix.'_contractor_name'} = $bill->{'bill'.ucfirst($billPrefix).'Name'};
        $this->{$documentPrefix.'_contractor_full_name'} = (string)$bill->{$billPrefix.'_contractor_full_name'} ?: $this->{$documentPrefix.'_contractor_name'};
        $this->{$documentPrefix.'_contractor_inn'} = $bill->{'bill'.ucfirst($billPrefix).'Inn'};
        $this->{$documentPrefix.'_contractor_kpp'} = $bill->{'bill'.ucfirst($billPrefix).'Kpp'};
        $this->{$documentPrefix.'_contractor_ogrn'} = $bill->{'bill'.ucfirst($billPrefix).'Ogrn'};
        $this->{$documentPrefix.'_contractor_address'} = $bill->{'bill'.ucfirst($billPrefix).'Address'};
        $this->{$documentPrefix.'_contractor_mailing_postcode'} = $bill->{'bill'.ucfirst($billPrefix).'Postcode'};
    }

    protected static function defaultSellerContractor()
    {
        return CmsContractor::find()->our()->one();
    }

    protected static function defaultBuyerContractor(ShopBill $bill)
    {
        if ($bill->company && $bill->company->contractors) {
            $contractors = $bill->company->contractors;
            return reset($contractors);
        }

        if ($bill->cmsUser && $bill->cmsUser->cmsContractors) {
            $contractors = $bill->cmsUser->cmsContractors;
            return reset($contractors);
        }

        return null;
    }

    protected function documentDataFromBill(ShopBill $bill, CmsContractor $sellerContractor = null)
    {
        $data = [];

        if ($sellerContractor && $sellerContractor->cmsImage) {
            $data['logo_src'] = $sellerContractor->cmsImage->absoluteSrc;
        } elseif ($bill->receiverContractor && $bill->receiverContractor->cmsImage) {
            $data['logo_src'] = $bill->receiverContractor->cmsImage->absoluteSrc;
        } elseif (\Yii::$app->has('skeeks') && \Yii::$app->skeeks->site && \Yii::$app->skeeks->site->image) {
            $data['logo_src'] = \Yii::$app->skeeks->site->image->absoluteSrc;
        }

        $sellerName = trim((string)($this->sellerFullName ?: $this->sellerName));
        $sellerAddress = trim((string)$this->sellerAddress);
        $buyerName = trim((string)($this->buyerFullName ?: $this->buyerName));
        $buyerAddress = trim((string)$this->buyerAddress);
        $sellerWithAddress = trim($sellerName.($sellerAddress ? ', '.$sellerAddress : ''));
        $buyerWithAddress = trim($buyerName.($buyerAddress ? ', '.$buyerAddress : ''));
        $paymentDocuments = $this->paymentDocumentsFromBill($bill);
        $paymentDocument = static::formatPaymentDocuments($paymentDocuments);
        $shipmentDocumentsText = $this->shipmentDocumentsTextFromBill($bill);

        $data['upd'] = [
            'status'                    => '2',
            'payment_documents'         => $paymentDocuments,
            'payment_document'          => $paymentDocument,
            'state_contract_identifier' => '',
            'advance_documents'         => [],
            'advance_document'          => '',
            'base_document'             => (string)$bill->description,
            'base_document_name'        => 'Счет',
            'base_document_number'      => (string)$bill->id,
            'base_document_date'        => date('Y-m-d', (int)$bill->created_at),
            'transport_info'            => '',
            'shipping_document'         => $shipmentDocumentsText,
            'seller_other_info'         => '',
            'buyer_other_info'          => '',
            'shipper'                   => $sellerWithAddress,
            'consignee'                 => $buyerWithAddress,
        ];

        $data['waybill'] = [
            'shipper'            => $sellerName,
            'shipper_address'    => $sellerAddress,
            'consignee'          => $buyerName,
            'consignee_address'  => $buyerAddress,
            'transport_document' => '',
            'base_document'      => (string)$bill->description,
            'operation_type'     => '',
        ];

        $data['invoice_facture'] = [
            'shipper'                   => $sellerName,
            'shipper_address'           => $sellerAddress,
            'consignee'                 => $buyerName,
            'consignee_address'         => $buyerAddress,
            'payment_documents'         => $paymentDocuments,
            'payment_document'          => $paymentDocument,
            'state_contract_identifier' => '',
            'advance_documents'         => [],
            'advance_document'          => '',
            'correction_number'         => '',
            'correction_date'           => '',
            'shipment_documents_text'   => $shipmentDocumentsText,
            'shipment_documents'        => [],
        ];

        return $data;
    }

    protected function paymentDocumentsFromBill(ShopBill $bill)
    {
        $items = [];
        foreach ((array)$bill->payments as $payment) {
            if (!$payment || !$payment->created_at) {
                continue;
            }

            $number = $payment->documentNumber();
            $date = $payment->documentDate();

            $items[] = [
                'number'        => $number,
                'date'          => $date,
                'amount'        => round((float)$payment->amount, 4),
                'currency_code' => $payment->currency_code ?: $bill->currency_code,
            ];
        }

        return $items;
    }

    public function resolvedPaymentDocuments($sectionName = 'upd')
    {
        $data = (array)$this->document_data;
        $section = (array)ArrayHelper::getValue($data, $sectionName, []);
        $storedRows = static::normalizePaymentDocuments(
            ArrayHelper::getValue($section, 'payment_documents', []),
            ArrayHelper::getValue($section, 'payment_document', ArrayHelper::getValue($data, 'payment_document', ''))
        );

        $liveRows = [];
        $liveRowsByInternalId = [];
        $seen = [];
        foreach ((array)$this->bills as $bill) {
            $billRows = $this->paymentDocumentsFromBill($bill);
            foreach ($billRows as $index => $row) {
                $payment = ArrayHelper::getValue((array)$bill->payments, $index);
                if ($payment) {
                    $liveRowsByInternalId[(string)$payment->id] = $row;
                }
                $key = implode('|', [
                    ArrayHelper::getValue($row, 'number'),
                    ArrayHelper::getValue($row, 'date'),
                    ArrayHelper::getValue($row, 'amount'),
                ]);
                if (!isset($seen[$key])) {
                    $seen[$key] = true;
                    $liveRows[] = $row;
                }
            }
        }

        if ($storedRows) {
            foreach ($storedRows as &$row) {
                $storedNumber = trim((string)ArrayHelper::getValue($row, 'number'));
                if (isset($liveRowsByInternalId[$storedNumber])) {
                    $row = $liveRowsByInternalId[$storedNumber];
                }
            }
            unset($row);
            return static::normalizePaymentDocuments($storedRows);
        }

        return static::normalizePaymentDocuments($liveRows);
    }

    public function resolvedBaseDocument($sectionName = 'upd')
    {
        $section = (array)ArrayHelper::getValue((array)$this->document_data, $sectionName, []);
        $name = trim((string)ArrayHelper::getValue($section, 'base_document_name'));
        $number = trim((string)ArrayHelper::getValue($section, 'base_document_number'));
        $date = static::normalizeDocumentDateValue(ArrayHelper::getValue($section, 'base_document_date'));
        $additionalInfo = trim((string)ArrayHelper::getValue($section, 'base_document', $this->description));

        if ($name !== '' || $number !== '' || $date !== '') {
            return compact('name', 'number', 'date', 'additionalInfo');
        }

        $bills = $this->bills;
        $bill = $bills ? reset($bills) : null;
        if ($bill) {
            return [
                'name'           => 'Счет',
                'number'         => (string)$bill->id,
                'date'           => date('Y-m-d', (int)$bill->created_at),
                'additionalInfo' => $additionalInfo,
            ];
        }

        return compact('name', 'number', 'date', 'additionalInfo');
    }

    public static function formatBaseDocument(array $row)
    {
        $name = trim((string)ArrayHelper::getValue($row, 'name'));
        $number = trim((string)ArrayHelper::getValue($row, 'number'));
        $date = static::normalizeDocumentDateValue(ArrayHelper::getValue($row, 'date'));
        $parts = [];
        if ($name !== '') {
            $parts[] = $name;
        }
        if ($number !== '') {
            $parts[] = '№'.$number;
        }
        if ($date !== '') {
            $parts[] = 'от '.date('d.m.Y', strtotime($date));
        }

        return trim(implode(' ', $parts));
    }

    protected function shipmentDocumentsTextFromBill(ShopBill $bill)
    {
        $items = [];
        foreach ((array)$bill->closingDocuments as $document) {
            if (!$document || (int)$document->id === (int)$this->id) {
                continue;
            }

            $items[] = $document->asText();
        }

        return implode("\n", $items);
    }

    public function normalizeDocumentData($event = null)
    {
        $data = (array)$this->document_data;

        foreach (['upd', 'invoice_facture'] as $sectionName) {
            $section = (array)ArrayHelper::getValue($data, $sectionName, []);

            $paymentDocuments = static::normalizePaymentDocuments(
                ArrayHelper::getValue($section, 'payment_documents', []),
                ArrayHelper::getValue($section, 'payment_document', ArrayHelper::getValue($data, 'payment_document', ''))
            );
            foreach ($paymentDocuments as &$paymentDocument) {
                if (empty($paymentDocument['currency_code'])) {
                    $paymentDocument['currency_code'] = $this->currency_code ?: \Yii::$app->money->currencyCode;
                }
            }
            unset($paymentDocument);
            $section['payment_documents'] = $paymentDocuments;
            $section['payment_document'] = $paymentDocuments
                ? static::formatPaymentDocuments($paymentDocuments)
                : trim((string)ArrayHelper::getValue($section, 'payment_document'));

            $advanceDocuments = static::normalizeNumberDateDocuments(
                ArrayHelper::getValue($section, 'advance_documents', []),
                ArrayHelper::getValue($section, 'advance_document', '')
            );
            $section['advance_documents'] = $advanceDocuments;
            $section['advance_document'] = $advanceDocuments
                ? static::formatNumberDateDocuments($advanceDocuments)
                : trim((string)ArrayHelper::getValue($section, 'advance_document'));

            $section['base_document_date'] = static::normalizeDocumentDateValue(
                ArrayHelper::getValue($section, 'base_document_date')
            );

            $data[$sectionName] = $section;
        }

        $this->document_data = $data;
    }

    public static function normalizePaymentDocuments($rows, $legacyText = '')
    {
        $result = [];
        foreach (static::rowsFromStructuredOrLegacy($rows, $legacyText) as $row) {
            $number = trim((string)ArrayHelper::getValue($row, 'number'));
            $date = static::normalizeDocumentDateValue(ArrayHelper::getValue($row, 'date'));
            $amount = trim((string)ArrayHelper::getValue($row, 'amount'));
            $currencyCode = strtoupper(trim((string)ArrayHelper::getValue($row, 'currency_code')));

            if ($number === '' && $date === '' && $amount === '') {
                continue;
            }

            $item = [
                'number' => $number,
                'date'   => $date,
            ];

            if ($amount !== '') {
                $item['amount'] = round((float)str_replace([' ', ','], ['', '.'], $amount), 4);
            }

            if ($currencyCode !== '') {
                $item['currency_code'] = $currencyCode;
            }

            $result[] = $item;
        }

        return $result;
    }

    public static function normalizeNumberDateDocuments($rows, $legacyText = '')
    {
        $result = [];
        foreach (static::rowsFromStructuredOrLegacy($rows, $legacyText) as $row) {
            $number = trim((string)ArrayHelper::getValue($row, 'number'));
            $date = static::normalizeDocumentDateValue(ArrayHelper::getValue($row, 'date'));

            if ($number === '' && $date === '') {
                continue;
            }

            $result[] = [
                'number' => $number,
                'date'   => $date,
            ];
        }

        return $result;
    }

    protected static function rowsFromStructuredOrLegacy($rows, $legacyText = '')
    {
        $structuredRows = [];
        foreach ((array)$rows as $row) {
            if (is_array($row)) {
                $structuredRows[] = $row;
            }
        }

        if ($structuredRows) {
            return $structuredRows;
        }

        return static::parseNumberDateDocumentsText($legacyText);
    }

    public static function parseNumberDateDocumentsText($text)
    {
        $text = trim((string)$text);
        if ($text === '') {
            return [];
        }

        $rows = [];
        preg_match_all('/№\s*([^,;]+?)\s+от\s+(\d{1,2}[.\-]\d{1,2}[.\-]\d{2,4}|\d{4}-\d{1,2}-\d{1,2})/u', $text, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $rows[] = [
                'number' => trim($match[1]),
                'date'   => static::normalizeDocumentDateValue($match[2]),
            ];
        }

        if ($rows) {
            return $rows;
        }

        return [];
    }

    public static function normalizeDocumentDateValue($value)
    {
        if ($value === null || $value === '') {
            return '';
        }

        if (is_numeric($value)) {
            return date('Y-m-d', (int)$value);
        }

        $value = trim((string)$value);
        if (preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})$/', $value, $matches)) {
            return sprintf('%04d-%02d-%02d', (int)$matches[1], (int)$matches[2], (int)$matches[3]);
        }

        if (preg_match('/^(\d{1,2})[.\-](\d{1,2})[.\-](\d{2}|\d{4})$/', $value, $matches)) {
            $year = (int)$matches[3];
            if ($year < 100) {
                $year += 2000;
            }

            return sprintf('%04d-%02d-%02d', $year, (int)$matches[2], (int)$matches[1]);
        }

        $time = strtotime($value);
        return $time ? date('Y-m-d', $time) : $value;
    }

    public static function formatDocumentDateForPrint($value)
    {
        $value = static::normalizeDocumentDateValue($value);
        if ($value && preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $value, $matches)) {
            return $matches[3].'.'.$matches[2].'.'.$matches[1];
        }

        return (string)$value;
    }

    public static function formatPaymentDocuments($rows, $fallback = '')
    {
        $rows = static::normalizePaymentDocuments($rows, $fallback);
        return static::formatNumberDateDocuments($rows, $fallback);
    }

    public static function formatNumberDateDocuments($rows, $fallback = '')
    {
        $items = [];
        foreach ((array)$rows as $row) {
            $number = trim((string)ArrayHelper::getValue($row, 'number'));
            $date = static::formatDocumentDateForPrint(ArrayHelper::getValue($row, 'date'));

            if ($number === '' && $date === '') {
                continue;
            }

            $items[] = trim('№'.$number.($date ? ' от '.$date : ''));
        }

        return $items ? implode(', ', $items) : trim((string)$fallback);
    }

    protected function documentItemExtraDataFromProduct(ShopProduct $product = null)
    {
        if (!$product || !in_array($this->type, [self::TYPE_UPD, self::TYPE_INVOICE_FACTURE], true)) {
            return [];
        }

        $element = $product->cmsContentElement;
        $country = $product->country;

        return [
            'code'               => trim((string)($product->brand_sku ?: ($element ? $element->code : ''))),
            'country_code'       => $country ? $country->iso : '',
            'country_name'       => $country ? $country->name : '',
            'declaration_number' => '',
        ];
    }

    public function normalizeDocumentItems($event = null)
    {
        if ($this->documentItemsData === null) {
            return;
        }

        $rows = $this->normalizeDocumentItemsData($this->documentItemsData);
        $this->documentItemsData = $rows;

        if (!$rows) {
            $this->addError('documentItemsData', 'Добавьте хотя бы одну позицию документа.');
            return;
        }

        $amount = 0;
        $discountAmount = 0;
        foreach ($rows as $row) {
            $amount += (float)$row['amount'];
            $discountAmount += (float)$row['discount_amount'];
        }

        $this->amount = round($amount, 4);
        $this->discount_amount = round($discountAmount, 4);
    }

    public function normalizeDocumentItemsData($rows)
    {
        $result = [];
        foreach ((array)$rows as $row) {
            $name = trim((string)ArrayHelper::getValue($row, 'name'));
            $productId = (int)ArrayHelper::getValue($row, 'shop_product_id');
            $product = null;
            $sourceBillId = (int)ArrayHelper::getValue($row, 'source_shop_bill_id');
            $sourceBillItemId = (int)ArrayHelper::getValue($row, 'source_shop_bill_item_id');
            $quantity = (float)str_replace(',', '.', (string)ArrayHelper::getValue($row, 'quantity', 1));
            $price = (float)str_replace(',', '.', (string)ArrayHelper::getValue($row, 'price', 0));

            if (!$name && !$productId && !$price) {
                continue;
            }

            if (!$quantity) {
                $quantity = 1;
            }

            if ($productId) {
                $product = ShopProduct::findOne($productId);
                if (!$name && $product) {
                    $name = $product->cmsContentElement->productName;
                }
            }

            if (!$sourceBillId && $sourceBillItemId) {
                if ($sourceBillItem = ShopBillItem::findOne($sourceBillItemId)) {
                    $sourceBillId = (int)$sourceBillItem->shop_bill_id;
                }
            }

            $baseAmount = $quantity * $price;
            $discountValue = trim((string)ArrayHelper::getValue($row, 'discount_value'));
            $discountAmount = $this->normalizeDiscountAmount($discountValue, $baseAmount, ArrayHelper::getValue($row, 'discount_amount', 0), $quantity);
            $extraData = ArrayHelper::getValue($row, 'extra_data');
            if (!$extraData && $product) {
                $extraData = $this->documentItemExtraDataFromProduct($product);
            }

            $result[] = [
                'id'                       => (int)ArrayHelper::getValue($row, 'id'),
                'shop_product_id'          => $productId ?: null,
                'source_shop_bill_id'      => $sourceBillId ?: null,
                'source_shop_bill_item_id' => $sourceBillItemId ?: null,
                'name'                     => $name,
                'measure_name'             => trim((string)ArrayHelper::getValue($row, 'measure_name')) ?: 'шт',
                'quantity'                 => $quantity,
                'price'                    => $price,
                'amount'                   => round($baseAmount - $discountAmount, 4),
                'discount_amount'          => $discountAmount,
                'discount_value'           => $discountValue ?: null,
                'discount_name'            => trim((string)ArrayHelper::getValue($row, 'discount_name')) ?: null,
                'currency_code'            => (string)$this->currency_code ?: \Yii::$app->money->currencyCode,
                'vat_name'                 => trim((string)ArrayHelper::getValue($row, 'vat_name')) ?: 'Без НДС',
                'extra_data'               => $extraData,
            ];
        }

        return $result;
    }

    protected function normalizeDiscountAmount($discountValue, $baseAmount, $fallbackAmount = 0, $quantity = 1)
    {
        $baseAmount = max((float)$baseAmount, 0);
        $discountValue = trim((string)$discountValue);
        $quantity = max((float)$quantity, 0);

        if ($discountValue !== '') {
            $normalized = str_replace([' ', ','], ['', '.'], $discountValue);
            if (strpos($normalized, '%') !== false) {
                $percent = (float)str_replace('%', '', $normalized);
                $amount = $baseAmount * $percent / 100;
            } else {
                $amount = (float)$normalized * $quantity;
            }
        } else {
            $amount = (float)str_replace(',', '.', (string)$fallbackAmount);
        }

        return round(min(max($amount, 0), $baseAmount), 4);
    }

    public function fillNumberAfterInsert($event = null)
    {
        if ($this->number) {
            return;
        }

        $number = (string)$this->id;
        static::updateAll(['number' => $number], ['id' => $this->id]);
        $this->number = $number;
    }

    public function saveDocumentItems($event = null)
    {
        if ($this->documentItemsData === null) {
            return;
        }

        ShopDocumentItem::deleteAll(['shop_document_id' => $this->id]);

        foreach ($this->documentItemsData as $index => $row) {
            $item = new ShopDocumentItem();
            $item->shop_document_id = $this->id;
            $item->shop_product_id = ArrayHelper::getValue($row, 'shop_product_id');
            $item->source_shop_bill_id = ArrayHelper::getValue($row, 'source_shop_bill_id');
            $item->source_shop_bill_item_id = ArrayHelper::getValue($row, 'source_shop_bill_item_id');
            $item->name = ArrayHelper::getValue($row, 'name');
            $item->measure_name = ArrayHelper::getValue($row, 'measure_name');
            $item->quantity = ArrayHelper::getValue($row, 'quantity');
            $item->price = ArrayHelper::getValue($row, 'price');
            $item->amount = ArrayHelper::getValue($row, 'amount');
            $item->discount_amount = ArrayHelper::getValue($row, 'discount_amount', 0);
            $item->discount_value = ArrayHelper::getValue($row, 'discount_value');
            $item->discount_name = ArrayHelper::getValue($row, 'discount_name');
            $item->currency_code = ArrayHelper::getValue($row, 'currency_code', $this->currency_code);
            $item->vat_name = ArrayHelper::getValue($row, 'vat_name');
            $item->extra_data = ArrayHelper::getValue($row, 'extra_data');
            $item->sort = ($index + 1) * 100;

            if (!$item->save()) {
                throw new \yii\base\Exception('Не удалось сохранить позицию документа: '.print_r($item->errors, true));
            }
        }
    }

    public function syncBillRelationsFromDocumentItems($event = null)
    {
        $billIds = [];

        if ($this->documentItemsData !== null) {
            foreach ((array)$this->documentItemsData as $row) {
                $billId = (int)ArrayHelper::getValue($row, 'source_shop_bill_id');
                if ($billId) {
                    $billIds[$billId] = $billId;
                }
            }
        } else {
            foreach ($this->documentItems as $item) {
                $billId = (int)$item->source_shop_bill_id;
                if ($billId) {
                    $billIds[$billId] = $billId;
                }
            }
        }

        if (!$billIds) {
            return;
        }

        $existingBillIds = ArrayHelper::getColumn($this->bills, 'id');

        foreach (array_diff($billIds, $existingBillIds) as $billId) {
            $link = new ShopDocument2bill();
            $link->shop_document_id = $this->id;
            $link->shop_bill_id = $billId;
            $link->save();
        }

        $this->populateRelation('bills', ShopBill::findAll(array_unique(array_merge($existingBillIds, $billIds))));
    }

    public function getCompany()
    {
        return $this->hasOne(CmsCompany::class, ['id' => 'cms_company_id']);
    }

    public function getCmsUser()
    {
        return $this->hasOne(CmsUser::class, ['id' => 'cms_user_id']);
    }

    public function getCreatedBy()
    {
        return $this->hasOne(CmsUser::class, ['id' => 'created_by']);
    }

    public function getSellerContractor()
    {
        return $this->hasOne(CmsContractor::class, ['id' => 'seller_contractor_id']);
    }

    public function getBuyerContractor()
    {
        return $this->hasOne(CmsContractor::class, ['id' => 'buyer_contractor_id']);
    }

    public function getCurrencyCode()
    {
        return $this->hasOne(MoneyCurrency::class, ['code' => 'currency_code']);
    }

    public function getDocumentItems()
    {
        return $this->hasMany(ShopDocumentItem::class, ['shop_document_id' => 'id'])
            ->orderBy(['sort' => SORT_ASC, 'id' => SORT_ASC]);
    }

    public function getBills()
    {
        return $this->hasMany(ShopBill::class, ['id' => 'shop_bill_id'])
            ->viaTable(ShopDocument2bill::tableName(), ['shop_document_id' => 'id']);
    }

    public function getDeals()
    {
        return $this->hasMany(CmsDeal::class, ['id' => 'cms_deal_id'])
            ->viaTable(ShopDocument2deal::tableName(), ['shop_document_id' => 'id']);
    }

    protected function snapshotValue($snapshotAttribute, $relation, $relationAttribute = null)
    {
        $value = trim((string)$this->{$snapshotAttribute});
        if ($value !== '') {
            return $value;
        }

        $related = $this->{$relation};
        if (!$related) {
            return '';
        }

        if ($relationAttribute === null) {
            return (string)$related;
        }

        return (string)$related->{$relationAttribute};
    }

    public function getSellerName()
    {
        return $this->snapshotValue('seller_contractor_name', 'sellerContractor', 'asShortText');
    }

    public function getSellerFullName()
    {
        $value = $this->snapshotValue('seller_contractor_full_name', 'sellerContractor', 'full_name');
        return $value ?: $this->sellerName;
    }

    public function getSellerInn()
    {
        return $this->snapshotValue('seller_contractor_inn', 'sellerContractor', 'inn');
    }

    public function getSellerKpp()
    {
        return $this->snapshotValue('seller_contractor_kpp', 'sellerContractor', 'kpp');
    }

    public function getSellerOgrn()
    {
        return $this->snapshotValue('seller_contractor_ogrn', 'sellerContractor', 'ogrn');
    }

    public function getSellerRegistrationDate()
    {
        return $this->snapshotValue('seller_contractor_registration_date', 'sellerContractor', 'registration_date');
    }

    public function getSellerAddress()
    {
        return $this->snapshotValue('seller_contractor_address', 'sellerContractor', 'address');
    }

    public function getBuyerName()
    {
        return $this->snapshotValue('buyer_contractor_name', 'buyerContractor', 'asShortText');
    }

    public function getBuyerFullName()
    {
        $value = $this->snapshotValue('buyer_contractor_full_name', 'buyerContractor', 'full_name');
        return $value ?: $this->buyerName;
    }

    public function getBuyerInn()
    {
        return $this->snapshotValue('buyer_contractor_inn', 'buyerContractor', 'inn');
    }

    public function getBuyerKpp()
    {
        return $this->snapshotValue('buyer_contractor_kpp', 'buyerContractor', 'kpp');
    }

    public function getBuyerOgrn()
    {
        return $this->snapshotValue('buyer_contractor_ogrn', 'buyerContractor', 'ogrn');
    }

    public function getBuyerRegistrationDate()
    {
        return $this->snapshotValue('buyer_contractor_registration_date', 'buyerContractor', 'registration_date');
    }

    public function getBuyerAddress()
    {
        return $this->snapshotValue('buyer_contractor_address', 'buyerContractor', 'address');
    }

    public function getMoney()
    {
        return new Money($this->amount, (string)$this->currency_code);
    }

    public function getUrl($scheme = false)
    {
        return Url::to(['/shop/shop-document/view', 'code' => $this->code], $scheme);
    }

    public function getPdfUrl($scheme = false)
    {
        return Url::to(['/shop/shop-document/pdf', 'code' => $this->code], $scheme);
    }

    public function defaultCommentAfter()
    {
        if ($this->type == self::TYPE_ACT) {
            return 'Вышеперечисленные услуги оказаны в полном объеме и в установленный срок. Заказчик не имеет претензий по качеству, срокам и объему оказанных услуг.';
        }

        if ($this->type == self::TYPE_WAYBILL) {
            return 'Товар передан покупателю в количестве и ассортименте, указанном в настоящей накладной.';
        }

        return '';
    }

    public static function find()
    {
        return (new ShopDocumentQuery(get_called_class()));
    }

    public function asText()
    {
        $number = $this->number ?: $this->id;
        return "{$this->typeAsText} №{$number} от ".\Yii::$app->formatter->asDate($this->issued_at ?: $this->created_at);
    }

    public function getAsText()
    {
        return $this->asText();
    }

    public function getAsFullText()
    {
        $text = $this->asText();
        if ($this->description) {
            $text .= " ({$this->description})";
        }
        return $text;
    }
}
