<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 03.04.2015
 */
namespace skeeks\cms\shop\widgets\cart;
use skeeks\cms\base\WidgetRenderable;
use skeeks\cms\helpers\UrlHelper;
use skeeks\cms\shop\assets\ShopAsset;
use skeeks\cms\widgets\base\hasTemplate\WidgetHasTemplate;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * Class ShopCartWidget
 * @package skeeks\cms\shop\widgets\cart
 */
class ShopCartWidget extends WidgetRenderable
{
    static public $isRegisteredAssets = false;

    /**
     * Подключить стандартные скрипты
     *
     * @var bool
     */
    public $allowRegisterAsset = true;

    public $clientOptions = [];

    public function init()
    {
        parent::init();

        $this->clientOptions = ArrayHelper::merge($this->baseClientOptions(), $this->clientOptions);
    }

    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            //[['allowRegisterAsset'], 'integer']
        ]);
    }

    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            //'allowRegisterAsset' => 'Подключить стандартные скрипты'
        ]);
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
            'backend-clear'         => UrlHelper::construct('shop/cart/clear-all')->toString()
        ];
    }

    /**
     * Подготовка данных для шаблона
     * @return $this
     */
    public function run()
    {
        if (static::$isRegisteredAssets === false && $this->allowRegisterAsset)
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
