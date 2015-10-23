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
            'id'                        => \skeeks\cms\shop\Module::t('app', 'ID'),
            'created_by'                => \skeeks\cms\shop\Module::t('app', 'Created By'),
            'updated_by'                => \skeeks\cms\shop\Module::t('app', 'Updated By'),
            'created_at'                => \skeeks\cms\shop\Module::t('app', 'Created At'),
            'updated_at'                => \skeeks\cms\shop\Module::t('app', 'Updated At'),
            'name'                      => \skeeks\cms\shop\Module::t('app', 'Name'),
            'code'                      => \skeeks\cms\shop\Module::t('app', 'Code'),
            'active'                    => \skeeks\cms\shop\Module::t('app', 'Active'),
            'priority'                  => \skeeks\cms\shop\Module::t('app', 'Priority'),
            'property_type'             => \skeeks\cms\shop\Module::t('app', 'Property Type'),
            'list_type'                 => \skeeks\cms\shop\Module::t('app', 'List Type'),
            'multiple'                  => \skeeks\cms\shop\Module::t('app', 'Multiple'),
            'multiple_cnt'              => \skeeks\cms\shop\Module::t('app', 'Multiple Cnt'),
            'with_description'          => \skeeks\cms\shop\Module::t('app', 'With Description'),
            'searchable'                => \skeeks\cms\shop\Module::t('app', 'Searchable'),
            'filtrable'                 => \skeeks\cms\shop\Module::t('app', 'Filtrable'),
            'is_required'               => \skeeks\cms\shop\Module::t('app', 'Is Required'),
            'version'                   => \skeeks\cms\shop\Module::t('app', 'Version'),
            'component'                 => \skeeks\cms\shop\Module::t('app', 'Component'),
            'component_settings'        => \skeeks\cms\shop\Module::t('app', 'Component Settings'),
            'hint'                      => \skeeks\cms\shop\Module::t('app', 'Hint'),
            'smart_filtrable'           => \skeeks\cms\shop\Module::t('app', 'Smart Filtrable'),
            'shop_person_type_id'       => \skeeks\cms\shop\Module::t('app', 'Shop Person Type ID'),
            'is_order_location_delivery' => \skeeks\cms\shop\Module::t('app', 'The value of the properties will be used as the location of the buyer to calculate the cost of delivery (only for type LOCATION)'),
            'is_order_location_tax'     => \skeeks\cms\shop\Module::t('app', 'The value of the properties will be used as the location of the buyer to calculate the tax (only for type LOCATION)'),
            'is_order_postcode'         => \skeeks\cms\shop\Module::t('app', 'The value of the properties will be used as the postcode for the buyer to calculate the cost of delivery'),
            'is_user_email'             => \skeeks\cms\shop\Module::t('app', 'The value of the properties will be used as the E-Mail with the new user registration'),
            'is_user_phone'             => \skeeks\cms\shop\Module::t('app', 'The value of the properties will be used as a phone when registering a new user'),
            'is_user_username'          => \skeeks\cms\shop\Module::t('app', 'The value of the properties will be used as the login when registering a new user'),
            'is_user_name'              => \skeeks\cms\shop\Module::t('app', 'The property value is used as a name when registering a new user'),
            'is_buyer_name'             => \skeeks\cms\shop\Module::t('app', 'The value of the properties will be used as the name of the buyer profile'),
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