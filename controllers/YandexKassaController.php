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
use yii\helpers\Json;
use yii\web\Controller;
use yii\web\Response;

/**
 * Class YandexKassaController
 * @package skeeks\cms\shop\controllers
 */
class YandexKassaController extends Controller
{
    /**
     * @inheritdoc
     */
    public $enableCsrfValidation = false;


    /**
     * Payment form
     *
     * @throws Exception
     */
    public function actionOrderForm()
    {
        if (!$key = \Yii::$app->request->get('key'))
        {
            throw new Exception('Order not found');
        }

        if (!$order = ShopOrder::find()->where(['key' => $key])->one())
        {
            throw new Exception('Order not found');
        }

        return $this->render($this->action->id, [
            'model' => $order
        ]);
    }

    public function actionCheckOrder()
    {
        \Yii::info("POST actionCheckOrder: " . Json::encode(\Yii::$app->request->post()), YandexKassaPaySystem::class);

        $request = \Yii::$app->request->post();
        $this->layout = false;

        if (!$request)
        {
            return [];
        }

        try
        {
            $shopOrder = $this->getOrder();
            /**
             * @var $yandexKassa YandexKassaPaySystem
             */
            $yandexKassa = $shopOrder->paySystem->handler;
            if (!$yandexKassa->checkRequest($request))
            {
                $response = $yandexKassa->buildResponse("checkOrder", $request['invoiceId'], 1);
                return $response;
            }

            /*if (\Yii::$app->request->post('Status') == "CONFIRMED")
            {
                \Yii::info("Успешный платеж", YandexKassaPaySystem::class);
                $shopOrder->processNotePayment();
            }*/

        } catch (\Exception $e)
        {
            \Yii::error($e->getMessage(), YandexKassaPaySystem::class);
            $response = $yandexKassa->buildResponse("checkOrder", $request['invoiceId'], 500, $e->getMessage());
            return $response;
        }

        if ($request['orderSumAmount'] < 100) {
            $response = $yandexKassa->buildResponse("checkOrder", $request['invoiceId'], 100, "The amount should be more than 100 rubles.");
        } else {
            $response = $yandexKassa->buildResponse("checkOrder", $request['invoiceId'], 0);
        }

        return $response;
    }


    public function getOrder()
    {
        if (!\Yii::$app->request->post('orderNumber'))
        {
            throw new Exception('Некорректный запрос от банка. Не указан orderNumber.');
        }

        /**
         * @var $shopOrder ShopOrder
         */
        if (!$shopOrder = ShopOrder::findOne(\Yii::$app->request->post('orderNumber')))
        {
            throw new Exception('Заказ не найден в базе.');
        }

        if ($shopOrder->id != \Yii::$app->request->post('orderNumber'))
        {
            throw new Exception('Не совпадает номер заказа.');
        }

        if ((float) $shopOrder->money->getValue() != (float) \Yii::$app->request->post('orderSumAmount'))
        {
            throw new Exception('Не совпадает сумма заказа.');
        }

        return $shopOrder;
    }

    /**
     * @return array|string
     */
    public function actionPaymentAviso()
    {
        \Yii::info("POST actionPaymentAviso:" . Json::encode(\Yii::$app->request->post()), YandexKassaPaySystem::class);


        $request = \Yii::$app->request->post();
        $this->layout = false;

        if (!$request)
        {
            return [];
        }

        try
        {
            $shopOrder = $this->getOrder();
            /**
             * @var $yandexKassa YandexKassaPaySystem
             */
            $yandexKassa = $shopOrder->paySystem->handler;
            if (!$yandexKassa->checkRequest($request))
            {
                $response = $yandexKassa->buildResponse("paymentAviso", $request['invoiceId'], 1);
                return $response;
            }

        } catch (\Exception $e)
        {
            \Yii::error($e->getMessage(), YandexKassaPaySystem::class);
            $response = $yandexKassa->buildResponse("paymentAviso", $request['invoiceId'], 500, $e->getMessage());
            return $response;
        }

        \Yii::info("Успешный платеж", YandexKassaPaySystem::class);
        $shopOrder->processNotePayment();

        $response = $yandexKassa->buildResponse("paymentAviso", $request['invoiceId'], 0);

        return $response;
    }
}