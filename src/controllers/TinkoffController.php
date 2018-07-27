<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 21.09.2015
 */

namespace skeeks\cms\shop\controllers;

use skeeks\cms\shop\models\ShopOrder;
use yii\base\Exception;
use yii\helpers\Json;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * Class TinkoffController
 * @package skeeks\cms\shop\controllers
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
        \Yii::info("POST: ".Json::encode(\Yii::$app->request->post()), self::class);

        try {
            if (!\Yii::$app->request->post('OrderId')) {
                throw new Exception('Некорректны запрос от банка.');
            }

            /**
             * @var $shopOrder ShopOrder
             */
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
                \Yii::info("Успешный платеж", self::class);
                $shopOrder->processNotePayment();
            }

        } catch (\Exception $e) {
            \Yii::error($e->getMessage(), self::class);
        }

        $this->layout = false;
        return "OK";
    }
}