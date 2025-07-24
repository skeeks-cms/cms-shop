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
use skeeks\cms\models\CmsDeal2payment;
use skeeks\cms\models\CmsUser;
use skeeks\cms\money\models\MoneyCurrency;
use skeeks\cms\money\Money;
use skeeks\cms\shop\models\queries\ShopPaymentQuery;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%shop_payment}}".
 *
 * @property int               $id
 * @property int               $created_by
 * @property int               $updated_by
 * @property int               $created_at
 * @property int               $updated_at
 *
 * @property integer           $cms_site_id
 * @property int|null          $cms_user_id Покупатель
 * @property int|null          $cms_company_id Компания
 * @property int|null          $sender_contractor_id Контрагент отправитель
 * @property int|null          $receiver_contractor_id Контрагент получатель
 * @property int|null          $receiver_contractor_bank_id Банк получатель
 * @property int|null          $sender_contractor_bank_id Банк отправитель
 *
 * @property int               $shop_order_id Заказ
 * @property int               $shop_check_id Чек
 * @property int|null          $shop_pay_system_id Платежная система
 *
 * @property int               $is_debit Дебет? (иначе кредит)
 *
 * @property string            $amount
 * @property string            $currency_code
 *
 * @property string            $comment комментарий к платежу
 *
 * @property int|null          $shop_store_id оплата в магазине
 * @property int|null          $shop_cashebox_shift_id Смена
 * @property int|null          $shop_cashebox_id Касса
 *
 * @property string|null       $shop_store_payment_type тип оплаты в магазине
 * @property string            $external_name
 * @property string            $external_id
 * @property string            $external_data
 *
 * //@property string            $t_name
 * //@property string            $t_id
 * //@property string            $t_data
 *
 * @property string            $shopStorePaymentTypeAsText
 * @property CmsUser           $cmsUser
 * @property ShopCheck         $shopCheck
 * @property ShopStore         $shopStore
 * @property ShopCashebox      $shopCashebox
 * @property ShopCasheboxShift $shopCasheboxShift
 * @property ShopBill[]        $shopBills
 * @property MoneyCurrency     $currencyCode
 * @property ShopBuyer         $shopBuyer
 * @property ShopOrder         $shopOrder
 * @property ShopPaySystem     $shopPaySystem
 * @property Money             $money
 * @property CmsSite           $cmsSite
 *
 * @property CmsCompany        $company
 * @property CmsContractor     $senderContractor
 * @property CmsContractor     $receiverContractor
 * @property CmsContractorBank $receiverContractorBank
 * @property CmsContractorBank $senderContractorBank
 *
 * @property ShopBill[]        $bills
 * @property CmsDeal[]         $deals
 *
 */
class ShopPayment extends \skeeks\cms\base\ActiveRecord
{
    use HasLogTrait;

