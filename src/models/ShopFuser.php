<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

namespace skeeks\cms\shop\models;

use skeeks\cms\components\Cms;
use skeeks\cms\models\behaviors\Implode;
use skeeks\cms\models\CmsContentElement;
use skeeks\cms\models\CmsSite;
use skeeks\cms\models\CmsUser;
use skeeks\cms\models\Core;
use skeeks\cms\models\User;
use skeeks\cms\money\Money;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;

/**
 *
 * Это объект корзины
 *
 * @property User                 $user
 * @property ShopBasket[]         $shopBaskets
 * @property ShopDelivery         $delivery
 * @property ShopBuyer            $buyer
 * @property ShopPaySystem        $paySystem
 *
 * @property ShopPersonType       $personType
 * @property CmsSite              $site
 *
 * @property int                  $countShopBaskets
 * @property float                $quantity
 *
 * @property ShopBuyer[]          $shopBuyers
 * @property ShopPaySystem[]      $paySystems
 *
 *
 * @property integer              $id
 * @property integer              $created_by
 * @property integer              $updated_by
 * @property integer              $created_at
 * @property integer              $updated_at
 * @property integer              $user_id Пользователь сайта
 * @property string               $additional
 * @property integer              $person_type_id
 * @property integer              $site_id
 * @property integer              $delivery_id
 * @property integer              $buyer_id
 * @property integer              $pay_system_id
 * @property integer              $store_id
 * @property array                $discount_coupons
 *
 * @property Money                $money
 * @property Money                $moneyOriginal
 * @property Money                $moneyVat
 * @property Money                $moneyDiscount
 * @property Money                $moneyDelivery
 * @property ShopDiscountCoupon[] $discountCoupons
 *
 * @property int                  $weight
 * @property bool                 $isEmpty
 *
 * @property ShopTypePrice        $buyTypePrices
 * @property ShopTypePrice        $viewTypePrices
 * @property CmsContentElement    $store
 *
 */
