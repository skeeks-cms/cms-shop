<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

namespace skeeks\cms\shop\controllers;

use skeeks\cms\helpers\RequestResponse;
use skeeks\cms\helpers\UrlHelper;
use skeeks\cms\models\CmsAgent;
use skeeks\cms\models\CmsUser;
use skeeks\cms\modules\admin\actions\AdminAction;
use skeeks\cms\modules\admin\controllers\AdminModelEditorController;
use skeeks\cms\modules\admin\traits\AdminModelEditorStandartControllerTrait;
use skeeks\cms\shop\models\ShopBasket;
use skeeks\cms\shop\models\ShopFuser;
use skeeks\cms\shop\models\ShopOrder;
use skeeks\cms\shop\models\ShopProduct;
use yii\base\Exception;
use yii\helpers\ArrayHelper;

/**
 * Class AdminExtraController
 * @package skeeks\cms\shop\controllers
 */
class AdminOrderController extends AdminModelEditorController
{
    use AdminModelEditorStandartControllerTrait;

    public function init()
    {
        $this->name = \Yii::t('skeeks/shop/app', 'Orders');
        $this->modelShowAttribute = "id";
        $this->modelClassName = ShopOrder::class;

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        $view = $this->view;

        return ArrayHelper::merge(parent::actions(),
            [

                'create' =>
                    [
                        'isVisible' => false,
                    ],

                'create-order' =>
                    [
                        'class'    => AdminAction::class,
                        'name'     => \Yii::t('skeeks/shop/app', 'Place your order'),
                        "icon"     => "glyphicon glyphicon-plus",
                        "callback" => [$this, 'createOrder'],
                    ],


                /*"view" =>
                [
                    'class'         => AdminOneModelEditAction::class,
                    "name"         => \Yii::t('skeeks/shop/app',"Информация"),
                    "icon"          => "glyphicon glyphicon-eye-open",
                    "priority"      => 5,
                    "callback"      => [$this, 'view'],
                ],*/

            ]
        );
    }

    public function view()
    {
        return $this->render($this->action->id, [
            'model' => $this->model,
        ]);
    }

    /**
     * @return array
     */
    public function actionPayValidate()
    {
        $rr = new RequestResponse();
        return $rr->ajaxValidateForm($this->model);
    }

    /**
     * @return array
     */
    public function actionValidate()
    {
        $rr = new RequestResponse();
        return $rr->ajaxValidateForm($this->model);
    }

    /**
     * @return array
     */
    public function actionPay()
    {
        $rr = new RequestResponse();

        /**
         * @var $model ShopOrder;
         */
        $model = $this->model;
        if ($model->load(\Yii::$app->request->post()) && $model->save()) {
            $rr->success = true;

            if ($model->payed != "Y") {
                $model->processNotePayment();
            } else {
                if (\Yii::$app->request->post('payment-close') == 1) {
                    $model->processCloseNotePayment();
                }
            }

            return $rr;
        }
    }

    /**
     * @return array
     */
    public function actionSave()
    {
        $rr = new RequestResponse();

        /**
         * @var $model ShopOrder;
         */
        $model = $this->model;
        if ($model->load(\Yii::$app->request->post()) && $model->save()) {
            $rr->success = true;
            return $rr;
        }
    }


    /**
     * @return array
     */
    public function actionCreateOrderFuserSave()
    {
        $rr = new RequestResponse();

        $model = null;
        if ($id = \Yii::$app->request->get('shopFuserId')) {
            $model = ShopFuser::findOne($id);
        }

        if ($model->load(\Yii::$app->request->post()) && $model->save()) {
            $rr->success = true;
            return $rr;
        } else {
            $rr->success = false;
            print_r($model->getErrors());
            die;
            $rr->message = implode(',', $model->getFirstError());
            return $rr;
        }
    }


    /**
     * @return array
     */
    public function actionCreateOrderAddProduct()
    {
        $rr = new RequestResponse();

        $shopFuser = null;
        if ($id = \Yii::$app->request->get('shopFuserId')) {
            $shopFuser = ShopFuser::findOne($id);
        }


        if ($rr->isRequestAjaxPost()) {
            $product_id = \Yii::$app->request->post('product_id');
            $quantity = \Yii::$app->request->post('quantity');

            /**
             * @var ShopProduct $product
             */
            $product = ShopProduct::find()->where(['id' => $product_id])->one();

            if (!$product) {
                $rr->message = \Yii::t('skeeks/shop/app', 'This product is not found, it may be removed.');
                return (array)$rr;
            }

            $shopBasket = ShopBasket::find()->where([
                'fuser_id'   => $shopFuser->id,
                'product_id' => $product_id,
                'order_id'   => null,
            ])->one();

            if (!$shopBasket) {
                $shopBasket = new ShopBasket([
                    'fuser_id'   => $shopFuser->id,
                    'product_id' => $product->id,
                    'quantity'   => 0,
                ]);
            }

            $shopBasket->quantity = $shopBasket->quantity + $quantity;


            if (!$shopBasket->recalculate()->save()) {
                $rr->success = false;
                $rr->message = \Yii::t('skeeks/shop/app', 'Failed to add item to cart');
            } else {
                $rr->success = true;
                $rr->message = \Yii::t('skeeks/shop/app', 'Item added to cart');
            }

            $shopFuser->link('site', \Yii::$app->cms->site);
            $rr->data = $shopFuser->toArray([], $shopFuser->extraFields());
            return (array)$rr;
        } else {
            return $this->goBack();
        }
    }

