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
use skeeks\cms\models\CmsCompareElement;
use skeeks\cms\shop\models\ShopCmsContentElement;
use skeeks\cms\shop\models\ShopFavoriteProduct;
use skeeks\cms\shop\models\ShopOrderItem;
use skeeks\cms\shop\models\ShopProduct;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class CompareController extends Controller
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
        $this->view->title = \Yii::t('skeeks/shop/app', 'Сравнение товаров');
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
             * @var ShopCmsContentElement $product
             */
            $product = ShopCmsContentElement::find()->where(['id' => $product_id])->one();

            if (!$product) {
                $rr->message = \Yii::t('skeeks/shop/app', 'This product is not found, it may be removed.');
                return (array)$rr;
            }

            if (\Yii::$app->shop->shopUser->isNewRecord) {
                \Yii::$app->shop->shopUser->save();
                \Yii::$app->getSession()->set(\Yii::$app->shop->sessionFuserName, \Yii::$app->shop->shopUser->id);
            }

            $compare = new CmsCompareElement();
            $compare->shop_user_id = \Yii::$app->shop->shopUser->id;
            $compare->cms_content_element_id = $product->id;

            $compare->save();

            $rr->data = [
                'total' => \Yii::$app->shop->shopUser->getCmsCompareElements()->count()
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

            $shopFavoriteProduct = \Yii::$app->shop->shopUser->getCmsCompareElements()->where(['cms_content_element_id' => $product_id])->one();
            if ($shopFavoriteProduct) {
                if ($shopFavoriteProduct->delete()) {
                    $rr->success = true;
                    $rr->message = \Yii::t('skeeks/shop/app', 'Position successfully removed');
                }
            }

            $rr->data = [
                'total' => \Yii::$app->shop->shopUser->getCmsCompareElements()->count()
            ];

            return (array)$rr;
        } else {
            return $this->goBack();
        }
    }
}