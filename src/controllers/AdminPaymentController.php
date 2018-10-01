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
use skeeks\cms\shop\models\ShopBill;
use skeeks\cms\shop\models\ShopOrderChange;
use skeeks\cms\shop\models\ShopOrderStatus;
use skeeks\cms\shop\models\ShopPayment;
use yii\helpers\ArrayHelper;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class AdminPaymentController extends BackendModelStandartController
{
    public function init()
    {
        $this->name = \Yii::t('skeeks/shop/app', 'Платежи по заказам');
        $this->modelShowAttribute = "name";
        $this->modelClassName = ShopPayment::class;

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
                        //'checkbox',
                        //'actions',
                        'id',

                        'created_at',

                        'shop_buyer_id',
                        'shop_order_id',

                        'shop_pay_system_id',

                        'amount',

                        'comment',

                    ],
                    'columns'        => [

                        'created_at'           => [
                            'class' => DateTimeColumnData::class,
                        ],
                        'closed_at'           => [
                            'class' => DateTimeColumnData::class,
                        ],
                        'amount'           => [
                            'value' => function(ShopPayment $shopPayment) {
                                return $shopPayment->money;
                            },
                        ],
                    ],
                ]
            ]
            /*"index" => [
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
                        'color',

                    ],
                    'columns'        => [
                        'name'           => [
                            'value' => function (ShopOrderStatus $shopOrderStatus) {
                                return \yii\helpers\Html::label($shopOrderStatus->name, null, [
                                    'style' => "background: {$shopOrderStatus->color}",
                                    'class' => "label",
                                ]);
                            },
                        ],
                    ],

                ],
            ],*/

        ]);

        ArrayHelper::remove($result, "create");
        ArrayHelper::remove($result, "update");
        ArrayHelper::remove($result, "delete");
        ArrayHelper::remove($result, "delete-multi");

        return $result;
    }
}
