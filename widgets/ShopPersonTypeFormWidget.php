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
use skeeks\cms\shop\models\ShopPersonType;
use yii\base\Widget;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * Class ShopGlobalWidget
 * @package skeeks\cms\shop\widgets
 */
class ShopPersonTypeFormWidget extends Widget
{
    public $clientOptions = [];

    public $viewFile        = 'shop-person-type-form-widget';

    /**
     * @var ShopPersonType
     */
    public $shopPersonType  = null;

    public function init()
    {
        parent::init();
    }

    /**
     * @return string
     */
    public function run()
    {
        return $this->render($this->viewFile, [
            'widget' => $this
        ]);
    }


}
