<?php

namespace skeeks\cms\shop\models;

/**
 * This is the model class for table "{{%shop_discount2type_price}}".
 *
 * @property integer       $id
 * @property integer       $created_by
 * @property integer       $updated_by
 * @property integer       $created_at
 * @property integer       $updated_at
 * @property integer       $discount_id
 * @property integer       $type_price_id
 *
 * @property ShopDiscount  $discount
 * @property ShopTypePrice $typePrice
 */
class ShopDiscount2typePrice extends \skeeks\cms\models\Core
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%shop_discount2type_price}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'discount_id', 'type_price_id'], 'integer'],
            [['discount_id', 'type_price_id'], 'required'],
            [
                ['discount_id', 'type_price_id'],
                'unique',
                'targetAttribute' => ['discount_id', 'type_price_id'],
                'message'         => 'The combination of Discount ID and Type Price ID has already been taken.',
            ],
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
            'discount_id'   => \Yii::t('skeeks/shop/app', 'Discount ID'),
            'type_price_id' => \Yii::t('skeeks/shop/app', 'Type Price ID'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDiscount()
    {
        return $this->hasOne(ShopDiscount::className(), ['id' => 'discount_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTypePrice()
    {
        return $this->hasOne(ShopTypePrice::className(), ['id' => 'type_price_id']);
    }
}