    const STORE_PAYMENT_TYPE_CASH = 'cash';
    const STORE_PAYMENT_TYPE_CARD = 'card';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%shop_payment}}';
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            RelationalBehavior::class => [
                'class' => RelationalBehavior::class,
                'relationNames' => [
                    'deals',
                    'bills',
                ],
            ],
            HasJsonFieldsBehavior::class => [
                'class'  => HasJsonFieldsBehavior::class,
                'fields' => ['external_data'],
            ],
            CmsLogBehavior::class     => [
                'class' => CmsLogBehavior::class,
                'relation_map' => [
                    'shop_pay_system_id' => 'shopPaySystem',
                ],
            ],
        ]);
    }

    public function init()
    {
        $this->on(static::EVENT_AFTER_FIND, function () {
            $this->amount = (float)$this->amount;
        });

        $this->on(static::EVENT_AFTER_UPDATE, [$this, "_afterSave"]);
        $this->on(static::EVENT_AFTER_INSERT, [$this, "_afterSave"]);

        return parent::init();
    }

    public function _afterSave()
    {
        //Если есть привязанные счета, нужно проверить их статус оплаты
        if ($this->bills) {
            /**
             * @var ShopBill $bill
             */
            foreach ($this->bills as $bill)
            {
                //Если связанный счет еще не оплачен
                if (!$bill->paid_at) {
                    //Берем все платежи по счету и считаем их сумму
                    if ($bill->payments) {
                        $amountPayemnt = 0;
                        $lastPayment = null;
                        foreach ($bill->payments as $payment)
                        {
                            $lastPayment = $payment;
                            $amountPayemnt = $amountPayemnt + $payment->amount;
                        }

                        //Если сумма платежей больше или равна сумме счета, делаем его оплаченным
                        if ($amountPayemnt >= $bill->amount) {
                            $bill->paid_at = $lastPayment->created_at;
                            $bill->update(false, ['paid_at']);
                        }
                    }
                }

            }
        }
    }

    static public function getShopStorePaymentTypes()
    {
        return [
            self::STORE_PAYMENT_TYPE_CASH => 'Наличные',
            self::STORE_PAYMENT_TYPE_CARD => 'Банковской картой',
        ];
    }

    public function getShopStorePaymentTypeAsText()
    {
        return (string)ArrayHelper::getValue(self::getShopStorePaymentTypes(), $this->shop_store_payment_type);
    }


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [
                [
                    'created_by',
                    'updated_by',
                    'created_at',
                    'updated_at',
                    'cms_user_id',
                    'shop_order_id',
                    'shop_check_id',
                    'shop_pay_system_id',
                    'is_debit',
                    'shop_store_id',
                    'shop_cashebox_shift_id',
                    'shop_cashebox_id',
                ],
                'integer',
            ],

            [
                [
                    'cms_company_id',
                    'sender_contractor_id',
                    'receiver_contractor_id',
                    'receiver_contractor_bank_id',
                    'sender_contractor_bank_id',
                ],
                'integer',
            ],
            [['cms_company_id'], 'default', 'value' => null],

            [['shop_store_payment_type'], 'string'],
            //[['shop_order_id'], 'required'],
            [['cms_site_id'], 'integer'],

            [['deals'], 'safe'],
            [['bills'], 'safe'],

            [['shop_store_id'], 'default', 'value' => null],
            [['shop_cashebox_shift_id'], 'default', 'value' => null],
            [['shop_cashebox_id'], 'default', 'value' => null],
            [['shop_store_payment_type'], 'default', 'value' => null],
            [['cms_user_id'], 'default', 'value' => null],
            [['cms_site_id'], 'default', 'value' => \Yii::$app->skeeks->site->id],


            [['amount'], 'number'],
            [['external_data'], 'safe'],
            [['comment'], 'string'],
            [['currency_code'], 'string', 'max' => 3],
            [['external_name', 'external_id'], 'string', 'max' => 255],
            [['currency_code'], 'exist', 'skipOnError' => true, 'targetClass' => MoneyCurrency::class, 'targetAttribute' => ['currency_code' => 'code']],
            [['cms_user_id'], 'exist', 'skipOnError' => true, 'targetClass' => CmsUser::class, 'targetAttribute' => ['cms_user_id' => 'id']],
            [['shop_order_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopOrder::class, 'targetAttribute' => ['shop_order_id' => 'id']],
            [['shop_pay_system_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopPaySystem::class, 'targetAttribute' => ['shop_pay_system_id' => 'id']],

            /*[['t_name'], 'string'],
            [['t_id'], 'string'],
            [['t_data'], 'string'],*/
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'created_at'                          => "Дата",
            'id'                          => Yii::t('skeeks/shop/app', 'ID'),
            'shop_store_id'               => Yii::t('skeeks/shop/app', 'Магазин'),
            'shop_cashebox_shift_id'      => Yii::t('skeeks/shop/app', 'Смена'),
            'shop_cashebox_id'            => Yii::t('skeeks/shop/app', 'Касса'),
            'shop_store_payment_type'     => Yii::t('skeeks/shop/app', 'Тип оплаты в магазине'),
            'cms_user_id'                 => "Клиент",
            'shop_order_id'               => Yii::t('skeeks/shop/app', 'Заказ'),
            'shop_check_id'               => Yii::t('skeeks/shop/app', 'Чек'),
            'shop_pay_system_id'          => Yii::t('skeeks/shop/app', 'Способ оплаты'),
            'is_debit'                    => Yii::t('skeeks/shop/app', 'Поступление?'),
            'amount'                      => Yii::t('skeeks/shop/app', 'Сумма'),
            'currency_code'               => Yii::t('skeeks/shop/app', 'Currency Code'),
            'comment'                     => Yii::t('skeeks/shop/app', 'Comment'),
            'external_name'               => Yii::t('skeeks/shop/app', 'Внешняя система'),
            'external_id'                 => Yii::t('skeeks/shop/app', 'Внешний ID'),
            'external_data'               => Yii::t('skeeks/shop/app', 'Данные из внешней системы'),
            'cms_site_id'                 => \Yii::t('skeeks/shop/app', 'Site'),
            'cms_company_id'              => "Компания",
            'sender_contractor_id'        => "Отправитель",
            'receiver_contractor_id'      => "Получатель",
            'receiver_contractor_bank_id' => "Банк получатель",
            'sender_contractor_bank_id'   => "Банк отправитель",
            'deals'                       => "Сделки",
            'bills'                       => "Счета",
        ]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCmsSite()
    {
        $class = \Yii::$app->skeeks->siteClass;
        return $this->hasOne($class, ['id' => 'cms_site_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopBills()
    {
        return $this->hasMany(ShopBill::class, ['shop_payment_id' => 'id']);
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
    public function getShopStore()
    {
        return $this->hasOne(ShopStore::class, ['id' => 'shop_store_id']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopCheck()
    {
        return $this->hasOne(ShopCheck::class, ['id' => 'shop_check_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopCasheboxShift()
    {
        return $this->hasOne(ShopCasheboxShift::class, ['id' => 'shop_cashebox_shift_id']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopCashebox()
    {
        return $this->hasOne(ShopCashebox::class, ['id' => 'shop_cashebox_id']);
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
    public function getShopPaySystem()
    {
        return $this->hasOne(ShopPaySystem::class, ['id' => 'shop_pay_system_id']);
    }


    /**
     * @return Money
     */
    public function getMoney()
    {
        return new Money($this->amount, (string)$this->currency_code);
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBill2payments()
    {
        return $this->hasMany(ShopBill2payment::className(), ['shop_bill_id' => 'id']);
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBills()
    {
        return $this->hasMany(ShopBill::class, ['id' => 'shop_bill_id'])
            ->viaTable(ShopBill2payment::tableName(), ['shop_payment_id' => 'id']);
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

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSenderContractorBank()
    {
        return $this->hasOne(CmsContractorBank::class, ['id' => 'sender_contractor_bank_id']);
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDeals()
    {
        return $this->hasMany(CmsDeal::class, ['id' => 'cms_deal_id'])
            ->viaTable(CmsDeal2payment::tableName(), ['shop_payment_id' => 'id']);
    }

    /**
     * @return ShopPaymentQuery|\skeeks\cms\query\CmsActiveQuery
     */
    public static function find()
    {
        return (new ShopPaymentQuery(get_called_class()));
    }

    public function asText()
    {
        return "Платеж №{$this->id} от ".\Yii::$app->formatter->asDate($this->created_at);
    }

}