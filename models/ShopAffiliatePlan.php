<?php

namespace skeeks\cms\shop\models;

use skeeks\cms\models\CmsSite;
use skeeks\modules\cms\money\models\Currency;
use Yii;

/**
 * This is the model class for table "{{%shop_affiliate_plan}}".
 *
 * @property integer $id
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $site_code
 * @property string $name
 * @property string $description
 * @property string $active
 * @property string $base_rate
 * @property string $base_rate_type
 * @property string $base_rate_currency_code
 * @property string $min_pay
 * @property string $min_plan_value
 * @property string $value_currency_code
 *
 * @property ShopAffiliate[] $shopAffiliates
 * @property Currency $valueCurrency
 * @property Currency $baseRateCurrency
 * @property CmsSite $site
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
            [['base_rate_currency_code', 'value_currency_code'], 'string', 'max' => 3]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'created_by' => Yii::t('app', 'Created By'),
            'updated_by' => Yii::t('app', 'Updated By'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'site_code' => Yii::t('app', 'Site'),
            'name' => Yii::t('app', 'Name'),
            'description' => Yii::t('app', 'Description'),
            'active' => Yii::t('app', 'Active'),
            'base_rate' => Yii::t('app', 'Комиссия аффилиата'),
            'base_rate_type' => Yii::t('app', 'Base Rate Type'),
            'base_rate_currency_code' => Yii::t('app', 'Base Rate Currency Code'),
            'min_pay' => Yii::t('app', 'Min Pay'),
            'min_plan_value' => Yii::t('app', 'План действует при продаже не менее (шт.)'),
            'value_currency_code' => Yii::t('app', 'Value Currency Code'),
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