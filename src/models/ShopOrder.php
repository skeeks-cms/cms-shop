<?php

namespace skeeks\cms\shop\models;

use skeeks\cms\components\Cms;
use skeeks\cms\models\CmsContentElement;
use skeeks\cms\models\CmsSite;
use skeeks\cms\models\CmsUser;
use skeeks\cms\money\models\MoneyCurrency;
use skeeks\cms\money\Money;
use skeeks\cms\shop\helpers\ProductPriceHelper;
use skeeks\cms\shop\Module;
use yii\base\Event;
use yii\base\ModelEvent;
use yii\db\AfterSaveEvent;
use yii\helpers\ArrayHelper;
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
 * @property integer                    $canceled_at
 * @property string                     $reason_canceled
 * @property string                     $status_code
 * @property integer                    $status_at
 * @property string                     $delivery_amount
 * @property string                     $allow_delivery
 * @property string                     $is_allowed_payment
 * @property integer                    $allow_delivery_at
 * @property string                     $amount
 * @property string                     $currency_code
 * @property string                     $discount_amount
 * @property integer                    $shop_pay_system_id
 * @property integer                    $shop_delivery_id
 * @property string                     $user_description
 * @property string                     $additional_info
 * @property string                     $comments
 * @property string                     $tax_amount
 * @property string                     $paid_amount
 * @property integer                    $locked_by
 * @property integer                    $locked_at
 * @property integer                    $shop_affiliate_id
 * @property string                     $delivery_doc_num
 * @property integer                    $delivery_doc_at
 * @property string                     $tracking_number
 * @property integer                    $shop_order_status_id
 * @property string                     $code
 * @property boolean                    $is_created Заказ создан? Если заказ не создан он связан с корзиной пользователя.
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
 * @property ShopCart                   $shopCart
 * @property CmsUser|null               $cmsUser
 *
 * @property CmsContentElement          $store
 * @property Currency                   $currency
 * @property CmsUser                    $lockedBy
 *
 * @property ShopOrderChange[]          $shopOrderChanges
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
 *
 *
 * @property string                     $email read-only
 * @property string                     $payUrl read-only ссылка на оплату
 * @property string                     $url read-only ссылка на заказ
 *
 * @property int                        $countShopOrderItems
 */
class ShopOrder extends \skeeks\cms\models\Core
{
    const EVENT_AFTER_RECALCULATE = 'afterRecalculate';

