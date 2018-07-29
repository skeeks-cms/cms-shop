<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */

namespace skeeks\cms\shop\models;

use skeeks\cms\base\ActiveRecord;
use skeeks\cms\components\Cms;
use skeeks\cms\models\CmsContentElement;
use skeeks\cms\models\CmsSite;
use skeeks\cms\models\CmsUser;
use skeeks\cms\models\User;
use skeeks\cms\money\Money;
use skeeks\cms\shop\helpers\ProductPriceHelper;
use yii\db\ActiveQuery;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 *
 * Это объект корзины
 *
 * @property integer           $id
 * @property integer           $created_by
 * @property integer           $updated_by
 * @property integer           $created_at
 * @property integer           $updated_at
 * @property integer           $cms_user_id Пользователь сайта
 * @property integer           $shop_order_id
 *
 * ***
 *
 * @property CmsUser           $cmsUser
 *
 * @property ShopBasket[]      $shopBaskets
 * @property ShopDelivery      $delivery
 * @property ShopBuyer         $buyer
 * @property ShopPaySystem     $paySystem
 *
 * @property ShopPersonType    $personType
 * @property CmsSite           $site
 *
 * @property int               $countShopBaskets
 * @property float             $quantity
 *
 * @property ShopBuyer[]       $shopBuyers
 * @property ShopPaySystem[]   $paySystems
 *
 *
 *
 *
 * @property ShopOrder         $shopOrder
 * @property Money             $money
 * @property Money             $moneyOriginal
 * @property Money             $moneyVat
 * @property Money             $moneyDiscount
 * @property Money             $moneyDelivery
 *
 * @property int               $weight
 * @property bool              $isEmpty
 *
 * @property ShopTypePrice     $buyTypePrices
 * @property ShopTypePrice     $viewTypePrices
 * @property CmsContentElement $store
 */
class ShopCart extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%shop_cart}}';
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'cms_user_id'   => \Yii::t('skeeks/shop/app', 'User site'),
            'shop_order_id' => \Yii::t('skeeks/shop/app', 'Заказ'),
        ]);
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
                    'cms_user_id',
                    'shop_order_id',
                ],
                'integer',
            ],
            [['cms_user_id'], 'unique'],
        ]);
    }
    /**
     * @return array
     */
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
    public function getCmsUser()
    {
        return $this->hasOne(User::class, ['id' => 'cms_user_id']);
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopOrder()
    {
        return $this->hasOne(ShopOrder::class, ['id' => 'shop_order_id']);
    }


    /**
     * @return \yii\db\ActiveQuery
     * @deprecated
     */
    public function getShopBaskets()
    {
        return $this->shopOrder->getShopOrderItems();
    }
    /**
     * Количество позиций в корзине
     *
     * @return int
     */
    public function getCountShopBaskets()
    {
        return $this->shopOrder->countShopOrderItems;
    }
    /**
     * @return float
     * @deprecated
     */
    public function getQuantity()
    {
        return $this->shopOrder->quantity;
    }




    /**
     *
     * Итоговая стоимость корзины с учетом скидок, доставок, наценок, то что будет платить человек
     *
     * @return Money
     */
    public function getMoney()
    {
        return $this->shopOrder->calcMoney;

        /*$money = \Yii::$app->money->newMoney();

        foreach ($this->shopBaskets as $shopBasket) {
            $money = $money->add($shopBasket->money->multiply($shopBasket->quantity));
        }

        if ($this->moneyDelivery) {
            $money = $money->add($this->moneyDelivery);
        }

        return $money;*/
    }

    /**
     * Итоговая стоимость корзины, без учета скидок
     * @return Money
     */
    public function getMoneyOriginal()
    {
        return $this->shopOrder->calcMoneyItems;
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
     * @return Money
     */
    public function getMoneyVat()
    {
        return $this->shopOrder->calcMoneyVat;
        /*$money = \Yii::$app->money->newMoney();

        foreach ($this->shopBaskets as $shopBasket) {
            $money = $money->add($shopBasket->moneyVat->multiply($shopBasket->quantity));
        }

        return $money;*/
    }
    /**
     *
     * Итоговая скидка по всей корзине
     *
     * @return Money
     * @deprecated
     */
    public function getMoneyDiscount()
    {
        return $this->shopOrder->calcMoneyDiscount;
        /*
        $money = \Yii::$app->money->newMoney();
        foreach ($this->shopBaskets as $shopBasket) {
            $money = $money->add($shopBasket->moneyDiscount->multiply($shopBasket->quantity));
        }
        return $money;*/
    }

    /**
     * Итоговая сумма доставки
     * @return Money
     * @deprecated
     */
    public function getMoneyDelivery()
    {
        return $this->shopOrder->calcMoneyDelivery;
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
        return (bool)($this->shopOrder->countShopOrderItems == 0);
    }

    /**
     *
     * @return ActiveQuery
     */
    public function getShopBuyers()
    {
        return $this->hasMany(ShopBuyer::class, ['cms_user_id' => 'id'])->via('user');
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
     * @deprecated
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
     * @return $this
     * @deprecated
     */
    public function recalculate()
    {
        return $this->shopOrder->recalculate();
        /*if ($this->shopBaskets) {
            foreach ($this->shopBaskets as $shopBasket) {
                $shopBasket->recalculate()->save();
            }
        }

        return $this;*/
    }


    /**
     * @param ShopCmsContentElement $shopCmsContentElement
     * @return ProductPriceHelper
     * @deprecated
     */
    public function getProductPriceHelper(ShopCmsContentElement $shopCmsContentElement)
    {
        return $this->shopOrder->getProductPriceHelper($shopCmsContentElement);
    }

    /**
     *
     * Доступные цены для покупки на сайте
     *
     * @return ShopTypePrice[]
     * @deprecated
     */
    public function getBuyTypePrices()
    {
        return $this->shopOrder->buyTypePrices;
    }


    /**
     * @return ShopDiscountCoupon[]
     * @deprecated
     */
    public function getDiscountCoupons()
    {
        return $this->shopOrder->shopDiscountCoupons;
    }

}