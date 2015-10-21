<?php

namespace skeeks\cms\shop\models;

use skeeks\cms\relatedProperties\models\RelatedElementPropertyModel;
use Yii;

/**
 * This is the model class for table "{{%shop_buyer_property}}".
 *
 * @property integer $id
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $property_id
 * @property integer $element_id
 * @property string $value
 * @property integer $value_enum
 * @property string $value_num
 * @property string $description
 *
 * @property ShopPersonTypeProperty $property
 * @property CmsUser $createdBy
 * @property ShopBuyer $element
 * @property CmsUser $updatedBy
 */
class ShopBuyerProperty extends RelatedElementPropertyModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%shop_buyer_property}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'property_id', 'element_id', 'value_enum'], 'integer'],
            [['value'], 'required'],
            [['value_num'], 'number'],
            [['value', 'description'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'            => \skeeks\cms\shop\Module::t('app', 'ID'),
            'created_by'    => \skeeks\cms\shop\Module::t('app', 'Created By'),
            'updated_by'    => \skeeks\cms\shop\Module::t('app', 'Updated By'),
            'created_at'    => \skeeks\cms\shop\Module::t('app', 'Created At'),
            'updated_at'    => \skeeks\cms\shop\Module::t('app', 'Updated At'),
            'property_id'   => \skeeks\cms\shop\Module::t('app', 'Property ID'),
            'element_id'    => \skeeks\cms\shop\Module::t('app', 'Element ID'),
            'value'         => \skeeks\cms\shop\Module::t('app', 'Value'),
            'value_enum'    => \skeeks\cms\shop\Module::t('app', 'Value Enum'),
            'value_num'     => \skeeks\cms\shop\Module::t('app', 'Value Num'),
            'description'   => \skeeks\cms\shop\Module::t('app', 'Description'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProperty()
    {
        return $this->hasOne(ShopPersonTypeProperty::className(), ['id' => 'property_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(CmsUser::className(), ['id' => 'created_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getElement()
    {
        return $this->hasOne(ShopBuyer::className(), ['id' => 'element_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUpdatedBy()
    {
        return $this->hasOne(CmsUser::className(), ['id' => 'updated_by']);
    }
}