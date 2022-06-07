<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */

namespace skeeks\cms\shop\delivery;

use skeeks\cms\money\Money;
use skeeks\cms\shop\models\ShopOrder;
use yii\base\Widget;

/**
 *
 * @property Money $money
 *
 * @author Semenov Alexander <semenov@skeeks.com>
 */
abstract class DeliveryCheckoutWidget extends Widget
{
    public $viewFile = "checkout";

    /**
     * @var ShopOrder
     */
    public $shopOrder = null;

    /**
     * @var DeliveryHandler
     */
    public $deliveryHandler = null;

    public function run()
    {
        return $this->render($this->viewFile);
    }
}