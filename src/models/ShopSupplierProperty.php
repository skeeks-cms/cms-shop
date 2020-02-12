<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */
namespace skeeks\cms\shop\models;

use skeeks\cms\base\ActiveRecord;
use skeeks\cms\models\CmsContentProperty;
use yii\helpers\ArrayHelper;
/**
 *
 * This is the model class for table "shop_supplier_property".
 *
 * @property int $id
 * @property int $shop_supplier_id
 * @property string $external_code
 * @property string|null $name
 * @property string|null $property_type
 * @property int $is_visible
 * @property int $priority
 * @property int|null $cms_content_property_id
 *
 * @property CmsContentProperty $cmsContentProperty
 * @property ShopSupplier $shopSupplier
 * @property ShopSupplierPropertyOption[] $shopSupplierPropertyOptions
 * 
 * @property string $propertyTypeAsText
 *
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class ShopSupplierProperty extends ActiveRecord
{
    const PROPERTY_TYPE_STRING = "string";
    const PROPERTY_TYPE_LIST = "list";
    const PROPERTY_TYPE_NUMBER = "number";
    const PROPERTY_TYPE_ARRAY = "array";
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%shop_supplier_property}}';
    }

    /**
     * @return array
     */
    static public function getPopertyTypeOptions()
    {
        return [
            self::PROPERTY_TYPE_LIST => 'Список',
            self::PROPERTY_TYPE_STRING => 'Строка',
            self::PROPERTY_TYPE_NUMBER => 'Число',
            self::PROPERTY_TYPE_ARRAY => 'Массив',
        ];
    }

    /**
     * @return string
     */
    public function getPropertyTypeAsText()
    {
        return (string) ArrayHelper::getValue(self::getPopertyTypeOptions(), $this->property_type);
    }
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
             [['shop_supplier_id', 'external_code'], 'required'],
             [['priority'], 'integer'],
             [['external_code'], 'trim'],
             [['property_type'], 'string'],
             [['property_type'], 'in', 'range' => array_keys(self::getPopertyTypeOptions())],
            [['shop_supplier_id', 'is_visible', 'cms_content_property_id'], 'integer'],
            [['external_code', 'name'], 'string', 'max' => 255],
            [['shop_supplier_id', 'external_code'], 'unique', 'targetAttribute' => ['shop_supplier_id', 'external_code']],
            [['cms_content_property_id'], 'exist', 'skipOnError' => true, 'targetClass' => CmsContentProperty::className(), 'targetAttribute' => ['cms_content_property_id' => 'id']],
            [['shop_supplier_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopSupplier::className(), 'targetAttribute' => ['shop_supplier_id' => 'id']],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'id' => 'ID',
            'shop_supplier_id' => 'Поставщик',
            'external_code' => 'Код свойства',
            'name' => 'Название',
            'is_visible' => 'Видимость',
            'cms_content_property_id' => 'Свойство товара в cms',
            'priority' => 'Сортировка',
            'property_type' => 'Тип свойства',
        ]);
    }

    /**
     * Gets query for [[CmsContentProperty]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCmsContentProperty()
    {
        return $this->hasOne(CmsContentProperty::className(), ['id' => 'cms_content_property_id']);
    }

    /**
     * Gets query for [[ShopSupplier]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getShopSupplier()
    {
        return $this->hasOne(ShopSupplier::className(), ['id' => 'shop_supplier_id']);
    }

    /**
     * Gets query for [[ShopSupplierPropertyOptions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getShopSupplierPropertyOptions()
    {
        return $this->hasMany(ShopSupplierPropertyOption::className(), ['shop_supplier_property_id' => 'id']);
    }
}