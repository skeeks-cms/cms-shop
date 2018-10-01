<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */

namespace skeeks\cms\shop\helpers;

use skeeks\cms\components\Cms;
use skeeks\cms\money\Money;
use skeeks\cms\shop\models\ShopCmsContentElement;
use skeeks\cms\shop\models\ShopDiscount;
use skeeks\cms\shop\models\shopOrder;
use skeeks\cms\shop\models\ShopProductPrice;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 *
 * @property ShopOrder        $shopOrder;
 *
 * @property ShopProductPrice $basePrice; Базовая цена
 *
 * @property Money            $minMoney; Минимальный объект цены по которой можно купить товар
 * @property ShopProductPrice $minPrice; Минимальная цена по которой можно купить товар
 * @property ShopDiscount[]   $applyedDiscounts; Примененные скидки
 *
 * @property bool             $hasDiscount; Есть скидка?
 * @property float            $percent; Процент скидки
 * @property Money            $discountMoney; Скидка в виде денежного объекта
 */
class ProductPriceHelper extends Component
{
    /**
     * @var ShopDiscount[]
     */
    static protected $_shopDiscounts = false;
    /**
     * @var ShopCmsContentElement
     */
    public $shopCmsContentElement;
    /**
     * @var ShopProductPrice
     */
    public $price;
    /**
     * @var shopOrder
     */
    protected $_shopOrder;
    /**
     * @var ShopProductPrice
     */
    protected $_minPrice;
    /**
     * @var ShopProductPrice
     */
    protected $_basePrice;
    /**
     * @var Money
     */
    protected $_minMoney;
    /**
     * @var ShopDiscount[]
     */
    protected $_applyedDiscounts;
    /**
     *
     */
    public function init()
    {
        parent::init();

        if (!$this->shopCmsContentElement) {
            throw new InvalidConfigException("Не заполнены обязательные данные");
        }

        if (!$this->price) {
            throw new InvalidConfigException("Не заполнены обязательные данные");
        }

        $price = $this->price;
        $money = clone $price->money;

        $applyedShopDiscounts = [];
        $shopDiscounts = [];

        /**
         * @var ShopDiscount $shopDiscount
         */
        $shopDiscountsTmp = self::getShopDiscounts();

        if ($shopDiscountsTmp) {
            foreach ($shopDiscountsTmp as $shopDiscount) {
                if (\Yii::$app->authManager->checkAccess($this->shopOrder->cmsUser ? $this->shopOrder->cmsUser->id : null, $shopDiscount->permissionName)) {
                    $shopDiscounts[$shopDiscount->id] = $shopDiscount;
                }
            }
        }

        if ($this->shopOrder->shopDiscountCoupons) {
            foreach ($this->shopOrder->shopDiscountCoupons as $discountCoupon) {
                $shopDiscounts[$discountCoupon->shopDiscount->id] = $discountCoupon->shopDiscount;
            }
        }

        if ($shopDiscounts) {
            ArrayHelper::multisort($shopDiscounts, 'priority');
        }

        if ($shopDiscounts) {

            $discountPercent = 0;

            foreach ($shopDiscounts as $shopDiscount) {

                if ($shopDiscount->isTrue($this->shopCmsContentElement, $price)) {
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
                        $discountMoney = new Money($shopDiscount->value, "RUB");

                        $money->sub($discountMoney);
                        $applyedShopDiscounts[] = $shopDiscount;

                        //Нужно остановится и не применять другие скидки
                        if ($shopDiscount->last_discount === Cms::BOOL_Y) {
                            break;
                        }
                    }
                }
            }
        }

        $this->_minMoney = $money;
        $this->_minPrice = $price;
        $this->_applyedDiscounts = $applyedShopDiscounts;
    }
    static public function getShopDiscounts()
    {
        if (self::$_shopDiscounts === false) {
            self::$_shopDiscounts = ShopDiscount::find()->active()->andWhere(['assignment_type' => ShopDiscount::ASSIGNMENT_TYPE_PRODUCT])->all();
        }

        return self::$_shopDiscounts;
    }
    /**
     * @return ShopDiscount[]
     */
    public function getApplyedDiscounts()
    {
        return $this->_applyedDiscounts;
    }

    /**
     * @return ShopProductPrice
     */
    public function getMinPrice()
    {
        return $this->_minPrice;
    }

    /**
     * @return Money
     */
    public function getMinMoney()
    {
        return $this->_minMoney;
    }

    /**
     * @return ShopOrder
     */
    public function getshopOrder()
    {
        if (!$this->_shopOrder) {
            $this->_shopOrder = \Yii::$app->shop->cart->shopOrder;
        }

        return $this->_shopOrder;
    }

    /**
     * @param shopOrder $shopOrder
     * @return $this
     */
    public function setshopOrder(ShopOrder $shopOrder)
    {
        $this->_shopOrder = $shopOrder;
        return $this;
    }


    /**
     * @return ShopProductPrice
     */
    public function getBasePrice()
    {
        if (!$this->_basePrice) {
            $this->_basePrice = $this->shopCmsContentElement->shopProduct->baseProductPrice;
        }

        return $this->_basePrice;
    }

    /**
     * @param ShopProductPrice $shopProductPrice
     * @return $this
     */
    public function setBasePrice(ShopProductPrice $shopProductPrice)
    {
        $this->_basePrice = $shopProductPrice;
        return $this;
    }

    /**
     * @return bool
     */
    public function getHasDiscount()
    {
        return (bool)($this->basePrice->money->amount != $this->minMoney->amount);
    }

    /**
     * @return bool
     */
    public function getPercent()
    {
        return ($this->basePrice->money->amount - $this->minMoney->amount) / $this->basePrice->money->amount;
    }

    /**
     * @return Money
     */
    public function getDiscountMoney()
    {
        $price = clone $this->basePrice->money;
        return $price->sub($this->minMoney);
    }
}