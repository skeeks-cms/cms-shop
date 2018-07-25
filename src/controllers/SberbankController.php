<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */

namespace skeeks\cms\shop\controllers;

use skeeks\cms\shop\models\ShopBill;
use skeeks\cms\shop\models\ShopOrder;
use yii\base\Exception;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\Controller;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class SberbankController extends Controller
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
    public function actionBillForm()
    {
        /**
         * @var $bill ShopBill
         */

        if (!$code = \Yii::$app->request->get('code')) {
            throw new Exception('Bill not found');
        }

        if (!$bill = ShopBill::find()->where(['code' => $code])->one()) {
            throw new Exception('Order not found');
        }

        if (isset($bill->external_data['formUrl'])) {
            return $this->redirect($bill->external_data['formUrl']);
        }

        $data = array(
            'userName' => $bill->shopPaySystem->handler->username,
            'password' => $bill->shopPaySystem->handler->password,
            'description' => "Заказ в магазине №{$bill->shopOrder->id}",
            'orderNumber' => urlencode($bill->id),
            'amount' => urlencode($bill->money->amount * 100), // передача данных в копейках/центах
            'returnUrl' => Url::toRoute(['/shop/sberbank/result', 'bill_id' => urlencode($bill->id)], true),
            //'failUrl' => Url::toRoute([$module->failUrl], true),
        );
        if ($bill->shopBuyer->email) {
            $data['jsonParams'] = '{"email":"' . $bill->shopBuyer->email . '"}';
        }
        /**
         * ЗАПРОС РЕГИСТРАЦИИ ОДНОСТАДИЙНОГО ПЛАТЕЖА В ПЛАТЕЖНОМ ШЛЮЗЕ
         *        register.do
         *
         * ПАРАМЕТРЫ
         *        userName            Логин магазина.
         *        password            Пароль магазина.
         *        orderNumber            Уникальный идентификатор заказа в магазине.
         *        amount                Сумма заказа.
         *        returnUrl            Адрес, на который надо перенаправить пользователя в случае успешной оплаты.
         *
         * ОТВЕТ
         *        В случае ошибки:
         *            errorCode        Код ошибки. Список возможных значений приведен в таблице ниже.
         *            errorMessage    Описание ошибки.
         *
         *        В случае успешной регистрации:
         *            orderId            Номер заказа в платежной системе. Уникален в пределах системы.
         *            formUrl            URL платежной формы, на который надо перенаправить браузер клиента.
         *
         *    Код ошибки        Описание
         *        0            Обработка запроса прошла без системных ошибок.
         *        1            Заказ с таким номером уже зарегистрирован в системе.
         *        3            Неизвестная (запрещенная) валюта.
         *        4            Отсутствует обязательный параметр запроса.
         *        5            Ошибка значения параметра запроса.
         *        7            Системная ошибка.
         */
        /*$response = $bill->shopPaySystem->handler->gateway('getOrderStatusExtended.do', $data);
                print_r($response);die;*/

        $response = $bill->shopPaySystem->handler->gateway('register.do', $data);

        /**
         * ЗАПРОС РЕГИСТРАЦИИ ДВУХСТАДИЙНОГО ПЛАТЕЖА В ПЛАТЕЖНОМ ШЛЮЗЕ
         *        registerPreAuth.do
         *
         * Параметры и ответ точно такие же, как и в предыдущем методе.
         * Необходимо вызывать либо register.do, либо registerPreAuth.do.
         */
//	$response = $module->gateway('registerPreAuth.do', $data);
        if (isset($response['errorCode'])) { // В случае ошибки вывести ее
            print_r($response);die;
            return $this->redirect(Url::toRoute(['/shop/sberbank/fail', 'response' => Json::encode($response)], true));
        } else { // В случае успеха перенаправить пользователя на плетжную форму
            $bill->external_data = $response;
            if (!$bill->save()) {

                //TODO: Add logs
                print_r($bill->errors);die;
            }

            return $this->redirect($bill->external_data['formUrl']);
        }

    }

    public function actionFail()
    {
        $orderId = \Yii::$app->request->get('OrderId');
        if (!$orderId) {
            throw new NotFoundHttpException('!!!');
        }

        if (!$shopOrder = ShopOrder::findOne($orderId)) {
            throw new NotFoundHttpException('!!!');
        }

        return $this->redirect($shopOrder->getPublicUrl(\Yii::$app->request->get()));
    }

    public function actionResult()
    {
        \Yii::info("Sberbank: " . print_r(\Yii::$app->request->post(), true) . print_r(\Yii::$app->request->get(), true), self::class);
        /*$orderId = \Yii::$app->request->get('OrderId');
        if (!$orderId) {
            throw new NotFoundHttpException('!!!');
        }

        if (!$shopOrder = ShopOrder::findOne($orderId)) {
            throw new NotFoundHttpException('!!!');
        }

        return $this->redirect($shopOrder->getPublicUrl(\Yii::$app->request->get()));*/
    }

    /*public function actionSuccess()
    {
        $orderId = \Yii::$app->request->get('OrderId');
        if (!$orderId) {
            throw new NotFoundHttpException('!!!');
        }

        if (!$shopOrder = ShopOrder::findOne($orderId)) {
            throw new NotFoundHttpException('!!!');
        }

        return $this->redirect($shopOrder->getPublicUrl(\Yii::$app->request->get()));
    }




    public function actionNotify()
    {
        \Yii::info("POST: " . Json::encode(\Yii::$app->request->post()), self::className());

        try {
            if (!\Yii::$app->request->post('OrderId')) {
                throw new Exception('Некорректны запрос от банка.');
            }

            /**
             * @var $shopOrder ShopOrder
            if (!$shopOrder = ShopOrder::findOne(\Yii::$app->request->post('OrderId'))) {
                throw new Exception('Заказ не найден в базе.');
            }

            if ($shopOrder->id != \Yii::$app->request->post('OrderId')) {
                throw new Exception('Не совпадает номер заказа.');
            }

            if ($shopOrder->money->getAmount() != \Yii::$app->request->post('Amount')) {
                throw new Exception('Не совпадает сумма заказа.');
            }

            if (\Yii::$app->request->post('Status') == "CONFIRMED") {
                \Yii::info("Успешный платеж", self::className());
                $shopOrder->processNotePayment();
            }

        } catch (\Exception $e) {
            \Yii::error($e->getMessage(), self::className());
        }

        $this->layout = false;
        return "OK";
    }*/
}