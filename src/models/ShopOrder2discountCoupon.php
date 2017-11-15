<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (�����)
 * @date 08.02.2017
 */

namespace skeeks\cms\shop\models;

use skeeks\cms\models\CmsUser;
use Yii;

/**
 * This is the model class for table "{{%shop_order2discount_coupon}}".
 *
 * @property integer $id
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $discount_coupon_id
 * @property integer $order_id
 *
 * @property ShopDiscountCoupon $discountCoupon
 * @property ShopOrder $order
 */
class ShopOrder2discountCoupon extends \skeeks\cms\models\Core
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%shop_order2discount_coupon}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'discount_coupon_id', 'order_id'], 'integer'],
            [['discount_coupon_id'], 'required'],
            [
                ['created_by'],
                'exist',
                'skipOnError' => true,
                'targetClass' => CmsUser::className(),
                'targetAttribute' => ['created_by' => 'id']
            ],
            [
                ['discount_coupon_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => ShopDiscountCoupon::className(),
                'targetAttribute' => ['discount_coupon_id' => 'id']
            ],
            [
                ['order_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => ShopOrder::className(),
                'targetAttribute' => ['order_id' => 'id']
            ],
            [
                ['updated_by'],
                'exist',
                'skipOnError' => true,
                'targetClass' => CmsUser::className(),
                'targetAttribute' => ['updated_by' => 'id']
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
            'discount_coupon_id' => Yii::t('skeeks/shop/app', 'Discount Coupon ID'),
            'order_id' => Yii::t('skeeks/shop/app', 'Order ID'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDiscountCoupon()
    {
        return $this->hasOne(ShopDiscountCoupon::className(), ['id' => 'discount_coupon_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrder()
    {
        return $this->hasOne(ShopOrder::className(), ['id' => 'order_id']);
    }

}