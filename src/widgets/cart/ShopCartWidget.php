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
use skeeks\cms\shop\widgets\ShopGlobalWidget;
use skeeks\cms\widgets\base\hasTemplate\WidgetHasTemplate;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * Class ShopCartWidget
 * @package skeeks\cms\shop\widgets\cart
 */
class ShopCartWidget extends WidgetRenderable
{
    /**
     * Подключить стандартные скрипты
     *
     * @var bool
     */
    public $allowRegisterAsset = true;

    /**
     * Глобавльные опции магазина
     *
     * @var array
     */
    public $shopClientOptions = [];

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
     * Подготовка данных для шаблона
     * @return $this
     */
    public function run()
    {
        if ($this->allowRegisterAsset) {
            ShopGlobalWidget::widget(['clientOptions' => $this->shopClientOptions]);
        }

        return parent::run();
    }


}
