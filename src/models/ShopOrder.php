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
 * @property string                     $payed
 * @property integer                    $payed_at
 * @property integer                    $shop_buyer_id
 * @property integer                    $canceled_at
 * @property string                     $reason_canceled
 * @property string                     $status_code
 * @property integer                    $status_at
 * @property string                     $delivery_amount
 * @property string                     $allow_delivery
 * @property string                     $allow_payment
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
 *
 * @property ShopCart                   $shopCart
 * @property CmsUser|null               $cmsUser
 *
 * @property CmsContentElement          $store
 * @property Currency                   $currency
 * @property CmsUser                    $lockedBy
 * @property ShopOrderStatus            $status
 *
 * @property ShopOrderChange[]          $shopOrderChanges
 * @property ShopUserTransact[]         $shopUserTransacts
 * @property ShopOrder2discountCoupon[] $shopOrder2discountCoupons
 * @property ShopDiscountCoupon[]       $discountCoupons
 *
 * @property ShopTypePrice[]            $buyTypePrices
 *
 *
 * @property Money                      $money
 * @property Money                      $moneyVat
 * @property Money                      $moneyDiscount
 * @property Money                      $moneyOriginal
 * @property Money                      $moneySummPaid
 * @property Money                      $moneyDelivery
 *
 * @property Money                      $basketsMoney
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
    protected $_email = null;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%shop_order}}';
    }
    /**
     * @param ShopFuser $shopFuser
     * @return static
     */
    static public function createOrderByFuser(ShopFuser $shopFuser, $isNotify = true)
    {
        $order = static::createByFuser($shopFuser);

        if ($order->save()) {
            foreach ($shopFuser->shopBaskets as $basket) {
                $basket->unlink('fuser', $shopFuser);
                $basket->link('order', $order);
            }

            if ($shopFuser->discountCoupons) {
                foreach ($shopFuser->discountCoupons as $discountCoupon) {
                    $shopOrder2discountCoupon = new ShopOrder2discountCoupon();
                    $shopOrder2discountCoupon->order_id = $order->id;
                    $shopOrder2discountCoupon->discount_coupon_id = $discountCoupon->id;

                    if (!$shopOrder2discountCoupon->save()) {
                        print_r($shopOrder2discountCoupon->errors);
                        die;
                    }
                }

                $shopFuser->discount_coupons = [];
                $shopFuser->save(false);
            }

            //Notify admins
            if (\Yii::$app->shop->notifyEmails) {
                foreach (\Yii::$app->shop->notifyEmails as $email) {
                    \Yii::$app->mailer->view->theme->pathMap['@app/mail'][] = '@skeeks/cms/shop/mail';

                    \Yii::$app->mailer->compose('create-order', [
                        'order' => $order,
                    ])
                        ->setFrom([\Yii::$app->cms->adminEmail => \Yii::$app->cms->appName.''])
                        ->setTo($email)
                        ->setSubject(\Yii::t('skeeks/shop/app',
                                'New order').' #'.$order->id)
                        ->send();
                }
            }

            //Письмо тому кто заказывает
            if ($order->email && $isNotify) {
                $order->notifyNew();
            }
        }

        return $order;
    }
    /**
     * @param ShopFuser $shopFuser
     * @return static
     */
    static public function createByFuser(ShopFuser $shopFuser)
    {
        $order = new static();

        $order->cms_site_id = $shopFuser->site->id;
        $order->shop_person_type_id = $shopFuser->shop_person_type_id;
        $order->shop_buyer_id = $shopFuser->shop_buyer_id;
        $order->user_id = $shopFuser->user_id;

        $order->price = $shopFuser->money ? ($shopFuser->money->amount) : "";
        $order->currency_code = $shopFuser->money ? $shopFuser->money->currency->code : "";
        if ($shopFuser->paySystem) {
            $order->shop_pay_system_id = $shopFuser->paySystem->id;
        }

        if ($shopFuser->moneyVat) {
            $order->tax_amount = $shopFuser->moneyVat->getValue();
        }

        if ($shopFuser->moneyDiscount) {
            $order->discount_amount = $shopFuser->moneyDiscount->getValue();
        }

        $order->shop_delivery_id = $shopFuser->shop_delivery_id;
        $order->store_id = $shopFuser->store_id;

        if ($shopFuser->delivery) {
            $order->delivery_amount = $shopFuser->delivery->money->amount;
        }

        return $order;
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

        //$this->on(self::EVENT_AFTER_INSERT, [$this, "afterInstertCallback"]);
        //$this->on(self::EVENT_AFTER_UPDATE,    [$this, "afterUpdateCallback"]);
        $this->on(self::EVENT_BEFORE_UPDATE, [$this, "beforeUpdateCallback"]);
    }
    public function afterInstertCallback($e)
    {
        (new ShopOrderChange([
            'type'          => ShopOrderChange::ORDER_ADDED,
            'shop_order_id' => $this->id,
        ]))->save();
    }
    public function beforeUpdateCallback($e)
    {
        if ($this->isAttributeChanged('canceled')) {
            $this->canceled_at = \Yii::$app->formatter->asTimestamp(time());
        }

        if ($this->isAttributeChanged('payed')) {
            $this->payed_at = \Yii::$app->formatter->asTimestamp(time());
        }

        if ($this->isAttributeChanged('status_code')) {
            $this->status_at = \Yii::$app->formatter->asTimestamp(time());

            (new ShopOrderChange([
                'type'          => ShopOrderChange::ORDER_STATUS_CHANGED,
                'shop_order_id' => $this->id,
                'data'          =>
                    [
                        'status' => $this->status->name,
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

        if ($this->isAttributeChanged('payed') && $this->payed == Cms::BOOL_Y) {
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

        if ($this->isAttributeChanged('allow_payment') && $this->allow_payment == Cms::BOOL_Y) {
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

        if ($this->isAttributeChanged('canceled') && $this->canceled == Cms::BOOL_Y) {
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
                    'payed_at',
                    'canceled_at',
                    'status_at',
                    'allow_delivery_at',
                    'shop_pay_system_id',
                    'locked_by',
                    'locked_at',
                    'shop_affiliate_id',
                    'delivery_doc_at',
                    'is_created',
                ],
                'integer',
            ],
            [['delivery_amount', 'amount', 'discount_amount', 'tax_amount', 'paid_amount'], 'number'],
            [['comments'], 'string'],
            [['shop_buyer_id'], 'integer'],
            [['cms_site_id'], 'integer'],
            [
                [
                    'status_code',
                    'allow_delivery',
                    'allow_payment',
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
            [['status_code'], 'default', 'value' => ShopOrderStatus::STATUS_CODE_START],
            [['status_at'], 'default', 'value' => \Yii::$app->formatter->asTimestamp(time())],
            [['currency_code'], 'default', 'value' => \Yii::$app->money->currencyCode],
            [['cms_site_id'], 'default', 'value' => \Yii::$app->cms->site->id],

            [['reason_canceled'], 'validateCanceled'],

            [
                ['allow_payment'],
                'default',
                'value' => function () {
                    return (\Yii::$app->shop->payAfterConfirmation == Cms::BOOL_Y) ? Cms::BOOL_N : Cms::BOOL_Y;
                },
            ],

            [['code'], 'string'],
            [['code'], 'default', 'value' => \Yii::$app->security->generateRandomString()],

            [['is_created'], 'default', 'value' => 0],

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
        $transaction = new ShopUserTransact();
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
        $transaction->save();

        $this->payed = "Y";
        $this->save();

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
            'payed_at'            => \Yii::t('skeeks/shop/app', 'Payed At'),
            'canceled'            => \Yii::t('skeeks/shop/app', 'Canceled'),
            'canceled_at'         => \Yii::t('skeeks/shop/app', 'Canceled At'),
            'reason_canceled'     => \Yii::t('skeeks/shop/app', 'Reason of cancellation'),
            'status_code'         => \Yii::t('skeeks/shop/app', 'Status'),
            'status_at'           => \Yii::t('skeeks/shop/app', 'Status At'),
            'delivery_amount'      => \Yii::t('skeeks/shop/app', 'Price Delivery'),
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
            'allow_payment'       => \Yii::t('skeeks/shop/app', 'Allow Payment'),

            'shop_delivery_id'   => \Yii::t('skeeks/shop/app', 'Delivery service'),
            'shop_buyer_id'      => \Yii::t('skeeks/shop/app', 'Profile of buyer'),
            'shop_pay_system_id' => \Yii::t('skeeks/shop/app', 'Payment system'),
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

        if ($this->buyer) {
            if ($this->buyer->email) {
                return $this->buyer->email;
            }
        }

        if ($this->user && $this->user->email) {
            return $this->user->email;
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
        return $this->hasOne(ShopOrderStatus::class, ['code' => 'status_code']);
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
    public function getShopOrderItems()
    {
        return $this->hasMany(ShopOrderItem::class, ['shop_order_id' => 'id']);
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
    public function getDiscountCoupons()
    {
        return $this->hasMany(ShopDiscountCoupon::class, ['id' => 'discount_coupon_id'])
            ->via('shopOrder2discountCoupons');
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopUserTransacts()
    {
        return $this->hasMany(ShopUserTransact::class, ['shop_order_id' => 'id'])->orderBy(['id' => SORT_DESC]);
    }


    /**
     *
     * Цена всех позиций в заказе, динамически рассчитанная
     *
     * @return Money
     */
    public function getBasketsMoney()
    {
        $money = new Money("", $this->currency_code);

        foreach ($this->shopOrderItems as $shopOrderItem) {
            $money = $money->add($shopOrderItem->money->multiply($shopOrderItem->quantity));
        }

        return $money;
    }

    /**
     * Итоговая стоимость заказа
     *
     * @return Money
     */
    public function getMoney()
    {
        return new Money($this->amount, $this->currency_code);
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
     * Скидка наценка
     *
     * @return Money
     */
    public function getMoneyDiscount()
    {
        return new Money($this->discount_amount, $this->currency_code);
    }

    /**
     * Итоговая стоимость позиции без скидок и наценок
     * Цена товара в момент укладки товара в корзину
     *
     * @return Money
     */
    public function getMoneyOriginal()
    {
        return new Money((string)($this->amount + $this->discount_amount), $this->currency_code);
    }

    /**
     * Уже оплачено по заказу
     *
     * @return Money
     */
    public function getMoneySummPaid()
    {
        return new Money($this->paid_amount, $this->currency_code);
    }

    /**
     * Стоимость доставки
     *
     * @return Money
     */
    public function getMoneyDelivery()
    {
        return new Money($this->delivery_amount, $this->currency_code);
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
        $money = $this->basketsMoney;
        if ($this->moneyDelivery) {
            $money->add($this->moneyDelivery);
        }

        $this->amount = $money->amount;
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

        if ($shopCmsContentElement->shopProduct->shopProductPrices) {
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
        $result = [];

        foreach (\Yii::$app->shop->shopTypePrices as $typePrice) {
            if (\Yii::$app->authManager->checkAccess($this->cmsUser ? $this->cmsUser->id : null, $typePrice->buyPermissionName)
                || $typePrice->isDefault
            ) {
                $result[$typePrice->id] = $typePrice;
            }
        }

        return $result;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopCart()
    {
        return $this->hasOne(ShopCart::className(), ['shop_order_id' => 'id']);
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
}