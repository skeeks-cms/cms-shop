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

    public function asText()
    {
        $result = parent::asText();

        if ($this->name) {
            $result = $result . " ({$this->external_code})";
        } else {
            $result = $result . $this->external_code;
        }
        return $result;
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
            'external_code'           => 'Код характеристики',
            'name'                    => 'Название',
            'is_visible'              => 'Видимость',
            'cms_content_property_id' => 'Характеристика на сайте',
            'priority'                => 'Сортировка',
            'property_type'           => 'Тип свойства',
            'shop_store_id'           => 'Склад',

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