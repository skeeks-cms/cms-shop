<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */

namespace skeeks\cms\shop\models;

use yii\helpers\ArrayHelper;

/**
 * @property int         $id
 * @property int         $shop_product_id
 * @property string      $barcode_type
 * @property string      $value
 *
 * @property ShopProduct $shopProduct
 *
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class ShopProductBarcode extends \skeeks\cms\base\ActiveRecord
{
    const TYPE_EAN13 = "ean13";
    const TYPE_EAN8 = "ean8";
    const TYPE_CODE128 = "code128";
    const TYPE_GTIN = "gtin";
    const TYPE_UPC = "upc";

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%shop_product_barcode}}';
    }

    static public function getBarcodeTypes()
    {
        return [
            self::TYPE_EAN8 => "EAN-8",
            self::TYPE_EAN13 => "EAN-13",
            self::TYPE_CODE128 => "Code128",
            self::TYPE_GTIN => "GTIN",
            self::TYPE_UPC => "UPC",
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['created_at', 'updated_at', 'shop_product_id'], 'integer'],
            [['shop_product_id', 'value'], 'required'],
            [['barcode_type'], 'string', 'max' => 12],
            [['value'], 'string', 'max' => 128],
            [['shop_product_id', 'value'], 'unique', 'targetAttribute' => ['shop_product_id', 'value']],
            //[['shop_product_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopProduct::class, 'targetAttribute' => ['shop_product_id' => 'id']],

            [['barcode_type'], 'default', 'value' => self::TYPE_EAN13],

            [['value'], 'trim'],
            [['barcode_type'], 'trim'],

            [['value'], 'string', 'max' => 12, 'min' => 12, "when" => function() {
                return $this->barcode_type == self::TYPE_UPC;
            }],

            [['value'], 'string', 'max' => 13, 'min' => 13, "when" => function() {
                return $this->barcode_type == self::TYPE_EAN13;
            }],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [

            'shop_product_id' => 'Shop Product ID',
            'barcode_type'    => 'Barcode Type',
            'value'           => 'Value',
        ]);
    }

    /**
     * Gets query for [[ShopProduct]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getShopProduct()
    {
        return $this->hasOne(ShopProduct::class, ['id' => 'shop_product_id']);
    }

}