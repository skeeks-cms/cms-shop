<?php

namespace skeeks\cms\shop\models;

use skeeks\cms\components\Cms;
use skeeks\cms\relatedProperties\models\RelatedPropertyModel;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%shop_person_type_property}}".
 *
 * @property integer $id
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $name
 * @property string $code
 * @property string $active
 * @property integer $priority
 * @property string $property_type
 * @property string $list_type
 * @property string $multiple
 * @property integer $multiple_cnt
 * @property string $with_description
 * @property string $searchable
 * @property string $filtrable
 * @property string $is_required
 * @property integer $version
 * @property string $component
 * @property string $component_settings
 * @property string $hint
 * @property string $smart_filtrable
 * @property integer $shop_person_type_id
 * @property string $is_order_location_delivery
 * @property string $is_order_location_tax
 * @property string $is_order_postcode
 * @property string $is_user_email
 * @property string $is_user_phone
 * @property string $is_user_username
 * @property string $is_user_name
 * @property string $is_buyer_name
 *
 * @property ShopPersonType $shopPersonType
 * @property CmsUser $createdBy
 * @property CmsUser $updatedBy
 * @property ShopPersonTypePropertyEnum[] $shopPersonTypePropertyEnums
 *
 * @property ShopPersonTypePropertyEnum[] $enums
 */
class ShopPersonTypeProperty extends RelatedPropertyModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%shop_person_type_property}}';
    }

    public function getElementProperties()
    {
        return [];
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEnums()
    {
        return $this->hasMany(ShopPersonTypePropertyEnum::className(), ['property_id' => 'id']);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'priority', 'multiple_cnt', 'version', 'shop_person_type_id'], 'integer'],
            [['name', 'shop_person_type_id'], 'required'],
            [['component_settings'], 'string'],
            [['name', 'component', 'hint'], 'string', 'max' => 255],
            [['is_order_location_delivery', 'is_order_location_tax', 'is_order_postcode', 'is_user_email', 'is_user_phone', 'is_user_username', 'is_user_name', 'is_buyer_name'], 'string', 'max' => 1],
            [['is_order_location_delivery', 'is_order_location_tax', 'is_order_postcode', 'is_user_email', 'is_user_phone', 'is_user_username', 'is_user_name', 'is_buyer_name'], 'default', 'value' => Cms::BOOL_N],
            [['code'], 'string', 'max' => 64],
            [['active', 'property_type', 'list_type', 'multiple', 'with_description', 'searchable', 'filtrable', 'is_required', 'smart_filtrable'], 'string', 'max' => 1],
            [['code', 'shop_person_type_id'], 'unique', 'targetAttribute' => ['shop_person_type_id', 'code'], 'message' => 'Для данного типа плательщика этот код уже занят.'],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array_merge(parent::rules(), [
            'id' => Yii::t('app', 'ID'),
            'created_by' => Yii::t('app', 'Created By'),
            'updated_by' => Yii::t('app', 'Updated By'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'name' => Yii::t('app', 'Name'),
            'code' => Yii::t('app', 'Code'),
            'active' => Yii::t('app', 'Active'),
            'priority' => Yii::t('app', 'Priority'),
            'property_type' => Yii::t('app', 'Property Type'),
            'list_type' => Yii::t('app', 'List Type'),
            'multiple' => Yii::t('app', 'Multiple'),
            'multiple_cnt' => Yii::t('app', 'Multiple Cnt'),
            'with_description' => Yii::t('app', 'With Description'),
            'searchable' => Yii::t('app', 'Searchable'),
            'filtrable' => Yii::t('app', 'Filtrable'),
            'is_required' => Yii::t('app', 'Is Required'),
            'version' => Yii::t('app', 'Version'),
            'component' => Yii::t('app', 'Component'),
            'component_settings' => Yii::t('app', 'Component Settings'),
            'hint' => Yii::t('app', 'Hint'),
            'smart_filtrable' => Yii::t('app', 'Smart Filtrable'),
            'shop_person_type_id' => Yii::t('app', 'Shop Person Type ID'),
            'is_order_location_delivery' => Yii::t('app', 'Значение свойства будет использовано как местоположение покупателя для расчета стоимости доставки (только для свойств типа LOCATION)'),
            'is_order_location_tax' => Yii::t('app', 'Значение свойства будет использовано как местоположение покупателя для расчета налогов (только для свойств типа LOCATION)'),
            'is_order_postcode' => Yii::t('app', 'Значение свойства будет использовано как почтовый индекс покупателя для расчета стоимости доставки'),
            'is_user_email' => Yii::t('app', 'Значение свойства будет использовано как E-Mail при регистрации нового пользователя'),
            'is_user_phone' => Yii::t('app', 'Значение свойства будет использовано как Телефон при регистрации нового пользователя'),
            'is_user_username' => Yii::t('app', 'Значение свойства будет использовано как Логин при регистрации нового пользователя'),
            'is_user_name' => Yii::t('app', 'Значение свойства будет использовано как Имя при регистрации нового пользователя'),
            'is_buyer_name' => Yii::t('app', 'Значение свойства будет использовано как Имя профиля покупателя'),
        ]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopPersonType()
    {
        return $this->hasOne(ShopPersonType::className(), ['id' => 'shop_person_type_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopPersonTypePropertyEnums()
    {
        return $this->hasMany(ShopPersonTypePropertyEnum::className(), ['property_id' => 'id']);
    }
}