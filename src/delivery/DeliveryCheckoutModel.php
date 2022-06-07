<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */

namespace skeeks\cms\shop\delivery;

use skeeks\cms\money\Money;
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
     * @return Money
     */
    public function getMoney()
    {
        return $this->shopOrder->shopDelivery->money;
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
    public function modifyOrder(ShopOrder $order)
    {
        return $this;
    }
}