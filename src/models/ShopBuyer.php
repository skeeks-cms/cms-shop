<?php

namespace skeeks\cms\shop\models;

use skeeks\cms\models\behaviors\HasRelatedProperties;
use skeeks\cms\models\behaviors\traits\HasRelatedPropertiesTrait;
use skeeks\cms\models\CmsUser;
use skeeks\cms\relatedProperties\models\RelatedElementModel;

/**
 * This is the model class for table "{{%shop_buyer}}".
 *
 * @property integer             $id
 * @property integer             $created_by
 * @property integer             $updated_by
 * @property integer             $created_at
 * @property integer             $updated_at
 * @property string              $name
 * @property integer             $cms_user_id
 * @property integer             $shop_person_type_id
 *
 * @property ShopPersonType      $shopPersonType
 * @property CmsUser             $cmsUser
 * @property CmsUser             $createdBy
 * @property CmsUser             $updatedBy
 * @property ShopBuyerProperty[] $shopBuyerProperties
 * @property ShopOrder[]         $shopOrders
 *
 * @property string              $email read-only
 * @property string              $phone read-only
 * @property string              $registerName read-only
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

            HasRelatedProperties::class =>
                [
                    'class'                           => HasRelatedProperties::class,
                    'relatedElementPropertyClassName' => ShopBuyerProperty::class,
                    'relatedPropertyClassName'        => ShopPersonTypeProperty::class,
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
            [['shop_person_type_id'], 'required'],
            [['name'], 'string', 'max' => 255],
            [['cms_user_id'], 'default', 'value' => null],
            [
                ['name'],
                'default',
                'value' => function (self $model) {
                    return $this->shopPersonType->name;
                },
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'                  => \Yii::t('skeeks/shop/app', 'ID'),
            'created_by'          => \Yii::t('skeeks/shop/app', 'Created By'),
            'updated_by'          => \Yii::t('skeeks/shop/app', 'Updated By'),
            'created_at'          => \Yii::t('skeeks/shop/app', 'Created At'),
            'updated_at'          => \Yii::t('skeeks/shop/app', 'Updated At'),
            'name'                => \Yii::t('skeeks/shop/app', 'The profile name'),
            'cms_user_id'         => \Yii::t('skeeks/shop/app', 'User site'),
            'shop_person_type_id' => \Yii::t('skeeks/shop/app', 'Profile type'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopPersonType()
    {
        return $this->hasOne(ShopPersonType::class, ['id' => 'shop_person_type_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCmsUser()
    {
        return $this->hasOne(CmsUser::class, ['id' => 'cms_user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(CmsUser::class, ['id' => 'created_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUpdatedBy()
    {
        return $this->hasOne(CmsUser::class, ['id' => 'updated_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopBuyerProperties()
    {
        return $this->hasMany(ShopBuyerProperty::class, ['element_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopOrders()
    {
        return $this->hasMany(ShopOrder::class, ['buyer_id' => 'id']);
    }


    /**
     *
     * Все возможные свойства связанные с моделью
     *
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getRelatedProperties()
    {
        //return $this->shopPersonType->getShopPersonTypeProperties();
        return $this->hasMany(ShopPersonTypeProperty::class, ['shop_person_type_id' => 'id'])
            ->via('shopPersonType')->orderBy(['priority' => SORT_ASC]);
    }


    /**
     * @return null|string
     */
    public function getEmail()
    {
        $this->relatedPropertiesModel->initAllProperties();
        if ($properties = $this->relatedPropertiesModel->properties) {
            /**
             * @var $property ShopPersonTypeProperty
             */
            foreach ($properties as $property) {
                if ($property->is_user_email == "Y") {
                    $value = $this->relatedPropertiesModel->getAttribute($property->code);
                    if ($value) {
                        return (string)$value;
                    }
                }
            }
        }

        if ($this->cmsUser && $this->cmsUser->email) {
            return $this->cmsUser->email;
        }

        return null;
    }

    /**
     * @return null|string
     */
    public function getPhone()
    {
        $this->relatedPropertiesModel->initAllProperties();
        if ($properties = $this->relatedPropertiesModel->properties) {
            /**
             * @var $property ShopPersonTypeProperty
             */
            foreach ($properties as $property) {
                if ($property->is_user_phone == "Y") {
                    $value = $this->relatedPropertiesModel->getAttribute($property->code);
                    if ($value) {
                        return (string)$value;
                    }
                }
            }
        }

        if ($this->cmsUser && $this->cmsUser->phone) {
            return $this->cmsUser->phone;
        }

        return null;
    }

    /**
     * @return null|string
     */
    public function getRegisterName()
    {
        $this->relatedPropertiesModel->initAllProperties();
        if ($properties = $this->relatedPropertiesModel->properties) {
            /**
             * @var $property ShopPersonTypeProperty
             */
            foreach ($properties as $property) {
                if ($property->is_buyer_name == "Y") {
                    $value = $this->relatedPropertiesModel->getAttribute($property->code);
                    if ($value) {
                        return (string)$value;
                    }
                }
            }
        }


        return null;
    }
}