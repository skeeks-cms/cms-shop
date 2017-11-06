<?php

namespace skeeks\cms\shop\models;

use skeeks\cms\models\CmsSite;
use Yii;

/**
 * This is the model class for table "{{%shop_affiliate}}".
 *
 * @property integer $id
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $site_code
 * @property integer $user_id
 * @property integer $plan_id
 * @property string $active
 * @property string $paid_sum
 * @property string $approved_sum
 * @property string $pending_sum
 * @property integer $items_number
 * @property string $items_sum
 * @property integer $last_calculate_at
 * @property string $aff_site
 * @property string $aff_description
 * @property string $fix_plan
 *
 * @property ShopAffiliatePlan $plan
 * @property ShopAffiliate[] $shopAffiliates
 * @property CmsUser $user
 * @property CmsSite $site
 * @property ShopAffiliate $affiliate
 */
class ShopAffiliate extends \skeeks\cms\models\Core
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%shop_affiliate}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'affiliate_id', 'user_id', 'plan_id', 'items_number', 'last_calculate_at'], 'integer'],
            [['site_code', 'user_id', 'plan_id'], 'required'],
            [['paid_sum', 'approved_sum', 'pending_sum', 'items_sum'], 'number'],
            [['aff_description'], 'string'],
            [['site_code'], 'string', 'max' => 15],
            [['active', 'fix_plan'], 'string', 'max' => 1],
            [['aff_site'], 'string', 'max' => 255],
            [['user_id', 'site_code'], 'unique', 'targetAttribute' => ['user_id', 'site_code'], 'message' => 'The combination of Site Code and User ID has already been taken.']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'                => \Yii::t('skeeks/shop/app', 'ID'),
            'created_by'        => \Yii::t('skeeks/shop/app', 'Created By'),
            'updated_by'        => \Yii::t('skeeks/shop/app', 'Updated By'),
            'created_at'        => \Yii::t('skeeks/shop/app', 'Created At'),
            'updated_at'        => \Yii::t('skeeks/shop/app', 'Updated At'),
            'affiliate_id'      => \Yii::t('skeeks/shop/app', 'Affiliate who helped your registration'),
            'site_code'         => \Yii::t('skeeks/shop/app', 'Site'),
            'user_id'           => \Yii::t('skeeks/shop/app', 'User'),
            'plan_id'           => \Yii::t('skeeks/shop/app', 'Plan ID'),
            'active'            => \Yii::t('skeeks/shop/app', 'Active'),
            'paid_sum'          => \Yii::t('skeeks/shop/app', 'Paid Sum'),
            'approved_sum'      => \Yii::t('skeeks/shop/app', 'Approved Sum'),
            'pending_sum'       => \Yii::t('skeeks/shop/app', 'Pending Sum'),
            'items_number'      => \Yii::t('skeeks/shop/app', 'Items Number'),
            'items_sum'         => \Yii::t('skeeks/shop/app', 'Items Sum'),
            'last_calculate_at' => \Yii::t('skeeks/shop/app', 'Last Calculate At'),
            'aff_site'          => \Yii::t('skeeks/shop/app', 'Aff Site'),
            'aff_description'   => \Yii::t('skeeks/shop/app', 'Aff Description'),
            'fix_plan'          => \Yii::t('skeeks/shop/app', 'Secure plan'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPlan()
    {
        return $this->hasOne(ShopAffiliatePlan::className(), ['id' => 'plan_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(CmsUser::className(), ['id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSite()
    {
        return $this->hasOne(CmsSite::className(), ['code' => 'site_code']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAffiliate()
    {
        return $this->hasOne(ShopAffiliate::className(), ['id' => 'affiliate_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopAffiliates()
    {
        return $this->hasMany(ShopAffiliate::className(), ['affiliate_id' => 'id']);
    }
}