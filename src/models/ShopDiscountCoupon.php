<?php

namespace skeeks\cms\shop\models;

use skeeks\cms\helpers\StringHelper;
use skeeks\cms\models\CmsUser;
use Yii;

/**
 * This is the model class for table "{{%shop_discount_coupon}}".
 *
 * @property integer $id
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $shop_discount_id
 * @property integer $is_active
 * @property integer $active_from
 * @property integer $active_to
 * @property string $coupon
 * @property integer $max_use
 * @property integer $use_count
 * @property integer $cms_user_id
 * @property string $description
 *
 * @property CmsUser $cmsUser
 * @property ShopDiscount $shopDiscount
 * @property ShopOrder2discountCoupon[] $shopOrder2discountCoupons
 */
class ShopDiscountCoupon extends \skeeks\cms\models\Core
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%shop_discount_coupon}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                [
                    'created_by',
                    'updated_by',
                    'created_at',
                    'updated_at',
                    'shop_discount_id',
                    'is_active',
                    'active_from',
                    'active_to',
                    'max_use',
                    'use_count',
                    'cms_user_id'
                ],
                'integer'
            ],
            [
                [
                    'shop_discount_id',
                    //'coupon'
                ],
                'required'
            ],
            [['coupon'], 'string', 'max' => 32],
            [['description'], 'string', 'max' => 255],
            [
                ['cms_user_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => CmsUser::className(),
                'targetAttribute' => ['cms_user_id' => 'id']
            ],
            [
                ['created_by'],
                'exist',
                'skipOnError' => true,
                'targetClass' => CmsUser::className(),
                'targetAttribute' => ['created_by' => 'id']
            ],
            [
                ['shop_discount_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => ShopDiscount::className(),
                'targetAttribute' => ['shop_discount_id' => 'id']
            ],
            [
                ['updated_by'],
                'exist',
                'skipOnError' => true,
                'targetClass' => CmsUser::className(),
                'targetAttribute' => ['updated_by' => 'id']
            ],

            [
                ['coupon'],
                'default',
                'value' => function () {
                    return "SO-" . StringHelper::strtoupper(\Yii::$app->security->generateRandomString(15));
                }
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('skeeks/shop/app', 'ID'),
            'created_by' => Yii::t('skeeks/shop/app', 'Created By'),
            'updated_by' => Yii::t('skeeks/shop/app', 'Updated By'),
            'created_at' => Yii::t('skeeks/shop/app', 'Created At'),
            'updated_at' => Yii::t('skeeks/shop/app', 'Updated At'),
            'shop_discount_id' => Yii::t('skeeks/shop/app', 'Discount'),
            'is_active' => Yii::t('skeeks/shop/app', 'Active'),
            'active_from' => Yii::t('skeeks/shop/app', 'Active from'),
            'active_to' => Yii::t('skeeks/shop/app', 'Active to'),
            'coupon' => Yii::t('skeeks/shop/app', 'Coupon'),
            'max_use' => Yii::t('skeeks/shop/app', 'Maximum number of performances'),
            'use_count' => Yii::t('skeeks/shop/app', 'Use count'),
            'cms_user_id' => Yii::t('skeeks/shop/app', 'User'),
            'description' => Yii::t('skeeks/shop/app', 'Description'),
        ];
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
    public function getShopDiscount()
    {
        return $this->hasOne(ShopDiscount::className(), ['id' => 'shop_discount_id']);
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopOrder2discountCoupons()
    {
        return $this->hasMany(ShopOrder2discountCoupon::className(), ['discount_coupon_id' => 'id']);
    }

}