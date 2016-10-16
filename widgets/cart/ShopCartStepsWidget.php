<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 03.04.2015
 */
namespace skeeks\cms\shop\widgets\cart;
use yii\base\Widget;

/**
 * Class ShopCartWidget
 * @package skeeks\cms\shop\widgets\cart
 */
class ShopCartStepsWidget extends Widget
{
    public $viewFile = 'cart-steps';

    public $options = [];

    public function init()
    {
        parent::init();

        $this->options['id'] = $this->id;
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
