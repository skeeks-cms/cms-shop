<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */

namespace skeeks\cms\shop\delivery;

use skeeks\cms\IHasConfigForm;
use skeeks\cms\shop\models\ShopDelivery;
use skeeks\cms\shop\models\ShopOrder;
use skeeks\cms\traits\HasComponentDescriptorTrait;
use skeeks\cms\traits\TConfigForm;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;

/**
 * @property Model $checkoutModel
 * 
 * @author Semenov Alexander <semenov@skeeks.com>
 */
abstract class DeliveryHandler extends Model implements IHasConfigForm
{
    use HasComponentDescriptorTrait;
    use TConfigForm;

    /**
     * @var string
     */
    public $checkoutModelClass = '';
    public $checkoutWidgetClass = '';
    public $checkoutWidgetConfig = [];


    /**
     * @var ShopOrder
     */
    public $shopOrder = null;

    /**
     * @var ShopDelivery
     */
    public $delivery = null;

    /**
     * @param ActiveForm $activeForm
     * @return string
     */
    public function renderWidget(ShopOrder $shopOrder)
    {
        $widgetClass = $this->checkoutWidgetClass;

        $config = ArrayHelper::merge($this->checkoutWidgetConfig, [
            'deliveryHandler' => $this,
            'shopOrder' => $shopOrder,
        ]);

        return $widgetClass::widget($config);
    }

    /**
     * @var null
     */
    protected $_checkoutModel = null;

    /**
     * @return Model
     */
    public function getCheckoutModel()
    {
        if ($this->_checkoutModel === null) {
            $class = $this->checkoutModelClass;
            $this->_checkoutModel = new $class();
            $this->_checkoutModel->delivery = $this->delivery;
        }

        return $this->_checkoutModel;
    }

    /**
     * @param Model $model
     * @return $this
     */
    /*public function setCheckoutModel(Model $model)
    {
        $this->_checkoutModel = $model;
        return $this;
    }*/
}