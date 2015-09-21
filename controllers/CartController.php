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
use skeeks\cms\shop\models\ShopBasket;
use skeeks\cms\shop\models\ShopProduct;
use yii\helpers\Json;

/**
 * Class CartController
 * @package skeeks\cms\shop\controllers
 */
class CartController extends Controller
{
    /**
     * Добавление продукта в корзину.
     *
     * @return array|\yii\web\Response
     */
    public function actionAddProduct()
    {
        $rr = new RequestResponse();

        if ($rr->isRequestAjaxPost())
        {
            $product_id = \Yii::$app->request->post('product_id');
            $quantity   = \Yii::$app->request->post('quantity');

            /**
             * @var ShopProduct $product
             */
            $product = ShopProduct::find()->where(['id' => $product_id])->one();

            if (!$product)
            {
                $rr->message = 'Товар не найден, возможно его только что удалили.';
                return (array) $rr;
            }

            $shopBasket = ShopBasket::find()->where([
                'fuser_id'      => \Yii::$app->shop->cart->shopFuser->id,
                'product_id'    => $product_id,
                'order_id'      => null,
            ])->one();

            if (!$shopBasket)
            {
                $shopBasket = new ShopBasket([
                    'fuser_id'      => \Yii::$app->shop->cart->shopFuser->id,
                    'name'          => $product->cmsContentElement->name,
                    'product_id'    => $product->id,
                    'price'         => $product->baseProductPrice->price,
                    'currency_code' => \Yii::$app->money->currencyCode,
                    'site_code'     => \Yii::$app->cms->site->code,
                    'quantity'      => 0,
                ]);
            }

            $shopBasket->quantity = $shopBasket->quantity + $quantity;
            if (!$shopBasket->save())
            {
                $rr->success = false;
                $rr->message = 'Ошибка добавления позиции в корзину';
            } else
            {
                $rr->success = true;
                $rr->message = 'Позиция добавлена в корзину';
            }

            //$shopBasket->recalculate();



            return (array) $rr;
        } else
        {
            return $this->goBack();
        }
    }

    public function actionRemoveBasket()
    {
        $rr = new RequestResponse();

        if ($rr->isRequestAjaxPost())
        {
            $basket_id = \Yii::$app->request->post('basket_id');

            $shopBasket = ShopBasket::find()->where(['id' => $basket_id ])->one();
            if ($shopBasket)
            {
                if ($shopBasket->delete())
                {
                    $rr->success = true;
                    $rr->message = 'Позиция успешно удалена';
                }
            }


            return (array) $rr;
        } else
        {
            return $this->goBack();
        }
    }

    public function actionUpdateBasket()
    {
        $rr = new RequestResponse();

        if ($rr->isRequestAjaxPost())
        {
            $basket_id  = (int) \Yii::$app->request->post('basket_id');
            $quantity   = (int) \Yii::$app->request->post('quantity');

            $shopBasket = ShopBasket::find()->where(['id' => $basket_id ])->one();
            if ($shopBasket)
            {
                if ($quantity > 0)
                {
                    $shopBasket->quantity = $quantity;
                    if ($shopBasket->save())
                    {
                        $rr->success = true;
                        $rr->message = 'Позиция успешно обновлена';

                        $shopBasket->recalculate();
                    }

                } else
                {
                    if ($shopBasket->delete())
                    {
                        $rr->success = true;
                        $rr->message = 'Позиция успешно удалена';
                    }
                }

            }


            return (array) $rr;
        } else
        {
            return $this->goBack();
        }
    }
}