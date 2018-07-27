<?php

namespace skeeks\cms\shop\models;

use skeeks\cms\models\behaviors\Serialize;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%shop_order_change}}".
 *
 * @property integer   $id
 * @property integer   $created_by
 * @property integer   $updated_by
 * @property integer   $created_at
 * @property integer   $updated_at
 * @property integer   $shop_order_id
 * @property string    $type
 * @property string    $data
 *
 * @property ShopOrder $shopOrder
 */
class ShopOrderChange extends \skeeks\cms\models\Core
{
    const ORDER_ADDED = "ORDER_ADDED";
    const ORDER_CANCELED = "ORDER_CANCELED";
    const ORDER_STATUS_CHANGED = "ORDER_STATUS_CHANGED";
    const ORDER_ALLOW_PAYMENT = "ORDER_ALLOW_PAYMENT";
    const ORDER_ALLOW_DELIVERY = "ORDER_ALLOW_DELIVERY";

    const ORDER_PAYED = "ORDER_PAYED";
    const ORDER_PRICE_CHANGE = "ORDER_PRICE_CHANGE";


    const BASKET_ADDED = "BASKET_ADDED";
    const BASKET_REMOVED = "BASKET_REMOVED";
    const BASKET_PRICE_CHANGED = "BASKET_PRICE_CHANGED";
    const BASKET_QUANTITY_CHANGED = "BASKET_QUANTITY_CHANGED";


    static public function types()
    {
        return [
            self::ORDER_ADDED          => \Yii::t('skeeks/shop/app', 'Create Order'),
            self::ORDER_CANCELED       => \Yii::t('skeeks/shop/app', 'Cancellations'),
            self::ORDER_STATUS_CHANGED => \Yii::t('skeeks/shop/app', 'Changing status'),
            self::ORDER_ALLOW_PAYMENT  => \Yii::t('skeeks/shop/app', 'Payment agreement'),
            self::ORDER_ALLOW_DELIVERY => \Yii::t('skeeks/shop/app', 'Shipping is permitted'),
            self::ORDER_PAYED          => \Yii::t('skeeks/shop/app', 'Order successfully paid'),
        ];
    }
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%shop_order_change}}';
    }
    /**
     * @return string
     */
    public function getDescription()
    {
        $message = ArrayHelper::getValue(self::typeMessages(), $this->type);
        return \Yii::t('skeeks/shop/app', $message, $this->data);
    }
    static public function typeMessages()
    {
        return [
            self::ORDER_ADDED          => \Yii::t('skeeks/shop/app', 'The order created'),
            self::ORDER_CANCELED       => \Yii::t('skeeks/shop/app', 'Order cancelled. The reason: "{reason canceled}"'),
            self::ORDER_STATUS_CHANGED => \Yii::t('skeeks/shop/app', 'Status changed to: "{status}"'),
            self::ORDER_ALLOW_PAYMENT  => \Yii::t('skeeks/shop/app', 'Payment agreement'),
            self::ORDER_ALLOW_DELIVERY => \Yii::t('skeeks/shop/app', 'Shipping is permitted'),
            self::ORDER_PAYED          => \Yii::t('skeeks/shop/app', 'Order successfully paid'),
        ];
    }
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [

            Serialize::className() =>
                [
                    'class'  => Serialize::className(),
                    "fields" => [
                        "data",
                    ],
                ],

        ]);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'shop_order_id'], 'integer'],
            [['shop_order_id', 'type'], 'required'],
            [['data'], 'safe'],
            [['type'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'            => \Yii::t('skeeks/shop/app', 'ID'),
            'created_by'    => \Yii::t('skeeks/shop/app', 'Created By'),
            'updated_by'    => \Yii::t('skeeks/shop/app', 'Updated By'),
            'created_at'    => \Yii::t('skeeks/shop/app', 'Created At'),
            'updated_at'    => \Yii::t('skeeks/shop/app', 'Updated At'),
            'shop_order_id' => \Yii::t('skeeks/shop/app', 'Shop Order ID'),
            'type'          => \Yii::t('skeeks/shop/app', 'Type'),
            'data'          => \Yii::t('skeeks/shop/app', 'Data'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopOrder()
    {
        return $this->hasOne(ShopOrder::className(), ['id' => 'shop_order_id']);
    }

}