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
use skeeks\cms\shop\models\ShopVat;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class AdminVatController extends AdminModelEditorController
{
    public function init()
    {
        $this->name = \Yii::t('skeeks/shop/app', 'VAT rates');
        $this->modelShowAttribute = "name";
        $this->modelClassName = ShopVat::class;

        parent::init();
    }

}
