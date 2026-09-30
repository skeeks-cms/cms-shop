<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */

namespace skeeks\cms\shop\delivery;

use skeeks\cms\money\Money;
use skeeks\cms\shop\models\ShopDelivery;
use skeeks\cms\shop\models\ShopOrder;
use yii\base\Model;

/**
 *
 * @property Money $money
 *
 * @author Semenov Alexander <semenov@skeeks.com>
 */
abstract class DeliveryCheckoutModel extends Model
{
    /**
     * @var ShopOrder
     */
    public $shopOrder = null;

    /**
     * @var DeliveryHandler
     */
    public $deliveryHandler = null;

    /**
     * @var ShopDelivery
     */
    public $delivery = null;

    /** Server-owned quote metadata. Never load these values from a checkout POST. */
    public $deliveryCalculationHash;
    public $deliveryCalculatedAt;
    public $deliveryCalculationError;

    /** Fixed-price and existing adapters remain opt-out. */
    public function supportsAutomaticCalculation()
    {
        return false;
    }

    /** Refresh from server-side cart data. On failure clear stale money and return false. */
    public function refreshDeliveryPrice($force = false)
    {
        return true;
    }

    /** Only persisted order data may restore quote metadata. */
    public function loadStoredDeliveryData(array $data)
    {
        $this->load($data, '');
        foreach (['deliveryCalculationHash', 'deliveryCalculatedAt', 'deliveryCalculationError'] as $attribute) {
            $this->$attribute = isset($data[$attribute]) ? $data[$attribute] : null;
        }
    }

    public function getStoredDeliveryData()
    {
        $data = $this->toArray();
        unset($data['shopOrder'], $data['deliveryHandler'], $data['delivery']);
        return $data;
    }

    /**
     * @return Money
     */
    public function getMoney()
    {
        return $this->delivery->money;
        return new Money("", $this->shopOrder->currency_code);
    }

    /**
     * @return array
     */
    public function getVisibleAttributes()
    {
        return [];
    }

    /**
     * Установить необходимые данные по заказу
     * Вызывается перед сохранением заказа
     *
     * @return $this
     */
    /**
     * Установить необходимые данные по заказу
     * Вызывается перед сохранением заказа
     *
     * @return $this
     */
    public function modifyOrder(ShopOrder $order)
    {
        //Чистка данных по пункту вывоза
        $order->shop_store_id = null;

        $order->delivery_address = null;
        $order->delivery_latitude = null;
        $order->delivery_longitude = null;
        $order->delivery_entrance = null;
        $order->delivery_apartment_number = null;
        $order->delivery_floor = null;
        $order->delivery_comment = null;

        return $this;
    }
}
