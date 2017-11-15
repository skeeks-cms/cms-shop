<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 21.09.2015
 */

namespace skeeks\cms\shop\controllers;

use skeeks\cms\shop\models\ShopOrder;
use skeeks\cms\shop\paySystems\YandexKassaPaySystem;
use yii\base\Exception;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\web\Controller;
use yii\web\Response;

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

            \Yii::info("{$action->id} URL: " . \Yii::$app->request->absoluteUrl, YandexKassaPaySystem::class);
        }

        return parent::beforeAction($action);
    }

    public function actionCheckOrder()
    {
        $request = YandexKassaPaySystem::getRequest();
        \Yii::info("{$action->id} REQUEST2: " . print_r($request, true), YandexKassaPaySystem::class);

        try {
            $shopOrder = $this->getOrder($request);
            /**
             * @var $yandexKassa YandexKassaPaySystem
             */
            $yandexKassa = $shopOrder->paySystem->handler;
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

        \Yii::info("Response actionCheckOrder: " . $response, YandexKassaPaySystem::class);
        return $response;
    }

    /**
     * @return array|string
     */
    public function actionPaymentAviso()
    {
        $request = YandexKassaPaySystem::getRequest();
        \Yii::info("{$this->action->id} REQUEST2: " . print_r($request, true), YandexKassaPaySystem::class);

        try {
            $shopOrder = $this->getOrder($request);
            /**
             * @var $yandexKassa YandexKassaPaySystem
             */
            $yandexKassa = $shopOrder->paySystem->handler;
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
        $shopOrder->processNotePayment();

        $response = $yandexKassa->buildResponse("paymentAviso", $request['invoiceId'], 0);

        \Yii::info("Response actionCheckOrder: " . $response, YandexKassaPaySystem::class);

        return $response;
    }


    /**
     * Payment form
     *
     * @throws Exception
     */
    public function actionOrderForm()
    {
        if (!$key = \Yii::$app->request->get('key')) {
            throw new Exception('Order not found');
        }

        if (!$order = ShopOrder::find()->where(['key' => $key])->one()) {
            throw new Exception('Order not found');
        }

        return $this->render($this->action->id, [
            'model' => $order
        ]);
    }


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
}