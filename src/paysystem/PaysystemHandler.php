<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */

namespace skeeks\cms\shop\paysystem;

use skeeks\cms\IHasConfigForm;
use skeeks\cms\shop\models\ShopBill;
use skeeks\cms\shop\models\ShopPayment;
use skeeks\cms\traits\HasComponentDescriptorTrait;
use skeeks\cms\traits\TConfigForm;
use yii\base\Model;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
abstract class PaysystemHandler extends Model implements IHasConfigForm
{
    use HasComponentDescriptorTrait;
    use TConfigForm;

    /**
     * @param ShopPayment $shopPayment
     * @return bool
     */
    public function actionPay(ShopPayment $shopPayment)
    {
        return true;
    }
}