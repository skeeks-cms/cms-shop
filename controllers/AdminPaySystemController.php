<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */
namespace skeeks\cms\shop\controllers;

use skeeks\cms\components\Cms;
use skeeks\cms\grid\BooleanColumn;
use skeeks\cms\models\CmsAgent;
use skeeks\cms\models\CmsContent;
use skeeks\cms\modules\admin\actions\modelEditor\AdminMultiModelEditAction;
use skeeks\cms\modules\admin\controllers\AdminModelEditorController;
use skeeks\cms\modules\admin\traits\AdminModelEditorStandartControllerTrait;
use skeeks\cms\shop\models\ShopOrderStatus;
use skeeks\cms\shop\models\ShopPaySystem;
use skeeks\cms\shop\models\ShopPersonType;
use yii\grid\DataColumn;
use yii\helpers\ArrayHelper;

/**
 * Class AdminPaySystemController
 * @package skeeks\cms\shop\controllers
 */
class AdminPaySystemController extends AdminModelEditorController
{
    use AdminModelEditorStandartControllerTrait;

    public function init()
    {
        $this->name                     = \skeeks\cms\shop\Module::t('app', 'Payment systems');
        $this->modelShowAttribute       = "name";
        $this->modelClassName           = ShopPaySystem::className();

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
                        'name',
                        'priority',

                        [
                            'class'         => DataColumn::className(),
                            'attribute'     => "personTypeIds",
                            'filter'        => false,
                            'value'         => function(ShopPaySystem $model)
                            {
                                return implode(", ", ArrayHelper::map($model->personTypes, 'id', 'name'));
                            }
                        ],

                        [
                            'class'         => BooleanColumn::className(),
                            'attribute'     => "active"
                        ]
                    ],
                ]
            ]
        );
    }

}
