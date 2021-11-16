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
use skeeks\cms\relatedProperties\PropertyType;
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
 * @property string|null               $property_nature
 * @property string|null               $import_delimetr
 * @property float|null                $import_multiply
 * @property int                       $is_options
 *
 * @property CmsContentProperty        $cmsContentProperty
 * @property ShopStore                 $shopStore
 * @property ShopStorePropertyOption[] $shopStorePropertyOptions
 *
 * @property string                    $propertyTypeAsText
 * @property string                    $propertyNatureAsText
 *
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class ShopStoreProperty extends ActiveRecord
{
    const PROPERTY_TYPE_STRING = "string";
    const PROPERTY_TYPE_LIST = "list";
    const PROPERTY_TYPE_NUMBER = "number";
    const PROPERTY_TYPE_ARRAY = "array";

    const PROPERTY_NATURE_BARCODE = "barcode";
    const PROPERTY_NATURE_HEIGHT = "height";
    const PROPERTY_NATURE_WEIGHT = "weight";
    const PROPERTY_NATURE_WIDTH = "width";
    const PROPERTY_NATURE_LENGTH = "length";
    const PROPERTY_NATURE_EAV = "eav";
    const PROPERTY_NATURE_TREE = "tree";
    const PROPERTY_NATURE_IMAGE = "image";
    const PROPERTY_NATURE_SECOND_IMAGE = "second_image";

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%shop_store_property}}';
    }

    public function init()
    {
        $this->on(self::EVENT_BEFORE_UPDATE, [$this, '_beforeUpdate']);
        return parent::init();
    }

    public function _beforeUpdate()
    {
        if (in_array($this->property_nature, [
            self::PROPERTY_NATURE_TREE,
        ])) {
            $this->is_options = 1;
        }

        if (in_array($this->property_nature, [
            self::PROPERTY_NATURE_EAV,
        ])) {
            if (in_array($this->cmsContentProperty->property_type, [
                PropertyType::CODE_LIST,
                PropertyType::CODE_ELEMENT,
            ])) {
                $this->is_options = 1;
                return;
            }
        }
    }


    /**
     * @return array
     */
    /*static public function getPopertyTypeOptions()
    {
        return [
            //self::PROPERTY_TYPE_LIST   => 'Список',
            //self::PROPERTY_TYPE_STRING => 'Строка',
            //self::PROPERTY_TYPE_NUMBER => 'Число',
            //self::PROPERTY_TYPE_ARRAY  => 'Массив',
        ];
    }*/


    /**
     * @return array
     */
    static public function getPropertyNatureOptions()
    {
        return [
            self::PROPERTY_NATURE_EAV          => 'Характеристика',
            self::PROPERTY_NATURE_SECOND_IMAGE => 'Вторые фото',
            self::PROPERTY_NATURE_IMAGE        => 'Главное фото',
            self::PROPERTY_NATURE_BARCODE      => 'Штрихкод',
            self::PROPERTY_NATURE_WEIGHT       => 'Вес, г',
            self::PROPERTY_NATURE_WIDTH        => 'Ширина, мм',
            self::PROPERTY_NATURE_LENGTH       => 'Длина, мм',
            self::PROPERTY_NATURE_HEIGHT       => 'Высота, мм',
            self::PROPERTY_NATURE_TREE         => 'Раздел',
        ];
    }

    public function asText()
    {
        $result = parent::asText();

        if ($this->name) {
            $result = $result." ({$this->external_code})";
        } else {
            $result = $result.$this->external_code;
        }
        return $result;
    }
    /**
     * @return string
     */
    public function getPropertyNatureAsText()
    {
        return (string)ArrayHelper::getValue(self::getPropertyNatureOptions(), $this->property_nature);
    }
    /**
     * @return string
     */
    /*public function getPropertyTypeAsText()
    {
        return (string)ArrayHelper::getValue(self::getPopertyTypeOptions(), $this->property_type);
    }*/

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['external_code'], 'required'],
            [['import_multiply'], 'number'],
            [['is_options'], 'number'],
            [['priority'], 'integer'],
            [['external_code'], 'trim'],
            [['property_type'], 'string'],
            [['property_nature'], 'string'],
            [['import_delimetr'], 'string'],
            //[['property_type'], 'in', 'range' => array_keys(self::getPopertyTypeOptions())],
            [['shop_store_id', 'is_visible', 'cms_content_property_id'], 'integer'],
            [['external_code', 'name'], 'string', 'max' => 255],
            [['import_multiply'], 'default', 'value' => null],
            [['cms_content_property_id'], 'exist', 'skipOnError' => true, 'targetClass' => CmsContentProperty::className(), 'targetAttribute' => ['cms_content_property_id' => 'id']],

            [
                ['shop_store_id', 'external_code'],
                'unique',
                'targetAttribute' => ['shop_store_id', 'external_code'],
                'when'            => function (self $model) {
                    return (bool)$model->external_code;
                },
            ],
            [
                ['cms_content_property_id'],
                'required',
                'when' => function (self $model) {
                    return (bool)($model->property_nature == self::PROPERTY_NATURE_EAV);
                },
            ],

            /*[
                ['property_type'],
                function() {
                    $this->property_type = self::PROPERTY_TYPE_STRING;
                },
                'when'  => function (self $model) {
                    if ($model->cmsContentProperty) {
                        if ($model->cmsContentProperty->property_type == PropertyType::CODE_STRING) {
                            return true;
                        }
                    }

                    return false;
                },
            ],*/
            /*[
                ['property_type'],
                function() {
                    $this->property_type = self::PROPERTY_TYPE_NUMBER;
                },
                'when'  => function (self $model) {
                    if (in_array($model->shop_property_code, [
                        self::SHOP_PROPETY_WIDTH,
                        self::SHOP_PROPETY_LENGTH,
                        self::SHOP_PROPETY_HEIGHT,
                        self::SHOP_PROPETY_WEIGHT,
                    ])) {
                        die;
                        return true;
                    }

                    return false;
                },
            ],*/

        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'id'                      => 'ID',
            'external_code'           => 'Код характеристики',
            'name'                    => 'Название',
            'is_visible'              => 'Видимость',
            'cms_content_property_id' => 'Какая характеристика?',
            'priority'                => 'Сортировка',
            'property_type'           => 'Тип свойства',
            'shop_store_id'           => 'Склад',
            'import_multiply'         => 'Умножить значение на',
            'property_nature'         => 'На сайте это',
            'is_options'              => 'Собирать опции',

            'import_delimetr' => 'Разделители',
        ]);
    }
    /**
     * @inheritdoc
     */
    public function attributeHints()
    {
        return ArrayHelper::merge(parent::attributeHints(), [
            'is_visible'      => 'При отображении карточки товара эта характеристика будет показываться или не будет.',
            'external_code'   => 'Так называется характеристика у поставщика, в его файлах выгрузки, в его системе. в 99% случаев не ПЕРЕИМЕНОВЫВАТЬ эту характеристику!',
            'name'            => 'Понятное название, которое объясняет что это за характеристика. Если это поле не заполнено, то будет показываться название характеристики как у поставщика. Предыдущее поле.',
            'import_delimetr' => 'В выгрузках поставщика данные могут быть плохо структурированы, и для каких то значений, поставщик может использовать разные разделители. 
            Ваша задача понять, что это за разделители и указать в этом поле.<br /><br />
            Наглядный пример:<br />
            <ol>
<li>Есть выгрузка поствщика в csv формате</li>            
<li>В ячейке цвет, у некоторых товаров указано по несколько цветов, и разделены они запятой</li>            
</ol><br />Значит в это поле указываем символ "запятая"',
            'property_type'   => '<b>ПОЛЕ ВАЖНО ВСЕГДА ЗАПОЛНЯТЬ!</b> От этого выбора зависит как будет обрабатываться эта характеристика. 
<br />Выберите <b>СПИСОК</b>, если хотите чтобы появились опции выбора у этой характеристики (например: цвет, бренд, степень защиты, назначение и т.д.).
<br />Выберите <b>ЧИСЛО</b>, если используетя фильтр "ползунок, диапазон" (например: мощьность, длина, ширина, высота, и т.д.).
<br />Выберите <b>СТРОКА</b>, если это описательное значение (например: артикул.).
<br />Выберите <b>МАССИВ</b>, если вы программист и понимаете что делаете.
',
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