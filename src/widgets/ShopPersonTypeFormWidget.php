<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 22.09.2015
 */

namespace skeeks\cms\shop\widgets;

use skeeks\cms\shop\models\ShopBuyer;
use skeeks\cms\shop\models\ShopPersonType;
use yii\base\Widget;

/**
 * Class ShopGlobalWidget
 * @package skeeks\cms\shop\widgets
 */
class ShopPersonTypeFormWidget extends Widget
{
    public $clientOptions = [];

    public $viewFile = 'shop-person-type-form-widget';

    /**
     * @var ShopPersonType
     */
    public $shopPersonType = null;

    /**
     * @var ShopBuyer
     */
    public $shopBuyer = null;

    public function init()
    {
        parent::init();

        if (!$this->shopPersonType) {
            $this->shopPersonType = \Yii::$app->shop->cart->personType;
        }

        if (!$this->shopBuyer) {
            $this->shopBuyer = \Yii::$app->shop->cart->buyer;
        }

        if ($this->shopBuyer) {
            $this->shopPersonType = $this->shopBuyer->shopPersonType;
        } else {
            if ($this->shopPersonType) {
                $this->shopBuyer = $this->shopPersonType->createModelShopBuyer();
            }
        }
    }

    /**
     * @return string
     */
    public function run()
    {
        return $this->render($this->viewFile, [
            'widget' => $this,
        ]);
    }
}
