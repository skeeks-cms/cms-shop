<?php

namespace skeeks\cms\shop\models;

use skeeks\cms\components\Cms;
use skeeks\cms\models\CmsContentElement;
use skeeks\cms\models\CmsSite;
use skeeks\cms\models\CmsUser;
use skeeks\cms\money\models\MoneyCurrency;
use skeeks\cms\shop\Module;
use skeeks\cms\money\Money;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/**
 * This is the model class for table "{{%shop_order}}".
 *
 * @property integer $id
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $site_id
 * @property integer $person_type_id
 * @property string $payed
 * @property integer $payed_at
 * @property integer $buyer_id
 * @property integer $emp_payed_id
 * @property string $canceled
 * @property integer $canceled_at
 * @property integer $emp_canceled_id
 * @property string $reason_canceled
 * @property string $status_code
 * @property integer $status_at
 * @property integer $emp_status_id
 * @property string $price_delivery
 * @property string $allow_delivery
 * @property string $allow_payment
 * @property integer $allow_delivery_at
 * @property integer $emp_allow_delivery_id
 * @property string $price
 * @property string $currency_code
 * @property string $discount_value
 * @property integer $user_id
 * @property integer $pay_system_id
 * @property integer $delivery_id
 * @property string $user_description
 * @property string $additional_info
 * @property string $ps_status
 * @property string $ps_status_code
 * @property string $ps_status_description
 * @property string $ps_status_message
 * @property string $ps_sum
 * @property string $ps_currency_code
 * @property integer $ps_response_at
 * @property string $comments
 * @property string $tax_value
 * @property string $stat_gid
 * @property string $sum_paid
 * @property integer $recuring_id
 * @property string $pay_voucher_num
 * @property integer $pay_voucher_at
 * @property integer $locked_by
 * @property integer $locked_at
 * @property string $recount_flag
 * @property integer $affiliate_id
 * @property string $delivery_doc_num
 * @property integer $delivery_doc_at
 * @property string $update_1c
 * @property string $deducted
 * @property integer $deducted_at
 * @property integer $emp_deducted_id
 * @property string $reason_undo_deducted
 * @property string $marked
 * @property integer $marked_at
 * @property integer $emp_marked_id
 * @property string $reason_marked
 * @property string $reserved
 * @property integer $store_id
 * @property string $order_topic
 * @property integer $responsible_id
 * @property integer $pay_before_at
 * @property integer $account_id
 * @property integer $bill_at
 * @property string $tracking_number
 * @property string $xml_id
 * @property string $id_1c
 * @property string $version_1c
 * @property integer $version
 * @property string $external_order
 * @property string $key
 *
 *
 * @property string $email read-only
 *
 * @property ShopBasket[] $shopBaskets
 * @property CmsContentElement $store
 * @property ShopAffiliate $affiliate
 * @property Currency $currency
 * @property CmsUser $lockedBy
 * @property ShopPaySystem $paySystem
 * @property ShopPersonType $personType
 * @property CmsSite $site
 * @property ShopOrderStatus $status
 * @property CmsUser $user
 * @property ShopBuyer $buyer
 * @property ShopDelivery $delivery
 *
 * @property ShopOrderChange[] $shopOrderChanges
 * @property ShopUserTransact[] $shopUserTransacts
 * @property ShopOrder2discountCoupon[] $shopOrder2discountCoupons
 * @property ShopDiscountCoupon[] $discountCoupons
 *
 * @property string $publicUrl
 *
 * @property Money $money
 * @property Money $moneyVat
 * @property Money $moneyDiscount
 * @property Money $moneyOriginal
 * @property Money $moneySummPaid
 * @property Money $moneyDelivery
 *
 * @property Money $basketsMoney
 *
 * @property int $weight
 */
