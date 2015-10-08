<?php

namespace skeeks\cms\shop\models;

use skeeks\cms\models\behaviors\Serialize;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%shop_order_change}}".
 *
 * @property integer $id
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $shop_order_id
 * @property string $type
 * @property string $data
 *
 * @property ShopOrder $shopOrder
 */
class ShopOrderChange extends \skeeks\cms\models\Core
{
    const ORDER_ADDED               = "ORDER_ADDED";
    const ORDER_CANCELED            = "ORDER_CANCELED";
    const ORDER_STATUS_CHANGED      = "ORDER_STATUS_CHANGED";
    const ORDER_ALLOW_PAYMENT       = "ORDER_ALLOW_PAYMENT";
    const ORDER_ALLOW_DELIVERY      = "ORDER_ALLOW_DELIVERY";

    const ORDER_PAYED               = "ORDER_PAYED";
    const ORDER_PRICE_CHANGE        = "ORDER_PRICE_CHANGE";


    const BASKET_ADDED              = "BASKET_ADDED";
    const BASKET_REMOVED            = "BASKET_REMOVED";
    const BASKET_PRICE_CHANGED      = "BASKET_PRICE_CHANGED";
    const BASKET_QUANTITY_CHANGED   = "BASKET_QUANTITY_CHANGED";


    static public function types()
    {
        return [
            self::ORDER_ADDED               => 'Создание заказа',
            self::ORDER_CANCELED            => 'Отмена заказа',
            self::ORDER_STATUS_CHANGED      => 'Изменение статуса заказа',
            self::ORDER_ALLOW_PAYMENT       => 'Оплата разрешена',
            self::ORDER_ALLOW_DELIVERY      => 'Доставка разрешена',
        ];
    }

    static public function typeMessages()
    {
        return [
            self::ORDER_ADDED               => 'Заказ создан',
            self::ORDER_CANCELED            => 'Заказ отменен. Причина: "{reason_canceled}"',
            self::ORDER_STATUS_CHANGED      => 'Статус изменен на: "{status}"',
            self::ORDER_ALLOW_PAYMENT       => 'Оплата разрешена',
            self::ORDER_ALLOW_DELIVERY      => 'Доставка разрешена',
        ];
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        $message = ArrayHelper::getValue(self::typeMessages(), $this->type);
        return \Yii::t('app', $message, $this->data);
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%shop_order_change}}';
    }

    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [

            Serialize::className() =>
            [
                'class' => Serialize::className(),
                "fields" =>  [
                    "data"
                ]
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
            [['type'], 'string', 'max' => 255]
        ];
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
            'shop_order_id' => Yii::t('app', 'Shop Order ID'),
            'type' => Yii::t('app', 'Type'),
            'data' => Yii::t('app', 'Data'),
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