<?php

namespace skeeks\cms\shop\models;

use skeeks\cms\models\CmsContentElement;
use skeeks\cms\models\CmsSite;

/**
 * This is the model class for table "{{%shop_viewed_product}}".
 *
 * @property integer           $id
 * @property integer           $created_by
 * @property integer           $updated_by
 * @property integer           $created_at
 * @property integer           $updated_at
 * @property integer           $shop_fuser_id
 * @property integer           $shop_product_id
 * @property integer           $site_id
 *
 * @property CmsSite           $site
 * @property ShopFuser         $shopFuser
 * @property ShopProduct       $shopProduct
 * @property CmsContentElement $cmsContentElement
 */
class ShopViewedProduct extends \skeeks\cms\models\Core
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%shop_viewed_product}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                ['created_by', 'updated_by', 'created_at', 'updated_at', 'shop_fuser_id', 'shop_product_id', 'site_id'],
                'integer',
            ],
            [['shop_fuser_id', 'shop_product_id', 'site_id'], 'required'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'              => \Yii::t('skeeks/shop/app', 'ID'),
            'created_by'      => \Yii::t('skeeks/shop/app', 'Created By'),
            'updated_by'      => \Yii::t('skeeks/shop/app', 'Updated By'),
            'created_at'      => \Yii::t('skeeks/shop/app', 'Created At'),
            'updated_at'      => \Yii::t('skeeks/shop/app', 'Updated At'),
            'shop_fuser_id'   => \Yii::t('skeeks/shop/app', 'Shop Fuser ID'),
            'shop_product_id' => \Yii::t('skeeks/shop/app', 'Shop Product ID'),
            'site_id'         => \Yii::t('skeeks/shop/app', 'Site ID'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSite()
    {
        return $this->hasOne(CmsSite::class, ['id' => 'site_id']);
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopFuser()
    {
        return $this->hasOne(ShopFuser::class, ['id' => 'shop_fuser_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopProduct()
    {
        return $this->hasOne(ShopProduct::class, ['id' => 'shop_product_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCmsContentElement()
    {
        return $this->hasOne(CmsContentElement::class, ['id' => 'shop_product_id']);
    }

}