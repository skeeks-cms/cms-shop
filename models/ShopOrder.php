<?php

namespace skeeks\cms\shop\models;

use skeeks\cms\components\Cms;
use skeeks\cms\models\CmsSite;
use skeeks\cms\models\CmsUser;
use skeeks\modules\cms\money\Currency;
use skeeks\modules\cms\money\Money;
use Yii;
use yii\behaviors\TimestampBehavior;

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
 *
 * @property ShopBasket[] $shopBaskets
 * @property ShopStore $store
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
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->on(self::EVENT_AFTER_INSERT,    [$this, "afterInstertCallback"]);
        //$this->on(self::EVENT_AFTER_UPDATE,    [$this, "afterUpdateCallback"]);
        $this->on(self::EVENT_BEFORE_UPDATE,    [$this, "beforeUpdateCallback"]);
    }


    public function afterInstertCallback($e)
    {
        ( new ShopOrderChange([
            'type'          => ShopOrderChange::ORDER_ADDED,
            'shop_order_id' => $this->id
        ]) )->save();
    }

    public function beforeUpdateCallback($e)
    {
        if ($this->isAttributeChanged('canceled'))
        {
            $this->canceled_at = \Yii::$app->formatter->asTimestamp(time());
        }

        if ($this->isAttributeChanged('payed'))
        {
            $this->payed_at = \Yii::$app->formatter->asTimestamp(time());
        }

        if ($this->isAttributeChanged('status_code'))
        {
            $this->status_at = \Yii::$app->formatter->asTimestamp(time());

            ( new ShopOrderChange([
                'type'          => ShopOrderChange::ORDER_STATUS_CHANGED,
                'shop_order_id' => $this->id,
                'data'          =>
                [
                    'status' => $this->status->name
                ]
            ]) )->save();


            //Письмо тому кто заказывает
            if ($this->user->email)
            {
                \Yii::$app->mailer->view->theme->pathMap['@app/mail'][] = '@skeeks/cms/shop/mail';

                \Yii::$app->mailer->compose('order-status-change', [
                    'order'  => $this
                ])
                    ->setFrom([\Yii::$app->cms->adminEmail => \Yii::$app->cms->appName . ''])
                    ->setTo($this->user->email)
                    ->setSubject(\Yii::$app->cms->appName . ': Изменение статуса заказа #' . $this->id)
                    ->send();
            }
        }

        if ($this->isAttributeChanged('allow_payment') && $this->allow_payment == Cms::BOOL_Y)
        {
            ( new ShopOrderChange([
                'type'          => ShopOrderChange::ORDER_ALLOW_PAYMENT,
                'shop_order_id' => $this->id,
            ]) )->save();


            //Письмо тому кто заказывает
            if ($this->user->email)
            {
                \Yii::$app->mailer->view->theme->pathMap['@app/mail'][] = '@skeeks/cms/shop/mail';

                \Yii::$app->mailer->compose('order-allow-payment', [
                    'order'  => $this
                ])
                    ->setFrom([\Yii::$app->cms->adminEmail => \Yii::$app->cms->appName . ''])
                    ->setTo($this->user->email)
                    ->setSubject(\Yii::$app->cms->appName . ': Разрешение оплаты по заказу #' . $this->id)
                    ->send();
            }
        }

        if ($this->isAttributeChanged('allow_delivery') && $this->allow_delivery == Cms::BOOL_Y)
        {
            ( new ShopOrderChange([
                'type'          => ShopOrderChange::ORDER_ALLOW_DELIVERY,
                'shop_order_id' => $this->id,
            ]) )->save();


            //Письмо тому кто заказывает
            if ($this->user->email)
            {
                \Yii::$app->mailer->view->theme->pathMap['@app/mail'][] = '@skeeks/cms/shop/mail';

                \Yii::$app->mailer->compose('order-allow-delivery', [
                    'order'  => $this
                ])
                    ->setFrom([\Yii::$app->cms->adminEmail => \Yii::$app->cms->appName . ''])
                    ->setTo($this->user->email)
                    ->setSubject(\Yii::$app->cms->appName . ': Разрешение доставки по заказу #' . $this->id)
                    ->send();
            }
        }

        if ($this->isAttributeChanged('canceled') && $this->canceled == Cms::BOOL_Y)
        {
            ( new ShopOrderChange([
                'type'          => ShopOrderChange::ORDER_CANCELED,
                'shop_order_id' => $this->id,
                'data' => [
                    'reason_canceled' => $this->reason_canceled
                ]
            ]) )->save();


            //Письмо тому кто заказывает
            if ($this->user->email)
            {
                \Yii::$app->mailer->view->theme->pathMap['@app/mail'][] = '@skeeks/cms/shop/mail';

                \Yii::$app->mailer->compose('order-canceled', [
                    'order'  => $this
                ])
                    ->setFrom([\Yii::$app->cms->adminEmail => \Yii::$app->cms->appName . ''])
                    ->setTo($this->user->email)
                    ->setSubject(\Yii::$app->cms->appName . ': Отмена заказа #' . $this->id)
                    ->send();
            }
        }
    }




    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'person_type_id', 'payed_at', 'emp_payed_id', 'canceled_at', 'emp_canceled_id', 'status_at', 'emp_status_id', 'allow_delivery_at', 'emp_allow_delivery_id', 'user_id', 'pay_system_id', 'ps_response_at', 'recuring_id', 'pay_voucher_at', 'locked_by', 'locked_at', 'affiliate_id', 'delivery_doc_at', 'deducted_at', 'emp_deducted_id', 'marked_at', 'emp_marked_id', 'store_id', 'responsible_id', 'pay_before_at', 'account_id', 'bill_at', 'version'], 'integer'],
            [['person_type_id', 'user_id'], 'required'],
            [['price_delivery', 'price', 'discount_value', 'ps_sum', 'tax_value', 'sum_paid'], 'number'],
            [['comments'], 'string'],
            [['buyer_id'], 'integer'],
            [['site_id'], 'integer'],
            [['id_1c', 'version_1c'], 'string', 'max' => 15],
            [['payed', 'canceled', 'status_code', 'allow_delivery', 'ps_status', 'recount_flag', 'update_1c', 'deducted', 'marked', 'reserved', 'external_order', 'allow_payment'], 'string', 'max' => 1],
            [['reason_canceled', 'user_description', 'additional_info', 'ps_status_description', 'ps_status_message', 'stat_gid', 'reason_undo_deducted', 'reason_marked', 'order_topic', 'xml_id'], 'string', 'max' => 255],
            [['currency_code', 'ps_currency_code'], 'string', 'max' => 3],
            [['delivery_id'], 'integer'],
            [['ps_status_code'], 'string', 'max' => 5],
            [['pay_voucher_num', 'delivery_doc_num'], 'string', 'max' => 20],
            [['tracking_number'], 'string', 'max' => 100],

            [['payed', 'canceled', 'status_code', 'allow_delivery', 'update_1c', 'deducted', 'marked', 'reserved', 'external_order'], 'default', 'value' => Cms::BOOL_N],
            [['recount_flag'], 'default', 'value' => Cms::BOOL_Y],
            [['status_at'], 'default', 'value' => \Yii::$app->formatter->asTimestamp(time())],
            [['currency_code'], 'default', 'value' => \Yii::$app->money->currencyCode],
            [['site_id'], 'default', 'value' => \Yii::$app->cms->site->id],

            [['canceled', 'reason_canceled'], 'validateCanceled'],

            [['allow_payment'], 'default', 'value' => function()
            {
                return (\Yii::$app->shop->payAfterConfirmation == Cms::BOOL_Y) ? Cms::BOOL_N: Cms::BOOL_Y;
            }],

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
        $transaction->cms_user_id           = $this->user_id;
        $transaction->shop_order_id         = $this->id;
        $transaction->amount                = $this->money->getAmount() / $this->money->getCurrency()->getSubUnit();
        $transaction->currency_code         = $this->money->getCurrency()->getCurrencyCode();
        $transaction->debit                 = "Y";
        $transaction->description           = ShopUserTransact::OUT_CHARGE_OFF;
        $transaction->save();


        $transaction = new ShopUserTransact();
        $transaction->cms_user_id           = $this->user_id;
        $transaction->shop_order_id         = $this->id;
        $transaction->amount                = $this->money->getAmount() / $this->money->getCurrency()->getSubUnit();
        $transaction->currency_code         = $this->money->getCurrency()->getCurrencyCode();
        $transaction->debit                 = "N";
        $transaction->description           = ShopUserTransact::ORDER_PAY;
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
        if ($this->canceled == Cms::BOOL_Y && !$this->reason_canceled)
        {
            $this->addError($attribute, 'Укажите причину отмены');
        }
    }


    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'                    => \skeeks\cms\shop\Module::t('app', 'ID'),
            'created_by'            => \skeeks\cms\shop\Module::t('app', 'Created By'),
            'updated_by'            => \skeeks\cms\shop\Module::t('app', 'Updated By'),
            'created_at'            => \skeeks\cms\shop\Module::t('app', 'Created At'),
            'updated_at'            => \skeeks\cms\shop\Module::t('app', 'Updated At'),
            'site_id'               => \skeeks\cms\shop\Module::t('app', 'Site'),
            'person_type_id'        => \skeeks\cms\shop\Module::t('app', 'Person Type ID'),
            'payed'                 => \skeeks\cms\shop\Module::t('app', 'Оплачен'),
            'payed_at'              => \skeeks\cms\shop\Module::t('app', 'Payed At'),
            'emp_payed_id'          => \skeeks\cms\shop\Module::t('app', 'Emp Payed ID'),
            'canceled'              => \skeeks\cms\shop\Module::t('app', 'Canceled'),
            'canceled_at'           => \skeeks\cms\shop\Module::t('app', 'Canceled At'),
            'emp_canceled_id'       => \skeeks\cms\shop\Module::t('app', 'Emp Canceled ID'),
            'reason_canceled'       => \skeeks\cms\shop\Module::t('app', 'Reason of cancellation'),
            'status_code'           => \skeeks\cms\shop\Module::t('app', 'Status'),
            'status_at'             => \skeeks\cms\shop\Module::t('app', 'Status At'),
            'emp_status_id'         => \skeeks\cms\shop\Module::t('app', 'Emp Status ID'),
            'price_delivery'        => \skeeks\cms\shop\Module::t('app', 'Price Delivery'),
            'allow_delivery'        => \skeeks\cms\shop\Module::t('app', 'Allow Delivery'),
            'allow_delivery_at'     => \skeeks\cms\shop\Module::t('app', 'Allow Delivery At'),
            'emp_allow_delivery_id' => \skeeks\cms\shop\Module::t('app', 'Emp Allow Delivery ID'),
            'price'                 => \skeeks\cms\shop\Module::t('app', 'Price'),
            'currency_code'         => \skeeks\cms\shop\Module::t('app', 'Currency Code'),
            'discount_value'        => \skeeks\cms\shop\Module::t('app', 'Discount Value'),
            'user_id'               => \skeeks\cms\shop\Module::t('app', 'User site'),
            'pay_system_id'         => \skeeks\cms\shop\Module::t('app', 'Pay System ID'),
            'delivery_id'           => \skeeks\cms\shop\Module::t('app', 'Delivery'),
            'user_description'      => \skeeks\cms\shop\Module::t('app', 'User Description'),
            'additional_info'       => \skeeks\cms\shop\Module::t('app', 'Additional Info'),
            'ps_status'             => \skeeks\cms\shop\Module::t('app', 'Ps Status'),
            'ps_status_code'        => \skeeks\cms\shop\Module::t('app', 'Ps Status Code'),
            'ps_status_description' => \skeeks\cms\shop\Module::t('app', 'Ps Status Description'),
            'ps_status_message'     => \skeeks\cms\shop\Module::t('app', 'Ps Status Message'),
            'ps_sum'                => \skeeks\cms\shop\Module::t('app', 'Ps Sum'),
            'ps_currency_code'      => \skeeks\cms\shop\Module::t('app', 'Ps Currency Code'),
            'ps_response_at'        => \skeeks\cms\shop\Module::t('app', 'Ps Response At'),
            'comments'              => \skeeks\cms\shop\Module::t('app', 'Comments'),
            'tax_value'             => \skeeks\cms\shop\Module::t('app', 'Tax Value'),
            'stat_gid'              => \skeeks\cms\shop\Module::t('app', 'Stat Gid'),
            'sum_paid'              => \skeeks\cms\shop\Module::t('app', 'Sum Paid'),
            'recuring_id'           => \skeeks\cms\shop\Module::t('app', 'Recuring ID'),
            'pay_voucher_num'       => \skeeks\cms\shop\Module::t('app', 'Pay Voucher Num'),
            'pay_voucher_at'        => \skeeks\cms\shop\Module::t('app', 'Pay Voucher At'),
            'locked_by'             => \skeeks\cms\shop\Module::t('app', 'Locked By'),
            'locked_at'             => \skeeks\cms\shop\Module::t('app', 'Locked At'),
            'recount_flag'          => \skeeks\cms\shop\Module::t('app', 'Recount Flag'),
            'affiliate_id'          => \skeeks\cms\shop\Module::t('app', 'Affiliate ID'),
            'delivery_doc_num'      => \skeeks\cms\shop\Module::t('app', 'Delivery Doc Num'),
            'delivery_doc_at'       => \skeeks\cms\shop\Module::t('app', 'Delivery Doc At'),
            'update_1c'             => \skeeks\cms\shop\Module::t('app', 'Update 1c'),
            'deducted'              => \skeeks\cms\shop\Module::t('app', 'Deducted'),
            'deducted_at'           => \skeeks\cms\shop\Module::t('app', 'Deducted At'),
            'emp_deducted_id'       => \skeeks\cms\shop\Module::t('app', 'Emp Deducted ID'),
            'reason_undo_deducted'  => \skeeks\cms\shop\Module::t('app', 'Reason Undo Deducted'),
            'marked'                => \skeeks\cms\shop\Module::t('app', 'Marked'),
            'marked_at'             => \skeeks\cms\shop\Module::t('app', 'Marked At'),
            'emp_marked_id'         => \skeeks\cms\shop\Module::t('app', 'Emp Marked ID'),
            'reason_marked'         => \skeeks\cms\shop\Module::t('app', 'Reason Marked'),
            'reserved'              => \skeeks\cms\shop\Module::t('app', 'Reserved'),
            'store_id'              => \skeeks\cms\shop\Module::t('app', 'Store ID'),
            'order_topic'           => \skeeks\cms\shop\Module::t('app', 'Order Topic'),
            'responsible_id'        => \skeeks\cms\shop\Module::t('app', 'Responsible ID'),
            'pay_before_at'         => \skeeks\cms\shop\Module::t('app', 'Pay Before At'),
            'account_id'            => \skeeks\cms\shop\Module::t('app', 'Account ID'),
            'bill_at'               => \skeeks\cms\shop\Module::t('app', 'Bill At'),
            'tracking_number'       => \skeeks\cms\shop\Module::t('app', 'Tracking Number'),
            'xml_id'                => \skeeks\cms\shop\Module::t('app', 'Xml ID'),
            'id_1c'                 => \skeeks\cms\shop\Module::t('app', 'Id 1c'),
            'version_1c'            => \skeeks\cms\shop\Module::t('app', 'Version 1c'),
            'version'               => \skeeks\cms\shop\Module::t('app', 'Version'),
            'external_order'        => \skeeks\cms\shop\Module::t('app', 'External Order'),
            'buyer_id'              => \skeeks\cms\shop\Module::t('app', 'Profile of buyer'),
            'allow_payment'         => \skeeks\cms\shop\Module::t('app', 'Allow Payment'),

            'delivery_id'       => \skeeks\cms\shop\Module::t('app', 'Служба доставки'),
            'buyer_id'          => \skeeks\cms\shop\Module::t('app', 'Профиль покупателя'),
            'pay_system_id'     => \skeeks\cms\shop\Module::t('app', 'Платежная система'),
        ];
    }


    /**
     * @param ShopFuser $shopFuser
     * @return static
     */
    static public function createOrderByFuser(ShopFuser $shopFuser)
    {
        $order = new static();

        $order->site_id         = $shopFuser->site->id;
        $order->person_type_id  = $shopFuser->personType->id;
        $order->buyer_id        = $shopFuser->buyer->id;
        $order->user_id         = $shopFuser->user->id;

        $order->price           = $shopFuser->money->getAmount() / $shopFuser->money->getCurrency()->getSubUnit();
        $order->currency_code   = $shopFuser->money->getCurrency()->getCurrencyCode();
        $order->pay_system_id   = $shopFuser->paySystem->id;
        $order->tax_value       = $shopFuser->moneyVat->getValue();
        $order->discount_value  = $shopFuser->moneyDiscount->getValue();
        $order->delivery_id     = $shopFuser->delivery_id;
        $order->store_id        = $shopFuser->store_id;

        if ($shopFuser->delivery)
        {
            $order->price_delivery  = $shopFuser->delivery->money->getAmount() / $shopFuser->delivery->money->getCurrency()->getSubUnit();
        }


        if ($order->save())
        {
            foreach ($shopFuser->shopBaskets as $basket)
            {
                $basket->unlink('fuser', $shopFuser);
                $basket->link('order', $order);
            }

            //Письмо тому кто заказывает
            if ($order->user->email)
            {
                \Yii::$app->mailer->view->theme->pathMap['@app/mail'][] = '@skeeks/cms/shop/mail';

                \Yii::$app->mailer->compose('create-order', [
                    'order'  => $order
                ])
                    ->setFrom([\Yii::$app->cms->adminEmail => \Yii::$app->cms->appName . ''])
                    ->setTo($order->user->email)
                    ->setSubject(\Yii::$app->cms->appName . ': Новый заказ #' . $order->id)
                    ->send();
            }
        }

        return $order;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStore()
    {
        return $this->hasOne(ShopStore::className(), ['id' => 'store_id']);
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
        return $this->hasOne(Currency::className(), ['code' => 'currency_code']);
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
        return $this->hasMany(ShopOrderChange::className(), ['shop_order_id' => 'id'])->orderBy(['created_at' => SORT_DESC]);
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
        $money = Money::fromString("", $this->currency_code);

        foreach ($this->shopBaskets as $shopBasket)
        {
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
        return Money::fromString($this->price, $this->currency_code);
    }

    /**
     * Налог
     *
     * @return Money
     */
    public function getMoneyVat()
    {
        return Money::fromString($this->tax_value, $this->currency_code);
    }

    /**
     * Скидка наценка
     *
     * @return Money
     */
    public function getMoneyDiscount()
    {
        return Money::fromString($this->discount_value, $this->currency_code);
    }

    /**
     * Итоговая стоимость позиции без скидок и наценок
     * Цена товара в момент укладки товара в корзину
     *
     * @return Money
     */
    public function getMoneyOriginal()
    {
        return  Money::fromString((string) ($this->price + $this->discount_value), $this->currency_code);
    }

    /**
     * Уже оплачено по заказу
     *
     * @return Money
     */
    public function getMoneySummPaid()
    {
        return Money::fromString($this->sum_paid, $this->currency_code);
    }

    /**
     * Стоимость доставки
     *
     * @return Money
     */
    public function getMoneyDelivery()
    {
        return Money::fromString($this->price_delivery, $this->currency_code);
    }



    /**
     * @return int
     */
    public function getWeight()
    {
        $result = 0;

        foreach ($this->shopBaskets as $shopBasket)
        {
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
        if ($this->moneyDelivery)
        {
            $money = $money->add($this->moneyDelivery);
        }

        $this->price = $money->getAmount() / $money->getCurrency()->getSubUnit();
        return $this;
    }
}