<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 03.04.2015
 */
namespace skeeks\cms\shop\widgets\cart;

use skeeks\cms\shop\models\ShopFuser;
use yii\base\Widget;

/**
 * Class ShopCartWidget
 * @package skeeks\cms\shop\widgets\cart
 */
class ShopFullCartWidget extends Widget
{
    /**
     * @var array
     */
    public $options = [];

    /**
     * @var string
     */
    public $viewFile = 'full-cart';

    /**
     * @var ShopFuser
     */
    public $shopFuser = null;

    public function init()
    {
        parent::init();

        $this->options['id'] = $this->id;
        if (!$this->shopFuser)
        {
            $this->shopFuser = \Yii::$app->shop->shopFuser;
        }
    }

    /**
     * Подготовка данных для шаблона
     * @return $this
     */
    public function run()
    {
        return $this->render($this->viewFile);
    }


}
