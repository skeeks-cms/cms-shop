<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 17.05.2015
 */

namespace skeeks\cms\shop\controllers;

use skeeks\cms\modules\admin\controllers\AdminModelEditorController;
use skeeks\cms\shop\models\ShopPersonTypePropertyEnum;

/**
 * Class AdminPersonTypePropertyEnumController
 * @package skeeks\cms\controllers
 */
class AdminPersonTypePropertyEnumController extends AdminModelEditorController
{
    public function init()
    {
        $this->name = \Yii::t('skeeks/shop/app', 'Control of property values payer');
        $this->modelShowAttribute = "value";
        $this->modelClassName = ShopPersonTypePropertyEnum::className();

        parent::init();

    }

}
