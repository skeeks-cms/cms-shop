<?php

namespace skeeks\cms\shop\models;

use skeeks\cms\models\CmsSite;
use skeeks\modules\cms\money\Currency;
use Yii;

/**
 * This is the model class for table "{{%shop_basket}}".
 *
 * @property integer $id
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $fuser_id
 * @property integer $order_id
 * @property integer $product_id
 * @property integer $product_price_id
 * @property string $price
 * @property string $currency_code
 * @property string $weight
 * @property string $quantity
 * @property string $site_code
 * @property string $delay
 * @property string $name
 * @property string $can_buy
 * @property string $callback_func
 * @property string $notes
 * @property string $order_callback_func
 * @property string $detail_page_url
 * @property string $discount_price
 * @property string $cancel_callback_func
 * @property string $pay_callback_func
 * @property string $catalog_xml_id
 * @property string $product_xml_id
 * @property string $discount_name
 * @property string $discount_value
 * @property string $discount_coupon
 * @property string $vat_rate
 * @property string $subscribe
 * @property string $barcode_multi
 * @property string $reserved
 * @property double $reserve_quantity
 * @property string $deducted
 * @property string $custom_price
 * @property string $dimensions
 * @property integer $type
 * @property integer $set_parent_id
 * @property string $measure_name
 * @property integer $measure_code
 * @property string $recommendation
 *
 * @property Currency $currency
 * @property ShopFuser $fuser
 * @property ShopOrder $order
 * @property ShopProduct $product
 * @property ShopProductPrice $productPrice
 * @property CmsSite $site
 */
class ShopBasket extends \skeeks\cms\models\Core
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%shop_basket}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'fuser_id', 'order_id', 'product_id', 'product_price_id', 'type', 'set_parent_id', 'measure_code'], 'integer'],
            [['fuser_id', 'price', 'currency_code', 'site_code', 'name'], 'required'],
            [['price', 'weight', 'quantity', 'discount_price', 'vat_rate', 'reserve_quantity'], 'number'],
            [['currency_code'], 'string', 'max' => 3],
            [['site_code'], 'string', 'max' => 15],
            [['delay', 'can_buy', 'subscribe', 'barcode_multi', 'reserved', 'deducted', 'custom_price'], 'string', 'max' => 1],
            [['name', 'callback_func', 'notes', 'order_callback_func', 'detail_page_url', 'cancel_callback_func', 'pay_callback_func', 'discount_name', 'dimensions', 'recommendation'], 'string', 'max' => 255],
            [['catalog_xml_id', 'product_xml_id'], 'string', 'max' => 100],
            [['discount_value', 'discount_coupon'], 'string', 'max' => 32],
            [['measure_name'], 'string', 'max' => 50],
            [['site_code'], 'default', 'value' => \Yii::$app->cms->site->code],
            [['currency_code'], 'default', 'value' => \Yii::$app->money->currencyCode]
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
            'fuser_id' => Yii::t('app', 'Fuser ID'),
            'order_id' => Yii::t('app', 'Order ID'),
            'product_id' => Yii::t('app', 'Product ID'),
            'product_price_id' => Yii::t('app', 'Product Price ID'),
            'price' => Yii::t('app', 'Price'),
            'currency_code' => Yii::t('app', 'Currency Code'),
            'weight' => Yii::t('app', 'Weight'),
            'quantity' => Yii::t('app', 'Quantity'),
            'site_code' => Yii::t('app', 'Site Code'),
            'delay' => Yii::t('app', 'Delay'),
            'name' => Yii::t('app', 'Name'),
            'can_buy' => Yii::t('app', 'Can Buy'),
            'callback_func' => Yii::t('app', 'Callback Func'),
            'notes' => Yii::t('app', 'Notes'),
            'order_callback_func' => Yii::t('app', 'Order Callback Func'),
            'detail_page_url' => Yii::t('app', 'Detail Page Url'),
            'discount_price' => Yii::t('app', 'Discount Price'),
            'cancel_callback_func' => Yii::t('app', 'Cancel Callback Func'),
            'pay_callback_func' => Yii::t('app', 'Pay Callback Func'),
            'catalog_xml_id' => Yii::t('app', 'Catalog Xml ID'),
            'product_xml_id' => Yii::t('app', 'Product Xml ID'),
            'discount_name' => Yii::t('app', 'Discount Name'),
            'discount_value' => Yii::t('app', 'Discount Value'),
            'discount_coupon' => Yii::t('app', 'Discount Coupon'),
            'vat_rate' => Yii::t('app', 'Vat Rate'),
            'subscribe' => Yii::t('app', 'Subscribe'),
            'barcode_multi' => Yii::t('app', 'Barcode Multi'),
            'reserved' => Yii::t('app', 'Reserved'),
            'reserve_quantity' => Yii::t('app', 'Reserve Quantity'),
            'deducted' => Yii::t('app', 'Deducted'),
            'custom_price' => Yii::t('app', 'Custom Price'),
            'dimensions' => Yii::t('app', 'Dimensions'),
            'type' => Yii::t('app', 'Type'),
            'set_parent_id' => Yii::t('app', 'Set Parent ID'),
            'measure_name' => Yii::t('app', 'Measure Name'),
            'measure_code' => Yii::t('app', 'Measure Code'),
            'recommendation' => Yii::t('app', 'Recommendation'),
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
    public function getFuser()
    {
        return $this->hasOne(ShopFuser::className(), ['id' => 'fuser_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrder()
    {
        return $this->hasOne(ShopOrder::className(), ['id' => 'order_id']);
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
    public function getProductPrice()
    {
        return $this->hasOne(ShopProductPrice::className(), ['id' => 'product_price_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSite()
    {
        return $this->hasOne(CmsSite::className(), ['code' => 'site_code']);
    }
}