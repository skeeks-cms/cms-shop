<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 03.04.2015
 */

namespace skeeks\cms\shop\widgets\cart;

use skeeks\cms\shop\widgets\ShopGlobalWidget;
use skeeks\cms\widgets\base\hasTemplate\WidgetHasTemplate;
use yii\base\Widget;
use yii\helpers\ArrayHelper;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class ShopCartWidget extends Widget
{
    /**
     * @var null Файл в котором будет реднериться виджет
     */
    public $viewFile = "default";

    /**
     * @deprecated 
     * @var string 
     */
    public $namespace = "";

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

        if ($this->viewFile) {
            return $this->render($this->viewFile, [
                'widget' => $this,
            ]);
        } else {
            return \Yii::t('skeeks/cms', "Template not found");
        }
    }


}
