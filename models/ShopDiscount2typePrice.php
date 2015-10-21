<?php

namespace skeeks\cms\shop\models;

use Yii;

/**
 * This is the model class for table "{{%shop_discount2type_price}}".
 *
 * @property integer $id
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $discount_id
 * @property integer $type_price_id
 *
 * @property ShopDiscount $discount
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
            [['discount_id', 'type_price_id'], 'unique', 'targetAttribute' => ['discount_id', 'type_price_id'], 'message' => 'The combination of Discount ID and Type Price ID has already been taken.']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'            => \skeeks\cms\shop\Module::t('app', 'ID'),
            'created_by'    => \skeeks\cms\shop\Module::t('app', 'Created By'),
            'updated_by'    => \skeeks\cms\shop\Module::t('app', 'Updated By'),
            'created_at'    => \skeeks\cms\shop\Module::t('app', 'Created At'),
            'updated_at'    => \skeeks\cms\shop\Module::t('app', 'Updated At'),
            'discount_id'   => \skeeks\cms\shop\Module::t('app', 'Discount ID'),
            'type_price_id' => \skeeks\cms\shop\Module::t('app', 'Type Price ID'),
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