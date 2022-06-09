<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 21.09.2015
 */

namespace skeeks\cms\shop\controllers;

use skeeks\cms\base\Controller;
use skeeks\cms\helpers\RequestResponse;
use skeeks\cms\models\CmsUser;
use skeeks\cms\shop\components\ShopComponent;
use skeeks\cms\shop\models\ShopBasket;
use skeeks\cms\shop\models\ShopDiscountCoupon;
use skeeks\cms\shop\models\ShopOrder;
use skeeks\cms\shop\models\ShopOrder2discountCoupon;
use skeeks\cms\shop\models\ShopOrderItem;
use skeeks\cms\shop\models\ShopProduct;
use yii\base\Exception;
use yii\base\UserException;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;

/**
 * Class CartController
 * @package skeeks\cms\shop\controllers
 */
class CartController extends Controller
{
    public $defaultAction = 'cart';

    /**
     * @return array
     */
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [

            'verbs' => [
                'class'   => VerbFilter::class,
                'actions' => [
                    'add-product'               => ['post'],
                    'remove-basket'             => ['post'],
                    'clear'                     => ['post'],
                    'update-basket'             => ['post'],
                    'shop-person-type-validate' => ['post'],
                    'shop-person-type-submit'   => ['post'],
                    'remove-discount-coupon'    => ['post'],
                    'add-discount-coupon'       => ['post'],
                ],
            ],
        ]);
    }


    /**
     * @return string
     */
    public function actionCart()
    {
        $this->view->title = \Yii::t('skeeks/shop/app', 'Basket').' | '.\Yii::t('skeeks/shop/app', 'Shop');
        return $this->render($this->action->id);
    }

    /**
     * @return string
     */
    public function actionCheckout()
    {
        $this->view->title = \Yii::t('skeeks/shop/app', 'Checkout').' | '.\Yii::t('skeeks/shop/app', 'Shop');
        return $this->render($this->action->id);
    }


    public function actionOrderCheckout()
    {
        $rr = new RequestResponse();

        if ($rr->isRequestAjaxPost()) {
            $t = \Yii::$app->db->beginTransaction();

            try {
                $order = \Yii::$app->shop->shopUser->shopOrder;
                if (!$order) {
                    throw new Exception("Обновите страницу заказ уже не найден");
                }
                $rr->success = true;

                //Сначала проверка корректности данных по заказу
                if (!$order->validate()) {
                    $errors = $order->getFirstErrors();
                    if ($errors) {
                        $codes = array_keys($errors);
                        $error = array_shift($errors);
                        $error_code = array_shift($codes);
                        
                        $rr->data = [
                            'error_code' => $error_code,
                        ];
                        $rr->message = $error;
                        $rr->success = false;
                        return $rr;
                    }
                }
                
                $deliveryHandlerCheckoutModel = null;
                if ($order->deliveryHandlerCheckoutModel) {
                    $deliveryHandlerCheckoutModel = $order->deliveryHandlerCheckoutModel;
                    if (!$deliveryHandlerCheckoutModel->validate()) {
                        $errors = $deliveryHandlerCheckoutModel->getFirstErrors();
                        if ($errors) {
                            
                            $codes = array_keys($errors);
                            $error = array_shift($errors);
                            $error_code = array_shift($codes);
                            
                            $errorElementId = Html::getInputId($deliveryHandlerCheckoutModel, $error_code);
                            
                            $rr->data = [
                                'error_code' => $error_code,
                                'error_element_id' => $errorElementId,
                            ];
                            $rr->message = $error;
                            $rr->success = false;
                            return $rr;
                        }
                    }
                }




                if (!$order->cms_user_id) {
                    //Нужно создать пользователя
                    $cmsUser = null;
                    if ($order->contact_phone) {
                        $cmsUser = CmsUser::find()->cmsSite()->phone($order->contact_phone)->one();
                    }
                    if ($order->contact_email) {
                        $cmsUser = CmsUser::find()->cmsSite()->email($order->contact_email)->one();
                    }
                    
                    if (!$cmsUser) {
                        $cmsUser = new CmsUser();
                        $cmsUser->phone = $order->contact_phone;
                        $cmsUser->email = $order->contact_email;
                        $cmsUser->first_name = $order->contact_first_name;
                        $cmsUser->last_name = $order->contact_last_name;
                        
                        if (!$cmsUser->save()) {
                            throw new Exception(print_r($cmsUser->errors, true));
                        }
                    }
                    
                    $order->cms_user_id = $cmsUser->id;
                } else {
                    $cmsUser = $order->cmsUser;
                    $order->contact_phone = $cmsUser->phone;
                    $order->contact_email = $cmsUser->email;
                    $order->contact_first_name = $cmsUser->first_name;
                    $order->contact_last_name = $cmsUser->last_name;
                }
                
                
                //Если указаны данные получателя, то нужно создать поьлзователя
                if ($order->hasReceiver) {
                    if (!$order->receiver_cms_user_id) {
                        $receiverCmsUser = null;
                        
                        if ($order->receiver_phone) {
                            $receiverCmsUser = CmsUser::find()->cmsSite()->phone($order->receiver_phone)->one();
                        }
                        if ($order->contact_email) {
                            $receiverCmsUser = CmsUser::find()->cmsSite()->email($order->receiver_email)->one();
                        }
                        
                        if (!$receiverCmsUser) {
                            $receiverCmsUser = new CmsUser();
                            $receiverCmsUser->phone = $order->receiver_phone;
                            $receiverCmsUser->email = $order->receiver_email;
                            $receiverCmsUser->first_name = $order->receiver_first_name;
                            $receiverCmsUser->last_name = $order->receiver_last_name;
                            
                            if (!$receiverCmsUser->save()) {
                                throw new Exception(print_r($receiverCmsUser->errors, true));
                            }
                        }
                        
                        $order->receiver_cms_user_id = $receiverCmsUser->id;
                        
                    }
                }

                if ($deliveryHandlerCheckoutModel) {
                    $deliveryHandlerCheckoutModel->modifyOrder($order);
                }


                $order->is_created = 1;
                if (!$order->save()) {
                    throw new UserException(print_r($order->errors, true));
                }
                
                
                $orderUrl = $order->getUrl(['is_created' => 'true']);
                $rr->success = true;
                $rr->redirect = $orderUrl;
                
                \Yii::$app->shop->shopUser->shop_order_id = null;
                \Yii::$app->shop->shopUser->save();
                
                \Yii::$app->session->setFlash("order", $order->id);
                
                $t->commit();
                
                
                /*$order->shopCart->shop_order_id = null;
                $order->shopCart->save();*/

            } catch (\Exception $exception) {
                $t->rollBack();

                /*throw $exception;*/

                $rr->message = $exception->getMessage();
                $rr->success = false;
            }
        }
        return $rr;
    }

    public function actionOrderUpdate()
    {
        $rr = new RequestResponse();

        if ($rr->isRequestAjaxPost()) {

            $order = null;
            if ($shopOrderId = \Yii::$app->request->post("shop_order_id")) {
                $order = ShopOrder::find()->cmsSite()->andWhere(['id' => $shopOrderId])->one();
            }

            if (!$order) {
                $order = \Yii::$app->shop->shopUser->shopOrder;
            }

            $data = \Yii::$app->request->post("data");

            try {

                $attributeForUpdate = [];

                //Изменения по доставке обрабатываются особенно
                $deliveryString = (string)ArrayHelper::getValue($data, 'delivery_nandler');
                $deliveryData = [];

                if ($deliveryString) {
                    parse_str($deliveryString, $deliveryData);
                }

                $deliveryId = (int)ArrayHelper::getValue($data, 'shop_delivery_id');
                if ($deliveryId) {
                    $order->shop_delivery_id = $deliveryId;
                    $order->delivery_handler_data_jsoned = null;

                    $attributeForUpdate = [
                        'shop_delivery_id',
                        'delivery_handler_data_jsoned',
                        'delivery_amount',
                    ];
                    //Если у этого способа есть своя доставка
                    if ($order->shopDelivery->handler) {
                        if ($deliveryData) {
                            $checkoutModel = $order->deliveryHandlerCheckoutModel;
                            $checkoutModel->load($deliveryData);
                            $order->delivery_handler_data_jsoned = Json::encode($checkoutModel->toArray());
                        }
                    }
                } else {
                    $order->setAttributes($data, false);
                    $attributeForUpdate = array_keys($data);
                }

                if (!$order->save(true, $attributeForUpdate)) {
                    $errors = $order->getFirstErrors();
                    if ($errors) {
                        $error = array_shift($errors);
                        $message = $error;
                        throw new Exception($message);
                    }
                    throw new Exception(print_r($order->errors, true));
                }


                $rr->data = ArrayHelper::merge($order->jsonSerialize(), []);
                $rr->success = true;
            } catch (\Exception $exception) {
                $rr->message = $exception->getMessage();
                $rr->success = false;
            }

        }

        return $rr;
    }

    /**
     * Adding a product to the cart.
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

            if (\Yii::$app->shop->shopUser->isNewRecord) {
                \Yii::$app->shop->shopUser->save();
                \Yii::$app->getSession()->set(\Yii::$app->shop->sessionFuserName, \Yii::$app->shop->shopUser->id);
            }

            $shopBasket = ShopOrderItem::find()->where([
                'shop_order_id'   => \Yii::$app->shop->shopUser->shopOrder->id,
                'shop_product_id' => $product_id,
            ])->one();

            if (!$shopBasket) {
                $shopBasket = new ShopOrderItem([
                    'shop_order_id'   => \Yii::$app->shop->shopUser->shopOrder->id,
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

            \Yii::$app->shop->shopUser->shopOrder->link('cmsSite', \Yii::$app->skeeks->site);
            \Yii::$app->shop->shopUser->shopOrder->refresh();

            $productData = ShopComponent::productDataForJsEvent($product->cmsContentElement);
            $productData['quantity'] = (float)$quantity;
            $rr->data = ArrayHelper::merge(\Yii::$app->shop->shopUser->shopOrder->jsonSerialize(), [
                'product' => $productData,
            ]);

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
    public function actionRemoveBasket()
    {
        $rr = new RequestResponse();

        if ($rr->isRequestAjaxPost()) {
            $basket_id = \Yii::$app->request->post('basket_id');

            $eventData = [];

            $shopBasket = ShopOrderItem::find()->where(['id' => $basket_id])->one();
            if ($shopBasket) {


                if ($shopBasket->delete()) {
                    $rr->success = true;
                    $rr->message = \Yii::t('skeeks/shop/app', 'Position successfully removed');

                    $productData = ShopComponent::productDataForJsEvent($shopBasket->shopProduct->cmsContentElement);
                    $productData['quantity'] = (float)$shopBasket->quantity;

                    $eventData['event'] = 'remove';
                    $eventData['product'] = $productData;
                }

            }

            \Yii::$app->shop->shopUser->shopOrder->link('cmsSite', \Yii::$app->skeeks->site);
            $rr->data = ArrayHelper::merge(\Yii::$app->shop->shopUser->shopOrder->jsonSerialize(), [
                'eventData' => $eventData,
            ]);

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
    public function actionClear()
    {
        $rr = new RequestResponse();

        if ($rr->isRequestAjaxPost()) {
            foreach (\Yii::$app->shop->shopUser->shopOrder->shopOrderItems as $basket) {
                $basket->delete();
            }

            \Yii::$app->shop->shopUser->shopOrder->link('cmsSite', \Yii::$app->skeeks->site);
            $rr->data = \Yii::$app->shop->shopUser->shopOrder->jsonSerialize();
            $rr->success = true;
            $rr->message = "";

            return (array)$rr;
        } else {
            return $this->goBack();
        }
    }

    /**
     * Updating the positions of the basket, such as changing the number of
     *
     * @return array|\yii\web\Response
     * @throws \Exception
     */
    public function actionUpdateBasket()
    {
        $rr = new RequestResponse();

        if ($rr->isRequestAjaxPost()) {
            $basket_id = (int)\Yii::$app->request->post('basket_id');
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

                    if ($shopBasket->shopProduct) {
                        $productData = ShopComponent::productDataForJsEvent($shopBasket->shopProduct->cmsContentElement);

                        if ($shopBasket->quantity < $quantity) {
                            //Стало больше, товары добавлены
                            $eventData['event'] = 'add';
                            $productData['quantity'] = $quantity - $shopBasket->quantity;

                        } else {
                            //Стало меньше товары удалены
                            $eventData['event'] = 'remove';
                            $productData['quantity'] = $shopBasket->quantity - $quantity;
                        }

                        $eventData['product'] = $productData;
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
                    //Удаление товаров из корзины
                    $eventData['event'] = 'remove';
                    if ($shopBasket->shopProduct) {
                        $productData = ShopComponent::productDataForJsEvent($shopBasket->shopProduct->cmsContentElement);
                        $productData['quantity'] = (float)$shopBasket->quantity;
                        $eventData['product'] = $productData;
                    }


                    if ($shopBasket->delete()) {
                        $rr->success = true;
                        $rr->message = \Yii::t('skeeks/shop/app', 'Position successfully removed');
                    }
                }
            }

            \Yii::$app->shop->shopUser->shopOrder->link('cmsSite', \Yii::$app->skeeks->site);
            \Yii::$app->shop->shopUser->shopOrder->refresh();

            $rr->data = ArrayHelper::merge(\Yii::$app->shop->shopUser->shopOrder->jsonSerialize(), [
                'eventData' => $eventData,
            ]);
            return (array)$rr;
        } else {
            return $this->goBack();
        }
    }

    /**
     * @return array|\yii\web\Response
     */
    public function actionRemoveDiscountCoupon()
    {
        $rr = new RequestResponse();

        if ($rr->isRequestAjaxPost()) {
            $couponId = \Yii::$app->request->post('coupon_id');

            try {
                if (!$couponId) {
                    throw new Exception(\Yii::t('skeeks/shop/app', 'Not set coupon code'));
                }


                ShopOrder2discountCoupon::deleteAll(['discount_coupon_id' => $couponId, 'order_id' => \Yii::$app->shop->shopUser->shopOrder->id]);

                foreach (\Yii::$app->shop->shopUser->shopOrder->shopOrderItems as $orderItem) {
                    $orderItem->recalculate()->save();
                };

                $rr->data = \Yii::$app->shop->shopUser->shopOrder->jsonSerialize();
                $rr->success = true;
                $rr->message = \Yii::t('skeeks/shop/app', 'Your coupon was successfully deleted');

            } catch (\Exception $e) {
                $rr->message = $e->getMessage();
                return (array)$rr;
            }

            return (array)$rr;
        } else {
            return $this->goBack();
        }
    }

    /**
     * Adding a product to the cart.
     *
     * @return array|\yii\web\Response
     */
    public function actionAddDiscountCoupon()
    {
        $rr = new RequestResponse();

        if ($rr->isRequestAjaxPost()) {
            $couponCode = \Yii::$app->request->post('coupon_code');

            try {
                if (!$couponCode) {
                    throw new Exception(\Yii::t('skeeks/shop/app', 'Not set coupon code'));
                }

                $applyShopDiscountCoupon = ShopDiscountCoupon::find()
                    ->where(['coupon' => $couponCode])
                    ->andWhere(['is_active' => 1])
                    ->andWhere([
                        'or',
                        ['>', 'active_to', time()],
                        ['active_to' => null],
                    ])
                    ->one();


                if (!$applyShopDiscountCoupon) {
                    throw new Exception(\Yii::t('skeeks/shop/app', 'Coupon does not exist or is not active'));
                }

                /*$discount_coupons = [];
                if (\Yii::$app->shop->shopUser->shopOrder->shopDiscountCoupons) {
                    $discount_coupons = ArrayHelper::map(\Yii::$app->shop->shopUser->shopOrder->shopDiscountCoupons, 'id', 'id');
                }
                $discount_coupons[] = $applyShopDiscountCoupon->id;
                array_unique($discount_coupons);*/

                $map = new ShopOrder2discountCoupon();
                $map->order_id = \Yii::$app->shop->shopUser->shopOrder->id;
                $map->discount_coupon_id = $applyShopDiscountCoupon->id;

                $map->save();
                //\Yii::$app->shop->shopUser->shopOrder->shopDiscountCoupons = $discount_coupons;
                //\Yii::$app->shop->shopUser->shopOrder->save();
                /*$order = \Yii::$app->shop->shopUser->shopOrder;
                $order->refresh();*/
                foreach (\Yii::$app->shop->shopUser->shopOrder->shopOrderItems as $orderItem) {
                    $orderItem->recalculate()->save();
                };
                //\Yii::$app->shop->shopUser->shopOrder->recalculate()->save();

                $rr->data = \Yii::$app->shop->shopUser->shopOrder->jsonSerialize();
                $rr->success = true;
                $rr->message = \Yii::t('skeeks/shop/app', 'Coupon successfully installed');

            } catch (\Exception $e) {
                $rr->message = $e->getMessage();
                return (array)$rr;
            }

            return (array)$rr;
        } else {
            return $this->goBack();
        }
    }

}