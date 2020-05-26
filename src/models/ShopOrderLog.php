<?php

namespace skeeks\cms\shop\models;

use skeeks\cms\base\ActiveRecord;
use skeeks\cms\models\behaviors\HasJsonFieldsBehavior;
use skeeks\cms\query\CmsActiveQuery;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%shop_order_change}}".
 *
 * @property integer    $id
 * @property integer    $created_by
 * @property integer    $updated_by
 * @property integer    $created_at
 * @property integer    $updated_at
 *
 * @property integer    $shop_order_id
 * @property string     $action_type
 * @property array|null $action_data
 *
 * ***
 *
 * @property string     $typeAsText
 * @property ShopOrder  $shopOrder
 */
class ShopOrderLog extends ActiveRecord
{
    const TYPE_ORDER_ADDED = "order_added";
    const TYPE_ORDER_STATUS_CHANGED = "order_status_changed";
    const TYPE_ORDER_ALLOW_PAYMENT = "order_allow_payment";

    const TYPE_ORDER_PAYED = "order_payed";

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%shop_order_log}}';
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
            self::TYPE_ORDER_ADDED          => \Yii::t('skeeks/shop/app', 'The order created'),
            self::TYPE_ORDER_STATUS_CHANGED => \Yii::t('skeeks/shop/app', 'Status changed to: "{status}"'),
            self::TYPE_ORDER_ALLOW_PAYMENT  => \Yii::t('skeeks/shop/app', 'Payment agreement'),
            self::TYPE_ORDER_PAYED          => \Yii::t('skeeks/shop/app', 'Order successfully paid'),
        ];
    }
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            HasJsonFieldsBehavior::class => [
                'class'  => HasJsonFieldsBehavior::class,
                "fields" => [
                    "action_data",
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
            [['shop_order_id', 'action_type'], 'required'],
            [['action_data'], 'safe'],
            [['action_type'], 'string', 'max' => 255],
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
            'shop_order_id' => \Yii::t('skeeks/shop/app', 'Заказ'),
            'action_type'   => \Yii::t('skeeks/shop/app', 'Тип действия'),
            'action_data'   => \Yii::t('skeeks/shop/app', 'Данные'),
        ];
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopOrder()
    {
        return $this->hasOne(ShopOrder::class, ['id' => 'shop_order_id']);
    }
    /**
     * @return string
     */
    public function getTypeAsText()
    {
        return (string) ArrayHelper::getValue(self::types(), $this->action_type);
    }

    /**
     * @return array
     */
    static public function types()
    {
        return [
            self::TYPE_ORDER_ADDED          => \Yii::t('skeeks/shop/app', 'Create Order'),
            self::TYPE_STATUS_CHANGED => \Yii::t('skeeks/shop/app', 'Changing status'),
            self::TYPE_ALLOW_PAYMENT  => \Yii::t('skeeks/shop/app', 'Payment agreement'),
            self::TYPE_PAYED          => \Yii::t('skeeks/shop/app', 'Order successfully paid'),
        ];
    }

}