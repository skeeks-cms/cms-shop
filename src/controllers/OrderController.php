<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 21.09.2015
 */

namespace skeeks\cms\shop\controllers;

use skeeks\cms\base\Controller;
use skeeks\cms\filters\CmsAccessControl;
use skeeks\cms\shop\models\ShopBill;
use skeeks\cms\shop\models\ShopOrder;
use yii\base\UserException;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

/**
 * Class CartController
 * @package skeeks\cms\shop\controllers
 */
class OrderController extends Controller
{
    public $defaultAction = 'view';

    /**
     * @return array
     */
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [

            'accessToView' => [
                'class' => CmsAccessControl::class,
                'only'  => ['view'],
                'rules' => [
                    // deny all POST request
                    //
                    [
                        'allow'         => true,
                        'matchCallback' => function ($rule, $action) {
                            $id = \Yii::$app->request->get('id');
                            $shopOrder = ShopOrder::findOne($id);

                            if (\Yii::$app->user->isGuest) {
                                return false;
                            }

                            if ($shopOrder->user_id == \Yii::$app->user->identity->id) {
                                return true;
                            }

                            return false;
                        },
                    ],
                ],
            ],

            'accessToList' => [
                'class' => CmsAccessControl::class,
                'only'  => ['list'],
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ]);
    }

    public function beforeAction($action)
    {
        if ($action->id == 'view') {
            $this->enableCsrfValidation = false;
        }

        return parent::beforeAction($action);
    }

    /**
     * @return string
     */
    public function actionList()
    {
        $this->view->title = \Yii::t('skeeks/shop/app', 'My orders').' | '.\Yii::t('skeeks/shop/app', 'Shop');

        return $this->render($this->action->id);
    }

    /**
     * @return string
     */
    public function actionView()
    {
        $this->view->title = \Yii::t('skeeks/shop/app', 'Order').' | '.\Yii::t('skeeks/shop/app', 'Shop');

        return $this->render($this->action->id, [
            'model' => ShopOrder::findOne(\Yii::$app->request->get('id')),
        ]);
    }

    /**
     * @return string
     */
    public function actionFinish()
    {
        $this->view->title = \Yii::t('skeeks/shop/app', 'Order').' | '.\Yii::t('skeeks/shop/app', 'Shop');

        return $this->render($this->action->id, [
            'model' => ShopOrder::find()->andWhere(['code' => \Yii::$app->request->get('code')])->one(),
        ]);
    }


    /**
     * Оплатить
     *
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionPay()
    {
        /**
         * @var $shopOrder ShopOrder
         */
        if (\Yii::$app->request->get('code')) {
            $shopOrder = ShopOrder::find()->where(['code' => \Yii::$app->request->get('code')])->one();
        } else {
            $shopOrder = ShopOrder::findOne(\Yii::$app->request->get('id'));
        }

        if (!$shopOrder) {
            throw new NotFoundHttpException;
        }

        $shopBill = new ShopBill();
        $shopBill->shop_order_id = $shopOrder->id;
        $shopBill->shop_buyer_id = $shopOrder->buyer_id;
        $shopBill->shop_pay_system_id = $shopOrder->pay_system_id;
        $shopBill->amount = $shopOrder->price;
        $shopBill->currency_code = $shopOrder->currency_code;
        $shopBill->description = "Оплата по заказу №".$shopOrder->id;
        if (!$shopBill->save()) {
            throw new UserException('Не создался счет: '.print_r($shopBill->errors, true));
        }

        return $shopBill->shopPaySystem->paySystemHandler->actionPaymentResponse($shopBill);

    }

    public function actionPayPal()
    {
        return $this->render($this->action->id, [
            'model' => ShopOrder::findOne(\Yii::$app->request->get('id')),
        ]);
    }


    /**
     * @deprecated
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionFinishPay()
    {
        /**
         * @var $shopOrder ShopOrder
         */
        if (\Yii::$app->request->get('code')) {
            $shopOrder = ShopOrder::find()->where(['code' => \Yii::$app->request->get('code')])->one();
        } else {
            $shopOrder = ShopOrder::findOne(\Yii::$app->request->get('id'));
        }

        if (!$shopOrder) {
            throw new NotFoundHttpException;
        }

        return $shopOrder->paySystem->paySystemHandler->paymentResponse($shopOrder);

    }

}