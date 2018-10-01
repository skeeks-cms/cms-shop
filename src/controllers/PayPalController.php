<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 21.09.2015
 */

namespace skeeks\cms\shop\controllers;

use skeeks\cms\base\Controller;
use skeeks\cms\shop\models\ShopOrder;
use skeeks\cms\shop\paySystems\PayPalPaySystem;

/**
 * Class RobocassaController
 * @package skeeks\cms\shop\controllers
 */
class PayPalController extends Controller
{
    /**
     * @inheritdoc
     */
    public $enableCsrfValidation = false;

    public function actionIpn()
    {
        $custom = (int)\Yii::$app->request->post('custom');
        \Yii::info('Ipn post data: '.serialize(\Yii::$app->request->post()), 'paypal');
        \Yii::info('Ipn custom: '.$custom, 'paypal');

        /*if (!$custom)
        {
            $custom = (int) \Yii::$app->request->get('custom');
        }*/

        if (!$custom) {
            \Yii::error('Order id not found', 'paypal');
            return false;
        }

        $shopOrder = ShopOrder::findOne($custom);
        \Yii::info('Ordder id: '.$shopOrder->id);

        if (!$shopOrder) {
            \Yii::error('Ordder not found: '.$custom, 'paypal');
        }

        /**
         * @var $payPal PayPalPaySystem
         * @var $shopOrder ShopOrder
         */
        $payPal = $shopOrder->paySystem->paySystemHandler;
        if (!$payPal instanceof PayPalPaySystem) {
            \Yii::error('Order handler not paypal: ', 'paypal');
        }

        if ($payPal->initIpn()) {
            if ($shopOrder->payed != "Y") {
                \Yii::info('Order processNotePayment', 'paypal');
                $shopOrder->processNotePayment();
            }

            $shopOrder->ps_status = "STATUS_SUCCESS";
            $shopOrder->payed = "Y";
            $shopOrder->save();

        } else {
            \Yii::error('Ipn false: ', 'paypal');
        }

        return "";
    }
}