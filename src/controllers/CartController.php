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
use skeeks\cms\shop\components\ShopComponent;
use skeeks\cms\shop\models\ShopBasket;
use skeeks\cms\shop\models\ShopDiscountCoupon;
use skeeks\cms\shop\models\ShopOrder2discountCoupon;
use skeeks\cms\shop\models\ShopOrderItem;
use skeeks\cms\shop\models\ShopProduct;
use yii\base\Exception;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;

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
            $productData['quantity'] = $quantity;
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
                    $productData['quantity'] = $shopBasket->quantity;

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
                        $productData['quantity'] = $shopBasket->quantity;
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