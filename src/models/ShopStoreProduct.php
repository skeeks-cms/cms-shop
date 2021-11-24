<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */

namespace skeeks\cms\shop\models;

use skeeks\cms\models\behaviors\HasJsonFieldsBehavior;
use skeeks\cms\relatedProperties\PropertyType;
use yii\helpers\ArrayHelper;

/**
 * @property integer      $shop_store_id
 * @property integer|nukk $shop_product_id
 * @property float        $quantity
 * @property string|null  $external_id
 * @property string|null  $name
 * @property array        $external_data
 * @property float        $purchase_price
 * @property float        $selling_price
 *
 * @property ShopProduct  $shopProduct
 * @property ShopStore    $shopStore
 *
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class ShopStoreProduct extends \skeeks\cms\base\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%shop_store_product}}';
    }

    public function init()
    {
        $this->on(self::EVENT_AFTER_FIND, [$this, "_afterFind"]);
        return parent::init();
    }

    public function _afterFind($event)
    {
        $this->quantity = (float)$this->quantity;
    }


    public function behaviors()
    {
        $behaviors = ArrayHelper::merge(parent::behaviors(), [
            HasJsonFieldsBehavior::class => [
                'class'  => HasJsonFieldsBehavior::class,
                'fields' => [
                    'external_data',
                ],
            ],
        ]);

        return $behaviors;
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [

            [['created_by', 'updated_by', 'created_at', 'updated_at'], 'integer'],


            [['quantity'], 'number'],
            [['name'], 'string'],
            [['external_id'], 'string'],
            [['purchase_price'], 'number'],
            [['selling_price'], 'number'],

            [['shop_store_id'], 'integer'],
            [['shop_product_id'], 'integer'],
            [['quantity'], 'default', 'value' => 0],

            [['external_id'], 'default', 'value' => null],
            [['external_data'], 'default', 'value' => null],
            [['name'], 'default', 'value' => null],
            [['shop_product_id'], 'default', 'value' => null],

            [
                ['shop_store_id', 'shop_product_id'],
                'unique',
                'targetAttribute' => ['shop_store_id', 'shop_product_id'],
                'when'            => function () {
                    return $this->shop_product_id;
                },
            ],

            [
                ['shop_store_id', 'external_id'],
                'unique',
                'targetAttribute' => ['shop_store_id', 'external_id'],
                'when'            => function () {
                    return $this->external_id;
                },
            ],

            [
                ['name'],
                'required',
                'when' => function () {
                    return !$this->shop_product_id;
                },
            ],
            [
                ['shop_product_id'],
                'required',
                'when' => function () {
                    return !$this->name;
                },
            ],

            [['external_data'], 'safe'],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'shop_store_id'   => "Склад",
            'shop_product_id' => "Товар",
            'quantity'        => "Количество",
            'shop_product_id' => "Товар",
            'name'            => "Название",
            'external_id'     => "Код",
            'purchase_price'  => "Закупочная цена",
            'selling_price'   => "Цена продажи",
        ]);
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopProduct()
    {
        return $this->hasOne(ShopProduct::class, ['id' => 'shop_product_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopStore()
    {
        return $this->hasOne(ShopStore::class, ['id' => 'shop_store_id']);
    }

    /**
     * @return string
     */
    public function asText()
    {
        if ($this->shopProduct) {
            $name = $this->shopProduct->asText;
        } else {
            $name = $this->name;
        }

        /*if ($this->external_id) {
            $name = $name . ", " . $this->external_id;
        }*/

        return $name;
    }


    public function loadDataToElementProduct(ShopCmsContentElement $model, ShopProduct $shopProduct)
    {
        $model->name = $this->name;

        if ($this->external_data) {
            //Это нужно для определения раздела
            foreach ($this->external_data as $key => $value) {
                /**
                 * @var $property ShopStoreProperty
                 * @var $option ShopStorePropertyOption
                 */
                $key = trim($key);

                if ($property = $this->shopStore->getShopStoreProperties()->andWhere(['external_code' => $key])->one()) {
                    if (in_array($property->property_nature, [
                        ShopStoreProperty::PROPERTY_NATURE_TREE,
                    ])) {


                        $code = "";
                        if ($property->cmsContentProperty) {
                            $code = $property->cmsContentProperty->code;
                        }

                        if ($property->is_options) {

                            if ($property->import_delimetr) {
                                $value = explode($property->import_delimetr, $value);
                                foreach ($value as $k => $v) {
                                    $value[$k] = trim($v);
                                }
                            }

                            if (is_array($value)) {
                                $data = [];
                                foreach ($value as $k => $v) {
                                    if ($option = $property->getShopStorePropertyOptions()->andWhere(['name' => $v])->one()) {
                                        if ($option->cms_tree_id) {
                                            $model->tree_id = $option->cms_tree_id;
                                        }
                                    }
                                }
                                if ($code && $model->relatedPropertiesModel->hasAttribute($code)) {
                                    $model->relatedPropertiesModel->setAttribute($code, $data);
                                }

                            } else {

                                if ($option = $property->getShopStorePropertyOptions()->andWhere(['name' => $value])->one()) {
                                    if ($option->cms_tree_id) {
                                        $model->tree_id = $option->cms_tree_id;
                                    }
                                }
                            }
                        }


                    }
                }
            }

            foreach ($this->external_data as $key => $value) {
                /**
                 * @var $property ShopStoreProperty
                 * @var $option ShopStorePropertyOption
                 */
                $key = trim($key);

                if ($property = $this->shopStore->getShopStoreProperties()->andWhere(['external_code' => $key])->one()) {
                    //if ($property->cmsContentProperty) {

                    if (in_array($property->property_nature, [
                        ShopStoreProperty::PROPERTY_NATURE_WIDTH,
                        ShopStoreProperty::PROPERTY_NATURE_HEIGHT,
                        ShopStoreProperty::PROPERTY_NATURE_LENGTH,
                        ShopStoreProperty::PROPERTY_NATURE_WEIGHT,
                    ])) {
                        
                        $code = "";
                        if ($property->cmsContentProperty) {
                            $code = $property->cmsContentProperty->code;
                        }
                        
                        if (is_string($value)) {
                            $value = trim($value);
                            $value = str_replace(" ", "", $value);
                            $value = str_replace(",", ".", $value);

                            if ($property->import_multiply) {
                                $value = ((float)$value) * $property->import_multiply;
                            }

                            if ($code && $model->relatedPropertiesModel->hasAttribute($code)) {
                                $model->relatedPropertiesModel->setAttribute($code, $value);
                            }

                            $shopProduct->{$property->property_nature} = $value;
                        }

                    } elseif (in_array($property->property_nature, [
                        ShopStoreProperty::PROPERTY_NATURE_BARCODE,
                    ])) {

                        $delimetr = ",";
                        if ($property->import_delimetr) {
                            $delimetr = $property->import_delimetr;
                        }

                        $value = explode($delimetr, $value);
                        foreach ($value as $k => $v) {
                            $value[$k] = trim($v);
                        }

                        if (is_array($value)) {

                            $shopProduct->setBarcodes($value);
                        }


                    }  elseif (in_array($property->property_nature, [
                            ShopStoreProperty::PROPERTY_NATURE_EAV,
                        ]) || $property->cmsContentProperty) {

                        $code = "";
                        if ($property->cmsContentProperty) {
                            $code = $property->cmsContentProperty->code;
                        }

                        if ($property->is_options) {

                            if ($property->import_delimetr) {
                                $value = explode($property->import_delimetr, $value);
                                foreach ($value as $k => $v) {
                                    $value[$k] = trim($v);
                                }
                            }

                            if (is_array($value)) {
                                $data = [];
                                foreach ($value as $k => $v) {
                                    if ($option = $property->getShopStorePropertyOptions()->andWhere(['name' => $v])->one()) {
                                        $data[] = (string)($option->cms_content_element_id ? $option->cms_content_element_id : $option->cms_content_property_enum_id);
                                    }
                                }
                                if ($code && $model->relatedPropertiesModel->hasAttribute($code)) {
                                    $model->relatedPropertiesModel->setAttribute($code, $data);
                                }

                            } else {

                                if ($option = $property->getShopStorePropertyOptions()->andWhere(['name' => $value])->one()) {
                                    if ($code && $model->relatedPropertiesModel->hasAttribute($code)) {
                                        $relatedProperty = $model->relatedPropertiesModel->getRelatedProperty($code);
                                        $val = $model->relatedPropertiesModel->getAttribute($code);


                                        if ($val && $relatedProperty->is_multiple) {
                                            $newVal = $option->cms_content_element_id ? $option->cms_content_element_id : $option->cms_content_property_enum_id;
                                            if (!is_array($val)) {
                                                $val = [$val];
                                            }
                                            $val = ArrayHelper::merge($val, [$newVal]);

                                            $model->relatedPropertiesModel->setAttribute($code, $val);
                                        } else {
                                            $model->relatedPropertiesModel->setAttribute($code, $option->cms_content_element_id ? $option->cms_content_element_id : $option->cms_content_property_enum_id);
                                        }

                                    }
                                }
                            }
                        } else {
                            if (is_array($value)) {

                            } else {

                                $isNumber = false;

                                if ($cmsProperty = $model->relatedPropertiesModel->getRelatedProperty($code)) {
                                    if ($cmsProperty->property_type == PropertyType::CODE_NUMBER) {
                                        $isNumber = true;
                                    }
                                }

                                $value = trim($value);

                                if ($isNumber && $value) {
                                    $value = str_replace(" ", "", $value);
                                    $value = str_replace(",", ".", $value);
                                    $value = (float)$value;
                                }

                                if ($property->import_multiply) {
                                    $value = str_replace(" ", "", $value);
                                    $value = str_replace(",", ".", $value);
                                    $value = ((float)$value) * $property->import_multiply;
                                }

                                if ($code && $model->relatedPropertiesModel->hasAttribute($code)) {
                                    $model->relatedPropertiesModel->setAttribute($code, $value);
                                }
                            }
                        }
                    }
                }
            }
            
            //print_r($model->relatedPropertiesModel->toArray());die;
        }
    }
}