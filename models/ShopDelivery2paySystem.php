<?php

namespace skeeks\cms\shop\models;

use Yii;

/**
 * This is the model class for table "{{%shop_delivery2pay_system}}".
 *
 * @property integer $id
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $pay_system_id
 * @property integer $delivery_id
 *
 * @property ShopDelivery $delivery
 * @property ShopPaySystem $paySystem
 */
class ShopDelivery2paySystem extends \skeeks\cms\models\Core
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%shop_delivery2pay_system}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'pay_system_id', 'delivery_id'], 'integer'],
            [['pay_system_id', 'delivery_id'], 'required'],
            [['pay_system_id', 'delivery_id'], 'unique', 'targetAttribute' => ['pay_system_id', 'delivery_id'], 'message' => 'The combination of Pay System ID and Delivery ID has already been taken.']
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
            'pay_system_id' => \Yii::t('skeeks/shop/app', 'Pay System ID'),
            'delivery_id'   => \Yii::t('skeeks/shop/app', 'Delivery ID'),
        ];
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
}