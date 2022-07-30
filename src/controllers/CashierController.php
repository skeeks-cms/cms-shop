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
use skeeks\cms\helpers\StringHelper;
use skeeks\cms\models\CmsAgent;
use skeeks\cms\models\CmsUser;
use skeeks\cms\shop\models\ShopCasheboxShift;
use skeeks\cms\shop\models\ShopCheck;
use skeeks\cms\shop\models\ShopCmsContentElement;
use skeeks\cms\shop\models\ShopOrder;
use skeeks\cms\shop\models\ShopOrderItem;
use skeeks\cms\shop\models\ShopOrderStatus;
use skeeks\cms\shop\models\ShopPayment;
use skeeks\cms\shop\models\ShopProduct;
use yii\base\Exception;
use yii\data\Pagination;

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
                    $order->cms_user_id = \Yii::$app->shop->backendShopStore->cashier_default_cms_user_id;
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
                $order->cms_user_id = \Yii::$app->shop->backendShopStore->cashier_default_cms_user_id;
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
            $page = \Yii::$app->request->post("page", 0);

            $query = ShopCmsContentElement::find()
                ->from(['cce' => ShopCmsContentElement::tableName()])
                ->innerJoinWith("shopProduct as shopProduct")
                ->groupBy(["cce.id"]);

            if ($q) {
                $q = trim($q);
                $query->joinWith("shopProduct.shopProductBarcodes as barcodes");
                $query->andWhere([
                    'or',
                    ['like', 'cce.name', $q],
                    ['=', 'cce.id', $q],
                    ['=', 'barcodes.value', $q],
                ]);
                $query->groupBy("shopProduct.id");
            }

            $countQuery = clone $query;
            $totalCount = $countQuery->count();


            if ($totalCount) {

                $pagination = new Pagination(['totalCount' => $totalCount, 'defaultPageSize' => 40]);
                $pagination->setPage($page);
                $models = $query->offset($pagination->offset)->limit($pagination->limit);


                $content = '';
                foreach ($query->each(10) as $element) {
                    $content .= $this->renderPartial('_product', [
                        'model' => $element,
                    ]);
                }
                $hasNextPage = (bool) ($pagination->page < ($pagination->pageCount-1));
                $nexPage = $pagination->page;
                if ($hasNextPage) {
                    $nexPage = $pagination->page + 1;
                }


                if ($hasNextPage) {
                    $content .= "<div class='sx-more'><button class='ui button btn-lg btn-block sx-btn-next-page' data-next-page='{$nexPage}' data-load-text='Ожидайте! Идет загрузка...'>Показать еще</button></div>";
                }

                $data['content'] = $content;
                $data['pagination'] = [
                    'offset'      => $pagination->offset,
                    'totalCount'  => (int)$pagination->totalCount,
                    'page'        => $pagination->page,
                    'pageSize'    => $pagination->pageSize,
                    'pageCount'   => $pagination->pageCount,
                    'hasNextPage' => $hasNextPage,
                    'nextPage' => $nexPage,
                ];
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
     * Это бэкенд для поиска товаров
     *
     * @return RequestResponse
     */
    public function actionGetOrderItemEdit()
    {
        $rr = new RequestResponse();

        if ($rr->isRequestAjaxPost()) {
            \Yii::$app->shop->backendShopStore;
            \Yii::$app->skeeks->site;
            try {
                $order_item_id = \Yii::$app->request->post("order_item_id");

                $orderItem = ShopOrderItem::find()->where(['id' => $order_item_id])->one();
                if (!$orderItem) {
                    throw new Exception("Не найден");
                }

                $data['content'] = $this->renderPartial("_order-item-edit", [
                    'model' => $orderItem
                ]);

                $rr->success = true;
                $rr->data = $data;

            } catch (\Exception $exception) {
                $rr->success = false;
                $rr->message = $exception->getMessage();
            }

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
     * Добавить товар
     *
     * @return array|\yii\web\Response
     */
    public function actionAddProductBarcode()
    {
        $rr = new RequestResponse();

        if ($rr->isRequestAjaxPost()) {

            $barcode = \Yii::$app->request->post('barcode');
            if (!$barcode) {
                return $rr;
            }

            $query = ShopCmsContentElement::find()
                ->from(['cce' => ShopCmsContentElement::tableName()])
                ->innerJoinWith("shopProduct as shopProduct")
                ->groupBy(["cce.id"]);

            if ($barcode) {
                $barcode = trim($barcode);
                $query->joinWith("shopProduct.shopProductBarcodes as barcodes");
                $query->andWhere(
                    ['=', 'barcodes.value', $barcode],
                );
                $query->groupBy("shopProduct.id");
            }

            $countQuery = clone $query;
            $totalCount = $countQuery->count();

            $product = [];
            if ($totalCount == 1) {
                $product = $query->one()->toArray();
            }
            $data["total"] = (int) $totalCount;
            $data["product"] = $product;

            $rr->data = $data;
            $rr->success = true;

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
            $post = \Yii::$app->request->post();

            $quantity = (float)\Yii::$app->request->post("quantity");
            $amount = (float)\Yii::$app->request->post("amount");
            $discount_amount = (float)\Yii::$app->request->post("discount_amount");
            $discount_percent = (float)\Yii::$app->request->post("discount_percent");

            $eventData = [];
            /**
             * @var $orderItem ShopOrderItem
             */
            $orderItem = ShopOrderItem::find()->where(['id' => $basket_id])->one();
            if ($orderItem) {

                if (isset($post['quantity'])) {
                    if ($quantity > 0) {
                        //Обновление корзины, это может быть как добавление позиции так и удаление
                        $product = $orderItem->product;

                        if ($product->measure_ratio > 1) {
                            if ($quantity % $product->measure_ratio != 0) {
                                $quantity = $product->measure_ratio;
                            }
                        }

                        $orderItem->quantity = $quantity;
                        if ($product->measure_ratio_min > $orderItem->quantity) {
                            $orderItem->quantity = $product->measure_ratio_min;
                        }

                        if ($orderItem->save()) {
                            $rr->success = true;
                            $rr->message = \Yii::t('skeeks/shop/app', 'Postion successfully updated');
                        }

                    }
                }

                if (isset($post['amount'])) {
                    //Обновление корзины, это может быть как добавление позиции так и удаление

                    $orderItem->amount = $amount;

                    if ($orderItem->save()) {
                        $rr->success = true;
                        $rr->message = \Yii::t('skeeks/shop/app', 'Postion successfully updated');
                    }
                }

                if (isset($post['discount_amount'])) {
                    //Обновление корзины, это может быть как добавление позиции так и удаление

                    $orderItem->discount_amount = $discount_amount;

                    if ($orderItem->save()) {
                        $rr->success = true;
                        $rr->message = \Yii::t('skeeks/shop/app', 'Postion successfully updated');
                    }
                }

                if (isset($post['discount_percent'])) {
                    //Обновление корзины, это может быть как добавление позиции так и удаление

                    $orderItem->discount_amount = $orderItem->amount*$discount_percent / 100;

                    if ($orderItem->save()) {
                        $rr->success = true;
                        $rr->message = \Yii::t('skeeks/shop/app', 'Postion successfully updated');
                    }
                }

            }

            $this->order->refresh();

            $rr->data = [
                'order' => $this->order->jsonSerialize(),
                'item' => $orderItem->toArray([], $orderItem->extraFields()),
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
    public function actionUpdateOrderData()
    {
        $rr = new RequestResponse();

        if ($rr->isRequestAjaxPost()) {
            $orderData = \Yii::$app->request->post();

            /**
             * @var $shopBasket ShopBasket
             */

            $this->order->load($orderData, "");
            $this->order->save(true, array_keys($orderData));
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
    public function actionCheckStatus()
    {
        $rr = new RequestResponse();

        if ($rr->isRequestAjaxPost()) {
            $t = \Yii::$app->db->beginTransaction();
            try {
                $check_id = (int)\Yii::$app->request->post('check_id');
                if (!$check_id) {
                    throw new Exception("Не корректный аргумент check_id");
                }

                /**
                 * @var $shopCheck ShopCheck
                 */
                $shopCheck = ShopCheck::find()->cmsSite()->andWhere(['id' => $check_id])->one();
                if (!$shopCheck) {
                    \Yii::error(__METHOD__."Чек не найден в базе сайта!", static::class);
                    throw new Exception("Чек не найден в базе сайта!");
                }

                if ($shopCheck->shopCashebox && $shopCheck->shopCashebox->shopCloudkassa) {
                    //Обновить статус продажи
                    $shopCheck->shopCashebox->shopCloudkassa->handler->updateStatus($shopCheck);
                }

                $checkHtml = '';
                if ($shopCheck->isApproved) {
                    $checkHtml = $this->renderPartial('_check', [
                        'model' => $shopCheck,
                    ]);
                }

                $rr->data = [
                    'check'      => $shopCheck->toArray(),
                    'check_html' => $checkHtml,
                ];
                $rr->success = true;
                $rr->message = "";

            } catch (\Exception $exception) {
                throw $exception;
                $rr->success = false;
                $rr->message = "Ошибка: ".$exception->getMessage();
            }
        }

        return $rr;

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
                $payment_type = (string)trim(\Yii::$app->request->post('payment_type'));
                $is_print = (int)trim(\Yii::$app->request->post('is_print'));

                $order = $this->order;

                $order->comment = $comment;
                $order->shop_store_id = \Yii::$app->shop->backendShopStore->id;
                $order->is_created = 1;
                $order->isNotifyChangeStatus = false;
                $order->isNotifyEmailCreated = false;
                $order->isNotifyEmailPayed = false;
                $order->paid_at = time();
                if (!$order->save()) {
                    throw new Exception("Не сохранился заказ: ".print_r($order->errors, true));
                }

                $order->refresh();
                $order->isNotifyChangeStatus = false;
                $lastStatus = ShopOrderStatus::find()->orderBy(['priority' => SORT_DESC])->limit(1)->one();
                $order->shop_order_status_id = $lastStatus->id;
                if (!$order->save()) {
                    throw new Exception("Не сохранился заказ: ".print_r($order->errors, true));
                }


                //Формирование чека

                $check = new ShopCheck();
                $check->shop_store_id = $order->shop_store_id;
                $check->shop_cashebox_id = $this->shift->shop_cashebox_id;
                $check->shop_cashebox_shift_id = $this->shift->id;
                $check->shop_order_id = $order->id;
                $check->cms_user_id = $order->cms_user_id;
                $check->is_print = $is_print;
                $check->cashier_cms_user_id = \Yii::$app->user->id;

                if ($order->order_type == ShopOrder::TYPE_SALE) {
                    $check->doc_type = ShopOrder::TYPE_SALE;
                } else {
                    $check->doc_type = ShopOrder::TYPE_RETURN;
                }
                /**
                 * Телефон или электронный адрес почты покупателя
                 * Допустимы символы для адреса электронной почты.
                 * Номер телефона в формате +7<10 цифр>
                 * или 8<10 цифр>
                 */
                if ($order->contact_email) {
                    $check->email = $order->contact_email;
                } elseif ($order->contact_phone) {
                    $phone = trim($order->contact_phone);
                    $phone = str_replace(" ", "", $phone);
                    $phone = str_replace("-", "", $phone);

                    $check->email = $phone;
                }


                $check->cashier_name = \Yii::$app->user->identity->shortDisplayName;
                //$check->cashier_position = "Кассир"; //Взятьи из настроек кассы
                $check->cashier_cms_user_id = \Yii::$app->user->id;
                $check->amount = $order->amount;


                $items = [];
                //Это формирование по правилам modulkassa
                foreach ($order->shopOrderItems as $item) {
                    $itemData = [
                        'name'          => $item->name,
                        'price'         => round($item->amount, 2),
                        'discSum'         => round($item->discount_amount, 2),
                        'quantity'      => (float)$item->quantity,
                        'measure'       => $item->measure_code == 796 ? "pcs" : "other",
                        'vatTag'        => 1105,
                        'paymentObject' => "commodity",
                        'paymentMethod' => "full_payment",
                    ];

                    $items[] = $itemData;
                }
                $check->inventPositions = $items;
                $check->moneyPositions = [
                    [
                        'paymentType' => StringHelper::strtoupper($payment_type),
                        'sum'         => round($order->amount, 2),
                    ],
                ];

                if (!$check->save()) {
                    throw new Exception("Не сохранился чек: ".print_r($check->errors, true));
                }

                $payment = new ShopPayment();
                $payment->cms_user_id = $order->cms_user_id;
                $payment->shop_order_id = $order->id;
                $payment->shop_store_id = $order->shop_store_id;
                $payment->shop_store_payment_type = $payment_type;
                $payment->amount = $order->amount;
                $payment->currency_code = $order->currency_code;
                $shopName = \Yii::$app->shop->backendShopStore->name;
                $payment->comment = $order->asText()." в магазине {$shopName}";

                if ($order->order_type == ShopOrder::TYPE_SALE) {
                    $payment->is_debit = 1;
                } else {
                    $payment->is_debit = 0;
                }


                if (!$payment->save()) {
                    throw new Exception("Не сохранился платеж: ".print_r($payment->errors, true));
                }

                //Работа с облачной кассой, нужно сделать чек
                if ($shopCloudkassa = $this->shift->shopCashebox->shopCloudkassa) {
                    $shopCloudkassa->handler->createFiscalCheck($check);
                }


                $newOrder = new ShopOrder();
                $newOrder->is_created = false;
                $newOrder->cms_user_id = \Yii::$app->shop->backendShopStore->cashier_default_cms_user_id;
                $newOrder->shop_store_id = \Yii::$app->shop->backendShopStore->id;
                $newOrder->validate();
                if (!$newOrder->save(false)) {
                    throw new Exception(print_r($newOrder->errors, true));
                }

                \Yii::$app->getSession()->set($this->orderSessionName, $newOrder->id);

                $checkHtml = '';
                if ($check->isNew) {
                    $checkHtml = $this->renderPartial('_check', [
                        'model' => $check,
                    ]);
                }

                $rr->data = [
                    'order' => $newOrder->jsonSerialize(),
                    'check' => $check->toArray(),
                    'check_html' => $checkHtml,
                ];
                $rr->success = true;
                $rr->message = "";

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
