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
            [
                [
                    'created_by',
                    'updated_by',
                    'created_at',
                    'updated_at',
                    'site_id',
                    'period_from',
                    'period_to',
                    'weight_from',
                    'weight_to',
                    'priority',
                    'logo_id'
                ],
                'integer'
            ],
            [['name'], 'required'],
            [['order_price_from', 'order_price_to', 'price'], 'number'],
            [['description', 'store', 'name'], 'string'],
            [['period_type', 'active'], 'string', 'max' => 1],
            [['priority'], 'default', 'value' => 1],
            [['order_currency_code', 'currency_code'], 'string', 'max' => 3],
            ['shopPaySystems', 'safe'],
            [['price'], 'default', 'value' => 0],
            [['active'], 'default', 'value' => Cms::BOOL_Y],
            [['currency_code'], 'default', 'value' => Yii::$app->money->currencyCode],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => \Yii::t('skeeks/shop/app', 'ID'),
            'created_by' => \Yii::t('skeeks/shop/app', 'Created By'),
            'updated_by' => \Yii::t('skeeks/shop/app', 'Updated By'),
            'created_at' => \Yii::t('skeeks/shop/app', 'Created At'),
            'updated_at' => \Yii::t('skeeks/shop/app', 'Updated At'),
            'site_id' => \Yii::t('skeeks/shop/app', 'Site'),
            'name' => \Yii::t('skeeks/shop/app', 'Name'),
            'period_from' => \Yii::t('skeeks/shop/app', 'Period From'),
            'period_to' => \Yii::t('skeeks/shop/app', 'Period To'),
            'period_type' => \Yii::t('skeeks/shop/app', 'Period Type'),
            'weight_from' => \Yii::t('skeeks/shop/app', 'Weight From'),
            'weight_to' => \Yii::t('skeeks/shop/app', 'Weight To'),
            'order_price_from' => \Yii::t('skeeks/shop/app', 'Order price from'),
            'order_price_to' => \Yii::t('skeeks/shop/app', 'Order price to'),
            'order_currency_code' => \Yii::t('skeeks/shop/app', 'Order currency code'),
            'active' => \Yii::t('skeeks/shop/app', 'Active'),
            'price' => \Yii::t('skeeks/shop/app', 'Price'),
            'currency_code' => \Yii::t('skeeks/shop/app', 'Currency Code'),
            'priority' => \Yii::t('skeeks/shop/app', 'Priority'),
            'description' => \Yii::t('skeeks/shop/app', 'Description'),
            'logo_id' => \Yii::t('skeeks/shop/app', 'Logo ID'),
            'store' => \Yii::t('skeeks/shop/app', 'Store'),
            'shopPaySystems' => \Yii::t('skeeks/shop/app', 'Payment systems'),
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
                    'class' => HasStorageFile::className(),
                    'fields' => ['logo_id']
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
     * Итоговая стоимость доставки
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