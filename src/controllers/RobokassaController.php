<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 21.09.2015
 */
namespace skeeks\cms\shop\controllers;

use skeeks\cms\base\Controller;
use skeeks\cms\components\Cms;
use skeeks\cms\filters\CmsAccessControl;
use skeeks\cms\helpers\RequestResponse;
use skeeks\cms\shop\models\ShopBasket;
use skeeks\cms\shop\models\ShopBuyer;
use skeeks\cms\shop\models\ShopFuser;
use skeeks\cms\shop\models\ShopOrder;
use skeeks\cms\shop\models\ShopPersonType;
use skeeks\cms\shop\models\ShopPersonTypeProperty;
use skeeks\cms\shop\models\ShopProduct;
use skeeks\cms\shop\paySystems\robokassa\Merchant;
use skeeks\cms\shop\paySystems\RobokassaPaySystem;
use yii\base\Exception;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
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

    public function actionSuccess()
    {
        RobokassaPaySystem::logInfo('success request');

        if (!isset($_REQUEST['OutSum'], $_REQUEST['InvId'], $_REQUEST['SignatureValue']))
        {
            RobokassaPaySystem::logError('Not found params');
            throw new BadRequestHttpException('Not found params');
        }

        $order = $this->loadModel($_REQUEST['InvId']);
        $merchant = $this->getMerchant($order);
        $shp = $this->getShp();

        if ($merchant->checkSignature($_REQUEST['SignatureValue'], $_REQUEST['OutSum'], $_REQUEST['InvId'], $merchant->sMerchantPass1, $shp)) {

            $order->ps_status = "STATUS_ACCEPTED";
            $order->save();
            return $this->redirect(Url::to(['/shop/order/view', 'id' => $order->id]));
        }

        RobokassaPaySystem::logError('bad signature');
        throw new BadRequestHttpException('bad signature');
    }


    public function actionResult()
    {
        RobokassaPaySystem::logInfo('result request');

        if (!isset($_REQUEST['OutSum'], $_REQUEST['InvId'], $_REQUEST['SignatureValue']))
        {
            RobokassaPaySystem::logError('Not found params');
            throw new BadRequestHttpException('Not found params');
        }

        $order = $this->loadModel($_REQUEST['InvId']);
        $merchant = $this->getMerchant($order);
        $shp = $this->getShp();

        if ($merchant->checkSignature($_REQUEST['SignatureValue'], $_REQUEST['OutSum'], $_REQUEST['InvId'], $merchant->sMerchantPass2, $shp)) {

            RobokassaPaySystem::logInfo('result signature OK');
            if ($order->payed != "Y")
            {
                $order->processNotePayment();
            }

            $order->ps_status = "STATUS_SUCCESS";
            $order->payed = "Y";
            $order->save();

            return 'Ok';
        }

        RobokassaPaySystem::logError('bad signature');

        throw new BadRequestHttpException;
    }

    public function actionFail()
    {
        RobokassaPaySystem::logInfo('fail request');

        if (!isset($_REQUEST['OutSum'], $_REQUEST['InvId']))
        {
            RobokassaPaySystem::logError('Not found params');
            throw new BadRequestHttpException;
        }

        $order = $this->loadModel($_REQUEST['InvId']);
        $merchant = $this->getMerchant($order);
        $shp = $this->getShp();

        $order->ps_status = "STATUS_FAIL";
        $order->save();
        return $this->redirect(Url::to(['/shop/order/view', 'id' => $order->id]));
        //$this->loadModel($nInvId)->updateAttributes(['status' => Invoice::STATUS_SUCCESS]);
        return 'Ok';
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
     * @param integer $nInvId invoice ID.
     * @param float $nOutSum sum.
     * @param array $shp user attributes.
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
     * Загрузка заказа
     *
     * @param integer $id
     * @return ShopOrder
     * @throws \yii\web\BadRequestHttpException
     */
    protected function loadModel($id)
    {
        $model = ShopOrder::findOne($id);
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
        if (!$paySystemHandler || !$paySystemHandler instanceof RobokassaPaySystem)
        {
            RobokassaPaySystem::logError('Not found pay system');
            throw new BadRequestHttpException('Not found pay system');
        }

        $merchant = $paySystemHandler->getMerchant();

        if (!$merchant instanceof Merchant)
        {
            RobokassaPaySystem::logError('Not found merchant');
            throw new BadRequestHttpException('Not found merchant');
        }

        return $merchant;
    }

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

}