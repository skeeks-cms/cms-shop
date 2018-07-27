<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

namespace skeeks\cms\shop\controllers;

use skeeks\cms\models\CmsAgent;
use skeeks\cms\models\CmsUser;
use skeeks\cms\modules\admin\controllers\AdminModelEditorController;
use skeeks\cms\modules\admin\traits\AdminModelEditorStandartControllerTrait;

/**
 * Class AdminBuyerUserController
 * @package skeeks\cms\shop\controllers
 */
class AdminBuyerUserController extends AdminModelEditorController
{
    use AdminModelEditorStandartControllerTrait;

    public function init()
    {
        $this->name = \Yii::t('skeeks/shop/app', 'Buyers');
        $this->modelShowAttribute = "displayName";
        $this->modelClassName = CmsUser::className();

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        $actions = parent::actions();
        unset($actions['create']);
        unset($actions['delete']);
        if (isset($actions['related-properties'])) {
            unset($actions['related-properties']);
        }

        return $actions;
    }
}
