<?php

namespace skeeks\cms\shop\models;

use skeeks\modules\cms\money\models\Currency;
use skeeks\modules\cms\money\Money;
use Yii;

/**
 * This is the model class for table "{{%shop_product_price}}".
 *
 * @property integer $id
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $product_id
 * @property integer $type_price_id
 * @property string $price
 * @property string $currency_code
 * @property integer $quantity_from
 * @property integer $quantity_to
 * @property string $tmp_id
 *
 * @property Currency $currency
 * @property ShopProduct $product
 * @property ShopTypePrice $typePrice
 * @property Money $money
 */
class ShopProductPrice extends \skeeks\cms\models\Core
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%shop_product_price}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'product_id', 'type_price_id', 'quantity_from', 'quantity_to'], 'integer'],
            [['product_id', 'type_price_id'], 'required'],
            [['price'], 'number'],
            [['currency_code'], 'string', 'max' => 3],
            [['tmp_id'], 'string', 'max' => 40],
            [['currency_code'], 'default', 'value' => \Yii::$app->money->currencyCode],
            [['price'], 'default', 'value' => 0],
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
            'product_id'    => \skeeks\cms\shop\Module::t('app', 'Product ID'),
            'type_price_id' => \skeeks\cms\shop\Module::t('app', 'Type Price ID'),
            'price'         => \skeeks\cms\shop\Module::t('app', 'Price'),
            'currency_code' => \skeeks\cms\shop\Module::t('app', 'Currency Code'),
            'quantity_from' => \skeeks\cms\shop\Module::t('app', 'Quantity From'),
            'quantity_to'   => \skeeks\cms\shop\Module::t('app', 'Quantity To'),
            'tmp_id'        => \skeeks\cms\shop\Module::t('app', 'Tmp ID'),
        ];
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
    public function getProduct()
    {
        return $this->hasOne(ShopProduct::className(), ['id' => 'product_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTypePrice()
    {
        return $this->hasOne(ShopTypePrice::className(), ['id' => 'type_price_id']);
    }

    /**
     * @return Money
     */
    public function getMoney()
    {
        return Money::fromString($this->price, $this->currency_code);
    }
}