class ShopOrder extends \skeeks\cms\models\Core
{
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
                        'order' => $order
                    ])
                        ->setFrom([\Yii::$app->cms->adminEmail => \Yii::$app->cms->appName . ''])
                        ->setTo($email)
                        ->setSubject(\Yii::$app->cms->appName . ': ' . \Yii::t('skeeks/shop/app',
                                'New order') . ' #' . $order->id)
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

        $order->site_id = $shopFuser->site->id;
        $order->person_type_id = $shopFuser->person_type_id;
        $order->buyer_id = $shopFuser->buyer_id;
        $order->user_id = $shopFuser->user_id;

        $order->price = $shopFuser->money ? ($shopFuser->money->amount) : "";
        $order->currency_code = $shopFuser->money ? $shopFuser->money->currency->code : "";
        if ($shopFuser->paySystem) {
            $order->pay_system_id = $shopFuser->paySystem->id;
        }

        if ($shopFuser->moneyVat) {
            $order->tax_value = $shopFuser->moneyVat->getValue();
        }

        if ($shopFuser->moneyDiscount) {
            $order->discount_value = $shopFuser->moneyDiscount->getValue();
        }

        $order->delivery_id = $shopFuser->delivery_id;
        $order->store_id = $shopFuser->store_id;

        if ($shopFuser->delivery) {
            $order->price_delivery = $shopFuser->delivery->money->amount;
        }

        return $order;
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->on(self::EVENT_AFTER_INSERT, [$this, "afterInstertCallback"]);
        //$this->on(self::EVENT_AFTER_UPDATE,    [$this, "afterUpdateCallback"]);
        $this->on(self::EVENT_BEFORE_UPDATE, [$this, "beforeUpdateCallback"]);
    }

    public function afterInstertCallback($e)
    {
        (new ShopOrderChange([
            'type' => ShopOrderChange::ORDER_ADDED,
            'shop_order_id' => $this->id
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
                'type' => ShopOrderChange::ORDER_STATUS_CHANGED,
                'shop_order_id' => $this->id,
                'data' =>
                    [
                        'status' => $this->status->name
                    ]
            ]))->save();


            //Письмо тому кто заказывает
            if ($this->email) {
                try {
                    \Yii::$app->mailer->view->theme->pathMap['@app/mail'][] = '@skeeks/cms/shop/mail';

                    \Yii::$app->mailer->compose('order-status-change', [
                        'order' => $this
                    ])
                        ->setFrom([\Yii::$app->cms->adminEmail => \Yii::$app->cms->appName . ''])
                        ->setTo($this->email)
                        ->setSubject(\Yii::$app->cms->appName . ': ' . \Yii::t('skeeks/shop/app',
                                'Change order status') . ' #' . $this->id)
                        ->send();

                } catch (\Exception $e) {
                    \Yii::error('Ошибка отправки email: ' . $e->getMessage(), Module::className());
                }

            }
        }

        if ($this->isAttributeChanged('payed') && $this->payed == Cms::BOOL_Y) {
            (new ShopOrderChange([
                'type' => ShopOrderChange::ORDER_PAYED,
                'shop_order_id' => $this->id,
            ]))->save();


            $emails = \Yii::$app->shop->notifyEmails;
            if ($this->email) {
                $emails[] = $this->email;
            }

            if ($emails) {
                foreach ($emails as $email) {
                    try {
                        \Yii::$app->mailer->view->theme->pathMap['@app/mail'][] = '@skeeks/cms/shop/mail';

                        \Yii::$app->mailer->compose('order-payed', [
                            'order' => $this
                        ])
                            ->setFrom([\Yii::$app->cms->adminEmail => \Yii::$app->cms->appName . ''])
                            ->setTo($email)
                            ->setSubject(\Yii::$app->cms->appName . ': ' . \Yii::t('skeeks/shop/app',
                                    'Order successfully paid') . ' #' . $this->id)
                            ->send();

                    } catch (\Exception $e) {
                        \Yii::error('Ошибка отправки email: ' . $e->getMessage(), Module::className());
                    }
                }

            }
        }

        if ($this->isAttributeChanged('allow_payment') && $this->allow_payment == Cms::BOOL_Y) {
            (new ShopOrderChange([
                'type' => ShopOrderChange::ORDER_ALLOW_PAYMENT,
                'shop_order_id' => $this->id,
            ]))->save();


            //Письмо тому кто заказывает
            if ($this->email) {
                try {

                    \Yii::$app->mailer->view->theme->pathMap['@app/mail'][] = '@skeeks/cms/shop/mail';

                    \Yii::$app->mailer->compose('order-allow-payment', [
                        'order' => $this
                    ])
                        ->setFrom([\Yii::$app->cms->adminEmail => \Yii::$app->cms->appName . ''])
                        ->setTo($this->email)
                        ->setSubject(\Yii::$app->cms->appName . ': ' . \Yii::t('skeeks/shop/app',
                                'Resolution of payment on request') . ' #' . $this->id)
                        ->send();

                } catch (\Exception $e) {
                    \Yii::error('Ошибка отправки email: ' . $e->getMessage(), Module::className());
                }
            }
        }

        if ($this->isAttributeChanged('allow_delivery') && $this->allow_delivery == Cms::BOOL_Y) {
            (new ShopOrderChange([
                'type' => ShopOrderChange::ORDER_ALLOW_DELIVERY,
                'shop_order_id' => $this->id,
            ]))->save();


            //Письмо тому кто заказывает
            if ($this->email) {
                try {

                    \Yii::$app->mailer->view->theme->pathMap['@app/mail'][] = '@skeeks/cms/shop/mail';

                    \Yii::$app->mailer->compose('order-allow-delivery', [
                        'order' => $this
                    ])
                        ->setFrom([\Yii::$app->cms->adminEmail => \Yii::$app->cms->appName . ''])
                        ->setTo($this->email)
                        ->setSubject(\Yii::$app->cms->appName . ': ' . \Yii::t('skeeks/shop/app',
                                'Resolution of payment on request') . ' #' . $this->id)
                        ->send();

                } catch (\Exception $e) {
                    \Yii::error('Ошибка отправки email: ' . $e->getMessage(), Module::className());
                }
            }
        }

        if ($this->isAttributeChanged('canceled') && $this->canceled == Cms::BOOL_Y) {
            (new ShopOrderChange([
                'type' => ShopOrderChange::ORDER_CANCELED,
                'shop_order_id' => $this->id,
                'data' => [
                    'reason_canceled' => $this->reason_canceled
                ]
            ]))->save();


            //Письмо тому кто заказывает
            if ($this->email) {
                try {

                    \Yii::$app->mailer->view->theme->pathMap['@app/mail'][] = '@skeeks/cms/shop/mail';

                    \Yii::$app->mailer->compose('order-canceled', [
                        'order' => $this
                    ])
                        ->setFrom([\Yii::$app->cms->adminEmail => \Yii::$app->cms->appName . ''])
                        ->setTo($this->email)
                        ->setSubject(\Yii::$app->cms->appName . ': ' . \Yii::t('skeeks/shop/app',
                                'Cancellations') . ' #' . $this->id)
                        ->send();
                } catch (\Exception $e) {
                    \Yii::error('Ошибка отправки email: ' . $e->getMessage(), Module::className());
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
                    'person_type_id',
                    'payed_at',
                    'emp_payed_id',
                    'canceled_at',
                    'emp_canceled_id',
                    'status_at',
                    'emp_status_id',
                    'allow_delivery_at',
                    'emp_allow_delivery_id',
                    'user_id',
                    'pay_system_id',
                    'ps_response_at',
                    'recuring_id',
                    'pay_voucher_at',
                    'locked_by',
                    'locked_at',
                    'affiliate_id',
                    'delivery_doc_at',
                    'deducted_at',
                    'emp_deducted_id',
                    'marked_at',
                    'emp_marked_id',
                    'store_id',
                    'responsible_id',
                    'pay_before_at',
                    'account_id',
                    'bill_at',
                    'version'
                ],
                'integer'
            ],
            [['person_type_id'], 'required'],
            [['price_delivery', 'price', 'discount_value', 'ps_sum', 'tax_value', 'sum_paid'], 'number'],
            [['comments'], 'string'],
            [['buyer_id'], 'integer'],
            [['site_id'], 'integer'],
            [['id_1c', 'version_1c'], 'string', 'max' => 15],
            [
                [
                    'payed',
                    'canceled',
                    'status_code',
                    'allow_delivery',
                    'ps_status',
                    'recount_flag',
                    'update_1c',
                    'deducted',
                    'marked',
                    'reserved',
                    'external_order',
                    'allow_payment'
                ],
                'string',
                'max' => 1
            ],
            [
                [
                    'reason_canceled',
                    'user_description',
                    'additional_info',
                    'ps_status_description',
                    'ps_status_message',
                    'stat_gid',
                    'reason_undo_deducted',
                    'reason_marked',
                    'order_topic',
                    'xml_id'
                ],
                'string',
                'max' => 255
            ],
            [['currency_code', 'ps_currency_code'], 'string', 'max' => 3],
            [['delivery_id'], 'integer'],
            [['ps_status_code'], 'string', 'max' => 5],
            [['pay_voucher_num', 'delivery_doc_num'], 'string', 'max' => 20],
            [['tracking_number'], 'string', 'max' => 100],

            [
                [
                    'payed',
                    'canceled',
                    'allow_delivery',
                    'update_1c',
                    'deducted',
                    'marked',
                    'reserved',
                    'external_order'
                ],
                'default',
                'value' => Cms::BOOL_N
            ],
            [['recount_flag'], 'default', 'value' => Cms::BOOL_Y],
            [['status_code'], 'default', 'value' => ShopOrderStatus::STATUS_CODE_START],
            [['status_at'], 'default', 'value' => \Yii::$app->formatter->asTimestamp(time())],
            [['currency_code'], 'default', 'value' => \Yii::$app->money->currencyCode],
            [['site_id'], 'default', 'value' => \Yii::$app->cms->site->id],

            [['canceled', 'reason_canceled'], 'validateCanceled'],

            [
                ['allow_payment'],
                'default',
                'value' => function () {
                    return (\Yii::$app->shop->payAfterConfirmation == Cms::BOOL_Y) ? Cms::BOOL_N : Cms::BOOL_Y;
                }
            ],

            [['key'], 'string'],
            [['key'], 'default', 'value' => \Yii::$app->security->generateRandomString()],

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
        if ($this->canceled == Cms::BOOL_Y && !$this->reason_canceled) {
            $this->addError($attribute, \Yii::t('skeeks/shop/app', 'Enter the reason for cancellation'));
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => \Yii::t('skeeks/shop/app', 'ID'),
            'created_by' => \Yii::t('skeeks/shop/app', 'Created By'),
            'updated_by' => \Yii::t('skeeks/shop/app', 'Updated By'),
            'created_at' => \Yii::t('skeeks/shop/app', 'Created At'),
            'updated_at' => \Yii::t('skeeks/shop/app', 'Updated At'),
            'site_id' => \Yii::t('skeeks/shop/app', 'Site'),
            'person_type_id' => \Yii::t('skeeks/shop/app', 'Person Type ID'),
            'payed' => \Yii::t('skeeks/shop/app', 'Оплачен'),
            'payed_at' => \Yii::t('skeeks/shop/app', 'Payed At'),
            'emp_payed_id' => \Yii::t('skeeks/shop/app', 'Emp Payed ID'),
            'canceled' => \Yii::t('skeeks/shop/app', 'Canceled'),
            'canceled_at' => \Yii::t('skeeks/shop/app', 'Canceled At'),
            'emp_canceled_id' => \Yii::t('skeeks/shop/app', 'Emp Canceled ID'),
            'reason_canceled' => \Yii::t('skeeks/shop/app', 'Reason of cancellation'),
            'status_code' => \Yii::t('skeeks/shop/app', 'Status'),
            'status_at' => \Yii::t('skeeks/shop/app', 'Status At'),
            'emp_status_id' => \Yii::t('skeeks/shop/app', 'Emp Status ID'),
            'price_delivery' => \Yii::t('skeeks/shop/app', 'Price Delivery'),
            'allow_delivery' => \Yii::t('skeeks/shop/app', 'Allow Delivery'),
            'allow_delivery_at' => \Yii::t('skeeks/shop/app', 'Allow Delivery At'),
            'emp_allow_delivery_id' => \Yii::t('skeeks/shop/app', 'Emp Allow Delivery ID'),
            'price' => \Yii::t('skeeks/shop/app', 'Price'),
            'currency_code' => \Yii::t('skeeks/shop/app', 'Currency Code'),
            'discount_value' => \Yii::t('skeeks/shop/app', 'Discount Value'),
            'user_id' => \Yii::t('skeeks/shop/app', 'User site'),
            'pay_system_id' => \Yii::t('skeeks/shop/app', 'Pay System ID'),
            'delivery_id' => \Yii::t('skeeks/shop/app', 'Delivery'),
            'user_description' => \Yii::t('skeeks/shop/app', 'User Description'),
            'additional_info' => \Yii::t('skeeks/shop/app', 'Additional Info'),
            'ps_status' => \Yii::t('skeeks/shop/app', 'Ps Status'),
            'ps_status_code' => \Yii::t('skeeks/shop/app', 'Ps Status Code'),
            'ps_status_description' => \Yii::t('skeeks/shop/app', 'Ps Status Description'),
            'ps_status_message' => \Yii::t('skeeks/shop/app', 'Ps Status Message'),
            'ps_sum' => \Yii::t('skeeks/shop/app', 'Ps Sum'),
            'ps_currency_code' => \Yii::t('skeeks/shop/app', 'Ps Currency Code'),
            'ps_response_at' => \Yii::t('skeeks/shop/app', 'Ps Response At'),
            'comments' => \Yii::t('skeeks/shop/app', 'Comments'),
            'tax_value' => \Yii::t('skeeks/shop/app', 'Tax Value'),
            'stat_gid' => \Yii::t('skeeks/shop/app', 'Stat Gid'),
            'sum_paid' => \Yii::t('skeeks/shop/app', 'Sum Paid'),
            'recuring_id' => \Yii::t('skeeks/shop/app', 'Recuring ID'),
            'pay_voucher_num' => \Yii::t('skeeks/shop/app', 'Pay Voucher Num'),
            'pay_voucher_at' => \Yii::t('skeeks/shop/app', 'Pay Voucher At'),
            'locked_by' => \Yii::t('skeeks/shop/app', 'Locked By'),
            'locked_at' => \Yii::t('skeeks/shop/app', 'Locked At'),
            'recount_flag' => \Yii::t('skeeks/shop/app', 'Recount Flag'),
            'affiliate_id' => \Yii::t('skeeks/shop/app', 'Affiliate ID'),
            'delivery_doc_num' => \Yii::t('skeeks/shop/app', 'Delivery Doc Num'),
            'delivery_doc_at' => \Yii::t('skeeks/shop/app', 'Delivery Doc At'),
            'update_1c' => \Yii::t('skeeks/shop/app', 'Update 1c'),
            'deducted' => \Yii::t('skeeks/shop/app', 'Deducted'),
            'deducted_at' => \Yii::t('skeeks/shop/app', 'Deducted At'),
            'emp_deducted_id' => \Yii::t('skeeks/shop/app', 'Emp Deducted ID'),
            'reason_undo_deducted' => \Yii::t('skeeks/shop/app', 'Reason Undo Deducted'),
            'marked' => \Yii::t('skeeks/shop/app', 'Marked'),
            'marked_at' => \Yii::t('skeeks/shop/app', 'Marked At'),
            'emp_marked_id' => \Yii::t('skeeks/shop/app', 'Emp Marked ID'),
            'reason_marked' => \Yii::t('skeeks/shop/app', 'Reason Marked'),
            'reserved' => \Yii::t('skeeks/shop/app', 'Reserved'),
            'store_id' => \Yii::t('skeeks/shop/app', 'Store ID'),
            'order_topic' => \Yii::t('skeeks/shop/app', 'Order Topic'),
            'responsible_id' => \Yii::t('skeeks/shop/app', 'Responsible ID'),
            'pay_before_at' => \Yii::t('skeeks/shop/app', 'Pay Before At'),
            'account_id' => \Yii::t('skeeks/shop/app', 'Account ID'),
            'bill_at' => \Yii::t('skeeks/shop/app', 'Bill At'),
            'tracking_number' => \Yii::t('skeeks/shop/app', 'Tracking Number'),
            'xml_id' => \Yii::t('skeeks/shop/app', 'Xml ID'),
            'id_1c' => \Yii::t('skeeks/shop/app', 'Id 1c'),
            'version_1c' => \Yii::t('skeeks/shop/app', 'Version 1c'),
            'version' => \Yii::t('skeeks/shop/app', 'Version'),
            'external_order' => \Yii::t('skeeks/shop/app', 'External Order'),
            'buyer_id' => \Yii::t('skeeks/shop/app', 'Profile of buyer'),
            'allow_payment' => \Yii::t('skeeks/shop/app', 'Allow Payment'),

            'delivery_id' => \Yii::t('skeeks/shop/app', 'Delivery service'),
            'buyer_id' => \Yii::t('skeeks/shop/app', 'Profile of buyer'),
            'pay_system_id' => \Yii::t('skeeks/shop/app', 'Payment system'),
        ];
    }

    public function notifyNew()
    {
        if ($this->email) {
            \Yii::$app->mailer->view->theme->pathMap['@app/mail'][] = '@skeeks/cms/shop/mail';

            \Yii::$app->mailer->compose('create-order', [
                'order' => $this
            ])
                ->setFrom([\Yii::$app->cms->adminEmail => \Yii::$app->cms->appName . ''])
                ->setTo($this->email)
                ->setSubject(\Yii::$app->cms->appName . ': ' . \Yii::t('skeeks/shop/app',
                        'New order') . ' #' . $this->id)
                ->send();
        }
    }

    protected $_email = null;

    /**
     * @return null|string
     */
    public function setEmail($email)
    {
        $this->_email = $email;
        return $this;
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
            if ($properties = $this->buyer->relatedPropertiesModel->properties) {
                /**
                 * @var $property ShopPersonTypeProperty
                 */
                foreach ($properties as $property) {
                    if ($property->is_user_email == "Y") {
                        $value = $this->buyer->relatedPropertiesModel->getAttribute($property->code);
                        if ($value) {
                            return (string)$value;
                        }
                    }
                }
            }
        }

        if ($this->user && $this->user->email) {
            return $this->user->email;
        }

        return null;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStore()
    {
        return $this->hasOne(CmsContentElement::className(), ['id' => 'store_id']);
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAffiliate()
    {
        return $this->hasOne(ShopAffiliate::className(), ['id' => 'affiliate_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCurrency()
    {
        return $this->hasOne(MoneyCurrency::className(), ['code' => 'currency_code']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLockedBy()
    {
        return $this->hasOne(CmsUser::className(), ['id' => 'locked_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDelivery()
    {
        return $this->hasOne(ShopDelivery::className(), ['id' => 'delivery_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPaySystem()
    {
        return $this->hasOne(ShopPaySystem::className(), ['id' => 'pay_system_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPersonType()
    {
        return $this->hasOne(ShopPersonType::className(), ['id' => 'person_type_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSite()
    {
        return $this->hasOne(CmsSite::className(), ['id' => 'site_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStatus()
    {
        return $this->hasOne(ShopOrderStatus::className(), ['code' => 'status_code']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(CmsUser::className(), ['id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBuyer()
    {
        return $this->hasOne(ShopBuyer::className(), ['id' => 'buyer_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopBaskets()
    {
        return $this->hasMany(ShopBasket::className(), ['order_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopOrderChanges()
    {
        return $this->hasMany(ShopOrderChange::className(),
            ['shop_order_id' => 'id'])->orderBy(['created_at' => SORT_DESC]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopOrder2discountCoupons()
    {
        return $this->hasMany(ShopOrder2discountCoupon::className(), ['order_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDiscountCoupons()
    {
        return $this->hasMany(ShopDiscountCoupon::className(), ['id' => 'discount_coupon_id'])
            ->via('shopOrder2discountCoupons');
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopUserTransacts()
    {
        return $this->hasMany(ShopUserTransact::className(), ['shop_order_id' => 'id'])->orderBy(['id' => SORT_DESC]);
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

        foreach ($this->shopBaskets as $shopBasket) {
            $money = $money->add($shopBasket->money->multiply($shopBasket->quantity));
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
        return new Money($this->price, $this->currency_code);
    }

    /**
     * Налог
     *
     * @return Money
     */
    public function getMoneyVat()
    {
        return new Money($this->tax_value, $this->currency_code);
    }

    /**
     * Скидка наценка
     *
     * @return Money
     */
    public function getMoneyDiscount()
    {
        return new Money($this->discount_value, $this->currency_code);
    }

    /**
     * Итоговая стоимость позиции без скидок и наценок
     * Цена товара в момент укладки товара в корзину
     *
     * @return Money
     */
    public function getMoneyOriginal()
    {
        return new Money((string)($this->price + $this->discount_value), $this->currency_code);
    }

    /**
     * Уже оплачено по заказу
     *
     * @return Money
     */
    public function getMoneySummPaid()
    {
        return new Money($this->sum_paid, $this->currency_code);
    }

    /**
     * Стоимость доставки
     *
     * @return Money
     */
    public function getMoneyDelivery()
    {
        return new Money($this->price_delivery, $this->currency_code);
    }


    /**
     * @return int
     */
    public function getWeight()
    {
        $result = 0;

        foreach ($this->shopBaskets as $shopBasket) {
            $result = $result + ($shopBasket->weight * $shopBasket->quantity);
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
        return $this->personType->getPaySystems()->andWhere([ShopPaySystem::tableName() . ".active" => Cms::BOOL_Y]);
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

        $this->price = $money->amount;
        return $this;
    }

    /**
     * @return string
     */
    public function getPublicUrl($options = [], $scheme = true)
    {
        return Url::to(ArrayHelper::merge(
            ['/shop/order/finish', 'key' => $this->key],
            $options
        ), $scheme);
    }
}