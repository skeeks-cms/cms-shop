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
        if ($this->isAttributeChanged('status_code'))
        {

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

            [['payed', 'canceled', 'status_code', 'allow_delivery', 'update_1c', 'deducted', 'marked', 'reserved', 'external_order', 'allow_payment'], 'default', 'value' => Cms::BOOL_N],
            [['recount_flag'], 'default', 'value' => Cms::BOOL_Y],
            [['status_at'], 'default', 'value' => \Yii::$app->formatter->asTimestamp(time())],
            [['currency_code'], 'default', 'value' => \Yii::$app->money->currencyCode],
            [['site_id'], 'default', 'value' => \Yii::$app->cms->site->id],

            [['canceled', 'reason_canceled'], 'validateCanceled'],

            /*['reason_canceled', 'required', 'when' => function($model) {
                return $model->canceled == Cms::BOOL_Y;
            }, 'whenClient' => "function (attribute, value) {
                return $('#country').val() == 'Y';
            }"],*/

        ];
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
            'id' => Yii::t('app', 'ID'),
            'created_by' => Yii::t('app', 'Created By'),
            'updated_by' => Yii::t('app', 'Updated By'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'site_id' => Yii::t('app', 'Site'),
            'person_type_id' => Yii::t('app', 'Person Type ID'),
            'payed' => Yii::t('app', 'Оплачен'),
            'payed_at' => Yii::t('app', 'Payed At'),
            'emp_payed_id' => Yii::t('app', 'Emp Payed ID'),
            'canceled' => Yii::t('app', 'Canceled'),
            'canceled_at' => Yii::t('app', 'Canceled At'),
            'emp_canceled_id' => Yii::t('app', 'Emp Canceled ID'),
            'reason_canceled' => Yii::t('app', 'Причина отмены'),
            'status_code' => Yii::t('app', 'Status'),
            'status_at' => Yii::t('app', 'Status At'),
            'emp_status_id' => Yii::t('app', 'Emp Status ID'),
            'price_delivery' => Yii::t('app', 'Price Delivery'),
            'allow_delivery' => Yii::t('app', 'Allow Delivery'),
            'allow_delivery_at' => Yii::t('app', 'Allow Delivery At'),
            'emp_allow_delivery_id' => Yii::t('app', 'Emp Allow Delivery ID'),
            'price' => Yii::t('app', 'Price'),
            'currency_code' => Yii::t('app', 'Currency Code'),
            'discount_value' => Yii::t('app', 'Discount Value'),
            'user_id' => Yii::t('app', 'Пользователь сайта'),
            'pay_system_id' => Yii::t('app', 'Pay System ID'),
            'delivery_id' => Yii::t('app', 'Delivery'),
            'user_description' => Yii::t('app', 'User Description'),
            'additional_info' => Yii::t('app', 'Additional Info'),
            'ps_status' => Yii::t('app', 'Ps Status'),
            'ps_status_code' => Yii::t('app', 'Ps Status Code'),
            'ps_status_description' => Yii::t('app', 'Ps Status Description'),
            'ps_status_message' => Yii::t('app', 'Ps Status Message'),
            'ps_sum' => Yii::t('app', 'Ps Sum'),
            'ps_currency_code' => Yii::t('app', 'Ps Currency Code'),
            'ps_response_at' => Yii::t('app', 'Ps Response At'),
            'comments' => Yii::t('app', 'Comments'),
            'tax_value' => Yii::t('app', 'Tax Value'),
            'stat_gid' => Yii::t('app', 'Stat Gid'),
            'sum_paid' => Yii::t('app', 'Sum Paid'),
            'recuring_id' => Yii::t('app', 'Recuring ID'),
            'pay_voucher_num' => Yii::t('app', 'Pay Voucher Num'),
            'pay_voucher_at' => Yii::t('app', 'Pay Voucher At'),
            'locked_by' => Yii::t('app', 'Locked By'),
            'locked_at' => Yii::t('app', 'Locked At'),
            'recount_flag' => Yii::t('app', 'Recount Flag'),
            'affiliate_id' => Yii::t('app', 'Affiliate ID'),
            'delivery_doc_num' => Yii::t('app', 'Delivery Doc Num'),
            'delivery_doc_at' => Yii::t('app', 'Delivery Doc At'),
            'update_1c' => Yii::t('app', 'Update 1c'),
            'deducted' => Yii::t('app', 'Deducted'),
            'deducted_at' => Yii::t('app', 'Deducted At'),
            'emp_deducted_id' => Yii::t('app', 'Emp Deducted ID'),
            'reason_undo_deducted' => Yii::t('app', 'Reason Undo Deducted'),
            'marked' => Yii::t('app', 'Marked'),
            'marked_at' => Yii::t('app', 'Marked At'),
            'emp_marked_id' => Yii::t('app', 'Emp Marked ID'),
            'reason_marked' => Yii::t('app', 'Reason Marked'),
            'reserved' => Yii::t('app', 'Reserved'),
            'store_id' => Yii::t('app', 'Store ID'),
            'order_topic' => Yii::t('app', 'Order Topic'),
            'responsible_id' => Yii::t('app', 'Responsible ID'),
            'pay_before_at' => Yii::t('app', 'Pay Before At'),
            'account_id' => Yii::t('app', 'Account ID'),
            'bill_at' => Yii::t('app', 'Bill At'),
            'tracking_number' => Yii::t('app', 'Tracking Number'),
            'xml_id' => Yii::t('app', 'Xml ID'),
            'id_1c' => Yii::t('app', 'Id 1c'),
            'version_1c' => Yii::t('app', 'Version 1c'),
            'version' => Yii::t('app', 'Version'),
            'external_order' => Yii::t('app', 'External Order'),
            'buyer_id' => Yii::t('app', 'Профиль покупателя'),
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
        //$order->delivery_id     = $shopFuser->del;

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
        return $this->hasMany(ShopUserTransact::className(), ['shop_order_id' => 'id']);
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
}