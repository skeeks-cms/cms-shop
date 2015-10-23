<?php

namespace skeeks\cms\shop\models;

use skeeks\cms\relatedProperties\models\RelatedPropertyEnumModel;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%shop_person_type_property_enum}}".
 *
 * @property integer $id
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $property_id
 * @property string $value
 * @property string $def
 * @property string $code
 * @property integer $priority
 *
 * @property ShopPersonTypeProperty $property
 * @property CmsUser $createdBy
 * @property CmsUser $updatedBy
 *
 */
class ShopPersonTypePropertyEnum extends RelatedPropertyEnumModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%shop_person_type_property_enum}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'property_id', 'priority'], 'integer'],
            [['value', 'code'], 'required'],
            [['value'], 'string', 'max' => 255],
            [['def'], 'string', 'max' => 1],
            [['code'], 'string', 'max' => 32]
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::rules(), [
            'id'            => \skeeks\cms\shop\Module::t('app', 'Person type ID'),
            'created_by'    => \skeeks\cms\shop\Module::t('app', 'Created By'),
            'updated_by'    => \skeeks\cms\shop\Module::t('app', 'Updated By'),
            'created_at'    => \skeeks\cms\shop\Module::t('app', 'Created At'),
            'updated_at'    => \skeeks\cms\shop\Module::t('app', 'Updated At'),
            'property_id'   => \skeeks\cms\shop\Module::t('app', 'Property ID'),
            'value'         => \skeeks\cms\shop\Module::t('app', 'Value'),
            'def'           => \skeeks\cms\shop\Module::t('app', 'Def'),
            'code'          => \skeeks\cms\shop\Module::t('app', 'Code'),
            'priority'      => \skeeks\cms\shop\Module::t('app', 'Priority'),
        ]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProperty()
    {
        return $this->hasOne(ShopPersonTypeProperty::className(), ['id' => 'property_id']);
    }


}