<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */

namespace skeeks\cms\shop\models;

use skeeks\cms\behaviors\CmsLogBehavior;
use skeeks\cms\behaviors\RelationalBehavior;
use skeeks\cms\models\behaviors\HasJsonFieldsBehavior;
use skeeks\cms\models\behaviors\traits\HasLogTrait;
use skeeks\cms\models\CmsCompany;
use skeeks\cms\models\CmsContractor;
use skeeks\cms\models\CmsContractorBank;
use skeeks\cms\models\CmsDeal;
use skeeks\cms\models\CmsDeal2bill;
use skeeks\cms\models\CmsUser;
use skeeks\cms\money\models\MoneyCurrency;
use skeeks\cms\money\Money;
use skeeks\cms\shop\models\queries\ShopBillQuery;
use Yii;
use yii\db\AfterSaveEvent;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/**
 * This is the model class for table "{{%shop_bill}}".
 *
 * @property int               $id
 * @property int               $created_by
 * @property int               $updated_by
 * @property int               $created_at
 * @property int               $updated_at
 * @property int|null          $cms_user_id Покупатель
 * @property int|null          $cms_company_id Компания
 * @property int|null          $sender_contractor_id Контрагент отправитель
 * @property int|null          $receiver_contractor_id Контрагент получатель
 * @property int|null          $receiver_contractor_bank_id Банк получатель
 * @property int               $shop_order_id Заказ
 * @property int               $shop_pay_system_id Платежная система
 * @property int               $paid_at Дата оплаты
 * @property int|null          $due_at Оплатить до
 * @property int               $shop_payment_id Платеж
 * @property int               $closed_at Дата отмены
 * @property string            $reason_closed Причина отмены
 * @property string            $amount
 * @property string            $discount_amount
 * @property string|null       $discount_value
 * @property string|null       $discount_name
 * @property string            $currency_code
 * @property string|null       $company_name
 * @property string|null       $shop_pay_system_name
 * @property string|null       $sender_contractor_type
 * @property string|null       $sender_contractor_name
 * @property string|null       $sender_contractor_full_name
 * @property string|null       $sender_contractor_inn
 * @property string|null       $sender_contractor_kpp
 * @property string|null       $sender_contractor_ogrn
 * @property string|null       $sender_contractor_address
 * @property string|null       $sender_contractor_mailing_postcode
 * @property string|null       $receiver_contractor_type
 * @property string|null       $receiver_contractor_name
 * @property string|null       $receiver_contractor_full_name
 * @property string|null       $receiver_contractor_inn
 * @property string|null       $receiver_contractor_kpp
 * @property string|null       $receiver_contractor_ogrn
 * @property string|null       $receiver_contractor_address
 * @property string|null       $receiver_contractor_mailing_postcode
 * @property string|null       $receiver_bank_name
 * @property string|null       $receiver_bank_bic
 * @property string|null       $receiver_bank_correspondent_account
 * @property string|null       $receiver_bank_checking_account
 * @property string|null       $receiver_bank_address
 * @property string            $description
 * @property string            $code Уникальный код счета
 * @property string            $external_id
 * @property string            $external_name
 * @property array             $external_data Внешние данные, например от платежной системы
 *
 * @property MoneyCurrency     $currencyCode
 * @property CmsUser           $cmsUser
 * @property ShopOrder         $shopOrder
 * @property ShopPayment       $shopPayment
 * @property ShopPaySystem     $shopPaySystem
 *
 * @property CmsCompany        $company
 * @property CmsContractor     $senderContractor
 * @property CmsContractor     $receiverContractor
 * @property CmsContractorBank $receiverContractorBank
 *
 * @property ShopPayment[]     $payments
 * @property ShopDocument[]    $documents
 * @property ShopDocument[]    $closingDocuments
 * @property ShopDocumentItem[] $sourceDocumentItems
 * @property ShopDocumentItem[] $closingDocumentItems
 * @property CmsDeal[]         $deals
 *
 * @property string            $url Ссылка на страницу счета
 * @property string            $payUrl Ссылка на страницу оплаты
 *
 * @property Money             $money
 * @property Money             $documentedMoney
 * @property Money             $documentBalanceMoney
 * @property float             $documentedAmount
 * @property float             $documentBalanceAmount
 * @property bool              $isClosedByDocuments
 * @property string            $asFullText
 */
