<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

namespace skeeks\cms\shop\controllers;

use skeeks\cms\backend\controllers\BackendModelStandartController;
use skeeks\cms\models\CmsAgent;
use skeeks\cms\shop\models\ShopOrderStatus;
use yii\helpers\ArrayHelper;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class AdminOrderStatusController extends BackendModelStandartController
{
    public function init()
    {
        $this->name = \Yii::t('skeeks/shop/app', 'Order statuses');
        $this->modelShowAttribute = "name";
        $this->modelClassName = ShopOrderStatus::class;

        $this->generateAccessActions = false;

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return ArrayHelper::merge(parent::actions(), [

            "index" => [
                "filters" => [
                    "visibleFilters" => [
                        'id',
                        'name',
                    ],
                ],
                'grid'    => [
                    'defaultOrder' => [
                        'priority' => SORT_ASC,
                    ],

                    'visibleColumns' => [
                        'checkbox',
                        'actions',
                        'id',

                        'name',

                        'description',

                        'priority',

                    ],
                    'columns'        => [
                        'name' => [
                            'value' => function (ShopOrderStatus $shopOrderStatus) {
                                return \yii\helpers\Html::label($shopOrderStatus->name, null, [
                                    'style' => "background: {$shopOrderStatus->color}",
                                    'class' => "label g-pl-5 g-pr-5",
                                ]);
                            },
                        ],
                    ],

                ],
            ],

        ]);
    }
}
