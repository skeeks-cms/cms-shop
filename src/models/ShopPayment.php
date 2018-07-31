<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */

namespace skeeks\cms\shop\models;

use skeeks\cms\models\behaviors\HasJsonFieldsBehavior;
use skeeks\cms\money\models\MoneyCurrency;
use skeeks\cms\money\Money;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%shop_payment}}".
 *
 * @property int           $id
 * @property int           $created_by
 * @property int           $updated_by
 * @property int           $created_at
 * @property int           $updated_at
 * @property int           $shop_buyer_id Покупатель
 * @property int           $shop_order_id Заказ
 * @property int           $shop_pay_system_id Платежная система
 * @property int           $is_debit Дебет? (иначе кредит)
 * @property int           $paid_at Дата оплаты
 * @property string        $amount
 * @property string        $currency_code
 * @property string        $comment
 * @property string        $external_name
 * @property string        $external_id
 * @property string        $external_data
 *
 * @property ShopBill[]    $shopBills
 * @property MoneyCurrency $currencyCode
 * @property ShopBuyer     $shopBuyer
 * @property ShopOrder     $shopOrder
 * @property ShopPaySystem $shopPaySystem
 * @property Money         $money
 */
class ShopPayment extends \skeeks\cms\base\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%shop_payment}}';
    }

    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            HasJsonFieldsBehavior::class => [
                'class'  => HasJsonFieldsBehavior::class,
                'fields' => ['external_data'],
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'shop_buyer_id', 'shop_order_id', 'shop_pay_system_id', 'is_debit', 'paid_at'], 'integer'],
            [['shop_buyer_id', 'shop_order_id', 'shop_pay_system_id'], 'required'],
            [['amount'], 'number'],
            [['external_data'], 'safe'],
            [['comment'], 'string'],
            [['currency_code'], 'string', 'max' => 3],
            [['external_name', 'external_id'], 'string', 'max' => 255],
            [['currency_code'], 'exist', 'skipOnError' => true, 'targetClass' => MoneyCurrency::class, 'targetAttribute' => ['currency_code' => 'code']],
            [['shop_buyer_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopBuyer::class, 'targetAttribute' => ['shop_buyer_id' => 'id']],
            [['shop_order_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopOrder::class, 'targetAttribute' => ['shop_order_id' => 'id']],
            [['shop_pay_system_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopPaySystem::class, 'targetAttribute' => ['shop_pay_system_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'id'                 => Yii::t('skeeks/shop/app', 'ID'),
            'shop_buyer_id'      => Yii::t('skeeks/shop/app', 'Покупатель'),
            'shop_order_id'      => Yii::t('skeeks/shop/app', 'Заказ'),
            'shop_pay_system_id' => Yii::t('skeeks/shop/app', 'Платежная система'),
            'is_debit'           => Yii::t('skeeks/shop/app', 'Дебет? (иначе кредит)'),
            'paid_at'            => Yii::t('skeeks/shop/app', 'Дата оплаты'),
            'amount'             => Yii::t('skeeks/shop/app', 'Amount'),
            'currency_code'      => Yii::t('skeeks/shop/app', 'Currency Code'),
            'comment'            => Yii::t('skeeks/shop/app', 'Comment'),
            'external_name'      => Yii::t('skeeks/shop/app', 'External Name'),
            'external_id'        => Yii::t('skeeks/shop/app', 'External ID'),
            'external_data'      => Yii::t('skeeks/shop/app', 'External Data'),
        ]);
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
    public function getShopBuyer()
    {
        return $this->hasOne(ShopBuyer::class, ['id' => 'shop_buyer_id']);
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


}