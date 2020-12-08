<?php

namespace skeeks\cms\shop\models;

use skeeks\cms\base\ActiveRecord;
use skeeks\cms\models\behaviors\HasStorageFile;
use skeeks\cms\models\behaviors\Serialize;
use skeeks\cms\models\CmsSite;
use skeeks\cms\models\CmsStorageFile;
use skeeks\cms\money\Money;
use skeeks\cms\shop\delivery\DeliveryHandler;
use skeeks\modules\cms\money\models\Currency;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%shop_delivery}}".
 *
 * @property integer                  $id
 * @property integer                  $created_by
 * @property integer                  $updated_by
 * @property integer                  $created_at
 * @property integer                  $updated_at
 * @property integer                  $cms_site_id
 * @property integer                  $weight_from
 * @property integer                  $weight_to
 * @property string                   $name
 * @property string                   $order_price_from
 * @property string                   $order_price_to
 * @property string                   $order_currency_code
 * @property boolean                  $is_active
 * @property string                   $price
 * @property string                   $currency_code
 * @property integer                  $priority
 * @property string                   $description
 * @property integer                  $logo_id
 * @property string                   $component
 * @property string                   $component_config
 *
 * @property DeliveryHandler          $handler
 * @property Money                    $money
 * @property CmsSite                  $site
 * @property Currency                 $currency
 * @property CmsStorageFile           $logo
 * @property Currency                 $orderCurrency
 * @property ShopDelivery2paySystem[] $shopDelivery2paySystems
 * @property ShopPaySystems[]         $shopPaySystems
 */
