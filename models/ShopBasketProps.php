<?php

namespace skeeks\cms\shop\models;

use Yii;

/**
 * This is the model class for table "{{%shop_basket_props}}".
 *
 * @property integer $id
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $shop_basket_id
 * @property string $name
 * @property string $value
 * @property string $code
 * @property integer $priority
 *
 * @property ShopBasket $shopBasket
 */
class ShopBasketProps extends \skeeks\cms\models\Core
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%shop_basket_props}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'shop_basket_id', 'priority'], 'integer'],
            [['shop_basket_id', 'name'], 'required'],
            [['name', 'value', 'code'], 'string', 'max' => 255],
            [['priority'], 'default', 'value' => 100]
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
            'shop_basket_id' => Yii::t('app', 'Shop Basket ID'),
            'name' => Yii::t('app', 'Name'),
            'value' => Yii::t('app', 'Value'),
            'code' => Yii::t('app', 'Code'),
            'priority' => Yii::t('app', 'Priority'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopBasket()
    {
        return $this->hasOne(ShopBasket::className(), ['id' => 'shop_basket_id']);
    }
}