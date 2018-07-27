<?php

namespace skeeks\cms\shop\models;

use skeeks\cms\relatedProperties\models\RelatedElementPropertyModel;

/**
 * This is the model class for table "{{%shop_buyer_property}}".
 *
 * @property integer                $id
 * @property integer                $created_by
 * @property integer                $updated_by
 * @property integer                $created_at
 * @property integer                $updated_at
 * @property integer                $property_id
 * @property integer                $element_id
 * @property string                 $value
 * @property integer                $value_enum
 * @property string                 $value_num
 * @property string                 $description
 *
 * @property ShopPersonTypeProperty $property
 * @property CmsUser                $createdBy
 * @property ShopBuyer              $element
 * @property CmsUser                $updatedBy
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