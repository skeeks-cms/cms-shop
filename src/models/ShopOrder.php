<?php

namespace skeeks\cms\shop\models;

use skeeks\cms\models\CmsContentElement;
use skeeks\cms\models\CmsUser;
use skeeks\cms\money\models\MoneyCurrency;
use skeeks\cms\money\Money;
use skeeks\cms\shop\delivery\DeliveryCheckoutModel;
use skeeks\cms\shop\helpers\ProductPriceHelper;
use skeeks\cms\shop\models\queries\ShopOrderQuery;
use skeeks\cms\shop\Module;
use yii\base\Event;
use yii\base\Model;
use yii\base\ModelEvent;
use yii\db\AfterSaveEvent;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\helpers\Url;

/**
 * This is the model class for table "{{%shop_order}}".
 *
 * @property integer                    $id
 * @property integer                    $created_by
 * @property integer                    $updated_by
 * @property integer                    $created_at
 * @property integer                    $updated_at
 * @property integer                    $cms_site_id
 * @property integer                    $shop_person_type_id
 * @property integer                    $paid_at
 * @property integer                    $shop_buyer_id
 * @property string                     $status_code
 * @property integer                    $status_at
 * @property string                     $delivery_amount
 * @property string                     $is_allowed_payment
 * @property string                     $amount
 * @property string                     $currency_code
 * @property string                     $discount_amount
 * @property integer                    $shop_pay_system_id
 * @property integer                    $shop_delivery_id
 * @property string                     $tax_amount
 * @property string                     $paid_amount
 * @property integer                    $shop_order_status_id
 * @property string                     $external_id
 * @property string                     $code
 * @property boolean                    $is_created Заказ создан? Если заказ не создан он связан с корзиной пользователя.
 * @property string                     $delivery_handler_data_jsoned
 *
 * ***
 *
 * @property ShopPaySystem              $shopPaySystem
 * @property ShopPersonType             $shopPersonType
 * @property ShopBuyer                  $shopBuyer
 * @property ShopDelivery               $shopDelivery
 * @property CmsSite                    $cmsSite
 * @property ShopAffiliate              $shopAffiliate
 * @property ShopOrderItem[]            $shopOrderItems
 * @property ShopOrderStatus            $shopOrderStatus
 *
 * @property ShopUser                   $shopCart
 * @property CmsUser|null               $cmsUser
 *
 * @property CmsContentElement          $store
 * @property Currency                   $currency
 *
 * @property ShopOrderLog[]             $shopOrderLogs
 * @property ShopOrderLog[]             $shopOrderStatusLogs
 * @property ShopOrderLog               $lastStatusLog
 *
 *
 * @property ShopOrder2discountCoupon[] $shopOrder2discountCoupons
 * @property ShopDiscountCoupon[]       $shopDiscountCoupons
 *
 * @property ShopTypePrice[]            $buyTypePrices
 *
 *
 * @property Money                      $money Итоговая цена к оплате
 * @property Money                      $moneyDelivery Цена доставки
 * @property Money                      $moneyVat Цена налога
 * @property Money                      $moneyDiscount Цена скидки
 * @property Money                      $moneyItems Цена всех товаров без скидки
 * @property Money                      $moneyPaid
 *
 * @property Money                      $calcMoney          Итоговая цена к оплате
 * @property Money                      $calcMoneyDelivery  Цена доставки
 * @property Money                      $calcMoneyVat  Цена налога
 * @property Money                      $calcMoneyDiscount  Цена скидки
 * @property Money                      $calcMoneyItems     Сумма всех позиций корзины
 *
 * @property int                        $weight
 * @property string                     $weightFormatted
 *
 *
 * @property string                     $email read-only
 * @property string                     $payUrl read-only ссылка на оплату
 * @property string                     $url read-only ссылка на заказ
 *
 * @property int                        $countShopOrderItems
 * @property array                      $deliveryHandlerData
 * @property DeliveryCheckoutModel      $deliveryHandlerCheckoutModel
 */
class ShopOrder extends \skeeks\cms\models\Core
{
    const EVENT_AFTER_RECALCULATE = 'afterRecalculate';

    protected $_email = null;


    /**
     * Уведомить по email о смене статуса?
     * @var bool
     */
    public $isNotifyChangeStatus = true;

