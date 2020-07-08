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
use skeeks\cms\shop\paySystems\robokassa\Merchant;
use skeeks\cms\shop\paySystems\RobokassaPaySystem;
use yii\helpers\Url;
use yii\web\BadRequestHttpException;

/**
 * Class RobocassaController
 * @package skeeks\cms\shop\controllers
 */
class RobokassaController extends Controller
{
    /**
     * @inheritdoc
     */
    public $enableCsrfValidation = false;

    /*public function actionInvoice()
    {
        $model = new Invoice();
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            /** @var \robokassa\Merchant $merchant
            $merchant = Yii::$app->get('robokassa');
            return $merchant->payment($model->sum, $model->id,  \Yii::t('skeeks/shop/app', 'Refill'), null, Yii::$app->user->identity->email);
        } else {
            return $this->render('invoice', [
                'model' => $model,
            ]);
        }
    }*/

    /**
     * Используется в случае успешного проведения платежа.
     *
     * @return \yii\web\Response
     * @throws BadRequestHttpException
     */
    public function actionSuccess()
    {
        RobokassaPaySystem::logInfo('success request');

        if (!isset($_REQUEST['OutSum'], $_REQUEST['InvId'], $_REQUEST['SignatureValue'])) {
            RobokassaPaySystem::logError('Not found params');
            throw new BadRequestHttpException('Not found params');
        }

        $bill = $this->loadModel($_REQUEST['InvId']);
        $merchant = $this->getMerchant($bill->shopOrder);
        $shp = $this->getShp();

        if ($merchant->checkSignature($_REQUEST['SignatureValue'], $_REQUEST['OutSum'], $_REQUEST['InvId'],
            $merchant->sMerchantPass1, $shp)
        ) {

            /*$order->ps_status = "STATUS_ACCEPTED";
            $order->save();*/
            return $this->redirect($bill->shopOrder->url);
            //return $this->redirect(Url::to(['/shop/order/view', 'id' => $order->id]));
        }

        RobokassaPaySystem::logError('bad signature');
        throw new BadRequestHttpException('bad signature');
    }
    /**
     * Загрузка заказа
     *
     * @param integer $id
     * @return ShopBill
     * @throws \yii\web\BadRequestHttpException
     */
    protected function loadModel($id)
    {
        $model = ShopBill::findOne($id);
        if ($model === null) {
            throw new BadRequestHttpException("Order: {$id} not found");
        }
        return $model;
    }


    /**
     * @param ShopOrder $order
     * @return \skeeks\cms\shop\paySystems\robokassa\Merchant
     * @throws BadRequestHttpException
     */
    protected function getMerchant(ShopOrder $order)
    {
        /** @var \skeeks\cms\shop\paySystems\robokassa\Merchant $merchant */
        $paySystemHandler = $order->paySystem->paySystemHandler;
        if (!$paySystemHandler || !$paySystemHandler instanceof RobokassaPaySystem) {
            RobokassaPaySystem::logError('Not found pay system');
            throw new BadRequestHttpException('Not found pay system');
        }

        $merchant = $paySystemHandler->getMerchant();

        if (!$merchant instanceof Merchant) {
            RobokassaPaySystem::logError('Not found merchant');
            throw new BadRequestHttpException('Not found merchant');
        }

        return $merchant;
    }

    /**
     * @inheritdoc
     */
    /*public function actions()
    {
        return [
            'result' => [
                'class' => '\robokassa\ResultAction',
                'callback' => [$this, 'resultCallback'],
            ],
            'success' => [
                'class' => '\robokassa\SuccessAction',
                'callback' => [$this, 'successCallback'],
            ],
            'fail' => [
                'class' => '\robokassa\FailAction',
                'callback' => [$this, 'failCallback'],
            ],
        ];
    }*/

    /**
     * Callback.
     * @param \robokassa\Merchant $merchant merchant.
     * @param integer             $nInvId invoice ID.
     * @param float               $nOutSum sum.
     * @param array               $shp user attributes.
     */
    /*public function successCallback($merchant, $nInvId, $nOutSum, $shp)
    {
        $this->loadModel($nInvId)->updateAttributes(['status' => Invoice::STATUS_ACCEPTED]);
        $order = $this->loadModel($nInvId);
        $order->ps_status = "STATUS_ACCEPTED";
        $order->save();
        return $this->goBack();
    }*/
    /**
     * @return array
     */
    public function getShp()
    {
        $shp = [];
        foreach ($_REQUEST as $key => $param) {
            if (strpos(strtolower($key), 'shp') === 0) {
                $shp[$key] = $param;
            }
        }

        return $shp;
    }

    /**
     * Используется для оповещения о платеже
     *
     * @return string
     * @throws BadRequestHttpException
     */
    public function actionResult()
    {
        RobokassaPaySystem::logInfo('result request');

        if (!isset($_REQUEST['OutSum'], $_REQUEST['InvId'], $_REQUEST['SignatureValue'])) {
            RobokassaPaySystem::logError('Not found params');
            throw new BadRequestHttpException('Not found params');
        }

        $bill = $this->loadModel($_REQUEST['InvId']);
        $merchant = $this->getMerchant($bill->shopOrder);
        $shp = $this->getShp();

        if ($merchant->checkSignature($_REQUEST['SignatureValue'], $_REQUEST['OutSum'], $_REQUEST['InvId'],
            $merchant->sMerchantPass2, $shp)
        ) {

            RobokassaPaySystem::logInfo('result signature OK');


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

                $bill->isNotifyUpdate = false;
                $bill->paid_at = time();
                $bill->shop_payment_id = $payment->id;

                if (!$bill->save()) {
                    throw new Exception("Не обновился счет: ".print_r($payment->errors, true));
                }

                $bill->shopOrder->paid_at = time();
                $bill->shopOrder->save();

                $transaction->commit();

                //return $this->redirect($bill->shopOrder->url);

            } catch (\Exception $e) {
                $transaction->rollBack();
                \Yii::error($e->getMessage(), self::class);
                throw $e;
            }

            return "OK{$bill->id}\n";
        }

        RobokassaPaySystem::logError('bad signature');

        throw new BadRequestHttpException;
    }


    /**
     * @return string|\yii\web\Response
     * @throws BadRequestHttpException
     */
    public function actionFail()
    {
        RobokassaPaySystem::logInfo('fail request');

        if (!isset($_REQUEST['OutSum'], $_REQUEST['InvId'])) {
            RobokassaPaySystem::logError('Not found params');
            throw new BadRequestHttpException;
        }

        $bill = $this->loadModel($_REQUEST['InvId']);
        $merchant = $this->getMerchant($bill->shopOrder);
        $shp = $this->getShp();

        /*$order->ps_status = "STATUS_FAIL";
        $order->save();*/
        return $this->redirect($bill->shopOrder->url);
        //$this->loadModel($nInvId)->updateAttributes(['status' => Invoice::STATUS_SUCCESS]);
        return 'Ok';
    }

}