<?php

namespace skeeks\cms\shop\models;

use skeeks\cms\components\Cms;
use skeeks\cms\models\CmsSite;
use skeeks\cms\models\CmsStorageFile;
use skeeks\modules\cms\money\models\Currency;
use skeeks\cms\models\behaviors\HasStorageFile;
use skeeks\modules\cms\money\Money;
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
 * @property Money $money
 * @property CmsSite $site
 * @property Currency $currency
 * @property CmsStorageFile $logo
 * @property Currency $orderCurrency
 * @property ShopDelivery2paySystem[] $shopDelivery2paySystems
 * @property ShopPaySystems[] $shopPaySystems
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
            [['name'], 'required'],
            [['order_price_from', 'order_price_to', 'price'], 'number'],
            [['description', 'store', 'name'], 'string'],
            [['period_type', 'active'], 'string', 'max' => 1],
            [['priority'], 'default', 'value' =>  1],
            [['order_currency_code', 'currency_code'], 'string', 'max' => 3],
            ['shopPaySystems', 'safe'],
            [['price'], 'default', 'value' =>  0],
            [['active'], 'default', 'value' => Cms::BOOL_Y],
            [['currency_code'], 'default', 'value' =>  Yii::$app->money->currencyCode],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'                    => \skeeks\cms\shop\Module::t('app', 'ID'),
            'created_by'            => \skeeks\cms\shop\Module::t('app', 'Created By'),
            'updated_by'            => \skeeks\cms\shop\Module::t('app', 'Updated By'),
            'created_at'            => \skeeks\cms\shop\Module::t('app', 'Created At'),
            'updated_at'            => \skeeks\cms\shop\Module::t('app', 'Updated At'),
            'site_id'               => \skeeks\cms\shop\Module::t('app', 'Site'),
            'name'                  => \skeeks\cms\shop\Module::t('app', 'Name'),
            'period_from'           => \skeeks\cms\shop\Module::t('app', 'Period From'),
            'period_to'             => \skeeks\cms\shop\Module::t('app', 'Period To'),
            'period_type'           => \skeeks\cms\shop\Module::t('app', 'Period Type'),
            'weight_from'           => \skeeks\cms\shop\Module::t('app', 'Weight From'),
            'weight_to'             => \skeeks\cms\shop\Module::t('app', 'Weight To'),
            'order_price_from'      => \skeeks\cms\shop\Module::t('app', 'Order price from'),
            'order_price_to'        => \skeeks\cms\shop\Module::t('app', 'Order price to'),
            'order_currency_code'   => \skeeks\cms\shop\Module::t('app', 'Order currency code'),
            'active'                => \skeeks\cms\shop\Module::t('app', 'Active'),
            'price'                 => \skeeks\cms\shop\Module::t('app', 'Price'),
            'currency_code'         => \skeeks\cms\shop\Module::t('app', 'Currency Code'),
            'priority'              => \skeeks\cms\shop\Module::t('app', 'Priority'),
            'description'           => \skeeks\cms\shop\Module::t('app', 'Description'),
            'logo_id'               => \skeeks\cms\shop\Module::t('app', 'Logo ID'),
            'store'                 => \skeeks\cms\shop\Module::t('app', 'Store'),
            'shopPaySystems'        => \skeeks\cms\shop\Module::t('app', 'Payment systems'),
        ];
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            \skeeks\cms\behaviors\RelationalBehavior::className(),
            HasStorageFile::className() =>
            [
                'class'     => HasStorageFile::className(),
                'fields'    => ['logo_id']
            ]
        ]);
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

    /**
     * Èòîãîâàÿ ñòîèìîñòü äîñòàâêè
     *
     * @return Money
     */
    public function getMoney()
    {
        return Money::fromString($this->price, $this->currency_code);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopPaySystems()
    {
        return $this->hasMany(ShopPaySystem::className(), ['id' => 'pay_system_id'])
            ->viaTable('shop_delivery2pay_system', ['delivery_id' => 'id']);
    }


}