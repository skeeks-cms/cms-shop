<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */

namespace skeeks\cms\shop\models;

use violuke\Barcodes\BarcodeValidator;
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
     * @param $type
     * @return string
     */
    static public function getBarcodeTypeFromValidator($type = '')
    {
        if ($type == BarcodeValidator::TYPE_EAN) {
            return self::TYPE_EAN13;
        } elseif($type == BarcodeValidator::TYPE_EAN_8) {
            return self::TYPE_EAN8;
        } elseif($type == BarcodeValidator::TYPE_UPC) {
            return self::TYPE_UPC;
        }elseif($type == BarcodeValidator::TYPE_GTIN) {
            return self::TYPE_GTIN;
        }
        
        return '';
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


            [['value'], 'trim'],
            [['barcode_type'], 'trim'],

            [['value'], 'string'],

            [['value'], function($attribute) {
                $bc_validator = new \violuke\Barcodes\BarcodeValidator($this->{$attribute});
                if (!$bc_validator->isValid()) {
                    $this->addError($attribute, "Штрихкод: " . $this->{$attribute} . " - не корректный");
                    return false;
                }

                return true;
            }],

            [['barcode_type'], 'default', 'value' => function() {
                $bc_validator = new \violuke\Barcodes\BarcodeValidator($this->value);
                if ($bc_validator->getType()) {
                    return self::getBarcodeTypeFromValidator($bc_validator->getType());
                }
                
                return '';
            }],
            
            [['barcode_type'],  function() {
                $bc_validator = new \violuke\Barcodes\BarcodeValidator($this->value);
                if ($bc_validator->getType()) {
                    $type = self::getBarcodeTypeFromValidator($bc_validator->getType());
                    if ($type && $type != $this->barcode_type) {
                        $this->barcode_type = $type;
                    }
                }
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