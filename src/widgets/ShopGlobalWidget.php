<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 22.09.2015
 */

namespace skeeks\cms\shop\widgets;

use skeeks\cms\helpers\UrlHelper;
use skeeks\cms\shop\assets\ShopAsset;
use yii\base\Widget;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\web\View;

/**
 * Class ShopGlobalWidget
 * @package skeeks\cms\shop\widgets
 */
class ShopGlobalWidget extends Widget
{
    static public $isRegisteredAssets = false;

    public $clientOptions = [];

    public function init()
    {
        parent::init();
        $this->clientOptions = ArrayHelper::merge($this->baseClientOptions(), $this->clientOptions);
    }

    /**
     * @return array
     */
    public function baseClientOptions()
    {
        return [
            'backend-order-update'            => UrlHelper::construct('/shop/cart/order-update')->toString(),

            'backend-add-product'            => UrlHelper::construct('/shop/cart/add-product')->toString(),
            'backend-add-products'            => UrlHelper::construct('/shop/cart/add-products')->toString(),
            'backend-remove-basket'          => UrlHelper::construct('/shop/cart/remove-basket')->toString(),
            'backend-update-basket'          => UrlHelper::construct('/shop/cart/update-basket')->toString(),
            'backend-clear-cart'             => UrlHelper::construct('/shop/cart/clear')->toString(),
            'backend-remove-discount-coupon' => UrlHelper::construct('/shop/cart/remove-discount-coupon')->toString(),
            'backend-add-discount-coupon'    => UrlHelper::construct('/shop/cart/add-discount-coupon')->toString(),

            'backend-favorite-add-product'    => UrlHelper::construct('/shop/favorite/add-product')->toString(),
            'backend-favorite-remove-product' => UrlHelper::construct('/shop/favorite/remove-product')->toString(),
            'backend-favorite'                => UrlHelper::construct('/shop/favorite')->toString(),

            'backend-compare-add-product'    => UrlHelper::construct('/shop/compare/add-product')->toString(),
            'backend-compare-remove-product' => UrlHelper::construct('/shop/compare/remove-product')->toString(),
            'backend-compare'                => UrlHelper::construct('/shop/compare')->toString(),

            'currencyCode' => \Yii::$app->money->currency_code,
        ];
    }

    /**
     * Подготовка данных для шаблона
     * @return $this
     */
    public function run()
    {
        if (static::$isRegisteredAssets === false && !\Yii::$app->request->isPjax) {
            ShopAsset::register($this->getView());
            $options = (array)$this->clientOptions;
            $options['cartData'] = \Yii::$app->shop->shopUser->shopOrder->jsonSerialize();

            $options = Json::encode($options);

            $this->getView()->registerJs(<<<JS
    (function(sx, $, _)
    {
        sx.Shop = new sx.classes.shop.App($options);
    })(sx, sx.$, sx._);
JS
                , View::POS_END
            );
            static::$isRegisteredAssets = true;
        }

        return parent::run();
    }


}
