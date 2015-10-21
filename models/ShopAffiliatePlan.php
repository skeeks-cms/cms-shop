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
            'id'                        => \skeeks\cms\shop\Module::t('app', 'ID'),
            'created_by'                => \skeeks\cms\shop\Module::t('app', 'Created By'),
            'updated_by'                => \skeeks\cms\shop\Module::t('app', 'Updated By'),
            'created_at'                => \skeeks\cms\shop\Module::t('app', 'Created At'),
            'updated_at'                => \skeeks\cms\shop\Module::t('app', 'Updated At'),
            'site_code'                 => \skeeks\cms\shop\Module::t('app', 'Site'),
            'name'                      => \skeeks\cms\shop\Module::t('app', 'Name'),
            'description'               => \skeeks\cms\shop\Module::t('app', 'Description'),
            'active'                    => \skeeks\cms\shop\Module::t('app', 'Active'),
            'base_rate'                 => \skeeks\cms\shop\Module::t('app', 'Commission affiliate'),
            'base_rate_type'            => \skeeks\cms\shop\Module::t('app', 'Base Rate Type'),
            'base_rate_currency_code'   => \skeeks\cms\shop\Module::t('app', 'Base Rate Currency Code'),
            'min_pay'                   => \skeeks\cms\shop\Module::t('app', 'Min Pay'),
            'min_plan_value'            => \skeeks\cms\shop\Module::t('app', 'Plan of action on the sale of at least (pcs.)'),
            'value_currency_code'       => \skeeks\cms\shop\Module::t('app', 'Value Currency Code'),
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