class ShopDelivery extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%shop_delivery}}';
    }

    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            Serialize::class      => [
                'class'  => Serialize::class,
                'fields' => ['component_config'],
            ],
            \skeeks\cms\behaviors\RelationalBehavior::class,
            HasStorageFile::class => [
                'class'  => HasStorageFile::class,
                'fields' => ['logo_id'],
            ],
        ]);
    }

    protected $_handler = null;

    /**
     * @return DeliveryHandler
     */
    public function getHandler()
    {
        if ($this->_handler !== null) {
            return $this->_handler;
        }

        if ($this->component) {
            try {

                $component = \Yii::createObject($this->component);
                $component->load($this->component_config, "");

                $this->_handler = $component;
                return $this->_handler;
            } catch (\Exception $e) {
                \Yii::error("Related property handler not found '{$this->component}'", self::class);
                return null;
            }

        }

        return null;
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
                    'cms_site_id',
                    'weight_from',
                    'weight_to',
                    'priority',
                    'logo_id',
                    'is_active',
                ],
                'integer',
            ],
            [['name'], 'required'],
            [['order_price_from', 'order_price_to', 'price'], 'number'],
            [['description', 'name'], 'string'],
            [['priority'], 'default', 'value' => 1],
            [['order_currency_code', 'currency_code'], 'string', 'max' => 3],
            ['shopPaySystems', 'safe'],
            [['price'], 'default', 'value' => 0],
            [['is_active'], 'default', 'value' => 1],
            [['currency_code'], 'default', 'value' => Yii::$app->money->currencyCode],
            [['order_currency_code'], 'default', 'value' => Yii::$app->money->currencyCode],

            [
                'cms_site_id',
                'default',
                'value' => function () {
                    if (\Yii::$app->skeeks->site) {
                        return \Yii::$app->skeeks->site->id;
                    }
                },
            ],

            [['component_config'], 'safe'],
            [['component'], 'string', 'max' => 255],

            [['component_config', 'component'], 'default', 'value' => null],

        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'                  => \Yii::t('skeeks/shop/app', 'ID'),
            'created_by'          => \Yii::t('skeeks/shop/app', 'Created By'),
            'updated_by'          => \Yii::t('skeeks/shop/app', 'Updated By'),
            'created_at'          => \Yii::t('skeeks/shop/app', 'Created At'),
            'updated_at'          => \Yii::t('skeeks/shop/app', 'Updated At'),
            'cms_site_id'         => \Yii::t('skeeks/shop/app', 'Site'),
            'name'                => \Yii::t('skeeks/shop/app', 'Name'),
            'period_from'         => \Yii::t('skeeks/shop/app', 'Period From'),
            'period_to'           => \Yii::t('skeeks/shop/app', 'Period To'),
            'period_type'         => \Yii::t('skeeks/shop/app', 'Period Type'),
            'weight_from'         => \Yii::t('skeeks/shop/app', 'Weight From'),
            'weight_to'           => \Yii::t('skeeks/shop/app', 'Weight To'),
            'order_price_from'    => \Yii::t('skeeks/shop/app', 'Order price from'),
            'order_price_to'      => \Yii::t('skeeks/shop/app', 'Order price to'),
            'order_currency_code' => \Yii::t('skeeks/shop/app', 'Order currency code'),
            'is_active'           => \Yii::t('skeeks/shop/app', 'Active'),
            'price'               => \Yii::t('skeeks/shop/app', 'Price'),
            'currency_code'       => \Yii::t('skeeks/shop/app', 'Currency Code'),
            'priority'            => \Yii::t('skeeks/shop/app', 'Priority'),
            'description'         => \Yii::t('skeeks/shop/app', 'Description'),
            'logo_id'             => \Yii::t('skeeks/shop/app', 'Logo ID'),
            'store'               => \Yii::t('skeeks/shop/app', 'Store'),
            'component'           => "Внешний обработчик",
            'shopPaySystems'      => \Yii::t('skeeks/shop/app', 'Payment systems'),
        ];
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCmsSite()
    {
        return $this->hasOne(CmsSite::class, ['id' => 'cms_site_id']);
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCurrency()
    {
        return $this->hasOne(Currency::class, ['code' => 'currency_code']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLogo()
    {
        return $this->hasOne(CmsStorageFile::class, ['id' => 'logo_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrderCurrency()
    {
        return $this->hasOne(Currency::class, ['code' => 'order_currency_code']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopDelivery2paySystems()
    {
        return $this->hasMany(ShopDelivery2paySystem::class, ['delivery_id' => 'id']);
    }

    /**
     * Итоговая стоимость доставки
     *
     * @return Money
     */
    public function getMoney()
    {
        return new Money($this->price, $this->currency_code);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopPaySystems()
    {
        return $this->hasMany(ShopPaySystem::class, ['id' => 'pay_system_id'])
            ->viaTable('shop_delivery2pay_system', ['delivery_id' => 'id']);
    }

    /**
     * @param ShopOrder|null $shopOrder
     * @return array|\yii\db\ActiveRecord[]
     */
    static public function getAllowForOrder(ShopOrder $shopOrder = null)
    {
        if ($shopOrder === null) {
            $shopOrder = \Yii::$app->shop->shopUser->shopOrder;
        }

        $q = static::findForOrder($shopOrder);
        if ($shopDeliveries = $q->all()) {
            foreach ($shopDeliveries as $key => $shopDelivery) {
                if (!$shopDelivery->isAllowForOrder($shopOrder)) {
                    unset($shopDeliveries[$key]);
                }
            }
        }

        return $shopDeliveries;
    }

    /**
     * @param ShopOrder|null $shopOrder
     */
    static public function findForOrder(ShopOrder $shopOrder = null)
    {
        if ($shopOrder === null) {
            $shopOrder = \Yii::$app->shop->shopUser->shopOrder;
        }

        $shopOrder->moneyItems->amount;

        $q = static::find()
            ->andWhere(['cms_site_id' => $shopOrder->cms_site_id])
            ->orderBy(['priority' => SORT_ASC])
            ->active();

        /*$q->andWhere([
            'or',
            [
                'and',
                ['order_price_from' => null],
                ['order_price_to' => null],
            ],
            'or',
            [
                'and',
                ['order_price_from' => null],
                ['order_price_to' => null],
            ],
        ]);*/

        return $q;
    }

    /**
     * @param ShopOrder $shopOrder
     * @return bool
     */
    public function isAllowForOrder(ShopOrder $shopOrder)
    {
        if ($this->order_price_from) {
            if ($this->order_price_from >= (float)$shopOrder->moneyItems->amount) {
                return false;
            }
        }
        if ($this->order_price_to) {
            if ($this->order_price_to <= (float)$shopOrder->moneyItems->amount) {
                return false;
            }
        }

        return true;
    }

}