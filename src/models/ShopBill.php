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
 * @property int               $shop_payment_id Платеж
 * @property int               $closed_at Дата отмены
 * @property string            $reason_closed Причина отмены
 * @property string            $amount
 * @property string            $currency_code
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
 * @property CmsDeal[]         $deals
 *
 * @property string            $url Ссылка на страницу счета
 * @property string            $payUrl Ссылка на страницу оплаты
 *
 * @property Money             $money
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
        parent::init();

        /*$this->on(self::EVENT_AFTER_INSERT, [$this, '_notifyCreate']);
        $this->on(self::EVENT_AFTER_UPDATE, [$this, '_notifyUpdate']);*/
    }

    public function _afterFind($event)
    {
        $this->amount = (float)$this->amount;
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
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'cms_user_id', 'shop_order_id', 'shop_pay_system_id', 'paid_at', 'shop_payment_id', 'closed_at'], 'integer'],
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
            [['amount'], 'number'],
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
            'shop_payment_id'             => Yii::t('skeeks/shop/app', 'Платеж'),
            'closed_at'                   => Yii::t('skeeks/shop/app', 'Дата отмены'),
            'reason_closed'               => Yii::t('skeeks/shop/app', 'Причина отмены'),
            'amount'                      => Yii::t('skeeks/shop/app', 'Сумма'),
            'currency_code'               => Yii::t('skeeks/shop/app', 'Currency Code'),
            'description'                 => Yii::t('skeeks/shop/app', 'Description'),
            'code'                        => Yii::t('skeeks/shop/app', 'Уникальный код счета'),
            'cms_company_id'              => "Компания",
            'sender_contractor_id'        => "Отправитель",
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