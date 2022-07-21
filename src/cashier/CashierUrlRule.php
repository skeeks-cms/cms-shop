<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */

namespace skeeks\cms\shop\cashier;


use skeeks\cms\backend\BackendUrlRule;
use skeeks\cms\shop\models\ShopStore;
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class CashierUrlRule extends BackendUrlRule
{
    const STORE_PARAM_NAME = '__shop_store_id';
    /**
     * @param \yii\web\UrlManager $manager
     * @param string              $route
     * @param array               $params
     * @return bool|string
     */
    public function createUrl($manager, $route, $params)
    {
        /*print_r($route);
        print_r($params);
                die;*/

        if (!isset($params[self::STORE_PARAM_NAME])) {
            if (\Yii::$app->shop->backendShopStore) {
                $params[self::STORE_PARAM_NAME] = \Yii::$app->shop->backendShopStore->id;
            } else {
                /*$shopStore = ShopStore::find()->one();
                if ($shopStore) {
                    $params[self::STORE_PARAM_NAME] = $shopStore->id;
                }*/
            }
        }

        return parent::createUrl($manager, $route, $params);
    }

    /**
     * @param \yii\web\UrlManager $manager
     * @param \yii\web\Request    $request
     * @return array|bool
     */
    public function parseRequest($manager, $request)
    {
        $params = $request->getQueryParams();
        if (isset($params[self::STORE_PARAM_NAME])) {
            \Yii::$app->shop->backendShopStore = ShopStore::find()->cmsSite()->andWhere(['id' => $params[self::STORE_PARAM_NAME]])->one();
        } else {
            $shopStore = ShopStore::find()->cmsSite()->one();
            if ($shopStore) {
                \Yii::$app->shop->backendShopStore = ShopStore::find()->cmsSite()->one();
            }
        }

        return parent::parseRequest($manager, $request);
    }
}