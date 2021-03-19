<?php

namespace skeeks\cms\shop\models;

use skeeks\cms\base\ActiveRecord;
use skeeks\cms\helpers\StringHelper;
use skeeks\cms\models\CmsUser;
use Yii;

/**
 * This is the model class for table "{{%shop_discount_coupon}}".
 *
 * @property integer                    $id
 * @property integer                    $created_by
 * @property integer                    $updated_by
 * @property integer                    $created_at
 * @property integer                    $updated_at
 * @property integer                    $shop_discount_id
 * @property integer                    $is_active
 * @property integer                    $active_from
 * @property integer                    $active_to
 * @property string                     $coupon
 * @property integer                    $max_use
 * @property integer                    $use_count
 * @property integer                    $cms_user_id
 * @property string                     $description
 *
 * @property string                     $publicUrl
 * @property CmsUser                    $cmsUser
 * @property ShopDiscount               $shopDiscount
 * @property ShopOrder2discountCoupon[] $shopOrder2discountCoupons
 */
class ShopDiscountCoupon extends ActiveRecord
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
                    'cms_user_id',
                ],
                'integer',
            ],
            [
                [
                    'shop_discount_id',
                    //'coupon'
                ],
                'required',
            ],
            [['coupon'], 'string', 'max' => 32],
            [['description'], 'string', 'max' => 255],
            [
                ['cms_user_id'],
                'exist',
                'skipOnError'     => true,
                'targetClass'     => CmsUser::class,
                'targetAttribute' => ['cms_user_id' => 'id'],
            ],
            [
                ['created_by'],
                'exist',
                'skipOnError'     => true,
                'targetClass'     => CmsUser::class,
                'targetAttribute' => ['created_by' => 'id'],
            ],
            [
                ['shop_discount_id'],
                'exist',
                'skipOnError'     => true,
                'targetClass'     => ShopDiscount::class,
                'targetAttribute' => ['shop_discount_id' => 'id'],
            ],
            [
                ['updated_by'],
                'exist',
                'skipOnError'     => true,
                'targetClass'     => CmsUser::class,
                'targetAttribute' => ['updated_by' => 'id'],
            ],

            [
                ['coupon'],
                'default',
                'value' => function () {
                    return "SO-".StringHelper::strtoupper(\Yii::$app->security->generateRandomString(15));
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
            'id'               => Yii::t('skeeks/shop/app', 'ID'),
            'created_by'       => Yii::t('skeeks/shop/app', 'Created By'),
            'updated_by'       => Yii::t('skeeks/shop/app', 'Updated By'),
            'created_at'       => Yii::t('skeeks/shop/app', 'Created At'),
            'updated_at'       => Yii::t('skeeks/shop/app', 'Updated At'),
            'shop_discount_id' => Yii::t('skeeks/shop/app', 'Discount'),
            'is_active'        => Yii::t('skeeks/shop/app', 'Active'),
            'active_from'      => Yii::t('skeeks/shop/app', 'Active from'),
            'active_to'        => Yii::t('skeeks/shop/app', 'Active to'),
            'coupon'           => Yii::t('skeeks/shop/app', 'Coupon'),
            'max_use'          => Yii::t('skeeks/shop/app', 'Maximum number of performances'),
            'use_count'        => Yii::t('skeeks/shop/app', 'Use count'),
            'cms_user_id'      => Yii::t('skeeks/shop/app', 'User'),
            'description'      => Yii::t('skeeks/shop/app', 'Description'),
        ];
    }


    /**
     * @return array
     */
    public function attributeHints()
    {
        return [
            'shop_discount_id' => Yii::t('skeeks/shop/app', 'Скидочный план, из него будут взяты все параметры скидки.'),
            'coupon'           => Yii::t('skeeks/shop/app', 'Код купона, если не будет указан, но будет сгенерирован автоматически.'),
            'max_use'          => Yii::t('skeeks/shop/app', 'Сколько раз может быть использован этот купон?'),
            'description'      => Yii::t('skeeks/shop/app', 'Короткое описание'),
        ];
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
    public function getShopDiscount()
    {
        return $this->hasOne(ShopDiscount::class, ['id' => 'shop_discount_id']);
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopOrder2discountCoupons()
    {
        return $this->hasMany(ShopOrder2discountCoupon::class, ['discount_coupon_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopOrders()
    {
        $query = ShopOrder::find()->isCreated();
        $query->joinWith('shopOrder2discountCoupons as shopOrder2discountCoupons');
        $query->andWhere(['shopOrder2discountCoupons.discount_coupon_id' => $this->id]);

        $query->multiple = true;
        return $query;
    }

    public function asText()
    {
        return $this->coupon;
    }

    /**
     * @return string
     */
    public function getPublicUrl()
    {
        return \yii\helpers\Url::to(['/shop/coupon', 'c' => $this->coupon], true);
    }
}