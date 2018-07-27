<?php

namespace skeeks\cms\shop\models;

use skeeks\cms\models\CmsSite;
use skeeks\modules\cms\money\models\Currency;

/**
 * This is the model class for table "{{%shop_affiliate_plan}}".
 *
 * @property integer         $id
 * @property integer         $created_by
 * @property integer         $updated_by
 * @property integer         $created_at
 * @property integer         $updated_at
 * @property string          $site_code
 * @property string          $name
 * @property string          $description
 * @property string          $active
 * @property string          $base_rate
 * @property string          $base_rate_type
 * @property string          $base_rate_currency_code
 * @property string          $min_pay
 * @property string          $min_plan_value
 * @property string          $value_currency_code
 *
 * @property ShopAffiliate[] $shopAffiliates
 * @property Currency        $valueCurrency
 * @property Currency        $baseRateCurrency
 * @property CmsSite         $site
 */
class ShopAffiliatePlan extends \skeeks\cms\models\Core
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%shop_affiliate_plan}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_by', 'updated_by', 'created_at', 'updated_at'], 'integer'],
            [['site_code', 'name'], 'required'],
            [['description'], 'string'],
            [['base_rate', 'min_pay', 'min_plan_value'], 'number'],
            [['site_code'], 'string', 'max' => 15],
            [['name'], 'string', 'max' => 255],
            [['active', 'base_rate_type'], 'string', 'max' => 1],
            [['base_rate_currency_code', 'value_currency_code'], 'string', 'max' => 3],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'                      => \Yii::t('skeeks/shop/app', 'ID'),
            'created_by'              => \Yii::t('skeeks/shop/app', 'Created By'),
            'updated_by'              => \Yii::t('skeeks/shop/app', 'Updated By'),
            'created_at'              => \Yii::t('skeeks/shop/app', 'Created At'),
            'updated_at'              => \Yii::t('skeeks/shop/app', 'Updated At'),
            'site_code'               => \Yii::t('skeeks/shop/app', 'Site'),
            'name'                    => \Yii::t('skeeks/shop/app', 'Name'),
            'description'             => \Yii::t('skeeks/shop/app', 'Description'),
            'active'                  => \Yii::t('skeeks/shop/app', 'Active'),
            'base_rate'               => \Yii::t('skeeks/shop/app', 'Commission affiliate'),
            'base_rate_type'          => \Yii::t('skeeks/shop/app', 'Base Rate Type'),
            'base_rate_currency_code' => \Yii::t('skeeks/shop/app', 'Base Rate Currency Code'),
            'min_pay'                 => \Yii::t('skeeks/shop/app', 'Min Pay'),
            'min_plan_value'          => \Yii::t('skeeks/shop/app', 'Plan of action on the sale of at least (pcs.)'),
            'value_currency_code'     => \Yii::t('skeeks/shop/app', 'Value Currency Code'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopAffiliates()
    {
        return $this->hasMany(ShopAffiliate::className(), ['plan_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getValueCurrency()
    {
        return $this->hasOne(Currency::className(), ['code' => 'value_currency_code']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBaseRateCurrency()
    {
        return $this->hasOne(Currency::className(), ['code' => 'base_rate_currency_code']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSite()
    {
        return $this->hasOne(CmsSite::className(), ['code' => 'site_code']);
    }

}