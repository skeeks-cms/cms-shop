<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 21.09.2015
 */

namespace skeeks\cms\shop\controllers;

use skeeks\cms\helpers\StringHelper;
use skeeks\cms\shop\models\ShopBill;
use skeeks\cms\shop\models\ShopOrder;
use skeeks\cms\shop\models\ShopPayment;
use skeeks\cms\shop\paySystems\YandexKassaPaySystem;
use YandexCheckout\Client;
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

        if (!$bill = ShopBill::find()->where(['code' => $code])->one()) {
            throw new Exception('Order not found');
        }

        /*return $this->render($this->action->id, [
            'model' => $bill,
        ]);*/


        /* @var $yandexKassa \skeeks\cms\shop\paySystems\YandexKassaPaySystem */
        /* @var $model \skeeks\cms\shop\models\ShopBill */

        $model = $bill;
        $yandexKassa = $model->shopPaySystem->paySystemHandler;
        $money = $model->money->convertToCurrency("RUB");
        $returnUrl = $model->shopOrder->getUrl([], true);

        /**
         * Для чеков нужно указывать информацию о товарах
         * https://kassa.yandex.ru/developers/api?lang=php#create_payment
         */
        $shopBuyer = $model->shopOrder->shopBuyer;
        $receipt = [];
        if ($yandexKassa->is_receipt) {
            if ($shopBuyer->email) {
                $receipt['customer'] = [
                    'email'     => $shopBuyer->email,
                    'full_name' => $shopBuyer->name,
                ];
            }

            foreach ($model->shopOrder->shopOrderItems as $shopOrderItem) {
                $itemData = [];

                $itemData['description'] = StringHelper::substr($shopOrderItem->name, 0, 128);
                $itemData['quantity'] = $shopOrderItem->quantity;
                $itemData['vat_code'] = 1; //todo: доработать этот момент
                $itemData['amount'] = [
                    'value'    => $shopOrderItem->money->amount,
                    'currency' => 'RUB',
                ];

                $receipt['items'][] = $itemData;
            }

            /**
             * Стоимость доставки так же нужно добавить
             */
            if ((float)$model->shopOrder->moneyDelivery->amount > 0) {
                $itemData = [];
                $itemData['description'] = StringHelper::substr($model->shopOrder->shopDelivery->name, 0, 128);
                $itemData['quantity'] = 1;
                $itemData['vat_code'] = 1; //todo: доработать этот момент
                $itemData['amount'] = [
                    'value'    => $model->shopOrder->moneyDelivery->amount,
                    'currency' => 'RUB',
                ];

                $receipt['items'][] = $itemData;
            }
            /**
             * Стоимость скидки
             */
            //todo: тут можно еще подумать, это временное решение
            if ((float)$model->shopOrder->moneyDiscount->amount > 0) {
                $discountValue = $model->shopOrder->moneyDiscount->amount;
                foreach ($receipt['items'] as $key => $item)
                {
                    if ($discountValue == 0) {
                        break;
                    }
                    if ($item['amount']['value']) {
                        if ($item['amount']['value'] >= $discountValue) {
                            $item['amount']['value'] = $item['amount']['value'] - $discountValue;
                            $discountValue = 0;
                        } else {
                            $item['amount']['value'] = 0;
                            $discountValue = $discountValue - $item['amount']['value'];
                        }
                    }
                    
                    $receipt['items'][$key] = $item;
                }

                //$receipt['items'][] = $itemData;
            }
        }

        $client = new Client();
        $client->setAuth($yandexKassa->shop_id, $yandexKassa->shop_password);
        $payment = $client->createPayment([
                'receipt'      => $receipt,
                'amount'       => [
                    'value'    => $money->amount,
                    'currency' => 'RUB',
                ],
                'confirmation' => [
                    'type'       => 'redirect',
                    'return_url' => $returnUrl,
                ],
                'description'  => 'Заказ №'.$model->shop_order_id,
            ],
            uniqid('', true)
        );


        \Yii::info(print_r($payment, true), self::class);

        if (!$payment->id) {
            throw new Exception('Yandex kassa payment id not found');
        }

        $model->external_data = [
            'id' => $payment->id,
        ];
        $model->save();


        /*$paymentId = $payment->id;
        $idempotenceKey = uniqid('', true);
        $response = $client->capturePayment(
          array(
              'amount' => array(
                  'value' => '2.00',
                  'currency' => 'RUB',
              ),
          ),
          $paymentId,
          $idempotenceKey
        );*/

        return $this->redirect($payment->confirmation->getConfirmationUrl());
    }

    public $enableCsrfValidation = false;

    public function actionPaymentListener()
    {
        \Yii::info(__METHOD__, self::class);
        /*\Yii::info(print_r($_SERVER, true), self::class);
        \Yii::info(print_r(\Yii::$app->request->post(), true), self::class);
        \Yii::info(print_r($_GET, true), self::class);
        \Yii::info(print_r($_POST, true), self::class);
        \Yii::info(print_r($_REQUEST, true), self::class);*/

        //todo: fix it
        /**
         * @var $shopBill ShopBill
         */
        $shopBill = ShopBill::find()->orderBy(['id' => SORT_DESC])->limit(1)->one();

        \Yii::info("Счет: ".print_r($shopBill->toArray(), true), self::class);

        /* @var $yandexKassa \skeeks\cms\shop\paySystems\YandexKassaPaySystem */
        /* @var $model \skeeks\cms\shop\models\ShopBill */

        $model = $shopBill;
        $yandexKassa = $model->shopPaySystem->paySystemHandler;

        if ($shopBill->paid_at) {
            \Yii::info("Счет: ".$shopBill->id." уже оплаечен", self::class);
            return "Ok";
        }

        //\Yii::info(print_r($model->shopPaySystem, true), self::class);
        //\Yii::info(print_r($yandexKassa, true), self::class);

        if ($paymentId = ArrayHelper::getValue($shopBill->external_data, 'id')) {

            \Yii::info("Запрос информации о платеже: ".print_r([
                    'shop_id'       => $yandexKassa->shop_id,
                    'shop_password' => $yandexKassa->shop_password,
                ], true), self::class);


            $client = new Client();
            $client->setAuth($yandexKassa->shop_id, $yandexKassa->shop_password);
            $payment = $client->getPaymentInfo($paymentId);

            \Yii::info("Информация о платеже в yandex kassa: ".print_r($payment, true), self::class);

            if ($payment->status == "waiting_for_capture") {

                $money = $model->money->convertToCurrency("RUB");

                $idempotenceKey = uniqid('', true);
                $response = $client->capturePayment(
                    [
                        'amount' => [
                            'value'    => $money->amount,
                            'currency' => 'RUB',
                        ],
                    ],
                    $paymentId,
                    $idempotenceKey
                );

                \Yii::info("Подтверждение оплаты: ".print_r($response, true), self::class);
            }

            $payment = $client->getPaymentInfo($paymentId);

            if ($payment->status == "succeeded") {


                $transaction = \Yii::$app->db->beginTransaction();

                try {

                    $payment = new ShopPayment();
                    $payment->shop_buyer_id = $shopBill->shop_buyer_id;
                    $payment->shop_pay_system_id = $shopBill->shop_pay_system_id;
                    $payment->shop_order_id = $shopBill->shop_order_id;
                    $payment->amount = $shopBill->amount;
                    $payment->currency_code = $shopBill->currency_code;
                    $payment->comment = "Оплата по счету №{$shopBill->id} от ".\Yii::$app->formatter->asDate($shopBill->created_at);
                    $payment->external_data = $response;

                    if (!$payment->save()) {
                        throw new Exception("Не сохранился платеж: ".print_r($payment->errors, true));
                    }

                    $shopBill->paid_at = time();
                    $shopBill->shop_payment_id = $payment->id;

                    if (!$shopBill->save()) {
                        throw new Exception("Не обновился счет: ".print_r($payment->errors, true));
                    }

                    $shopBill->shopOrder->paid_at = time();
                    $shopBill->shopOrder->save();

                    $transaction->commit();

                    return $this->redirect($shopBill->shopOrder->url);

                } catch (\Exception $e) {
                    $transaction->rollBack();
                    \Yii::error($e->getMessage(), YandexKassaPaySystem::class);
                    throw $e;
                }


            }
        }
        /*$client = new Client();
        $client->setAuth($yandexKassa->shop_id, $yandexKassa->shop_password);
        $client->getPaymentInfo()*/

        return "Ok";
    }
}