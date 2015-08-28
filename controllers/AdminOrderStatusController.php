<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */
namespace skeeks\cms\shop\controllers;

use skeeks\cms\components\Cms;
use skeeks\cms\models\CmsAgent;
use skeeks\cms\models\CmsContent;
use skeeks\cms\modules\admin\actions\modelEditor\AdminMultiModelEditAction;
use skeeks\cms\modules\admin\controllers\AdminModelEditorController;
use skeeks\cms\modules\admin\traits\AdminModelEditorStandartControllerTrait;
use skeeks\cms\shop\models\ShopOrderStatus;
use yii\helpers\ArrayHelper;

/**
 * Class AdminOrderStatusController
 * @package skeeks\cms\shop\controllers
 */
class AdminOrderStatusController extends AdminModelEditorController
{
    use AdminModelEditorStandartControllerTrait;

    public function init()
    {
        $this->name                     = "Статусы заказов";
        $this->modelShowAttribute       = "id";
        $this->modelClassName           = ShopOrderStatus::className();

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return ArrayHelper::merge(parent::actions(),
            [
                'index' =>
                [
                    "columns"      => [
                        'code',
                        'name',
                        'description',
                        'priority',
                    ],
                ]
            ]
        );
    }

}