    /**
     * @return array
     */
    public function actionUpdateOrderAddProduct()
    {
        $rr = new RequestResponse();

        if ($this->model) {
            $model = $this->model;
        }


        if ($rr->isRequestAjaxPost()) {
            $product_id = \Yii::$app->request->post('product_id');
            $quantity = \Yii::$app->request->post('quantity');

            /**
             * @var ShopProduct $product
             */
            $product = ShopProduct::find()->where(['id' => $product_id])->one();

            if (!$product) {
                $rr->message = \Yii::t('skeeks/shop/app', 'This product is not found, it may be removed.');
                return (array)$rr;
            }

            $shopBasket = ShopBasket::find()->where([
                'order_id'   => $model->id,
                'product_id' => $product_id,
                'fuser_id'   => null,
            ])->one();

            if (!$shopBasket) {
                $shopBasket = new ShopBasket([
                    'order_id'   => $model->id,
                    'product_id' => $product->id,
                    'quantity'   => 0,
                ]);
            }

            $shopBasket->quantity = $shopBasket->quantity + $quantity;


            if (!$shopBasket->recalculate()->save()) {
                $rr->success = false;
                $rr->message = \Yii::t('skeeks/shop/app', 'Failed to add item to cart');
            } else {
                $rr->success = true;
                $rr->message = \Yii::t('skeeks/shop/app', 'Item added to cart');
            }

            $rr->data = $model->toArray([], $model->extraFields());
            return (array)$rr;
        } else {
            return $this->goBack();
        }
    }


    public function createOrder()
    {
        $cmsUser = null;
        if ($userId = \Yii::$app->request->get('cmsUserId')) {
            $cmsUser = CmsUser::findOne($userId);
        }

        if ($cmsUser) {
            /**
             * @var $shopFuser ShopFuser
             */
            $shopFuser = ShopFuser::getInstanceByUser($cmsUser);
            $model = $shopFuser;

            $rr = new RequestResponse();

            if (\Yii::$app->request->isAjax && !\Yii::$app->request->isPjax) {
                $model->scenario = ShopFuser::SCENARIO_CREATE_ORDER;
                return $rr->ajaxValidateForm($model);
            }

            if ($rr->isRequestPjaxPost()) {
                try {
                    if ($model->load(\Yii::$app->request->post()) && $model->save()) {

                        $model->scenario = ShopFuser::SCENARIO_CREATE_ORDER;

                        if ($model->validate()) {
                            $order = ShopOrder::createOrderByFuser($model);

                            if (!$order->isNewRecord) {
                                \Yii::$app->getSession()->setFlash('success',
                                    \Yii::t('skeeks/shop/app', 'The order #{order_id} created successfully',
                                        ['order_id' => $order->id])
                                );

                                if (\Yii::$app->request->post('submit-btn') == 'apply') {
                                    return $this->redirect(
                                        UrlHelper::constructCurrent()->setCurrentRef()->enableAdmin()->setRoute($this->modelDefaultAction)->normalizeCurrentRoute()
                                            ->addData([$this->requestPkParamName => $order->id])
                                            ->toString()
                                    );
                                } else {
                                    return $this->redirect(
                                        $this->url
                                    );
                                }


                            } else {
                                throw new Exception(\Yii::t('skeeks/shop/app',
                                        'Incorrect data of the new order').": ".array_shift($order->getFirstErrors()));
                            }

                        } else {
                            throw new Exception(\Yii::t('skeeks/shop/app',
                                    'Not enogh data for ordering').": ".array_shift($model->getFirstErrors()));
                        }
                    } else {
                        throw new Exception(\Yii::t('skeeks/shop/app', 'Could not save'));
                    }
                } catch (\Exception $e) {
                    \Yii::$app->getSession()->setFlash('error', $e->getMessage());
                }

            }

            return $this->render($this->action->id, [
                'cmsUser'   => $cmsUser,
                'shopFuser' => $model,
            ]);
        } else {
            return $this->render($this->action->id."-select-user");
        }
    }

}
