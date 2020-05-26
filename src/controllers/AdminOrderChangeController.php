<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

namespace skeeks\cms\shop\controllers;

use skeeks\cms\backend\controllers\BackendModelStandartController;
use skeeks\cms\grid\DateTimeColumnData;
use skeeks\cms\models\CmsAgent;
use skeeks\cms\shop\models\ShopOrderChange;
use skeeks\cms\shop\models\ShopOrderLog;
use skeeks\cms\shop\models\ShopOrderStatus;
use yii\helpers\ArrayHelper;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class AdminOrderChangeController extends BackendModelStandartController
{
    public function init()
    {
        $this->name = \Yii::t('skeeks/shop/app', 'История изменения заказов');
        $this->modelShowAttribute = "name";
        $this->modelClassName = ShopOrderLog::class;

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        $result = ArrayHelper::merge(parent::actions(), [

            "index" => [
                "filters" => [
                    "visibleFilters" => [
                        //'id',
                        'shop_order_id'
                    ]
                ],

                'grid'    => [
                    'defaultOrder' => [
                        'created_at' => SORT_DESC,
                    ],

                    'visibleColumns' => [
                        'checkbox',
                        'actions',
                        //'id',

                        'created_at',

                        'shop_order_id',

                        'action_type',

                    ],

                    'columns'        => [
                        'created_at'           => [
                            'class' => DateTimeColumnData::class,
                        ],
                        'action_type'           => [
                            'value' => function(ShopOrderLog $shopOrderChange) {
                                return $shopOrderChange->typeAsText;
                            },
                        ],
                    ],
                ]
            ]
        ]);

        ArrayHelper::remove($result, "create");
        ArrayHelper::remove($result, "update");
        ArrayHelper::remove($result, "delete");
        ArrayHelper::remove($result, "delete-multi");

        return $result;
    }
}
