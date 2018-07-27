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
use skeeks\cms\modules\admin\traits\AdminModelEditorStandartControllerTrait;
use skeeks\cms\shop\components\CartComponent;
use skeeks\cms\shop\models\ShopFuser;

/**
 * Class AdminFuserController
 * @package skeeks\cms\shop\controllers
 */
class AdminFuserController extends AdminModelEditorController
{
    use AdminModelEditorStandartControllerTrait;

    public function init()
    {
        $this->name = \Yii::t('skeeks/shop/app', 'Baskets');
        $this->modelShowAttribute = "name";
        $this->modelClassName = ShopFuser::className();

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        $actions = parent::actions();;

        unset($actions['create']);
        unset($actions['update']);

        return $actions;
    }

}