class ShopFuser extends Core implements \JsonSerializable
{
    const SCENARIO_CREATE_ORDER = 'scentarioCreateOrder';
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%shop_fuser}}';
    }
    /**
     * @param CmsUser $cmsUser
     * @return array|null|\yii\db\ActiveRecord|static
     */
    static public function getInstanceByUser(CmsUser $cmsUser)
    {
        $shopFuser = static::find()->where(['user_id' => $cmsUser->id])->one();

        if (!$shopFuser) {
            $shopFuser = new static();
            $shopFuser->user_id = $cmsUser->id;

            $shopFuser->save();
        }

        return $shopFuser;
    }
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            Implode::class =>
                [
                    'class'  => Implode::class,
                    'fields' => ['discount_coupons'],
                ],
        ]);
    }
    public function loadDefaultValues($skipIfSet = true)
    {
        parent::loadDefaultValues($skipIfSet);

        if (!$this->site_id) {
            $this->site_id = \Yii::$app->cms->site->id;
        }

        if (!$this->delivery_id) {
            //$this->delivery_id = \Yii::$app->cms->site->id;
        }

        if (!$this->person_type_id && \Yii::$app->shop->shopPersonTypes) {
            $shopPersonType = \Yii::$app->shop->shopPersonTypes[0];
            $this->person_type_id = $shopPersonType->id;
        }

        if (!$this->pay_system_id && $this->paySystems) {
            $paySystem = $this->paySystems[0];
            $this->pay_system_id = $paySystem->id;
        }

        $deliveries = \skeeks\cms\shop\models\ShopDelivery::find()->active()->all();
        if (!$this->delivery_id && $deliveries) {
            $delivery = $deliveries[0];
            $this->delivery_id = $delivery->id;
        }
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'user_id'          => \Yii::t('skeeks/shop/app', 'User site'),
            'additional'       => \Yii::t('skeeks/shop/app', 'Additional'),
            'person_type_id'   => \Yii::t('skeeks/shop/app', 'Type of buyer'),
            'site_id'          => \Yii::t('skeeks/shop/app', 'Site ID'),
            'delivery_id'      => \Yii::t('skeeks/shop/app', 'Delivery service'),
            'buyer_id'         => \Yii::t('skeeks/shop/app', 'Profile of buyer'),
            'pay_system_id'    => \Yii::t('skeeks/shop/app', 'Payment system'),
            'store_id'         => \Yii::t('skeeks/shop/app', 'Warehouse/Store'),
            'discount_coupons' => \Yii::t('skeeks/shop/app', 'Discount coupons'),
        ]);
    }
    public function init()
    {
        parent::init();

        $this->on(self::EVENT_BEFORE_INSERT, [$this, 'beforeSaveCallback']);
        $this->on(self::EVENT_BEFORE_UPDATE, [$this, 'beforeSaveCallback']);
    }
    public function beforeSaveCallback()
    {
        if ($this->buyer) {
            $this->person_type_id = $this->buyer->shopPersonType->id;
        }
    }
    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_CREATE_ORDER] = $scenarios[self::SCENARIO_DEFAULT];
        return $scenarios;
    }
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [
                [
                    'created_by',
                    'updated_by',
                    'created_at',
                    'updated_at',
                    'user_id',
                    'person_type_id',
                    'site_id',
                    'store_id',
                ],
                'integer',
            ],
            [['additional'], 'string'],
            [['discount_coupons'], 'safe'],
            [['delivery_id'], 'integer'],
            [['user_id'], 'unique'],
            [['buyer_id'], 'integer'],
            [['pay_system_id'], 'integer'],
            [
                ['person_type_id'],
                'default',
                'value' => function (ShopFuser $model) {
                    return ($model->buyer && $model->buyer->shopPersonType) ? $model->buyer->shopPersonType->id : null;
                },
            ],
            [['pay_system_id', 'buyer_id', 'site_id', 'user_id'], 'required', 'on' => self::SCENARIO_CREATE_ORDER],

        ]);
    }
    /**
     *
     * Массив для json ответа, используется при обновлении корзины, добавлении позиций и т.д.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return ArrayHelper::merge($this->toArray([], $this->extraFields()), [
            'money'         => ArrayHelper::merge($this->money->jsonSerialize(),
                ['convertAndFormat' => \Yii::$app->money->convertAndFormat($this->money)]),
            'moneyDelivery' => ArrayHelper::merge($this->moneyDelivery->jsonSerialize(),
                ['convertAndFormat' => \Yii::$app->money->convertAndFormat($this->moneyDelivery)]),
            'moneyDiscount' => ArrayHelper::merge($this->moneyDiscount->jsonSerialize(),
                ['convertAndFormat' => \Yii::$app->money->convertAndFormat($this->moneyDiscount)]),
            'moneyOriginal' => ArrayHelper::merge($this->moneyOriginal->jsonSerialize(),
                ['convertAndFormat' => \Yii::$app->money->convertAndFormat($this->moneyOriginal)]),
            'moneyVat'      => ArrayHelper::merge($this->moneyVat->jsonSerialize(), [
                'convertAndFormat' => \Yii::$app->money->convertAndFormat($this->moneyVat),
            ]),
        ]);
    }
    public function extraFields()
    {
        return [
            'countShopBaskets',
            'shopBaskets',
            'quantity',
        ];
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPersonType()
    {
        return $this->hasOne(ShopPersonType::className(), ['id' => 'person_type_id']);
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
    public function getBuyer()
    {
        return $this->hasOne(ShopBuyer::className(), ['id' => 'buyer_id']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDelivery()
    {
        return $this->hasOne(ShopDelivery::className(), ['id' => 'delivery_id']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPaySystem()
    {
        return $this->hasOne(ShopPaySystem::className(), ['id' => 'pay_system_id']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStore()
    {
        return $this->hasOne(CmsContentElement::className(), ['id' => 'store_id']);
    }
    /**
     * @return ShopDiscountCoupon[]
     */
    public function getDiscountCoupons()
    {
        if (!$this->discount_coupons) {
            return [];
        }

        return ShopDiscountCoupon::find()->where(['id' => $this->discount_coupons])->all();
    }
    /**
     * Добавить корзины этому пользователю
     *
     * @param ShopBasket[] $baskets
     * @return $this
     */
    public function addBaskets($baskets = [])
    {
        /**
         * @var $currentBasket ShopBasket
         */
        foreach ($baskets as $basket) {
            //Если в корзине которую необходимо добавить продукт такой же который уже есть у текущего пользователя, то нужно обновить количество.
            if ($currentBasket = $this->getShopBaskets()->andWhere(['product_id' => $basket->product_id])->one()) {
                $currentBasket->quantity = $currentBasket->quantity + $basket->quantity;
                $currentBasket->save();

                $basket->delete();
            } else {
                $basket->fuser_id = $this->id;
                $basket->save();
            }
        }

        return $this;
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopBaskets()
    {
        return $this->hasMany(ShopBasket::className(), ['fuser_id' => 'id']);
    }
    /**
     * Количество позиций в корзине
     *
     * @return int
     */
    public function getCountShopBaskets()
    {
        return count($this->shopBaskets);
    }
    /**
     * @return float
     */
    public function getQuantity()
    {
        $result = 0;

        if ($this->shopBaskets) {
            foreach ($this->shopBaskets as $shopBasket) {
                $result = $shopBasket->quantity + $result;
            }
        }
        return (float)$result;
    }
    /**
     *
     * Итоговая стоимость корзины с учетом скидок, то что будет платить человек
     *
     * @return Money
     */
    public function getMoney()
    {
        $money = \Yii::$app->money->newMoney();

        foreach ($this->shopBaskets as $shopBasket) {
            $money = $money->add($shopBasket->money->multiply($shopBasket->quantity));
        }

        if ($this->moneyDelivery) {
            $money = $money->add($this->moneyDelivery);
        }

        return $money;
    }
    /**
     *
     * Итоговая стоимость корзины, без учета скидок
     *
     * @return Money
     */
    public function getMoneyOriginal()
    {
        $money = \Yii::$app->money->newMoney();

        foreach ($this->shopBaskets as $shopBasket) {
            $money = $money->add($shopBasket->moneyOriginal->multiply($shopBasket->quantity));
        }

        return $money;
    }
    /**
     * @return int
     */
    public function getWeight()
    {
        $result = 0;

        foreach ($this->shopBaskets as $shopBasket) {
            $result = $result + ($shopBasket->weight * $shopBasket->quantity);
        }

        return $result;
    }
    /**
     *
     * Итоговая стоимость налога
     *
     * @return Money
     */
    public function getMoneyVat()
    {
        $money = \Yii::$app->money->newMoney();

        foreach ($this->shopBaskets as $shopBasket) {
            $money = $money->add($shopBasket->moneyVat->multiply($shopBasket->quantity));
        }

        return $money;
    }
    /**
     *
     * Итоговая скидка по всей корзине
     *
     * @return Money
     */
    public function getMoneyDiscount()
    {
        $money = \Yii::$app->money->newMoney();
        foreach ($this->shopBaskets as $shopBasket) {
            $money = $money->add($shopBasket->moneyDiscount->multiply($shopBasket->quantity));
        }
        return $money;
    }
    /**
     *
     * Итоговая скидка по всей корзине
     *
     * @return Money
     */
    public function getMoneyDelivery()
    {
        if ($this->delivery) {
            return $this->delivery->money;
        }

        return \Yii::$app->money->newMoney();
    }
    /**
     * @return bool
     */
    public function getIsEmpty()
    {
        return $this->isEmpty();
    }
    /**
     * @return bool
     */
    public function isEmpty()
    {
        return (bool)$this->countShopBaskets == 0;
    }
    /**
     * Возможные опции для выбора покупателя
     * @return array
     */
    public function getBuyersList()
    {
        $result = [];

        if (\Yii::$app->shop->shopPersonTypes) {
            foreach (\Yii::$app->shop->shopPersonTypes as $shopPersonType) {
                $result[$shopPersonType->name] = [
                    'shopPersonType-'.$shopPersonType->id => " + ".\Yii::t('skeeks/shop/app',
                            'New profile')." ({$shopPersonType->name})",
                ];

                if ($existsBuyers = $this->getShopBuyers()->andWhere(['shop_person_type_id' => $shopPersonType->id])->all()) {
                    $result[$shopPersonType->name] = ArrayHelper::merge($result[$shopPersonType->name],
                        ArrayHelper::map($existsBuyers, 'id', 'name'));
                }
            }
        }

        return $result;
    }
    /**
     *
     * @return ActiveQuery
     */
    public function getShopBuyers()
    {
        return $this->hasMany(ShopBuyer::className(), ['cms_user_id' => 'id'])->via('user');
    }
    /**
     * Доступные платежные системы
     *
     * @return ShopPaySystem[]
     */
    public function getPaySystems()
    {
        if (!$this->personType) {
            $query = ShopPaySystem::find()->andWhere([ShopPaySystem::tableName().".active" => Cms::BOOL_Y]);
            $query->multiple = true;

            return $query;
        }

        return $this->personType->getPaySystems()->andWhere([ShopPaySystem::tableName().".active" => Cms::BOOL_Y]);
    }


    /**
     *
     * Доступные типы цен для просмотра
     *
     * @return ShopTypePrice[]
     */
    public function getViewTypePrices()
    {
        $result = [];

        foreach (\Yii::$app->shop->shopTypePrices as $typePrice) {
            if (\Yii::$app->authManager->checkAccess($this->user->id, $typePrice->viewPermissionName)) {
                $result[$typePrice->id] = $typePrice;
            }
        }

        return $result;
    }

    /**
     *
     * Доступные цены для покупки на сайте
     *
     * @return ShopTypePrice[]
     */
    public function getBuyTypePrices()
    {
        $result = [];

        foreach (\Yii::$app->shop->shopTypePrices as $typePrice) {
            if (\Yii::$app->authManager->checkAccess($this->user ? $this->user->id : null, $typePrice->buyPermissionName)) {
                $result[$typePrice->id] = $typePrice;
            }
        }

        return $result;
    }


    /**
     * @return $this
     */
    public function recalculate()
    {
        if ($this->shopBaskets) {
            foreach ($this->shopBaskets as $shopBasket) {
                $shopBasket->recalculate()->save();
            }
        }

        return $this;
    }


    /**
     * Минимальная цена по которой можно купить товар
     *
     * @param ShopCmsContentElement $shopCmsContentElement
     * @return array
     */
    public function getMinMoneyByProduct(ShopCmsContentElement $shopCmsContentElement)
    {
        $price = $shopCmsContentElement->shopProduct->baseProductPrice;
        $money = clone $price->money;

        $applyedShopDiscounts = [];

        $shopDiscounts = [];
        /**
         * @var ShopDiscount $shopDiscount
         */
        $shopDiscountsTmp = ShopDiscount::find()
            ->active()
            ->orderBy(['shop_discount.priority' => SORT_ASC])
            ->leftJoin('shop_discount2type_price', 'shop_discount2type_price.discount_id = shop_discount.id')
            ->andWhere([
                'or',
                ['shop_discount.site_id' => ""],
                ['shop_discount.site_id' => null],
                ['shop_discount.site_id' => \Yii::$app->cms->site->id],
            ])
            ->andWhere([
                'shop_discount2type_price.type_price_id' => $price->typePrice->id,
            ])
            ->all();


        if ($shopDiscountsTmp) {
            foreach ($shopDiscountsTmp as $shopDiscount) {
                if (\Yii::$app->authManager->checkAccess($this->user ? $this->user->id : null, $shopDiscount->permissionName)) {
                    $shopDiscounts[$shopDiscount->id] = $shopDiscount;
                }
            }
        }

        if ($this->discountCoupons) {
            foreach ($this->discountCoupons as $discountCoupon) {
                $shopDiscounts[$discountCoupon->shopDiscount->id] = $discountCoupon->shopDiscount;
            }
        }

        if ($shopDiscounts) {
            ArrayHelper::multisort($shopDiscounts, 'priority');
        }

        if ($shopDiscounts) {

            $discountPercent = 0;

            foreach ($shopDiscounts as $shopDiscount) {

                if ($shopDiscount->isTrueConditions($shopCmsContentElement)) {
                    if ($shopDiscount->value_type == ShopDiscount::VALUE_TYPE_P) {

                        $percent = $shopDiscount->value / 100;
                        $discountPercent = $discountPercent + $percent;

                        $discountMoney = clone $money;
                        $discountMoney->multiply($percent);

                        if ($shopDiscount->max_discount > 0) {
                            if ($shopDiscount->max_discount < $discountMoney->amount) {
                                $discountMoney->amount = $shopDiscount->max_discount;
                            }
                        }
                        $money->sub($discountMoney);
                        $applyedShopDiscounts[] = $shopDiscount;

                        //Нужно остановится и не применять другие скидки
                        if ($shopDiscount->last_discount === Cms::BOOL_Y) {
                            break;
                        }
                    } elseif ($shopDiscount->value_type == ShopDiscount::VALUE_TYPE_F) {

                    }
                }
            }
        }

        return [$money, $applyedShopDiscounts];
    }
}