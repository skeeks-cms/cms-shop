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
            'backend-add-product'   => UrlHelper::construct('shop/cart/add-product')->toString(),
            'backend-remove-basket' => UrlHelper::construct('shop/cart/remove-basket')->toString(),
            'backend-update-basket' => UrlHelper::construct('shop/cart/update-basket')->toString(),
            'backend-clear-cart'    => UrlHelper::construct('shop/cart/clear')->toString()
        ];
    }

    /**
     * Подготовка данных для шаблона
     * @return $this
     */
    public function run()
    {
        if (static::$isRegisteredAssets === false)
        {
            ShopAsset::register($this->getView());
            $options = $this->clientOptions;
            $options = Json::encode($options);

            $this->getView()->registerJs(<<<JS
    (function(sx, $, _)
    {
        sx.Shop = new sx.classes.shop.App($options);
    })(sx, sx.$, sx._);
JS
);
            static::$isRegisteredAssets = true;
        }

        return parent::run();
    }


}
