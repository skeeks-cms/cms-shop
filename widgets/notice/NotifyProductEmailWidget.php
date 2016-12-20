<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 21.12.2016
 */
namespace skeeks\cms\shop\widgets\notice;
use yii\base\Widget;
use yii\bootstrap\Modal;

/**
 * Class NotifyProductEmailWidget
 * @package skeeks\cms\shop\widgets
 */
class NotifyProductEmailWidget extends Widget
{
    public $options     = null;

    public $product_id  = null;

    /**
     * Подготовка данных для шаблона
     * @return $this
     */
    public function run()
    {
        return Modal::widget([
            'header' => '<h2>Hello world</h2>',
            'toggleButton' => ['label' => 'click me'],
        ]);
    }


}
