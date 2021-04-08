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
 * @property int                       $id
 * @property int                       $shop_store_id
 * @property string                    $external_code
 * @property string|null               $name
 * @property string|null               $property_type
 * @property int                       $is_visible
 * @property int                       $priority
 * @property int|null                  $cms_content_property_id
 * @property string|null               $import_delimetr
 *
 * @property CmsContentProperty        $cmsContentProperty
 * @property ShopStore                 $shopStore
 * @property ShopStorePropertyOption[] $shopStorePropertyOptions
 *
 * @property string                    $propertyTypeAsText
 *
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class ShopStoreProperty extends ActiveRecord
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
        return '{{%shop_store_property}}';
    }

    /**
     * @return array
     */
    static public function getPopertyTypeOptions()
    {
        return [
            self::PROPERTY_TYPE_LIST   => 'Список',
            self::PROPERTY_TYPE_STRING => 'Строка',
            self::PROPERTY_TYPE_NUMBER => 'Число',
            self::PROPERTY_TYPE_ARRAY  => 'Массив',
        ];
    }

    /**
     * @return string
     */
    public function getPropertyTypeAsText()
    {
        return (string)ArrayHelper::getValue(self::getPopertyTypeOptions(), $this->property_type);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['external_code'], 'required'],
            [['priority'], 'integer'],
            [['external_code'], 'trim'],
            [['property_type'], 'string'],
            [['import_delimetr'], 'string'],
            [['property_type'], 'in', 'range' => array_keys(self::getPopertyTypeOptions())],
            [['shop_store_id', 'is_visible', 'cms_content_property_id'], 'integer'],
            [['external_code', 'name'], 'string', 'max' => 255],
            [['cms_content_property_id'], 'exist', 'skipOnError' => true, 'targetClass' => CmsContentProperty::className(), 'targetAttribute' => ['cms_content_property_id' => 'id']],

            [
                ['shop_store_id', 'external_code'],
                'unique',
                'targetAttribute' => ['shop_store_id', 'external_code'],
                'when'            => function (self $model) {
                    return (bool)$model->external_code;
                },
            ],

        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'id'                      => 'ID',
            'external_code'           => 'Код свойства',
            'name'                    => 'Название',
            'is_visible'              => 'Видимость',
            'cms_content_property_id' => 'Свойство товара в cms',
            'priority'                => 'Сортировка',
            'property_type'           => 'Тип свойства',
            'shop_store_id'            => 'Склад',

            'import_delimetr' => 'Разделители',
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
    public function getShopStore()
    {
        return $this->hasOne(ShopStore::className(), ['id' => 'shop_store_id']);
    }

    /**
     * Gets query for [[ShopSupplierPropertyOptions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getShopStorePropertyOptions()
    {
        return $this->hasMany(ShopStorePropertyOption::className(), ['shop_store_property_id' => 'id']);
    }
}