<?php

namespace skeeks\cms\shop\models;

use skeeks\cms\models\CmsSite;
use skeeks\cms\models\CmsStorageFile;
use skeeks\modules\cms\money\models\Currency;
use Yii;

/**
 * This is the model class for table "{{%shop_delivery}}".
 *
 * @property integer $id
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $site_id
 * @property integer $period_from
 * @property integer $period_to
 * @property string $period_type
 * @property integer $weight_from
 * @property integer $weight_to
 * @property string $order_price_from
 * @property string $order_price_to
 * @property string $order_currency_code
 * @property string $active
 * @property string $price
 * @property string $currency_code
 * @property integer $priority
 * @property string $description
 * @property integer $logo_id
 * @property string $store
 *
 * @property CmsSite $site
 * @property Currency $currency
 * @property CmsStorageFile $logo
 * @property Currency $orderCurrency
 * @property ShopDelivery2paySystem[] $shopDelivery2paySystems
 */
class ShopDelivery extends \skeeks\cms\models\Core
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%shop_delivery}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'site_id', 'period_from', 'period_to', 'weight_from', 'weight_to', 'priority', 'logo_id'], 'integer'],
            [['site_id', 'price', 'currency_code'], 'required'],
            [['order_price_from', 'order_price_to', 'price'], 'number'],
            [['description', 'store'], 'string'],
            [['period_type', 'active'], 'string', 'max' => 1],
            [['order_currency_code', 'currency_code'], 'string', 'max' => 3]
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
            'site_id' => Yii::t('app', 'Site ID'),
            'period_from' => Yii::t('app', 'Period From'),
            'period_to' => Yii::t('app', 'Period To'),
            'period_type' => Yii::t('app', 'Period Type'),
            'weight_from' => Yii::t('app', 'Weight From'),
            'weight_to' => Yii::t('app', 'Weight To'),
            'order_price_from' => Yii::t('app', 'Order Price From'),
            'order_price_to' => Yii::t('app', 'Order Price To'),
            'order_currency_code' => Yii::t('app', 'Order Currency Code'),
            'active' => Yii::t('app', 'Active'),
            'price' => Yii::t('app', 'Price'),
            'currency_code' => Yii::t('app', 'Currency Code'),
            'priority' => Yii::t('app', 'Priority'),
            'description' => Yii::t('app', 'Description'),
            'logo_id' => Yii::t('app', 'Logo ID'),
            'store' => Yii::t('app', 'Store'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSite()
    {
        return $this->hasOne(CmsSite::className(), ['id' => 'site_id']);
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCurrency()
    {
        return $this->hasOne(Currency::className(), ['code' => 'currency_code']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLogo()
    {
        return $this->hasOne(CmsStorageFile::className(), ['id' => 'logo_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrderCurrency()
    {
        return $this->hasOne(Currency::className(), ['code' => 'order_currency_code']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopDelivery2paySystems()
    {
        return $this->hasMany(ShopDelivery2paySystem::className(), ['delivery_id' => 'id']);
    }
}