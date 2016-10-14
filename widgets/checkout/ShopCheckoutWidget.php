<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 14.10.2016
 */
namespace skeeks\cms\shop\widgets\checkout;

use yii\base\Widget;

/**
 * Class ShopCheckoutWidget
 *
 * @package skeeks\cms\shop\widgets\checkout
 */
class ShopCheckoutWidget extends Widget
{
    public static $autoIdPrefix = 'ShopCheckoutWidget';

    public $viewFile = 'default';

    public $options = [];

    public function init()
    {
        parent::init();

        $this->options['id'] = $this->id;
    }

    public function run()
    {
        return $this->render($this->viewFile);
    }
}
