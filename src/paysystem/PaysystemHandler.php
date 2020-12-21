<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */

namespace skeeks\cms\shop\paysystem;

use skeeks\cms\IHasConfigForm;
use skeeks\cms\shop\models\ShopBill;
use skeeks\cms\shop\models\ShopOrder;
use skeeks\cms\traits\HasComponentDescriptorTrait;
use skeeks\cms\traits\TConfigForm;
use yii\base\Model;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
abstract class PaysystemHandler extends Model implements IHasConfigForm
{
    use HasComponentDescriptorTrait;
    use TConfigForm;

    /**
     * @param ShopOrder $shopOrder
     * @return bool
     */
    public function actionPayOrder(ShopOrder $shopOrder)
    {
        return true;
    }

    /**
     * @param ShopOrder $shopOrder
     * @return ShopBill
     */
    public function getShopBill(ShopOrder $shopOrder)
    {
        if (!$shopBill = $shopOrder->getShopOrderBills()
            ->andWhere([
                'paid_at' => null
            ])
            ->andWhere([
                'shop_pay_system_id' => $shopOrder->shop_pay_system_id,
            ])
            ->andWhere([
                'amount' => $shopOrder->amount,
            ])
            ->andWhere([
                'shop_buyer_id' => $shopOrder->shop_buyer_id,
            ])
            ->andWhere([
                'currency_code' => $shopOrder->currency_code,
            ])
            ->one()) {

            $shopBill = $this->createShopBill($shopOrder);
        }

        return $shopBill;
    }

    /**
     * @param ShopOrder $shopOrder
     * @return ShopBill
     */
    public function createShopBill(ShopOrder $shopOrder)
    {
        $shopBill = new ShopBill();

        $shopBill->shop_order_id = $shopOrder->id;
        $shopBill->shop_buyer_id = $shopOrder->shop_buyer_id;
        $shopBill->shop_pay_system_id = $shopOrder->shop_pay_system_id;

        $shopBill->amount = $shopOrder->amount;
        $shopBill->currency_code = $shopOrder->currency_code;

        $shopBill->description = "Оплата по заказу №".$shopOrder->id;

        if (!$shopBill->save()) {
            throw new UserException('Не создался платеж: '.print_r($shopPayment->errors, true));
        }

        return $shopBill;
    }
}