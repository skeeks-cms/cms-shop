<?php

namespace skeeks\cms\shop\models;

use skeeks\cms\models\behaviors\HasRelatedProperties;
use skeeks\cms\models\behaviors\traits\HasRelatedPropertiesTrait;
use skeeks\cms\relatedProperties\models\RelatedElementModel;
use Yii;

/**
 * This is the model class for table "{{%shop_buyer}}".
 *
 * @property integer $id
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $name
 * @property integer $cms_user_id
 * @property integer $shop_person_type_id
 *
 * @property ShopPersonType $shopPersonType
 * @property CmsUser $cmsUser
 * @property CmsUser $createdBy
 * @property CmsUser $updatedBy
 * @property ShopBuyerProperty[] $shopBuyerProperties
 * @property ShopOrder[] $shopOrders
 */
class ShopBuyer extends RelatedElementModel
{
    use HasRelatedPropertiesTrait;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%shop_buyer}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [

            HasRelatedProperties::className() =>
            [
                'class'                             => HasRelatedProperties::className(),
                'relatedElementPropertyClassName'   => ShopBuyerProperty::className(),
                'relatedPropertyClassName'          => ShopPersonTypeProperty::className(),
            ],

        ]);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'cms_user_id', 'shop_person_type_id'], 'integer'],
            [['name', 'cms_user_id', 'shop_person_type_id'], 'required'],
            [['name'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'                    => \skeeks\cms\shop\Module::t('app', 'ID'),
            'created_by'            => \skeeks\cms\shop\Module::t('app', 'Created By'),
            'updated_by'            => \skeeks\cms\shop\Module::t('app', 'Updated By'),
            'created_at'            => \skeeks\cms\shop\Module::t('app', 'Created At'),
            'updated_at'            => \skeeks\cms\shop\Module::t('app', 'Updated At'),
            'name'                  => \skeeks\cms\shop\Module::t('app', 'The profile name'),
            'cms_user_id'           => \skeeks\cms\shop\Module::t('app', 'User site'),
            'shop_person_type_id'   => \skeeks\cms\shop\Module::t('app', 'Type payer'),
        ];
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
    public function getCmsUser()
    {
        return $this->hasOne(CmsUser::className(), ['id' => 'cms_user_id']);
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
    public function getUpdatedBy()
    {
        return $this->hasOne(CmsUser::className(), ['id' => 'updated_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopBuyerProperties()
    {
        return $this->hasMany(ShopBuyerProperty::className(), ['element_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopOrders()
    {
        return $this->hasMany(ShopOrder::className(), ['buyer_id' => 'id']);
    }


    /**
     *
     * Все возможные свойства связанные с моделью
     *
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getRelatedProperties()
    {
        return $this->shopPersonType->getShopPersonTypeProperties();
    }
}