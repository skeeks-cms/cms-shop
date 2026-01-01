<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */

namespace skeeks\cms\shop\paysystem;

use skeeks\cms\IHasConfigForm;
use skeeks\cms\models\CmsUser;
use skeeks\cms\shop\models\ShopBill;
use skeeks\cms\shop\models\ShopOrder;
use skeeks\cms\traits\HasComponentDescriptorTrait;
use skeeks\cms\traits\TConfigForm;
use yii\base\Model;
use yii\base\UserException;

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
                'closed_at' => null
            ])
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
                'cms_user_id' => $shopOrder->cms_user_id,
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

        if (!$shopOrder->cms_user_id) {
            $cmsUser = null;
            if ($shopOrder->contact_phone) {
                //Если пользователь найден по телефону, то привязычваем заказа к нему
                $cmsUser = CmsUser::find()->cmsSite()->phone($shopOrder->contact_phone)->one();
            }
    
            //Если пользователь не найден по телефону пробуем найти по еmail
            if ($cmsUser === null && $shopOrder->contact_email) {
                $cmsUser = CmsUser::find()->cmsSite()->email($shopOrder->contact_email)->one();
            }
    
    
    
            if (!$cmsUser) {
                $cmsUser = new CmsUser();
                $cmsUser->phone = $shopOrder->contact_phone;
                $cmsUser->email = $shopOrder->contact_email;
                $cmsUser->first_name = $shopOrder->contact_first_name;
                $cmsUser->last_name = $shopOrder->contact_last_name;
    
                if (!$cmsUser->save()) {
                    throw new Exception(print_r($cmsUser->errors, true));
                }
            }
            
            $shopOrder->cms_user_id = $cmsUser->id;
            
            $shopOrder->update(false, ['cms_user_id']);
        }
        
        
        $shopBill->cms_user_id = $shopOrder->cms_user_id;
        $shopBill->shop_pay_system_id = $shopOrder->shop_pay_system_id;
        $shopBill->shop_order_id = $shopOrder->id;

        $shopBill->amount = $shopOrder->amount;
        $shopBill->currency_code = $shopOrder->currency_code;

        $shopBill->description = "Оплата по заказу №".$shopOrder->id;

        if (!$shopBill->save()) {
            throw new UserException('Не создался платеж: '.print_r($shopBill->errors, true));
        }

        return $shopBill;
    }
}