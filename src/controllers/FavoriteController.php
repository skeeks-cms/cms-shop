<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */

namespace skeeks\cms\shop\controllers;

use skeeks\cms\base\Controller;
use skeeks\cms\helpers\RequestResponse;
use skeeks\cms\shop\models\ShopFavoriteProduct;
use skeeks\cms\shop\models\ShopOrderItem;
use skeeks\cms\shop\models\ShopProduct;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class FavoriteController extends Controller
{
    /**
     * @return array
     */
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [

            'verbs' => [
                'class'   => VerbFilter::class,
                'actions' => [
                    'add-product'    => ['post'],
                    'remove-product' => ['post'],
                    'clear'          => ['post'],
                ],
            ],
        ]);
    }


    /**
     * @return string
     */
    public function actionIndex()
    {
        $this->view->title = \Yii::t('skeeks/shop/app', 'Favorite products');
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

            /**
             * @var ShopProduct $product
             */
            $product = ShopProduct::find()->where(['id' => $product_id])->one();

            if (!$product) {
                $rr->message = \Yii::t('skeeks/shop/app', 'This product is not found, it may be removed.');
                return (array)$rr;
            }

            if (\Yii::$app->shop->cart->isNewRecord) {
                \Yii::$app->shop->cart->save();
                \Yii::$app->getSession()->set(\Yii::$app->shop->sessionFuserName, \Yii::$app->shop->cart->id);
            }

            $shopFavoriteProduct = new ShopFavoriteProduct();
            $shopFavoriteProduct->shop_cart_id = \Yii::$app->shop->cart->id;
            $shopFavoriteProduct->shop_product_id = $product->id;
            $shopFavoriteProduct->cms_site_id = \Yii::$app->cms->site->id;

            $shopFavoriteProduct->save();

            $rr->data = [
                'total' => \Yii::$app->shop->cart->getShopFavoriteProducts()->count()
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
    public function actionRemoveProduct()
    {
        $rr = new RequestResponse();

        if ($rr->isRequestAjaxPost()) {
            $product_id = \Yii::$app->request->post('product_id');

            $shopFavoriteProduct = \Yii::$app->shop->cart->getShopFavoriteProducts()->where(['shop_product_id' => $product_id])->one();
            if ($shopFavoriteProduct) {
                if ($shopFavoriteProduct->delete()) {
                    $rr->success = true;
                    $rr->message = \Yii::t('skeeks/shop/app', 'Position successfully removed');
                }
            }

            $rr->data = [
                'total' => \Yii::$app->shop->cart->getShopFavoriteProducts()->count()
            ];

            return (array)$rr;
        } else {
            return $this->goBack();
        }
    }
}