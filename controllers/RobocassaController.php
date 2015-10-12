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
use yii\base\Exception;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\web\BadRequestHttpException;

/**
 * Class RobocassaController
 * @package skeeks\cms\shop\controllers
 */
class RobocassaController extends Controller
{
    public function actionInvoice()
    {
        $model = new Invoice();
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            /** @var \robokassa\Merchant $merchant */
            $merchant = Yii::$app->get('robokassa');
            return $merchant->payment($model->sum, $model->id, 'Пополнение счета', null, Yii::$app->user->identity->email);
        } else {
            return $this->render('invoice', [
                'model' => $model,
            ]);
        }
    }

    /**
     * @inheritdoc
     */
    public function actions()
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
    }

    /**
     * Callback.
     * @param \robokassa\Merchant $merchant merchant.
     * @param integer $nInvId invoice ID.
     * @param float $nOutSum sum.
     * @param array $shp user attributes.
     */
    public function successCallback($merchant, $nInvId, $nOutSum, $shp)
    {
        //$this->loadModel($nInvId)->updateAttributes(['status' => Invoice::STATUS_ACCEPTED]);
        $order = $this->loadModel($nInvId);
        $order->ps_status = "STATUS_ACCEPTED";
        $order->save();
        return $this->goBack();
    }
    public function resultCallback($merchant, $nInvId, $nOutSum, $shp)
    {
        $order = $this->loadModel($nInvId);
        $order->ps_status = "STATUS_SUCCESS";
        $order->save();
        //$this->loadModel($nInvId)->updateAttributes(['status' => Invoice::STATUS_SUCCESS]);
        return 'Ok';
    }

    public function failCallback($merchant, $nInvId, $nOutSum, $shp)
    {
        $order = $this->loadModel($nInvId);
        $order->ps_status = "STATUS_FAIL";
        $order->save();
        //$this->loadModel($nInvId)->updateAttributes(['status' => Invoice::STATUS_SUCCESS]);
        return 'Ok';

        /*$model = $this->loadModel($nInvId);
        if ($model->status == Invoice::STATUS_PENDING) {
            $model->updateAttributes(['status' => Invoice::STATUS_FAIL]);
            return 'Ok';
        } else {
            return 'Status has not changed';
        }*/
    }

    /**
     * @param integer $id
     * @return ShopOrder
     * @throws \yii\web\BadRequestHttpException
     */
    protected function loadModel($id)
{
        $model = ShopOrder::findOne($id);
        if ($model === null) {
            throw new BadRequestHttpException;
        }
        return $model;
    }

}