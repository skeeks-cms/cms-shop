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
 * @property int                          $id
 * @property int                          $cms_site_id
 * @property string                       $external_code
 * @property string|null                  $name
 * @property string|null                  $property_type
 * @property int                          $is_visible
 * @property int                          $priority
 * @property int|null                     $cms_content_property_id
 * @property string|null                  $import_delimetr
 * @property string|null                  $import_replace
 * @property number|null                  $import_miltiple
 *
 * @property CmsContentProperty           $cmsContentProperty
 * @property CmsSite                      $cmsSite
 * @property ShopSupplierPropertyOption[] $shopSupplierPropertyOptions
 *
 * @property string                       $propertyTypeAsText
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
            [['import_replace'], 'string'],
            [['import_miltiple'], 'integer'],
            [['property_type'], 'in', 'range' => array_keys(self::getPopertyTypeOptions())],
            [['cms_site_id', 'is_visible', 'cms_content_property_id'], 'integer'],
            [['external_code', 'name'], 'string', 'max' => 255],
            [['cms_content_property_id'], 'exist', 'skipOnError' => true, 'targetClass' => CmsContentProperty::className(), 'targetAttribute' => ['cms_content_property_id' => 'id']],

            [
                ['cms_site_id', 'external_code'],
                'unique',
                'targetAttribute' => ['cms_site_id', 'external_code'],
                'when'            => function (self $model) {
                    return (bool)$model->external_code;
                },
            ],

            [
                'cms_site_id',
                'default',
                'value' => function () {
                    if (\Yii::$app->skeeks->site) {
                        return \Yii::$app->skeeks->site->id;
                    }
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

            'import_delimetr' => 'Разделители',
            'import_replace'  => 'Символы для замены',
            'import_miltiple' => 'Умножить на',
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
    public function getCmsSite()
    {
        return $this->hasOne(CmsSite::className(), ['id' => 'cms_site_id']);
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