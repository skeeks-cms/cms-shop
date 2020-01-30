<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

namespace skeeks\cms\shop\controllers;

use skeeks\cms\models\CmsAgent;
use skeeks\cms\modules\admin\controllers\AdminModelEditorController;
use skeeks\cms\shop\models\ShopDiscountCoupon;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class AdminDiscountCouponController extends AdminModelEditorController
{
    public function init()
    {
        $this->name = \Yii::t('skeeks/shop/app', 'Discount coupons');
        $this->modelShowAttribute = "id";
        $this->modelClassName = ShopDiscountCoupon::class;

        parent::init();
    }
}
