<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

namespace skeeks\cms\shop\controllers;

use skeeks\cms\backend\BackendController;
use skeeks\cms\components\Cms;
use skeeks\cms\helpers\RequestResponse;
use skeeks\cms\models\CmsAgent;
use skeeks\cms\models\CmsUser;
use skeeks\cms\shop\models\ShopCasheboxShift;
use skeeks\cms\shop\models\ShopCmsContentElement;
use skeeks\cms\shop\models\ShopOrder;
use skeeks\cms\shop\models\ShopOrderItem;
use skeeks\cms\shop\models\ShopOrderStatus;
use skeeks\cms\shop\models\ShopPayment;
use skeeks\cms\shop\models\ShopPaySystem;
use skeeks\cms\shop\models\ShopProduct;
use yii\base\Exception;

/**
 * @property ShopCasheboxShift $shift текущая смена;
 * @property ShopOrder         $order текущий заказ;
 * @property ShopCasheboxShift $orderSessionName название сессии для текущего магазина;
 *
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class CashierController extends BackendController
{
    /**
     *
     */
    const ORDER_SESSION = "ORDER_SESSION";

    /**
     * @var null
     */
    protected $_shopShift = null;

    /**
     * @var null
     */
    protected $_shopOrder = null;
    /**
     * @return void
     */
    public function init()
    {
        $this->name = \Yii::t('skeeks/shop/app', 'Товары');

        $this->permissionName = Cms::UPA_PERMISSION;

        //Эта панелька мешается
        if ($debug = \Yii::$app->getModule("debug")) {
            $debug->panels = [];
        }
        parent::init();
    }

    /**
     * @return string
     */
    public function getOrderSessionName()
    {
        return self::ORDER_SESSION."_".\Yii::$app->shop->backendShopStore->id;
    }


    /**
     * @return ShopOrder
     */
    public function getOrder()
    {
        if ($this->_shopOrder === null) {
            if ($order_id = \Yii::$app->getSession()->get($this->orderSessionName)) {

                $order = ShopOrder::find()->cmsSite()->andWhere(['id' => $order_id])->one();

                if (!$order || $order->is_created) {
                    //Обновить сессию если заказ уже создан или
                    $order = new ShopOrder();
                    $order->is_created = false;
                    $order->shop_store_id = \Yii::$app->shop->backendShopStore->id;
                    $order->validate();
                    if (!$order->save(false)) {
                        throw new Exception(print_r($order->errors, true));
                    }
                    \Yii::$app->getSession()->set($this->orderSessionName, $order->id);
                }

                $this->_shopOrder = $order;

            } else {
                $order = new ShopOrder();
                $order->is_created = false;
                $order->shop_store_id = \Yii::$app->shop->backendShopStore->id;
                $order->validate();
                if (!$order->save(false)) {
                    throw new Exception(print_r($order->errors, true));
                }
                \Yii::$app->getSession()->set($this->orderSessionName, $order->id);
            }
        }

        return $this->_shopOrder;

    }


    /**
     * @return ShopCasheboxShift
     */
    public function getShift()
    {
        if ($this->_shopShift === null) {

            $this->_shopShift = ShopCasheboxShift::find()
                ->notClosed() //не закрытая смена
                ->innerJoinWith('shopCashebox as shopCashebox')
                ->andWhere(['shopCashebox.shop_store_id' => \Yii::$app->shop->backendShopStore->id]) //только кассы для текущего магазина
                ->createdBy(\Yii::$app->user->id) //Смена открытая текущим пользователем
                ->one();
        }

        return $this->_shopShift;


        //\Yii::$app->shop->backendShopStore->cas
        /*ShopCasheboxShift::find()
            ->innerJoinWith('shopCashebox as shopCashebox')
            ->andWhere(['shopCashebox.shop_store_id' => \Yii::$app->shop->backendShopStore->id]) //только кассы для текущего магазина
            ->createdBy(\Yii::$app->user->id) //Смена открытая текущим пользователем
            ->closed_at(\Yii::$app->user->id) //Смена открытая текущим пользователем
        ;*/
    }

    /**
     * @return string
     */
    public function actionIndex()
    {
        return $this->render($this->action->id);
    }

    /**
     * Это бэкенд для поиска товаров
     *
     * @return RequestResponse
     */
    public function actionProducts()
    {
        $rr = new RequestResponse();

        if ($rr->isRequestAjaxPost()) {
            \Yii::$app->shop->backendShopStore;
            \Yii::$app->skeeks->site;
            $q = \Yii::$app->request->post("q");

            $query = ShopCmsContentElement::find()
                ->from(['cce' => ShopCmsContentElement::tableName()])
                ->innerJoinWith("shopProduct")
                ->limit(40);

            if ($q) {
                $query->andWhere([
                    'or',
                    ['like', 'cce.name', $q],
                    ['=', 'cce.id', $q],
                ]);
            }

            if ($query->count()) {
                $content = '';
                foreach ($query->each(10) as $element) {
                    $content .= $this->renderPartial('_product', [
                        'model' => $element,
                    ]);
                }
                $data['content'] = $content;
            } else {
                $content = "<div class='sx-not-found-products'><h1>Товар не найден</h1></div>";
                $data['content'] = $content;
            }


            $rr->success = true;
            $rr->data = $data;

        }

        return $rr;
    }


    /**
     * Это бэкенд для поиска товаров
     *
     * @return RequestResponse
     */
    public function actionUsers()
    {
        $rr = new RequestResponse();

        if ($rr->isRequestAjaxPost()) {
            \Yii::$app->shop->backendShopStore;
            \Yii::$app->skeeks->site;
            $q = \Yii::$app->request->post("q");

            $query = CmsUser::find()
                ->limit(40);

            if ($q) {
                $query->search($q);
            }

            if ($query->count()) {
                $content = '';
                foreach ($query->each(10) as $element) {
                    $content .= $this->renderPartial('_user', [
                        'model' => $element,
                    ]);
                }
                $data['content'] = $content;
            } else {
                $content = "<div class='sx-not-found-users'><h1>Клиент не найден</h1></div>";
                $data['content'] = $content;
            }


            $rr->success = true;
            $rr->data = $data;

        }

        return $rr;
    }


    /**
     * Создание смены
     * @return RequestResponse
     */
    public function actionCreateShift()
    {
        $rr = new RequestResponse();

        if ($rr->isRequestAjaxPost()) {
            \Yii::$app->shop->backendShopStore;
            \Yii::$app->skeeks->site;

            try {
                if ($this->shift) {
                    throw new \yii\base\Exception("Уже открыта другая смена");
                }

                $shopCasheboxShift = new ShopCasheboxShift();
                if (!$shopCasheboxShift->load(\Yii::$app->request->post()) || !$shopCasheboxShift->validate()) {
                    $message = "Проверьте корректность данных";

                    $errors = $shopCasheboxShift->getFirstErrors();
                    if ($errors) {
                        $error = array_shift($errors);
                        $message = $error;
                    }

                    throw new \yii\base\Exception($message);
                }

                if (!$shopCasheboxShift->save()) {
                    if ($shopCasheboxShift->getFirstErrors()) {
                        $errors = $shopCasheboxShift->getFirstErrors();
                        $error = array_shift($errors);
                        throw new \yii\base\Exception($error);
                    }
                }

                $rr->success = true;
                $rr->message = "Смена открыта";

            } catch (\Exception $exception) {
                $rr->success = false;
                $rr->message = $exception->getMessage();
            }


        }

        return $rr;
    }

    /**
     * Закрытие смены
     * @return RequestResponse
     */
    public function actionCloseShift()
    {
        $rr = new RequestResponse();

        if ($rr->isRequestAjaxPost()) {
            \Yii::$app->shop->backendShopStore;
            \Yii::$app->skeeks->site;

            try {
                if (!$this->shift) {
                    throw new \yii\base\Exception("Смена уже закрыта");
                }

                $shift = $this->shift;
                $shift->closed_at = time();

                if (!$shift->save()) {
                    if ($shift->getFirstErrors()) {
                        $errors = $shift->getFirstErrors();
                        $error = array_shift($errors);
                        throw new \yii\base\Exception($error);
                    }
                }

                $rr->success = true;
                $rr->message = "Смена закрыта";

            } catch (\Exception $exception) {
                $rr->success = false;
                $rr->message = $exception->getMessage();
            }


        }

        return $rr;
    }


    /**
     * Добавить товар
     *
     * @return array|\yii\web\Response
     */
    public function actionAddProduct()
    {
        $rr = new RequestResponse();

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

            if ($product->isOffersProduct) {
                $rr->message = \Yii::t('skeeks/shop/app', 'Этот товар является общим, и не может быть добавлен в корзину.');
                return (array)$rr;
            }

            if ($product->measure_ratio > 1) {
                if ($quantity % $product->measure_ratio != 0) {
                    $quantity = $product->measure_ratio;
                }
            }


            $shopBasket = ShopOrderItem::find()->where([
                'shop_order_id'   => $this->order->id,
                'shop_product_id' => $product_id,
            ])->one();

            if (!$shopBasket) {
                $shopBasket = new ShopOrderItem([
                    'shop_order_id'   => $this->order->id,
                    'shop_product_id' => $product->id,
                    'quantity'        => 0,
                ]);
            }


            $shopBasket->quantity = $shopBasket->quantity + $quantity;
            if ($product->measure_ratio_min > $shopBasket->quantity) {
                $shopBasket->quantity = $product->measure_ratio_min;
            }

            $int = round($shopBasket->quantity / $product->measure_ratio);
            $shopBasket->quantity = $int * $product->measure_ratio;

            if ($product->measure_ratio_min > $shopBasket->quantity) {
                $shopBasket->quantity = $product->measure_ratio_min;
            }


            if (!$shopBasket->recalculate()->save()) {
                $rr->success = false;
                $rr->message = \Yii::t('skeeks/shop/app', 'Failed to add item to cart');
            } else {
                $shopBasket->recalculate()->save();

                $rr->success = true;
                $rr->message = \Yii::t('skeeks/shop/app', 'Item added to cart');
            }
            $this->order->refresh();

            $rr->data = [
                'order' => $this->order->jsonSerialize(),
            ];

            return (array)$rr;
        } else {
            return $this->goBack();
        }
    }


    /**
     * Removing the basket position
     *
     * @return array|\yii\web\Response
     * @throws \Exception
     */
    public function actionRemoveOrderItem()
    {
        $rr = new RequestResponse();

        if ($rr->isRequestAjaxPost()) {
            $basket_id = \Yii::$app->request->post('order_item_id');

            $eventData = [];

            $shopBasket = ShopOrderItem::find()->where(['id' => $basket_id])->one();
            if ($shopBasket) {


                if ($shopBasket->delete()) {
                    $rr->success = true;
                    $rr->message = \Yii::t('skeeks/shop/app', 'Position successfully removed');

                    $productData = ShopComponent::productDataForJsEvent($shopBasket->shopProduct->cmsContentElement);
                    $productData['quantity'] = (float)$shopBasket->quantity;

                }

            }

            $this->order->refresh();

            $rr->data = [
                'order' => $this->order->jsonSerialize(),
            ];

            return (array)$rr;
        } else {
            return $this->goBack();
        }
    }

    /**
     * Cleaning the entire basket
     *
     * @return array|\yii\web\Response
     * @throws \Exception
     */
    public function actionClearOrderItems()
    {
        $rr = new RequestResponse();

        if ($rr->isRequestAjaxPost()) {
            foreach ($this->order->shopOrderItems as $basket) {
                $basket->delete();
            }

            $this->order->refresh();

            $rr->data = [
                'order' => $this->order->jsonSerialize(),
            ];
            $rr->success = true;
            $rr->message = "";

            return (array)$rr;
        } else {
            return $this->goBack();
        }
    }

    /**
     * @return array|\yii\web\Response
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionUpdateOrderItem()
    {
        $rr = new RequestResponse();

        if ($rr->isRequestAjaxPost()) {
            $basket_id = (int)\Yii::$app->request->post('order_item_id');
            $quantity = (float)\Yii::$app->request->post('quantity');

            $eventData = [];
            /**
             * @var $shopBasket ShopBasket
             */
            $shopBasket = ShopOrderItem::find()->where(['id' => $basket_id])->one();
            if ($shopBasket) {
                if ($quantity > 0) {
                    //Обновление корзины, это может быть как добавление позиции так и удаление
                    $product = $shopBasket->product;

                    if ($product->measure_ratio > 1) {
                        if ($quantity % $product->measure_ratio != 0) {
                            $quantity = $product->measure_ratio;
                        }
                    }

                    $shopBasket->quantity = $quantity;
                    if ($product->measure_ratio_min > $shopBasket->quantity) {
                        $shopBasket->quantity = $product->measure_ratio_min;
                    }

                    if ($shopBasket->recalculate()->save()) {
                        $rr->success = true;
                        $rr->message = \Yii::t('skeeks/shop/app', 'Postion successfully updated');
                    }

                } else {

                    if ($shopBasket->delete()) {
                        $rr->success = true;
                        $rr->message = \Yii::t('skeeks/shop/app', 'Position successfully removed');
                    }
                }
            }

            $this->order->refresh();

            $rr->data = [
                'order' => $this->order->jsonSerialize(),
            ];
            $rr->success = true;
            $rr->message = "";

            return (array)$rr;
        } else {
            return $this->goBack();
        }
    }

    /**
     * @return array|\yii\web\Response
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionUpdateOrderUser()
    {
        $rr = new RequestResponse();

        if ($rr->isRequestAjaxPost()) {
            $user_id = (int)\Yii::$app->request->post('user_id');

            /**
             * @var $shopBasket ShopBasket
             */
            $cmsUser = CmsUser::find()->cmsSite()->andWhere(['id' => $user_id])->one();

            if ($cmsUser) {
                $this->order->cms_user_id = $cmsUser->id;
                $this->order->contact_phone = $cmsUser->phone;
                $this->order->contact_email = $cmsUser->email;
                $this->order->contact_first_name = $cmsUser->first_name;
                $this->order->contact_last_name = $cmsUser->last_name;

                $this->order->update(false, [
                    'cms_user_id',
                    'contact_phone',
                    'contact_email',
                    'contact_first_name',
                    'contact_last_name',
                ]);
            }

            $this->order->refresh();

            $rr->data = [
                'order' => $this->order->jsonSerialize(),
            ];
            $rr->success = true;
            $rr->message = "";

            return (array)$rr;
        } else {
            return $this->goBack();
        }
    }

    /**
     * @return array|\yii\web\Response
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionOrderCreate()
    {
        $rr = new RequestResponse();

        if ($rr->isRequestAjaxPost()) {
            $t = \Yii::$app->db->beginTransaction();
            try {
                $comment = (string)\Yii::$app->request->post('comment');
                $payment_type = (string)\Yii::$app->request->post('payment_type');

                $order = $this->order;

                $order->comment = $comment;
                $order->shop_store_id = \Yii::$app->shop->backendShopStore->id;
                $order->is_created = 1;
                $order->isNotifyChangeStatus = false;
                $order->isNotifyEmailCreated = false;
                $order->isNotifyEmailPayed = false;
                $order->paid_at = time();
                if (!$order->save()) {
                    throw new Exception("Не сохранился заказ: " . print_r($order->errors, true));
                }

                $order->refresh();
                $order->isNotifyChangeStatus = false;
                $lastStatus = ShopOrderStatus::find()->orderBy(['priority' => SORT_DESC])->limit(1)->one();
                $order->shop_order_status_id = $lastStatus->id;
                if (!$order->save()) {
                    throw new Exception("Не сохранился заказ: " . print_r($order->errors, true));
                }

                $payment = new ShopPayment();
                $payment->cms_user_id = $order->cms_user_id;
                $payment->shop_order_id = $order->id;
                $payment->shop_store_id = $order->shop_store_id;
                $payment->shop_store_payment_type = $payment_type;
                $payment->amount = $order->amount;
                $payment->currency_code = $order->currency_code;
                $shopName = \Yii::$app->shop->backendShopStore->name;
                $payment->comment = "Продажа №{$order->id} от в магазине {$shopName}";

                if (!$payment->save()) {
                    throw new Exception("Не сохранился платеж: " . print_r($payment->errors, true));
                }

                $newOrder = new ShopOrder();
                $newOrder->is_created = false;
                $newOrder->shop_store_id = \Yii::$app->shop->backendShopStore->id;
                $newOrder->validate();
                if (!$newOrder->save(false)) {
                    throw new Exception(print_r($newOrder->errors, true));
                }
                \Yii::$app->getSession()->set($this->orderSessionName, $newOrder->id);

                $rr->data = [
                    'order' => $newOrder->jsonSerialize(),
                ];
                $rr->success = true;
                $rr->message = "Продажа прошла успешно";

                $t->commit();

            } catch (\Exception $exception) {

                $t->rollBack();
                throw $exception;
                $rr->success = false;
                $rr->message = "Ошибка: ".$exception->getMessage();
            }


            return (array)$rr;
        } else {
            return $this->goBack();
        }
    }

}
