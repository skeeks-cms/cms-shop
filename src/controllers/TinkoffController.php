<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 21.09.2015
 */

namespace skeeks\cms\shop\controllers;

use skeeks\cms\base\Controller;
use skeeks\cms\shop\models\ShopBill;
use skeeks\cms\shop\models\ShopOrder;
use skeeks\cms\shop\models\ShopPayment;
use yii\base\Exception;
use yii\helpers\Json;
use yii\web\NotFoundHttpException;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class TinkoffController extends Controller
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
        if (!$key = \Yii::$app->request->get('key')) {
            throw new Exception('Order not found');
        }

        if (!$order = ShopOrder::find()->where(['key' => $key])->one()) {
            throw new Exception('Order not found');
        }

        return $this->render($this->action->id, [
            'model' => $order,
        ]);
    }


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
            throw new Exception('Bill not found');
        }

        return $this->render($this->action->id, [
            'model' => $bill,
        ]);

    }

    public function actionSuccess()
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

    public function actionNotify()
    {
        \Yii::info("actionNotify", self::class);

        $json = file_get_contents('php://input');
        \Yii::info("JSON: ".$json, self::class);

        try {

            if (!$json) {
                throw new Exception('От банка не пришли данные json.');
            }

            $data = Json::decode($json);

            if (!isset($data['OrderId']) && !$data['OrderId']) {
                throw new Exception('Некорректны запрос от банка нет order id.');
            }

            /**
             * @var $shopBill ShopBill
             */
            if (!$shopBill = ShopBill::findOne($data['OrderId'])) {
                throw new Exception('Заказ не найден в базе.');
            }

            if ($shopBill->id != $data['OrderId']) {
                throw new Exception('Не совпадает номер заказа.');
            }

            $amount = $shopBill->money->amount * $shopBill->money->currency->subUnit;
            if ($amount != $data['Amount']) {
                throw new Exception('Не совпадает сумма заказа.');
            }

            if ($data['Status'] == "CONFIRMED") {
                \Yii::info("Успешный платеж", self::class);

                $transaction = \Yii::$app->db->beginTransaction();

                try {

                    $payment = new ShopPayment();
                    $payment->shop_buyer_id = $shopBill->shop_buyer_id;
                    $payment->shop_pay_system_id = $shopBill->shop_pay_system_id;
                    $payment->shop_order_id = $shopBill->shop_order_id;
                    $payment->amount = $shopBill->amount;
                    $payment->currency_code = $shopBill->currency_code;
                    $payment->comment = "Оплата по счету №{$shopBill->id} от ".\Yii::$app->formatter->asDate($shopBill->created_at);
                    $payment->external_data = $data;

                    if (!$payment->save()) {
                        throw new Exception("Не сохранился платеж: ".print_r($payment->errors, true));
                    }

                    $shopBill->paid_at = time();
                    $shopBill->shop_payment_id = $payment->id;

                    if (!$shopBill->save()) {
                        throw new Exception("Не обновился счет: ".print_r($shopBill->errors, true));
                    }

                    $shopBill->shopOrder->paid_at = time();
                    $shopBill->shopOrder->save();


                    $transaction->commit();
                } catch (\Exception $e) {
                    $transaction->rollBack();
                    \Yii::error($e->getMessage(), self::class);
                    return $e->getMessage();
                }

            }

        } catch (\Exception $e) {
            \Yii::error($e->getMessage(), self::class);
            return $e->getMessage();
        }

        $this->layout = false;
        return "OK";
    }
}