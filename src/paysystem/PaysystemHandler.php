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
     * @param ShopBill $shopBill
     * @return bool
     */
    public function actionPaymentResponse(ShopBill $shopBill)
    {
        return true;
        //return \Yii::$app->response->redirect(['shop/sberbank/order-form', 'key' => $shopOrder->key]);;
    }
}