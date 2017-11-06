<?php

namespace skeeks\cms\shop\models;

use skeeks\modules\cms\money\models\Currency;
use skeeks\modules\cms\money\Money;
use Yii;

/**
 * This is the model class for table "{{%shop_product_price_change}}".
 *
 * @property integer $id
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $shop_product_price_id
 * @property string $price
 * @property string $currency_code
 * @property integer $quantity_from
 * @property integer $quantity_to
 *
 * @property ShopProductPrice $shopProductPrice
 * @property Currency $currency
 * @property Money $money
 */
class ShopProductPriceChange extends \skeeks\cms\models\Core
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%shop_product_price_change}}';
    }


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'shop_product_price_id', 'quantity_from', 'quantity_to'], 'integer'],
            [['price', 'currency_code'], 'required'],
            [['price'], 'number'],
            [['currency_code'], 'string', 'max' => 3]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => \Yii::t('skeeks/shop/app', 'ID'),
            'created_by' => \Yii::t('skeeks/shop/app', 'Created By'),
            'updated_by' => \Yii::t('skeeks/shop/app', 'Updated By'),
            'created_at' => \Yii::t('skeeks/shop/app', 'Created At'),
            'updated_at' => \Yii::t('skeeks/shop/app', 'Updated At'),
            'shop_product_price_id' => \Yii::t('skeeks/shop/app', 'Shop Product Price ID'),
            'price' => \Yii::t('skeeks/shop/app', 'Price'),
            'currency_code' => \Yii::t('skeeks/shop/app', 'Currency Code'),
            'quantity_from' => \Yii::t('skeeks/shop/app', 'Quantity From'),
            'quantity_to' => \Yii::t('skeeks/shop/app', 'Quantity To'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopProductPrice()
    {
        return $this->hasOne(ShopProductPrice::className(), ['id' => 'shop_product_price_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCurrency()
    {
        return $this->hasOne(Currency::className(), ['code' => 'currency_code']);
    }

    /**
     * @return Money
     */
    public function getMoney()
    {
        return Money::fromString($this->price, $this->currency_code);
    }
}