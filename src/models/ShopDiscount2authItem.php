<?php

namespace skeeks\cms\shop\models;

use skeeks\cms\rbac\models\CmsAuthItem;
/**
 * This is the model class for table "{{%shop_discount2type_price}}".
 *
 * @property integer      $id
 * @property integer      $created_by
 * @property integer      $updated_by
 * @property integer      $created_at
 * @property integer      $updated_at
 * @property integer      $shop_discount_id
 * @property string       $auth_item_name
 *
 * @property ShopDiscount $shopDiscount
 * @property CmsAuthItem  $cmsAuthItem
 */
class ShopDiscount2authItem extends \skeeks\cms\models\Core
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%shop_discount2auth_item}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['shop_discount_id'], 'integer'],
            [['auth_item_name'], 'string'],
            [['discount_id', 'auth_item_name'], 'required'],
            [
                ['shop_discount_id', 'auth_item_name'],
                'unique',
                'targetAttribute' => ['shop_discount_id', 'auth_item_name'],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'               => \Yii::t('skeeks/shop/app', 'ID'),
            'shop_discount_id' => \Yii::t('skeeks/shop/app', 'Discount ID'),
            'auth_item_name'   => \Yii::t('skeeks/shop/app', 'Type Price ID'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopDiscount()
    {
        return $this->hasOne(ShopDiscount::class, ['id' => 'shop_discount_id']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCmsAuthItem()
    {
        return $this->hasOne(CmsAuthItem::class, ['name' => 'auth_item_name']);
    }

}