class ShopBill extends \skeeks\cms\base\ActiveRecord
{
    use HasLogTrait;

    /**
     * @var bool
     */
    public $isNotifyCreate = false;
    public $isNotifyUpdate = false;
    public $billItemsData = null;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%shop_bill}}';
    }

    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            RelationalBehavior::class => [
                'class' => RelationalBehavior::class,
                'relationNames' => [
                    'deals',
                ],
            ],
            HasJsonFieldsBehavior::class => [
                'class'  => HasJsonFieldsBehavior::class,
                'fields' => ['external_data'],
            ],
            CmsLogBehavior::class     => [
                'class' => CmsLogBehavior::class,
                /*'relation_map' => [
                    'cms_company_status_id' => 'status',
                ],*/
            ],
        ]);
    }
    /**
     *
     */
    public function init()
    {
        $this->on(self::EVENT_AFTER_FIND, [$this, "_afterFind"]);
        $this->on(self::EVENT_BEFORE_VALIDATE, [$this, "normalizeDueAt"]);
        $this->on(self::EVENT_BEFORE_VALIDATE, [$this, "ensureSnapshotData"]);
        $this->on(self::EVENT_BEFORE_VALIDATE, [$this, "normalizeBillItems"]);
        $this->on(self::EVENT_AFTER_INSERT, [$this, "saveBillItems"]);
        $this->on(self::EVENT_AFTER_UPDATE, [$this, "saveBillItems"]);
        parent::init();

        /*$this->on(self::EVENT_AFTER_INSERT, [$this, '_notifyCreate']);
        $this->on(self::EVENT_AFTER_UPDATE, [$this, '_notifyUpdate']);*/
    }

    public function _afterFind($event)
    {
        $this->amount = (float)$this->amount;
        $this->discount_amount = (float)$this->discount_amount;
    }

    public function normalizeDueAt($event = null)
    {
        if ($this->due_at === '' || $this->due_at === null) {
            $this->due_at = $this->isNewRecord ? strtotime('today +30 days') : null;
            return;
        }

        if (is_numeric($this->due_at)) {
            $this->due_at = (int)$this->due_at;
            return;
        }

        $value = trim((string)$this->due_at);
        if (preg_match('/^(\d{1,2})\.(\d{1,2})\.(\d{4})$/', $value, $matches)) {
            $this->due_at = mktime(0, 0, 0, (int)$matches[2], (int)$matches[1], (int)$matches[3]);
            return;
        }

        if (preg_match('/^(\d{1,2})-(\d{1,2})-(\d{4})$/', $value, $matches)) {
            $this->due_at = mktime(0, 0, 0, (int)$matches[2], (int)$matches[1], (int)$matches[3]);
            return;
        }

        $time = strtotime($value);
        $this->due_at = $time ? $time : null;
    }

    /**
     * Уведомление о создании счета на оплату заказчику услуги
     */
    public function _notifyCreate()
    {
        if ($this->shopBuyer && $this->shopBuyer->email && $this->isNotifyCreate) {

            \Yii::$app->mailer->view->theme->pathMap['@app/mail'][] = '@skeeks/cms/shop/mail/bill';

            \Yii::$app->mailer->compose('create', [
                'model' => $this,
            ])
                ->setFrom([\Yii::$app->cms->adminEmail => \Yii::$app->cms->appName.''])
                ->setTo($this->shopBuyer->email)
                ->setSubject('Выставлен счет №'.$this->id." от ".\Yii::$app->formatter->asDate($this->created_at)." по заказу №".$this->shopOrder->id)
                ->send();
        }
    }
    /**
     * Уведомление об оплате счета заказчику услуги
     *
     * @param AfterSaveEvent $event
     */
    public function _notifyUpdate(AfterSaveEvent $event)
    {
        if ($this->shopBuyer && $this->shopBuyer->email && $this->isNotifyUpdate) {

            //Если счет стал оплаченым
            if (in_array("paid_at", array_keys($event->changedAttributes)) && $this->paid_at) {

                try {

                    //2 уведомить
                    \Yii::$app->mailer->view->theme->pathMap['@app/mail'][] = '@skeeks/cms/shop/mail/bill';

                    \Yii::$app->mailer->compose('paid', [
                        'model' => $this,
                    ])
                        ->setFrom([\Yii::$app->cms->adminEmail => \Yii::$app->cms->appName.''])
                        ->setTo($this->shopBuyer->email)
                        ->setSubject('Оплачен счет №'.$this->id." от ".\Yii::$app->formatter->asDate($this->created_at)." по заказу №".$this->shopOrder->id)
                        ->send();

                } catch (\Exception $e) {
                    \Yii::error($e->getMessage(), self::class);
                }

            }
        }
    }
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'cms_user_id', 'shop_order_id', 'shop_pay_system_id', 'paid_at', 'due_at', 'shop_payment_id', 'closed_at'], 'integer'],
            [
                [
                    'cms_company_id',
                    'sender_contractor_id',
                    'receiver_contractor_id',
                    'receiver_contractor_bank_id',
                ],
                'integer',
            ],

            [['cms_user_id'], 'default', 'value' => null],
            [['cms_company_id'], 'default', 'value' => null],

            [['shop_pay_system_id'], 'required'],
            [['external_id', 'external_name'], 'default', 'value' => null],
            [['external_id', 'external_name'], 'string'],
            [['reason_closed', 'description'], 'string'],
            [['external_data'], 'safe'],
            [['amount', 'discount_amount'], 'number'],
            [['discount_value'], 'string', 'max' => 32],
            [['discount_name'], 'string', 'max' => 255],
            [
                [
                    'company_name',
                    'shop_pay_system_name',
                    'sender_contractor_name',
                    'sender_contractor_full_name',
                    'sender_contractor_address',
                    'receiver_contractor_name',
                    'receiver_contractor_full_name',
                    'receiver_contractor_address',
                    'receiver_bank_name',
                    'receiver_bank_address',
                ],
                'string',
                'max' => 255,
            ],
            [
                [
                    'sender_contractor_type',
                    'receiver_contractor_type',
                    'sender_contractor_inn',
                    'sender_contractor_kpp',
                    'sender_contractor_ogrn',
                    'sender_contractor_mailing_postcode',
                    'receiver_contractor_inn',
                    'receiver_contractor_kpp',
                    'receiver_contractor_ogrn',
                    'receiver_contractor_mailing_postcode',
                ],
                'string',
                'max' => 32,
            ],
            [['receiver_bank_bic'], 'string', 'max' => 12],
            [['receiver_bank_correspondent_account', 'receiver_bank_checking_account'], 'string', 'max' => 20],
            [['discount_amount'], 'default', 'value' => 0],
            [['discount_value', 'discount_name'], 'default', 'value' => null],
            [['currency_code'], 'string', 'max' => 3],
            [['code'], 'string', 'max' => 255],
            [['code'], 'unique'],

            [['currency_code'], 'exist', 'skipOnError' => true, 'targetClass' => MoneyCurrency::class, 'targetAttribute' => ['currency_code' => 'code']],
            [['cms_user_id'], 'exist', 'skipOnError' => true, 'targetClass' => CmsUser::class, 'targetAttribute' => ['cms_user_id' => 'id']],
            [['shop_order_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopOrder::class, 'targetAttribute' => ['shop_order_id' => 'id']],
            [['shop_payment_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopPayment::class, 'targetAttribute' => ['shop_payment_id' => 'id']],
            [['shop_pay_system_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopPaySystem::class, 'targetAttribute' => ['shop_pay_system_id' => 'id']],

            [['code'], 'default', 'value' => \Yii::$app->security->generateRandomString()],
            [['shop_order_id'], 'default', 'value' => null],

            /*[['deals'], 'required'],*/
            [['deals'], 'safe'],
            [['billItemsData'], 'safe'],

            [
                ['cms_company_id'],
                'required',
                'when' => function () {
                    return !$this->cms_user_id;
                },
            ],
            [
                ['cms_user_id'],
                'required',
                'when' => function () {
                    return !$this->cms_company_id;
                },
            ],
        ]);
    }

    public function normalizeBillItems($event = null)
    {
        if ($this->billItemsData === null) {
            return;
        }

        $rows = $this->normalizeBillItemsData($this->billItemsData);
        $this->billItemsData = $rows;

        if (!$rows) {
            $this->addError('billItemsData', 'Добавьте хотя бы одну позицию счета.');
            return;
        }

        $amount = 0;
        $discountAmount = 0;
        foreach ($rows as $row) {
            $amount += (float)$row['amount'];
            $discountAmount += (float)$row['discount_amount'];
        }

        $this->discount_amount = round($discountAmount, 4);
        $this->amount = round($amount, 4);
    }

    public function normalizeBillItemsData($rows)
    {
        $result = [];
        foreach ((array)$rows as $row) {
            $name = trim((string)ArrayHelper::getValue($row, 'name'));
            $productId = (int)ArrayHelper::getValue($row, 'shop_product_id');
            $quantity = (float)str_replace(',', '.', (string)ArrayHelper::getValue($row, 'quantity', 1));
            $price = (float)str_replace(',', '.', (string)ArrayHelper::getValue($row, 'price', 0));

            if (!$name && !$productId && !$price) {
                continue;
            }

            if (!$quantity) {
                $quantity = 1;
            }

            $baseAmount = $quantity * $price;
            $discountValue = trim((string)ArrayHelper::getValue($row, 'discount_value'));
            $discountAmount = $this->normalizeDiscountAmount($discountValue, $baseAmount, ArrayHelper::getValue($row, 'discount_amount', 0), $quantity);

            if (!$name && $productId) {
                if ($product = ShopProduct::findOne($productId)) {
                    $name = $product->cmsContentElement->productName;
                }
            }

            $result[] = [
                'id'              => (int)ArrayHelper::getValue($row, 'id'),
                'shop_product_id' => $productId ?: null,
                'name'            => $name,
                'measure_name'    => trim((string)ArrayHelper::getValue($row, 'measure_name')) ?: 'шт',
                'quantity'        => $quantity,
                'price'           => $price,
                'amount'          => round($baseAmount - $discountAmount, 4),
                'discount_amount' => $discountAmount,
                'discount_value'  => $discountValue ?: null,
                'discount_name'   => trim((string)ArrayHelper::getValue($row, 'discount_name')) ?: null,
                'currency_code'   => (string)$this->currency_code ?: \Yii::$app->money->currencyCode,
                'vat_name'        => trim((string)ArrayHelper::getValue($row, 'vat_name')) ?: 'Без НДС',
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

    public function ensureSnapshotData($event = null)
    {
        if ($this->isNewRecord || $this->isAttributeChanged('cms_company_id') || !$this->company_name) {
            $this->company_name = $this->company ? (string)$this->company->name : $this->company_name;
        }

        if ($this->isNewRecord || $this->isAttributeChanged('shop_pay_system_id') || !$this->shop_pay_system_name) {
            $this->shop_pay_system_name = $this->shopPaySystem ? (string)$this->shopPaySystem->name : $this->shop_pay_system_name;
        }

        if ($this->isNewRecord || $this->isAttributeChanged('sender_contractor_id') || !$this->sender_contractor_name) {
            $this->fillContractorSnapshot('sender', $this->senderContractor);
        }

        if ($this->isNewRecord || $this->isAttributeChanged('receiver_contractor_id') || !$this->receiver_contractor_name) {
            $this->fillContractorSnapshot('receiver', $this->receiverContractor);
        }

        if ($this->isNewRecord || $this->isAttributeChanged('receiver_contractor_bank_id') || !$this->receiver_bank_name) {
            $this->fillReceiverBankSnapshot($this->receiverContractorBank);
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
        $this->{$prefix.'_contractor_address'} = $contractor->address;
        $this->{$prefix.'_contractor_mailing_postcode'} = $contractor->mailing_postcode;
    }

    protected function fillReceiverBankSnapshot(CmsContractorBank $bank = null)
    {
        if (!$bank) {
            return;
        }

        $this->receiver_bank_name = $bank->bank_name;
        $this->receiver_bank_bic = $bank->bic;
        $this->receiver_bank_correspondent_account = $bank->correspondent_account;
        $this->receiver_bank_checking_account = $bank->checking_account;
        $this->receiver_bank_address = $bank->bank_address;
    }

    public function saveBillItems($event = null)
    {
        if ($this->billItemsData === null) {
            return;
        }

        ShopBillItem::deleteAll(['shop_bill_id' => $this->id]);

        foreach ($this->billItemsData as $index => $row) {
            $item = new ShopBillItem();
            $item->shop_bill_id = $this->id;
            $item->shop_product_id = ArrayHelper::getValue($row, 'shop_product_id');
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
            $item->sort = ($index + 1) * 100;

            if (!$item->save()) {
                throw new \yii\base\Exception('Не удалось сохранить позицию счета: '.print_r($item->errors, true));
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'id'                          => Yii::t('skeeks/shop/app', 'ID'),
            'cms_user_id'                 => "Клиент",
            'shop_order_id'               => Yii::t('skeeks/shop/app', 'Заказ'),
            'shop_pay_system_id'          => "Способ оплаты",
            'paid_at'                     => Yii::t('skeeks/shop/app', 'Дата оплаты'),
            'due_at'                      => 'Оплатить до',
            'shop_payment_id'             => Yii::t('skeeks/shop/app', 'Платеж'),
            'closed_at'                   => Yii::t('skeeks/shop/app', 'Дата отмены'),
            'reason_closed'               => Yii::t('skeeks/shop/app', 'Причина отмены'),
            'amount'                      => Yii::t('skeeks/shop/app', 'Сумма'),
            'discount_amount'             => Yii::t('skeeks/shop/app', 'Скидка'),
            'discount_value'              => Yii::t('skeeks/shop/app', 'Скидка'),
            'discount_name'               => Yii::t('skeeks/shop/app', 'Название скидки'),
            'currency_code'               => Yii::t('skeeks/shop/app', 'Currency Code'),
            'company_name'                => 'Компания на момент выставления счета',
            'shop_pay_system_name'        => 'Способ оплаты на момент выставления счета',
            'sender_contractor_name'      => 'Плательщик на момент выставления счета',
            'receiver_contractor_name'    => 'Получатель на момент выставления счета',
            'receiver_bank_name'          => 'Банк получателя на момент выставления счета',
            'description'                 => Yii::t('skeeks/shop/app', 'Основание или комментарий к счету'),
            'code'                        => Yii::t('skeeks/shop/app', 'Уникальный код счета'),
            'cms_company_id'              => "Компания",
            'sender_contractor_id'        => "Плательщик",
            'receiver_contractor_id'      => "Получатель",
            'receiver_contractor_bank_id' => "Банк получатель",
            'external_id'                 => Yii::t('skeeks/shop/app', 'Идентификатор внешней системы'),
            'deals'                       => "Сделки",
        ]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCurrencyCode()
    {
        return $this->hasOne(MoneyCurrency::class, ['code' => 'currency_code']);
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCmsUser()
    {
        $userClass = \Yii::$app->user->identityClass;
        return $this->hasOne($userClass, ['id' => 'cms_user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopOrder()
    {
        return $this->hasOne(ShopOrder::class, ['id' => 'shop_order_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopPayment()
    {
        return $this->hasOne(ShopPayment::class, ['id' => 'shop_payment_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopPaySystem()
    {
        return $this->hasOne(ShopPaySystem::class, ['id' => 'shop_pay_system_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCompany()
    {
        return $this->hasOne(CmsCompany::class, ['id' => 'cms_company_id']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSenderContractor()
    {
        return $this->hasOne(CmsContractor::class, ['id' => 'sender_contractor_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getReceiverContractor()
    {
        return $this->hasOne(CmsContractor::class, ['id' => 'receiver_contractor_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getReceiverContractorBank()
    {
        return $this->hasOne(CmsContractorBank::class, ['id' => 'receiver_contractor_bank_id']);
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

    public function getBillCompanyName()
    {
        return $this->snapshotValue('company_name', 'company', 'name');
    }

    public function getBillPaySystemName()
    {
        return $this->snapshotValue('shop_pay_system_name', 'shopPaySystem', 'name');
    }

    public function getBillSenderName()
    {
        return $this->snapshotValue('sender_contractor_name', 'senderContractor', 'asShortText');
    }

    public function getBillSenderInn()
    {
        return $this->snapshotValue('sender_contractor_inn', 'senderContractor', 'inn');
    }

    public function getBillSenderKpp()
    {
        return $this->snapshotValue('sender_contractor_kpp', 'senderContractor', 'kpp');
    }

    public function getBillSenderOgrn()
    {
        return $this->snapshotValue('sender_contractor_ogrn', 'senderContractor', 'ogrn');
    }

    public function getBillSenderAddress()
    {
        return $this->snapshotValue('sender_contractor_address', 'senderContractor', 'address');
    }

    public function getBillSenderPostcode()
    {
        return $this->snapshotValue('sender_contractor_mailing_postcode', 'senderContractor', 'mailing_postcode');
    }

    public function getBillReceiverName()
    {
        return $this->snapshotValue('receiver_contractor_name', 'receiverContractor', 'asShortText');
    }

    public function getBillReceiverFullName()
    {
        $value = $this->snapshotValue('receiver_contractor_full_name', 'receiverContractor', 'full_name');
        return $value ?: $this->billReceiverName;
    }

    public function getBillReceiverInn()
    {
        return $this->snapshotValue('receiver_contractor_inn', 'receiverContractor', 'inn');
    }

    public function getBillReceiverKpp()
    {
        return $this->snapshotValue('receiver_contractor_kpp', 'receiverContractor', 'kpp');
    }

    public function getBillReceiverOgrn()
    {
        return $this->snapshotValue('receiver_contractor_ogrn', 'receiverContractor', 'ogrn');
    }

    public function getBillReceiverType()
    {
        return $this->snapshotValue('receiver_contractor_type', 'receiverContractor', 'contractor_type');
    }

    public function getBillReceiverAddress()
    {
        return $this->snapshotValue('receiver_contractor_address', 'receiverContractor', 'address');
    }

    public function getBillReceiverPostcode()
    {
        return $this->snapshotValue('receiver_contractor_mailing_postcode', 'receiverContractor', 'mailing_postcode');
    }

    public function getBillReceiverBankName()
    {
        return $this->snapshotValue('receiver_bank_name', 'receiverContractorBank', 'bank_name');
    }

    public function getBillReceiverBankBic()
    {
        return $this->snapshotValue('receiver_bank_bic', 'receiverContractorBank', 'bic');
    }

    public function getBillReceiverBankCorrespondentAccount()
    {
        return $this->snapshotValue('receiver_bank_correspondent_account', 'receiverContractorBank', 'correspondent_account');
    }

    public function getBillReceiverBankCheckingAccount()
    {
        return $this->snapshotValue('receiver_bank_checking_account', 'receiverContractorBank', 'checking_account');
    }

    public function getBillReceiverBankAddress()
    {
        return $this->snapshotValue('receiver_bank_address', 'receiverContractorBank', 'bank_address');
    }

    public function getHasBillReceiverBankData()
    {
        return (bool)($this->billReceiverBankName || $this->billReceiverBankBic || $this->billReceiverBankCheckingAccount);
    }

    public function getBillReceiverOgrnLabel()
    {
        if ($this->billReceiverType == CmsContractor::TYPE_INDIVIDUAL) {
            return 'ОГРНИП';
        }

        if ($this->billReceiverType == CmsContractor::TYPE_LEGAL) {
            return 'ОГРН';
        }

        return 'ОГРН';
    }


    /**
     * @return Money
     */
    public function getMoney()
    {
        return new Money($this->amount, (string)$this->currency_code);
    }

    /**
     * @param bool $scheme
     * @return string
     */
    public function getUrl($scheme = false)
    {
        return Url::to(['/shop/shop-bill/view', 'code' => $this->code], $scheme);
    }
    /**
     * @param bool $scheme
     * @return string
     */
    public function getPayUrl($scheme = false)
    {
        return Url::to(['/shop/shop-bill/go', 'code' => $this->code], $scheme);
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBill2payments()
    {
        return $this->hasMany(ShopBill2payment::className(), ['shop_payment_id' => 'id']);
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPayments()
    {
        return $this->hasMany(ShopPayment::class, ['id' => 'shop_payment_id'])
            ->viaTable(ShopBill2payment::tableName(), ['shop_bill_id' => 'id'])
            ->orderBy(['created_at' => SORT_ASC]);
    }

    public function getDocument2bills()
    {
        return $this->hasMany(ShopDocument2bill::className(), ['shop_bill_id' => 'id']);
    }

    public function getDocuments()
    {
        return $this->hasMany(ShopDocument::class, ['id' => 'shop_document_id'])
            ->viaTable(ShopDocument2bill::tableName(), ['shop_bill_id' => 'id'])
            ->orderBy([ShopDocument::tableName().'.issued_at' => SORT_ASC, ShopDocument::tableName().'.id' => SORT_ASC]);
    }

    public function getClosingDocuments()
    {
        return $this->getDocuments()
            ->andWhere([ShopDocument::tableName().'.type' => ShopDocument::closingTypes()])
            ->andWhere(['<>', ShopDocument::tableName().'.status', ShopDocument::STATUS_CANCELED]);
    }

    public function getSourceDocumentItems()
    {
        return $this->hasMany(ShopDocumentItem::class, ['source_shop_bill_id' => 'id'])
            ->orderBy([ShopDocumentItem::tableName().'.sort' => SORT_ASC, ShopDocumentItem::tableName().'.id' => SORT_ASC]);
    }

    public function getClosingDocumentItems()
    {
        return $this->getSourceDocumentItems()
            ->joinWith('document')
            ->andWhere([ShopDocument::tableName().'.type' => ShopDocument::closingTypes()])
            ->andWhere(['<>', ShopDocument::tableName().'.status', ShopDocument::STATUS_CANCELED]);
    }

    public function getDocumentedAmount()
    {
        $amount = 0;
        foreach ($this->closingDocuments as $document) {
            $amount += $this->closingDocumentAmountForBill($document);
        }

        return round($amount, 4);
    }

    protected function closingDocumentAmountForBill(ShopDocument $document)
    {
        $sourceAmount = 0;
        $hasAnySourceItems = false;
        $hasSourceItems = false;

        foreach ($document->documentItems as $item) {
            if ((int)$item->source_shop_bill_id) {
                $hasAnySourceItems = true;
            }

            if ((int)$item->source_shop_bill_id == (int)$this->id) {
                $hasSourceItems = true;
                $sourceAmount += (float)$item->amount;
            }
        }

        if ($hasSourceItems) {
            return $sourceAmount;
        }

        if ($hasAnySourceItems) {
            return 0;
        }

        return (float)$document->amount;
    }

    public function getDocumentBalanceAmount()
    {
        return round(max((float)$this->amount - $this->documentedAmount, 0), 4);
    }

    public function getIsClosedByDocuments()
    {
        return $this->documentBalanceAmount <= 0.009;
    }

    public function getDocumentedMoney()
    {
        return new Money($this->documentedAmount, (string)$this->currency_code);
    }

    public function getDocumentBalanceMoney()
    {
        return new Money($this->documentBalanceAmount, (string)$this->currency_code);
    }

    public function getBillItems()
    {
        return $this->hasMany(ShopBillItem::class, ['shop_bill_id' => 'id'])
            ->orderBy(['sort' => SORT_ASC, 'id' => SORT_ASC]);
    }

    public function getPrintableBillItems()
    {
        $items = $this->billItems;
        if ($items) {
            return $items;
        }

        $item = new ShopBillItem();
        $item->name = $this->description ?: $this->asText();
        $item->quantity = 1;
        $item->measure_name = 'шт';
        $item->price = $this->amount;
        $item->amount = $this->amount;
        $item->discount_amount = 0;
        $item->currency_code = $this->currency_code;
        $item->vat_name = 'Без НДС';

        return [$item];
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDeals()
    {
        return $this->hasMany(CmsDeal::class, ['id' => 'cms_deal_id'])
            ->viaTable(CmsDeal2bill::tableName(), ['shop_bill_id' => 'id']);
    }


    /**
     * @return ShopBillQuery|\skeeks\cms\query\CmsActiveQuery
     */
    public static function find()
    {
        return (new ShopBillQuery(get_called_class()));
    }

    public function asText()
    {
        return "Счет №{$this->id} от ".\Yii::$app->formatter->asDate($this->created_at);
    }

    public function getAsFullText()
    {
        return "Счет №{$this->id} от ".\Yii::$app->formatter->asDate($this->created_at)." ({$this->description})";
    }
}
