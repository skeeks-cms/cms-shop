<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 17.05.2015
 */
namespace skeeks\cms\shop\controllers;

use skeeks\cms\models\CmsTreeTypeProperty;
use skeeks\cms\modules\admin\controllers\AdminModelEditorController;
use skeeks\cms\relatedProperties\models\RelatedPropertyModel;
use skeeks\cms\shop\models\ShopPersonTypeProperty;
use skeeks\cms\shop\models\ShopPersonTypePropertyEnum;
use yii\helpers\ArrayHelper;

/**
 * Class AdminPersonTypePropertyEnumController
 * @package skeeks\cms\controllers
 */
class AdminPersonTypePropertyEnumController extends AdminModelEditorController
{
    public function init()
    {
        $this->name                   = skeeks\cms\shop\Module::t('app', 'Control of property values payer');
        $this->modelShowAttribute      = "value";
        $this->modelClassName          = ShopPersonTypePropertyEnum::className();

        parent::init();

    }

}
