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
        
        $this->permissionName = "shop/admin-order";
        
        $this->generateAccessActions = false;

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
                        'actions',
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
                        /*'paid_at'           => [
                            'value' => function (ShopPayment $shopPayment, $key) {
                                $reuslt = "<div>";
                                if ($shopPayment->paid_at) {
                                    $this->view->registerJs(<<<JS
$('tr[data-key={$key}]').addClass('sx-tr-green');
JS
                                    );

                                    $this->view->registerCss(<<<CSS
tr.sx-tr-green, tr.sx-tr-green:nth-of-type(odd), tr.sx-tr-green td
{
background: #d5ffd5 !important;
}
CSS
                                    );
                                    $reuslt = "<div style='color: green;'>";
                                }

                                $reuslt .= $shopPayment->paid_at ? \Yii::$app->formatter->asDatetime($shopPayment->paid_at) : "-";
                                $reuslt .= "</div>";
                                return $reuslt;
                            },
                        ],*/
                        'amount'           => [
                            'value' => function(ShopPayment $shopPayment) {
                                return $shopPayment->money;
                            },
                        ],
                    ],
                ]
            ]
        ]);

        ArrayHelper::remove($result, "create");
        ArrayHelper::remove($result, "update");
        //ArrayHelper::remove($result, "delete");
        ArrayHelper::remove($result, "delete-multi");

        return $result;
    }
}