    /**
     * Сообщение привязанное к смене статуса
     * @var string
     */
    public $statusComment = "";


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%shop_order}}';
    }


    public function notifyNew()
    {
        if ($this->email) {
            \Yii::$app->mailer->view->theme->pathMap['@app/mail'][] = '@skeeks/cms/shop/mail';

            \Yii::$app->mailer->compose('create-order', [
                'order' => $this,
            ])
                ->setFrom([\Yii::$app->cms->adminEmail => \Yii::$app->cms->appName.''])
                ->setTo($this->email)
                ->setSubject(\Yii::t('skeeks/shop/app',
                        'New order').' №'.$this->id)
                ->send();
        }
    }
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        //$this->on(self::EVENT_AFTER_UPDATE,    [$this, "afterUpdateCallback"]);
        $this->on(self::EVENT_AFTER_UPDATE, [$this, "_afterUpdateCallback"]);
        $this->on(self::EVENT_BEFORE_UPDATE, [$this, "_beforeUpdateCallback"]);


    }
    public function _afterUpdateCallback(AfterSaveEvent $event)
    {
        //Заказ создан
        if (in_array("is_created", array_keys($event->changedAttributes)) && $this->is_created) {

            \Yii::info($this->id." is_created!", self::class);

            (new ShopOrderLog([
                'action_type'   => ShopOrderLog::TYPE_ORDER_ADDED,
                'shop_order_id' => $this->id,
            ]))->save();

            try {
                //Notify admins
                if ($emails = $this->cmsSite->shopSite->notifyEmails) {

                    \Yii::$app->mailer->view->theme->pathMap['@app/mail'][] = '@skeeks/cms/shop/mail';

                    \Yii::$app->mailer->compose('create-order', [
                        'order' => $this,
                    ])
                        ->setFrom([\Yii::$app->cms->adminEmail => \Yii::$app->name.''])
                        ->setTo($emails)
                        ->setSubject(\Yii::t('skeeks/shop/app', 'New order').' №'.$this->id)
                        ->send();
                }
            } catch (\Exception $e) {
                \Yii::error("Email seinding error: ".$e->getMessage(), self::class);
            }

            try {
                //Письмо тому кто заказывает
                if ($this->email) {
                    $this->email = trim($this->email);
                    $this->notifyNew();
                }
            } catch (\Exception $e) {
                \Yii::error("Email client seinding error '{$this->email}': ".$e->getMessage(), self::class);
            }

        }


        if (in_array("paid_at", array_keys($event->changedAttributes)) && $this->paid_at) {


            \Yii::info(print_r($this->toArray(), true), self::class);


            (new ShopOrderLog([
                'action_type'   => ShopOrderLog::TYPE_ORDER_PAYED,
                'shop_order_id' => $this->id,
            ]))->save();


            //Если в базе есть статус, который должен быть установлен после оплаты заказа, то нужно его установить.
            if ($shopOrderStatus = ShopOrderStatus::find()->where(['is_install_after_pay' => 1])->one()) {
                $this->shop_order_status_id = $shopOrderStatus->id;
                if (!$this->save()) {
                    \Yii::error('Статус заказа после оплаты не обновлен: '.$e->getMessage(), self::class);
                }
            } else {
                //Уведомить клиента об оплате
                if ($this->email) {
                    try {
                        \Yii::$app->mailer->view->theme->pathMap['@app/mail'][] = '@skeeks/cms/shop/mail/order';

                        \Yii::$app->mailer->compose('payed', [
                            'order' => $this,
                        ])
                            ->setFrom([\Yii::$app->cms->adminEmail => \Yii::$app->cms->appName.''])
                            ->setTo($this->email)
                            ->setSubject(\Yii::t('skeeks/shop/app', 'Заказ').' №'.$this->id." — Оплачен")
                            ->send();

                    } catch (\Exception $e) {
                        \Yii::error('Ошибка отправки email: '.$e->getMessage(), self::class);
                    }
                }

                //Уведомить администраторов об оплате
                if ($emails = $this->cmsSite->shopSite->notifyEmails) {
                    try {
                        \Yii::$app->mailer->view->theme->pathMap['@app/mail'][] = '@skeeks/cms/shop/mail/order';

                        \Yii::$app->mailer->compose('payed', [
                            'order' => $this,
                        ])
                            ->setFrom([\Yii::$app->cms->adminEmail => \Yii::$app->name.''])
                            ->setTo($emails)
                            ->setSubject(\Yii::t('skeeks/shop/app', 'Заказ').' №'.$this->id." — Оплачен")
                            ->send();

                    } catch (\Exception $e) {
                        \Yii::error('Ошибка отправки email: '.$e->getMessage(), self::class);
                    }
                }
            }
        }
    }

    public function _beforeUpdateCallback(ModelEvent $e)
    {
        //После создания заказа делать его пересчет
        if ($this->isAttributeChanged('is_created') && $this->is_created) {
            $this->created_at = time();
            $this->recalculate();
        }
        if ($this->isAttributeChanged('shop_delivery_id') && !$this->isAttributeChanged('delivery_amount')) {
            $this->delivery_amount = $this->calcMoneyDelivery->amount;
            $this->recalculate();
        }

        if ($this->isAttributeChanged('delivery_handler_data_jsoned')) {
            $this->recalculate();
        }

        if ($this->isAttributeChanged('delivery_amount')) {
            $this->recalculate();
        }

        if ($this->isAttributeChanged('shop_pay_system_id')) {
            $this->recalculate();
        }

        if ($this->isAttributeChanged('shop_order_status_id')) {
            $this->status_at = time();

            (new ShopOrderLog([
                'action_type'   => ShopOrderLog::TYPE_ORDER_STATUS_CHANGED,
                'shop_order_id' => $this->id,
                'action_data'   => [
                    'status'    => $this->shopOrderStatus->name,
                    'status_id' => $this->shopOrderStatus->id,
                ],
                'comment'       => $this->statusComment,
            ]))->save();


            //Письмо тому кто заказывает
            if ($this->email && $this->isNotifyChangeStatus) {
                try {
                    \Yii::$app->mailer->view->theme->pathMap['@app/mail'][] = '@skeeks/cms/shop/mail';

                    \Yii::$app->mailer->compose('order-status-change', [
                        'order' => $this,
                    ])
                        ->setFrom([\Yii::$app->cms->adminEmail => \Yii::$app->cms->appName.''])
                        ->setTo($this->email)
                        ->setSubject("Заказ №".$this->id." — ".$this->shopOrderStatus->name)
                        ->send();

                } catch (\Exception $e) {
                    \Yii::error('Ошибка отправки email: '.$e->getMessage(), Module::class);
                }
            }

            try {
                //Notify admins
                if ($emails = $this->cmsSite->shopSite->notifyEmails) {

                    \Yii::$app->mailer->view->theme->pathMap['@app/mail'][] = '@skeeks/cms/shop/mail';

                    \Yii::$app->mailer->compose('order-status-change', [
                        'order' => $this,
                    ])
                        ->setFrom([\Yii::$app->cms->adminEmail => \Yii::$app->name.''])
                        ->setTo($emails)
                        ->setSubject("Заказ №".$this->id." — ".$this->shopOrderStatus->name)
                        ->send();
                }
            } catch (\Exception $e) {
                \Yii::error("Email seinding error: ".$e->getMessage(), self::class);
            }
        }


    }
    /**
     * @inheritdoc
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
                    'shop_person_type_id',
                    'paid_at',
                    'status_at',
                    'shop_pay_system_id',
                    'shop_order_status_id',
                ],
                'integer',
            ],
            [
                [
                    'is_created',
                ],
                'boolean',
            ],
            [
                [
                    'shop_pay_system_id',
                ],
                'required',
                'when' => function () {
                    return $this->is_created && ShopPaySystem::find()->active()->cmsSite()->exists();
                },
            ],
            /*[
                [
                    'shop_delivery_id',
                ],
                'required',
                'when' => function () {
                    return $this->is_created && ShopDelivery::find()->active()->cmsSite()->exists();
                },
            ],*/

            [
                ['shop_delivery_id'],
                'default',
                'value' => function () {
                    $shopDelivery = ShopDelivery::find()->orderBy(['priority' => SORT_ASC])->active()->cmsSite()->one();
                    if ($shopDelivery) {
                        return $shopDelivery->id;
                    }
                },
            ],

            [
                ['shop_pay_system_id',],
                'default',
                'value' => function () {
                    $shopPaySystem = ShopPaySystem::find()->orderBy(['priority' => SORT_ASC])->active()->cmsSite()->one();
                    if ($shopPaySystem) {
                        return $shopPaySystem->id;
                    }
                },
            ],


            [['delivery_amount', 'amount', 'discount_amount', 'tax_amount', 'paid_amount'], 'number'],
            [['shop_buyer_id'], 'integer'],
            [['cms_site_id'], 'integer'],

            [['currency_code'], 'string', 'max' => 3],
            [['shop_delivery_id'], 'integer'],

            [['status_at'], 'default', 'value' => \Yii::$app->formatter->asTimestamp(time())],
            [['currency_code'], 'default', 'value' => \Yii::$app->money->currencyCode],
            [['cms_site_id'], 'default', 'value' => \Yii::$app->skeeks->site->id],


            [['code'], 'string'],
            [['code'], 'default', 'value' => \Yii::$app->security->generateRandomString()],

            [['delivery_handler_data_jsoned'], 'string'],
            [['delivery_handler_data_jsoned'], 'default', 'value' => null],
            /*[['delivery_handler_data_jsoned'], 'required', 'when' => function () {
                return (bool) $this->deliveryHandlerCheckoutModel;
            }],*/
            [
                ['delivery_handler_data_jsoned'],
                function () {
                    if (!$this->shopDelivery) {
                        $this->delivery_handler_data_jsoned = null;
                        return true;
                    }

                    if (!$this->shopDelivery->handler) {
                        $this->delivery_handler_data_jsoned = null;
                        return true;
                    }
                },
            ],

            [['is_created'], 'default', 'value' => 0],
            [
                ['shop_order_status_id'],
                'default',
                'value' => function () {
                    $shopOrder = ShopOrderStatus::find()->orderBy(['priority' => SORT_ASC])->one();
                    if ($shopOrder) {
                        return $shopOrder->id;
                    }
                },
            ],


            [
                ['shop_person_type_id'],
                'default',
                'value' => function () {
                    $shopPersonType = \Yii::$app->shop->shopPersonTypes[0];
                    return $shopPersonType->id;
                },
            ],

            [
                'isNotifyChangeStatus',
                'boolean',
            ],

            ['statusComment', 'string'],
            [
                'statusComment',
                'required',
                'when' => function () {
                    if ($this->shopOrderStatus) {
                        if ($this->shopOrderStatus->is_comment_required) {
                            return true;
                        }
                    }

                    return false;
                },
            ],

            [['external_id'], 'default', 'value' => null],
            [['external_id'], 'string'],

            [
                ['cms_site_id', 'external_id'],
                'unique',
                'targetAttribute' => ['cms_site_id', 'external_id'],
                'when'            => function (self $model) {
                    return (bool)$model->external_id;
                },
            ],


        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'                   => \Yii::t('skeeks/shop/app', 'ID'),
            'created_by'           => \Yii::t('skeeks/shop/app', 'Created By'),
            'updated_by'           => \Yii::t('skeeks/shop/app', 'Updated By'),
            'created_at'           => \Yii::t('skeeks/shop/app', 'Created At'),
            'updated_at'           => \Yii::t('skeeks/shop/app', 'Updated At'),
            'cms_site_id'          => \Yii::t('skeeks/shop/app', 'Site'),
            'shop_person_type_id'  => \Yii::t('skeeks/shop/app', 'Person Type ID'),
            'paid_at'              => \Yii::t('skeeks/shop/app', 'Оплачен'),
            'shop_order_status_id' => \Yii::t('skeeks/shop/app', 'Status'),
            'status_at'            => \Yii::t('skeeks/shop/app', 'Status At'),
            'delivery_amount'      => \Yii::t('skeeks/shop/app', 'Price Delivery'),
            'amount'               => \Yii::t('skeeks/shop/app', 'Price'),
            'currency_code'        => \Yii::t('skeeks/shop/app', 'Currency Code'),
            'discount_amount'      => \Yii::t('skeeks/shop/app', 'Discount Value'),
            'shop_delivery_id'     => \Yii::t('skeeks/shop/app', 'Delivery'),
            'tax_amount'           => \Yii::t('skeeks/shop/app', 'Tax Value'),
            'paid_amount'          => \Yii::t('skeeks/shop/app', 'Sum Paid'),
            'shop_buyer_id'        => \Yii::t('skeeks/shop/app', 'Buyer'),
            'isNotifyChangeStatus' => \Yii::t('skeeks/shop/app', 'Отправить email уведомление клиенту?'),
            'statusComment'        => \Yii::t('skeeks/shop/app', 'Комментарий к смене статуса'),
            'external_id'          => "ID из внешней системы",

            'shop_pay_system_id' => \Yii::t('skeeks/shop/app', 'Оплата'),

            'is_created'                   => \Yii::t('skeeks/shop/app', 'Заказ создан?'),
            'delivery_handler_data_jsoned' => "Данные службы доставки",
        ];
    }
    /**
     * @return null|string
     */
    public function getEmail()
    {
        if ($this->_email !== null) {
            return $this->_email;
        }

        if ($this->shopBuyer) {
            if ($this->shopBuyer->email) {
                return $this->shopBuyer->email;
            }
        }

        if ($this->cmsUser && $this->cmsUser->email) {
            return $this->cmsUser->email;
        }

        return null;
    }
    /**
     * @return null|string
     */
    public function setEmail($email)
    {
        $this->_email = $email;
        return $this;
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCurrency()
    {
        return $this->hasOne(MoneyCurrency::class, ['code' => 'currency_code']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopDelivery()
    {
        return $this->hasOne(ShopDelivery::class, ['id' => 'shop_delivery_id']);
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
    public function getShopPersonType()
    {
        return $this->hasOne(ShopPersonType::class, ['id' => 'shop_person_type_id']);
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
    public function getStatus()
    {
        return $this->getShopOrderStatus();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopOrderStatus()
    {
        return $this->hasOne(ShopOrderStatus::class, ['id' => 'shop_order_status_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopBuyer()
    {
        return $this->hasOne(ShopBuyer::class, ['id' => 'shop_buyer_id']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopOrderLogs()
    {
        return $this->hasMany(ShopOrderLog::class,
            ['shop_order_id' => 'id'])->orderBy(['created_at' => SORT_DESC]);
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopOrderStatusLogs()
    {
        return $this->getShopOrderLogs()->andWhere([
            'in',
            'action_type',
            [
                ShopOrderLog::TYPE_ORDER_STATUS_CHANGED,
                ShopOrderLog::TYPE_ORDER_ADDED,
            ],
        ]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLastStatusLog()
    {
        $q = $this->getShopOrderStatusLogs()->limit(1);
        $q->multiple = false;

        return $q;
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopOrder2discountCoupons()
    {
        return $this->hasMany(ShopOrder2discountCoupon::class, ['order_id' => 'id']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopDiscountCoupons()
    {
        return $this->hasMany(ShopDiscountCoupon::class, ['id' => 'discount_coupon_id'])
            ->via('shopOrder2discountCoupons');
    }
    /**
     * Итоговая стоимость заказа с учетом скидок, доставок, наценок, то что будет платить человек
     * @return Money
     */
    public function getMoney()
    {
        return new Money($this->amount, $this->currency_code);
    }
    /**
     * Итоговая стоимость заказа с учетом скидок, доставок, наценок, то что будет платить человек
     * Рассчитанная динамически
     *
     * @return Money
     */
    public function getCalcMoney()
    {
        $money = $this->calcMoneyItems;
        $money->add($this->moneyDelivery);
        $money->sub($this->calcMoneyDiscount);
        return $money;
    }


    /**
     * Цена всех позиций в заказе без скидок
     * Динамически рассчитанная
     *
     * @return Money
     */
    public function getCalcMoneyItems()
    {
        $money = new Money("", $this->currency_code);

        foreach ($this->shopOrderItems as $shopOrderItem) {
            $money = $money->add($shopOrderItem->moneyOriginal->multiply($shopOrderItem->quantity));
        }

        return $money;
    }

    /**
     * @return Money
     */
    public function getMoneyItems()
    {
        $money = new Money("", $this->currency_code);

        $money->add($this->money);
        $money->sub($this->moneyDelivery);
        $money->add($this->moneyDiscount);

        return $money;
    }

    /**
     * Налог
     *
     * @return Money
     */
    public function getMoneyVat()
    {
        return new Money($this->tax_amount, $this->currency_code);
    }
    /**
     * Налог
     *
     * @return Money
     */
    public function getCalcMoneyVat()
    {
        $money = new Money("", $this->currency_code);

        foreach ($this->shopOrderItems as $shopOrderItem) {
            $money = $money->add($shopOrderItem->moneyVat->multiply($shopOrderItem->quantity));
        }

        return $money;
    }


    /**
     * Скидка наценка
     *
     * @return Money
     */
    public function getMoneyDiscount()
    {
        return new Money($this->discount_amount, $this->currency_code);
    }

    /**
     * Налог
     *
     * @return Money
     */
    public function getCalcMoneyDiscount()
    {
        $money = new Money("", $this->currency_code);

        //Товарные скидки
        foreach ($this->shopOrderItems as $shopOrderItem) {
            $money = $money->add($shopOrderItem->moneyDiscount->multiply($shopOrderItem->quantity));
        }

        //Скидка на корзину
        if ($this->shopDiscountCoupons) {
            foreach ($this->shopDiscountCoupons as $shopDiscountCoupon) {
                $shopDiscount = $shopDiscountCoupon->shopDiscount;
                if ($shopDiscountCoupon->shopDiscount->assignment_type == ShopDiscount::ASSIGNMENT_TYPE_CART) {

                    if ($shopDiscount->value_type == ShopDiscount::VALUE_TYPE_F) {
                        $discountMoney = new Money($shopDiscount->value, $shopDiscount->currency_code);
                        $money->add($discountMoney);
                    }

                    if ($shopDiscount->value_type == ShopDiscount::VALUE_TYPE_P) {
                        $moneyOrderItems = $this->calcMoneyItems;
                        $moneyValue = $moneyOrderItems->amount * $shopDiscount->value / 100;
                        $discountMoney = new Money($moneyValue, $moneyOrderItems->currency);
                        $money->add($discountMoney);
                    }

                    if ($shopDiscount->isLast) {
                        break;
                    }

                }
            }
        }


        return $money;
    }


    /**
     * Итоговая стоимость позиции без скидок и наценок
     * Цена товара в момент укладки товара в корзину
     *
     * @return Money
     * @deprecated
     */
    public function getMoneyOriginal()
    {
        return $this->moneyItems;
    }


    /**
     * Уже оплачено по заказу
     *
     * @return Money
     */
    public function getMoneyPaid()
    {
        return new Money($this->paid_amount, $this->currency_code);
    }
    /**
     * @return Money
     * @deprecated
     */
    public function getMoneySummPaid()
    {
        return $this->moneyPaid;
    }

    /**
     * Стоимость доставки
     * @return Money
     */
    public function getMoneyDelivery()
    {
        return new Money($this->delivery_amount, $this->currency_code);
    }

    /**
     * Стоимость доставки
     * @return Money
     */
    public function getCalcMoneyDelivery()
    {
        if ($this->shopDelivery) {
            if ($this->deliveryHandlerCheckoutModel) {
                return $this->deliveryHandlerCheckoutModel->money;
            } else {
                return $this->shopDelivery->money;
            }
        }

        return new Money("", $this->currency_code);
    }

    /**
     * @return string
     */
    public function getWeightFormatted()
    {
        if ($this->weight >= 1000 && $this->weight <= 1000000) {
            return \Yii::$app->formatter->asDecimal(($this->weight / 1000))." кг.";
        } elseif ($this->weight >= 1000000) {
            return \Yii::$app->formatter->asDecimal(($this->weight / 1000000))." т.";
        } else {
            return \Yii::$app->formatter->asDecimal(($this->weight))." г.";
        }
    }

    /**
     * Вес товара в граммах
     *
     * @return int
     */
    public function getWeight()
    {
        $result = 0;

        foreach ($this->shopOrderItems as $shopOrderItem) {
            $result = $result + ($shopOrderItem->weight * $shopOrderItem->quantity);
        }

        return $result;
    }


    /**
     * Доступные платежные системы
     *
     * @return ShopPaySystem[]
     */
    public function getPaySystems()
    {
        $q = $this->shopPersonType->getPaySystems()
            ->andWhere([ShopPaySystem::tableName().".is_active" => 1])
            ->andWhere([ShopPaySystem::tableName().".cms_site_id" => $this->cms_site_id])
            ->orderBy([ShopPaySystem::tableName().".priority" => SORT_ASC]);

        //Если в заказе выбран способ доставки, и у способа доставки заданы способы оплаты, то накладываем доп фильтрацию
        if ($this->shopDelivery) {
            if ($shopDelivery2paySystems = $this->shopDelivery->shopDelivery2paySystems) {
                $ids = ArrayHelper::map($shopDelivery2paySystems, "pay_system_id", "pay_system_id");;
                $q->andWhere([ShopPaySystem::tableName().".id" => $ids]);

            }
        }
        return $q;
    }
    /**
     * @return $this
     */
    public function recalculate()
    {
        $this->tax_amount = $this->calcMoneyVat->amount;
        $this->amount = $this->calcMoney->amount;
        $this->discount_amount = $this->calcMoneyDiscount->amount;

        $this->trigger(self::EVENT_AFTER_RECALCULATE, new Event());

        return $this;
    }
    /**
     * @param array $options
     * @param bool  $scheme
     * @return string
     */
    public function getPayUrl($options = [], $scheme = true)
    {
        return Url::to(ArrayHelper::merge(
            ['/shop/order/pay', 'code' => $this->code],
            $options
        ), $scheme);
    }
    /**
     * @return string
     * @deprecated
     */
    public function getPublicUrl($options = [], $scheme = true)
    {
        return $this->getUrl($options = [], $scheme = true);
    }
    /**
     * @param array $options
     * @param bool  $scheme
     * @return string
     */
    public function getUrl($options = [], $scheme = true)
    {
        return Url::to(ArrayHelper::merge(
            ['/shop/order/finish', 'code' => $this->code],
            $options
        ), $scheme);
    }
    /**
     *
     * Массив для json ответа, используется при обновлении корзины, добавлении позиций и т.д.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return ArrayHelper::merge($this->toArray([], $this->extraFields()), [
            'money'         => ArrayHelper::merge($this->money->jsonSerialize(),
                ['convertAndFormat' => \Yii::$app->money->convertAndFormat($this->money)]),
            'moneyDelivery' => ArrayHelper::merge($this->moneyDelivery->jsonSerialize(),
                ['convertAndFormat' => \Yii::$app->money->convertAndFormat($this->moneyDelivery)]),
            'moneyDiscount' => ArrayHelper::merge($this->moneyDiscount->jsonSerialize(),
                ['convertAndFormat' => \Yii::$app->money->convertAndFormat($this->moneyDiscount)]),
            'moneyOriginal' => ArrayHelper::merge($this->moneyOriginal->jsonSerialize(),
                ['convertAndFormat' => \Yii::$app->money->convertAndFormat($this->moneyOriginal)]),
            'moneyVat'      => ArrayHelper::merge($this->moneyVat->jsonSerialize(), [
                'convertAndFormat' => \Yii::$app->money->convertAndFormat($this->moneyVat),
            ]),
        ]);
    }
    /**
     * @return array
     */
    public function extraFields()
    {
        return [
            'shopOrderItems',
            'quantity',
            'countShopOrderItems',

            'countShopBaskets',
        ];
    }
    /**
     * Количество позиций в корзине
     *
     * @return int
     */
    public function getCountShopOrderItems()
    {
        return count($this->shopOrderItems);
    }
    /**
     * @return float
     */
    public function getQuantity()
    {
        $result = 0;

        if ($this->shopOrderItems) {
            foreach ($this->shopOrderItems as $item) {
                $result = $item->quantity + $result;
            }
        }
        return (float)$result;
    }
    /**
     * @param ShopCmsContentElement $shopCmsContentElement
     * @return ProductPriceHelper
     */
    public function getProductPriceHelper(ShopCmsContentElement $shopCmsContentElement)
    {
        $ids = ArrayHelper::map($this->buyTypePrices, 'id', 'id');
        $minPh = null;

        $minPh = new ProductPriceHelper([
            'shopCmsContentElement' => $shopCmsContentElement,
            'shopOrder'             => $this,
            'price'                 => new ShopProductPrice([
                'currency_code' => \Yii::$app->money->currencyCode,
                'price'         => 0,
            ]),
        ]);;

        if ($shopCmsContentElement->shopProduct && $shopCmsContentElement->shopProduct->shopProductPrices) {
            foreach ($shopCmsContentElement->shopProduct->shopProductPrices as $price) {


                if (in_array($price->type_price_id, $ids)) {

                    $ph = new ProductPriceHelper([
                        'shopCmsContentElement' => $shopCmsContentElement,
                        'shopOrder'             => $this,
                        'price'                 => $price,
                    ]);

                    if ($minPh === null) {
                        $minPh = $ph;
                        continue;
                    }


                    if ((float)$minPh->minMoney->amount == 0) {
                        $minPh = $ph;
                    } elseif ((float)$minPh->minMoney->amount > (float)$ph->minMoney->amount && (float)$ph->minMoney->amount > 0) {
                        $minPh = $ph;
                    }
                }
            }
        }

        return $minPh;
    }
    /**
     *
     * Доступные цены для покупки на сайте
     *
     * @return ShopTypePrice[]
     */
    public function getBuyTypePrices()
    {
        return \Yii::$app->shop->getCanBuyTypePrices($this->cmsUser);
    }
    /**
     * Добавить в заказ еще позиции
     *
     * @param array $items
     * @return $this
     */
    public function addShopOrderItems($items = [])
    {
        /**
         * @var ShopOrderItem[] $items
         * @var ShopOrderItem   $currentBasket
         */
        foreach ($items as $item) {
            //Если в корзине которую необходимо добавить продукт такой же который уже есть у текущего пользователя, то нужно обновить количество.
            if ($currentBasket = $this->getShopOrderItems()->andWhere(['shop_product_id' => $item->shop_product_id])->one()) {
                $currentBasket->quantity = $currentBasket->quantity + $item->quantity;
                $currentBasket->save();

                $item->delete();
            } else {
                $item->shop_order_id = $this->id;
                $item->save();
            }
        }

        return $this;
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopOrderItems()
    {
        return $this->hasMany(ShopOrderItem::class, ['shop_order_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopCart()
    {
        return $this->hasOne(ShopUser::class, ['shop_order_id' => 'id']);
    }

    /**
     * @return null|CmsUser
     */
    public function getCmsUser()
    {
        if ($this->shopBuyer) {
            return $this->shopBuyer->cmsUser;
        }

        if ($this->shopCart) {
            return $this->shopCart->cmsUser;
        }

        return null;
    }


    /**
     * @return int
     * @deprecated
     */
    public function getCountShopBaskets()
    {
        return $this->countShopOrderItems;
    }


    /**
     * @return Money
     * @deprecated
     */
    public function getBasketsMoney()
    {
        return $this->calcMoneyItems;
    }

    /**
     * @return \yii\db\ActiveQuery
     * @deprecated
     */
    public function getPaySystem()
    {
        return $this->getShopPaySystem();
    }

    /**
     * @return \yii\db\ActiveQuery
     * @deprecated
     */
    public function getBuyer()
    {
        return $this->getShopBuyer();
    }
    /**
     * @return \yii\db\ActiveQuery
     * @deprecated
     */
    public function getShopBaskets()
    {
        return $this->getShopOrderItems();
    }

    /**
     * @return \skeeks\cms\query\CmsActiveQuery|ShopOrderQuery
     */
    public static function find()
    {
        return new ShopOrderQuery(get_called_class());
    }


    /**
     * @return array
     */
    public function getDeliveryHandlerData()
    {
        if ($this->delivery_handler_data_jsoned) {
            return Json::decode($this->delivery_handler_data_jsoned);
        }

        return [];
    }


    /**
     * @return Model
     */
    public function getDeliveryHandlerCheckoutModel()
    {
        $model = null;

        if ($this->shopDelivery && $this->shopDelivery->handler) {
            $model = $this->shopDelivery->handler->checkoutModel;
            $model->shopOrder = $this;
            $model->load($this->deliveryHandlerData, "");
        }

        return $model;
    }
}