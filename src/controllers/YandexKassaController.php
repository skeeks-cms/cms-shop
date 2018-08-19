<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 21.09.2015
 */

namespace skeeks\cms\shop\controllers;

use skeeks\cms\shop\models\ShopBill;
use skeeks\cms\shop\models\ShopOrder;
use skeeks\cms\shop\models\ShopPayment;
use skeeks\cms\shop\paySystems\YandexKassaPaySystem;
use yii\base\Exception;
use yii\helpers\ArrayHelper;
use yii\web\Controller;

/**
 * Class YandexKassaController
 * @package skeeks\cms\shop\controllers
 */
class YandexKassaController extends Controller
{
    public function beforeAction($action)
    {
        if (in_array($action->id, ['check-order', 'payment-aviso'])) {
            $this->enableCsrfValidation = false;
            \Yii::$app->response->setStatusCode(200);
            \Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
            \Yii::$app->response->headers->set('Content-Type', 'application/xml; charset=utf-8');
            $this->layout = false;

            \Yii::info("{$action->id} URL: ".\Yii::$app->request->absoluteUrl, YandexKassaPaySystem::class);
        }

        return parent::beforeAction($action);
    }

    public function actionCheckOrder()
    {
        $request = YandexKassaPaySystem::getRequest();
        \Yii::info("{$action->id} REQUEST2: ".print_r($request, true), YandexKassaPaySystem::class);

        try {
            $shopOrder = $this->getBill($request);
            /**
             * @var $yandexKassa YandexKassaPaySystem
             */
            $yandexKassa = $shopOrder->shopPaySystem->handler;
            if (!$yandexKassa->checkRequest($request)) {
                $response = $yandexKassa->buildResponse("checkOrder", $request['invoiceId'], 1);
                return $response;
            }

            /*if (\Yii::$app->request->post('Status') == "CONFIRMED")
            {
                \Yii::info("Успешный платеж", YandexKassaPaySystem::class);
                $shopOrder->processNotePayment();
            }*/

        } catch (\Exception $e) {
            \Yii::error($e->getMessage(), YandexKassaPaySystem::class);
            $response = $yandexKassa->buildResponse("checkOrder", $request['invoiceId'], 500, $e->getMessage());
            return $response;
        }

        if ($request['orderSumAmount'] < 1) {
            \Yii::error("The amount should be more than 1 rubles.", YandexKassaPaySystem::class);
            $response = $yandexKassa->buildResponse("checkOrder", $request['invoiceId'], 100,
                "The amount should be more than 100 rubles.");
        } else {
            $response = $yandexKassa->buildResponse("checkOrder", $request['invoiceId'], 0);
        }

        \Yii::info("Response actionCheckOrder: ".$response, YandexKassaPaySystem::class);
        return $response;
    }
    /**
     * @param $request
     * @return ShopOrder
     * @throws Exception
     * @deprecated
     */
    public function getOrder($request)
    {
        if (!$orderNumber = ArrayHelper::getValue($request, 'orderNumber')) {
            throw new Exception('Некорректный запрос от банка. Не указан orderNumber.');
        }

        /**
         * @var $shopOrder ShopOrder
         */
        if (!$shopOrder = ShopOrder::findOne($orderNumber)) {
            throw new Exception('Заказ не найден в базе.');
        }

        if ($shopOrder->id != $orderNumber) {
            throw new Exception('Не совпадает номер заказа.');
        }

        if ((float)$shopOrder->money->getValue() != (float)ArrayHelper::getValue($request, 'orderSumAmount')) {
            throw new Exception('Не совпадает сумма заказа.');
        }

        return $shopOrder;
    }
    /**
     * @param $request
     * @return ShopBill
     * @throws Exception
     */
    public function getBill($request)
    {
        if (!$orderNumber = ArrayHelper::getValue($request, 'orderNumber')) {
            throw new Exception('Некорректный запрос от банка. Не указан orderNumber.');
        }

        /**
         * @var $shopOrder ShopBill
         */
        if (!$shopOrder = ShopBill::findOne($orderNumber)) {
            throw new Exception('Заказ не найден в базе.');
        }

        if ($shopOrder->id != $orderNumber) {
            throw new Exception('Не совпадает номер заказа.');
        }

        if ((float)$shopOrder->money->amount != (float)ArrayHelper::getValue($request, 'orderSumAmount')) {
            throw new Exception('Не совпадает сумма заказа.');
        }

        return $shopOrder;
    }
    /**
     * @return array|string
     */
    public function actionPaymentAviso()
    {
        $request = YandexKassaPaySystem::getRequest();
        \Yii::info("{$this->action->id} REQUEST2: ".print_r($request, true), YandexKassaPaySystem::class);

        try {
            $bill = $this->getBill($request);
            /**
             * @var $yandexKassa YandexKassaPaySystem
             */
            $yandexKassa = $bill->shopPaySystem->handler;
            if (!$yandexKassa->checkRequest($request)) {
                $response = $yandexKassa->buildResponse("paymentAviso", $request['invoiceId'], 1);
                return $response;
            }

        } catch (\Exception $e) {
            \Yii::error($e->getMessage(), YandexKassaPaySystem::class);
            $response = $yandexKassa->buildResponse("paymentAviso", $request['invoiceId'], 500, $e->getMessage());
            return $response;
        }

        \Yii::info("Успешный платеж", YandexKassaPaySystem::class);


        //$shopOrder->processNotePayment();




        $transaction = \Yii::$app->db->beginTransaction();

        try {

            $payment = new ShopPayment();
            $payment->shop_buyer_id = $bill->shop_buyer_id;
            $payment->shop_pay_system_id = $bill->shop_pay_system_id;
            $payment->shop_order_id = $bill->shop_order_id;
            $payment->amount = $bill->amount;
            $payment->currency_code = $bill->currency_code;
            $payment->comment = "Оплата по счету №{$bill->id} от ".\Yii::$app->formatter->asDate($bill->created_at);
            $payment->external_data = $response;

            if (!$payment->save()) {
                throw new Exception("Не сохранился платеж: ".print_r($payment->errors, true));
            }

            $bill->paid_at = time();
            $bill->shop_payment_id = $payment->id;

            if (!$bill->save()) {
                throw new Exception("Не обновился счет: ".print_r($payment->errors, true));
            }

            $bill->shopOrder->paid_at = time();
            $bill->shopOrder->save();

            $transaction->commit();

            return $this->redirect($bill->shopOrder->url);

        } catch (\Exception $e) {
            $transaction->rollBack();
            \Yii::error($e->getMessage(), YandexKassaPaySystem::class);
            throw $e;
        }




        $response = $yandexKassa->buildResponse("paymentAviso", $request['invoiceId'], 0);

        \Yii::info("Response actionCheckOrder: ".$response, YandexKassaPaySystem::class);

        return $response;
    }
    /**
     * Payment form
     *
     * @throws Exception
     */
    public function actionBillForm()
    {
        if (!$code = \Yii::$app->request->get('code')) {
            throw new Exception('Order not found');
        }

        if (!$order = ShopBill::find()->where(['code' => $code])->one()) {
            throw new Exception('Order not found');
        }

        return $this->render($this->action->id, [
            'model' => $order,
        ]);
    }
}