    protected $_email = null;
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
                ->setSubject(\Yii::$app->cms->appName.': '.\Yii::t('skeeks/shop/app',
                        'New order').' #'.$this->id)
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
        $this->on(self::EVENT_AFTER_UPDATE,    [$this, "_afterUpdateCallback"]);
        $this->on(self::EVENT_BEFORE_UPDATE, [$this, "_beforeUpdateCallback"]);


    }
    public function _afterUpdateCallback(AfterSaveEvent $event)
    {
        //Заказ создан
        if (in_array("is_created", array_keys($event->changedAttributes)) && $this->is_created) {

            \Yii::info($this->id . " is_created!", self::class);
            
            (new ShopOrderChange([
                'type'          => ShopOrderChange::ORDER_ADDED,
                'shop_order_id' => $this->id,
            ]))->save();

            try {
                //Notify admins
                if (\Yii::$app->shop->notifyEmails) {
                    foreach (\Yii::$app->shop->notifyEmails as $email) {

                        \Yii::$app->mailer->view->theme->pathMap['@app/mail'][] = '@skeeks/cms/shop/mail';

                        \Yii::$app->mailer->compose('create-order', [
                            'order' => $this,
                        ])
                            ->setFrom([\Yii::$app->cms->adminEmail => \Yii::$app->cms->appName.''])
                            ->setTo($email)
                            ->setSubject(\Yii::t('skeeks/shop/app',
                                    'New order').' #'.$this->id)
                            ->send();
                    }
                }
            } catch (\Exception $e) {
                \Yii::error("Email seinding error: " . $e->getMessage(), self::class);
            }
            
            try {
                //Письмо тому кто заказывает
                if ($this->email) {
                    $this->email = trim($this->email);
                    $this->notifyNew();
                }
            } catch (\Exception $e) {
                \Yii::error("Email client seinding error '{$this->email}': " . $e->getMessage(), self::class);
            }

        }
    }

    public function _beforeUpdateCallback(ModelEvent $e)
    {
        /*var_dump($this->is_created);
        var_dump($this->isAttributeChanged('is_created'));
        die;*/
        //После создания заказа делать его пересчет
        if ($this->isAttributeChanged('is_created') && $this->is_created) {
            $this->recalculate();
        }
        if ($this->isAttributeChanged('shop_delivery_id')) {
            $this->recalculate();
        }
        if ($this->isAttributeChanged('shop_pay_system_id')) {
            $this->recalculate();
        }

        if ($this->isAttributeChanged('shop_order_status_id')) {
            $this->status_at = \Yii::$app->formatter->asTimestamp(time());

            (new ShopOrderChange([
                'type'          => ShopOrderChange::ORDER_STATUS_CHANGED,
                'shop_order_id' => $this->id,
                'data'          =>
                [
                    'status' => $this->shopOrderStatus->name,
                ],
            ]))->save();


            //Письмо тому кто заказывает
            if ($this->email) {
                try {
                    \Yii::$app->mailer->view->theme->pathMap['@app/mail'][] = '@skeeks/cms/shop/mail';

                    \Yii::$app->mailer->compose('order-status-change', [
                        'order' => $this,
                    ])
                        ->setFrom([\Yii::$app->cms->adminEmail => \Yii::$app->cms->appName.''])
                        ->setTo($this->email)
                        ->setSubject(\Yii::t('skeeks/shop/app',
                                'Change order status').' #'.$this->id)
                        ->send();

                } catch (\Exception $e) {
                    \Yii::error('Ошибка отправки email: '.$e->getMessage(), Module::class);
                }

            }
        }

        if ($this->isAttributeChanged('paid_at') && $this->paid_at) {

            \Yii::info(print_r($this->toArray(), true), self::class);

            (new ShopOrderChange([
                'type'          => ShopOrderChange::ORDER_PAYED,
                'shop_order_id' => $this->id,
            ]))->save();


            $emails = \Yii::$app->shop->notifyEmails;
            if ($this->email) {
                $emails[] = $this->email;
            }

            if ($emails) {
                foreach ($emails as $email) {
                    try {
                        \Yii::$app->mailer->view->theme->pathMap['@app/mail'][] = '@skeeks/cms/shop/mail/order';

                        \Yii::$app->mailer->compose('payed', [
                            'order' => $this,
                        ])
                            ->setFrom([\Yii::$app->cms->adminEmail => \Yii::$app->cms->appName.''])
                            ->setTo($email)
                            ->setSubject(\Yii::t('skeeks/shop/app',
                                    'Order successfully paid').' №'.$this->id)
                            ->send();

                    } catch (\Exception $e) {
                        \Yii::error('Ошибка отправки email: '.$e->getMessage(), Module::class);
                    }
                }

            }
        }

        if ($this->isAttributeChanged('is_allowed_payment') && $this->is_allowed_payment) {
            (new ShopOrderChange([
                'type'          => ShopOrderChange::ORDER_ALLOW_PAYMENT,
                'shop_order_id' => $this->id,
            ]))->save();


            //Письмо тому кто заказывает
            if ($this->email) {
                try {

                    \Yii::$app->mailer->view->theme->pathMap['@app/mail'][] = '@skeeks/cms/shop/mail/order';

                    \Yii::$app->mailer->compose('allow-payment', [
                        'order' => $this,
                    ])
                        ->setFrom([\Yii::$app->cms->adminEmail => \Yii::$app->cms->appName.''])
                        ->setTo($this->email)
                        ->setSubject(\Yii::t('skeeks/shop/app',
                                'Resolution of payment on request').' №'.$this->id)
                        ->send();

                } catch (\Exception $e) {
                    \Yii::error('Ошибка отправки email: '.$e->getMessage(), Module::class);
                }
            }
        }

        if ($this->isAttributeChanged('allow_delivery') && $this->allow_delivery == Cms::BOOL_Y) {
            (new ShopOrderChange([
                'type'          => ShopOrderChange::ORDER_ALLOW_DELIVERY,
                'shop_order_id' => $this->id,
            ]))->save();


            //Письмо тому кто заказывает
            if ($this->email) {
                try {

                    \Yii::$app->mailer->view->theme->pathMap['@app/mail'][] = '@skeeks/cms/shop/mail';

                    \Yii::$app->mailer->compose('order-allow-delivery', [
                        'order' => $this,
                    ])
                        ->setFrom([\Yii::$app->cms->adminEmail => \Yii::$app->cms->appName.''])
                        ->setTo($this->email)
                        ->setSubject(\Yii::t('skeeks/shop/app',
                                'Resolution of payment on request').' №'.$this->id)
                        ->send();

                } catch (\Exception $e) {
                    \Yii::error('Ошибка отправки email: '.$e->getMessage(), Module::class);
                }
            }
        }

        if ($this->isAttributeChanged('canceled_at') && $this->canceled_at) {
            (new ShopOrderChange([
                'type'          => ShopOrderChange::ORDER_CANCELED,
                'shop_order_id' => $this->id,
                'data'          => [
                    'reason_canceled' => $this->reason_canceled,
                ],
            ]))->save();


            //Письмо тому кто заказывает
            if ($this->email) {
                try {

                    \Yii::$app->mailer->view->theme->pathMap['@app/mail'][] = '@skeeks/cms/shop/mail/order';

                    \Yii::$app->mailer->compose('canceled', [
                        'order' => $this,
                    ])
                        ->setFrom([\Yii::$app->cms->adminEmail => \Yii::$app->cms->appName.''])
                        ->setTo($this->email)
                        ->setSubject(\Yii::t('skeeks/shop/app',
                                'Cancellations').' №'.$this->id)
                        ->send();
                } catch (\Exception $e) {
                    \Yii::error('Ошибка отправки email: '.$e->getMessage(), Module::class);
                }
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
                    'canceled_at',
                    'status_at',
                    'allow_delivery_at',
                    'shop_pay_system_id',
                    'locked_by',
                    'locked_at',
                    'shop_affiliate_id',
                    'delivery_doc_at',
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
            [['delivery_amount', 'amount', 'discount_amount', 'tax_amount', 'paid_amount'], 'number'],
            [['comments'], 'string'],
            [['shop_buyer_id'], 'integer'],
            [['is_allowed_payment'], 'integer'],
            [['cms_site_id'], 'integer'],
            [
                [
                    'allow_delivery',
                ],
                'string',
                'max' => 1,
            ],
            [
                [
                    'reason_canceled',
                    'user_description',
                    'additional_info',
                ],
                'string',
                'max' => 255,
            ],
            [['currency_code'], 'string', 'max' => 3],
            [['shop_delivery_id'], 'integer'],
            [['delivery_doc_num'], 'string', 'max' => 20],
            [['tracking_number'], 'string', 'max' => 100],

            [
                [
                    'allow_delivery',
                ],
                'default',
                'value' => Cms::BOOL_N,
            ],
            [['status_at'], 'default', 'value' => \Yii::$app->formatter->asTimestamp(time())],
            [['currency_code'], 'default', 'value' => \Yii::$app->money->currencyCode],
            [['cms_site_id'], 'default', 'value' => \Yii::$app->cms->site->id],

            [['reason_canceled'], 'validateCanceled'],

            [
                ['is_allowed_payment'],
                'default',
                'value' => function () {
                    return (\Yii::$app->shop->payAfterConfirmation == Cms::BOOL_Y) ? 0 : 1;
                },
            ],

            [['code'], 'string'],
            [['code'], 'default', 'value' => \Yii::$app->security->generateRandomString()],

            [['is_created'], 'default', 'value' => 0],
            [['shop_order_status_id'], 'default', 'value' => \Yii::$app->shop->start_order_status_id],


            [
                ['shop_person_type_id'],
                'default',
                'value' => function () {
                    $shopPersonType = \Yii::$app->shop->shopPersonTypes[0];
                    return $shopPersonType->id;
                },
            ],

            /*['reason_canceled', 'required', 'when' => function($model) {
                return $model->canceled == Cms::BOOL_Y;
            }, 'whenClient' => "function (attribute, value) {
                return $('#country').val() == 'Y';
            }"],*/

        ];
    }
    /**
     * Процесс оплаты заказа
     *
     * @return $this
     */
    public function processNotePayment()
    {
        /*$transaction = new ShopUserTransact();
        $transaction->cms_user_id = $this->user_id;
        $transaction->shop_order_id = $this->id;
        $transaction->amount = $this->money->amount;
        $transaction->currency_code = $this->money->currency->code;
        $transaction->debit = "Y";
        $transaction->description = ShopUserTransact::OUT_CHARGE_OFF;
        $transaction->save();


        $transaction = new ShopUserTransact();
        $transaction->cms_user_id = $this->user_id;
        $transaction->shop_order_id = $this->id;
        $transaction->amount = $this->money->amount;
        $transaction->currency_code = $this->money->currency->code;
        $transaction->debit = "N";
        $transaction->description = ShopUserTransact::ORDER_PAY;
        $transaction->save();*/

        /*$this->payed = "Y";
        $this->save();*/

        return $this;
    }
    /**
     * Отмена оплаты заказа
     * @return $this
     */
    public function processCloseNotePayment()
    {
        $this->payed = "N";
        $this->save();

        return $this;
    }
    /**
     * Валидация причины отмены
     *
     * @param $attribute
     */
    public function validateCanceled($attribute)
    {
        if ($this->canceled_at && !$this->reason_canceled) {
            $this->addError($attribute, \Yii::t('skeeks/shop/app', 'Enter the reason for cancellation'));
        }
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'                  => \Yii::t('skeeks/shop/app', 'ID'),
            'created_by'          => \Yii::t('skeeks/shop/app', 'Created By'),
            'updated_by'          => \Yii::t('skeeks/shop/app', 'Updated By'),
            'created_at'          => \Yii::t('skeeks/shop/app', 'Created At'),
            'updated_at'          => \Yii::t('skeeks/shop/app', 'Updated At'),
            'cms_site_id'         => \Yii::t('skeeks/shop/app', 'Site'),
            'shop_person_type_id' => \Yii::t('skeeks/shop/app', 'Person Type ID'),
            'payed'               => \Yii::t('skeeks/shop/app', 'Оплачен'),
            'paid_at'            => \Yii::t('skeeks/shop/app', 'Оплачен'),
            'canceled'            => \Yii::t('skeeks/shop/app', 'Canceled'),
            'canceled_at'         => \Yii::t('skeeks/shop/app', 'Canceled'),
            'reason_canceled'     => \Yii::t('skeeks/shop/app', 'Reason of cancellation'),
            'shop_order_status_id'         => \Yii::t('skeeks/shop/app', 'Status'),
            'status_at'           => \Yii::t('skeeks/shop/app', 'Status At'),
            'delivery_amount'     => \Yii::t('skeeks/shop/app', 'Price Delivery'),
            'allow_delivery'      => \Yii::t('skeeks/shop/app', 'Allow Delivery'),
            'allow_delivery_at'   => \Yii::t('skeeks/shop/app', 'Allow Delivery At'),
            'amount'              => \Yii::t('skeeks/shop/app', 'Price'),
            'currency_code'       => \Yii::t('skeeks/shop/app', 'Currency Code'),
            'discount_amount'     => \Yii::t('skeeks/shop/app', 'Discount Value'),
            'shop_pay_system_id'  => \Yii::t('skeeks/shop/app', 'Pay System ID'),
            'shop_delivery_id'    => \Yii::t('skeeks/shop/app', 'Delivery'),
            'user_description'    => \Yii::t('skeeks/shop/app', 'User Description'),
            'additional_info'     => \Yii::t('skeeks/shop/app', 'Additional Info'),
            'comments'            => \Yii::t('skeeks/shop/app', 'Comments'),
            'tax_amount'          => \Yii::t('skeeks/shop/app', 'Tax Value'),
            'paid_amount'         => \Yii::t('skeeks/shop/app', 'Sum Paid'),
            'locked_by'           => \Yii::t('skeeks/shop/app', 'Locked By'),
            'locked_at'           => \Yii::t('skeeks/shop/app', 'Locked At'),
            'shop_affiliate_id'   => \Yii::t('skeeks/shop/app', 'Affiliate ID'),
            'delivery_doc_num'    => \Yii::t('skeeks/shop/app', 'Delivery Doc Num'),
            'delivery_doc_at'     => \Yii::t('skeeks/shop/app', 'Delivery Doc At'),
            'tracking_number'     => \Yii::t('skeeks/shop/app', 'Tracking Number'),
            'shop_buyer_id'       => \Yii::t('skeeks/shop/app', 'Profile of buyer'),
            'is_allowed_payment'       => \Yii::t('skeeks/shop/app', 'Allow Payment'),

            'shop_delivery_id'   => \Yii::t('skeeks/shop/app', 'Delivery service'),
            'shop_buyer_id'      => \Yii::t('skeeks/shop/app', 'Profile of buyer'),
            'shop_pay_system_id' => \Yii::t('skeeks/shop/app', 'Payment system'),

            'is_created' => \Yii::t('skeeks/shop/app', 'Заказ создан?'),
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
    public function getShopAffiliate()
    {
        return $this->hasOne(ShopAffiliate::class, ['id' => 'shop_affiliate_id']);
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
    public function getLockedBy()
    {
        return $this->hasOne(CmsUser::class, ['id' => 'locked_by']);
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
        return $this->hasOne(CmsSite::class, ['id' => 'cms_site_id']);
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
    public function getShopOrderChanges()
    {
        return $this->hasMany(ShopOrderChange::class,
            ['shop_order_id' => 'id'])->orderBy(['created_at' => SORT_DESC]);
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
        $money->add($this->calcMoneyDelivery);
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
            foreach ($this->shopDiscountCoupons as $shopDiscountCoupon)
            {
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
            return $this->shopDelivery->money;
        }

        return new Money("", $this->currency_code);
    }

    /**
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
        return $this->shopPersonType->getPaySystems()->andWhere([ShopPaySystem::tableName().".active" => Cms::BOOL_Y]);
    }
    /**
     * @return $this
     */
    public function recalculate()
    {
        $this->tax_amount = $this->calcMoneyVat->amount;
        $this->amount = $this->calcMoney->amount;
        $this->discount_amount = $this->calcMoneyDiscount->amount;
        $this->delivery_amount = $this->calcMoneyDelivery->amount;

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
     * @deprecated
     * @return string
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

        /*$ph = new ProductPriceHelper([
            'shopCmsContentElement' => $shopCmsContentElement,
            'shopOrder'             => $this,
            'price'                 => new ShopProductPrice([
                'currency_code' => \Yii::$app->money->currencyCode,
                'price' => 0,
            ]),
        ]);;*/

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

        /*foreach (\Yii::$app->shop->shopTypePrices as $typePrice) {
            if (\Yii::$app->authManager->checkAccess($this->cmsUser ? $this->cmsUser->id : null, $typePrice->buyPermissionName)
                || $typePrice->isDefault
            ) {
                $result[$typePrice->id] = $typePrice;
            }
        }

        return $result;*/
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
        return $this->hasOne(ShopCart::class, ['shop_order_id' => 'id']);
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
     * @return string
     * @deprecated
     */
    public function getPayed()
    {
        return $this->paid_at ? "Y" : "N";
    }

    /**
     * @return string
     * @deprecated
     */
    public function getCanceled()
    {
        return $this->canceled_at ? "Y" : "N";
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

    public function getAllow_payment()
    {
        return $this->is_allowed_payment ? "Y" : "N";
    }
}