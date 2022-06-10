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
use yii\helpers\ArrayHelper;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class AdminBillController extends BackendModelStandartController
{
    public function init()
    {
        $this->name = \Yii::t('skeeks/shop/app', 'Счета по заказам');
        $this->modelShowAttribute = "name";
        $this->modelClassName = ShopBill::class;

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
                        'checkbox',
                        'actions',
                        'id',

                        'created_at',

                        'cms_user_id',
                        'shop_order_id',

                        'shop_pay_system_id',

                        'paid_at',
                        'closed_at',

                        'amount',

                        'description',
                        'go',

                    ],

                    'columns'        => [
                        'paid_at'           => [
                            'value' => function (ShopBill $shopBill, $key) {
                                $reuslt = "<div>";
                                if ($shopBill->paid_at) {
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

                                $reuslt .= $shopBill->paid_at ? \Yii::$app->formatter->asDatetime($shopBill->paid_at) : "-";
                                $reuslt .= "</div>";
                                return $reuslt;
                            },
                        ],
                        'created_at'           => [
                            'class' => DateTimeColumnData::class,
                        ],
                        'closed_at'           => [
                            'class' => DateTimeColumnData::class,
                        ],
                        'amount'           => [
                            'value' => function(ShopBill $shopBill) {
                                return $shopBill->money;
                            },
                        ],

                        'go'                   => [
                            'format' => "raw",
                            'value'  => function (ShopBill $shopBill) {
                                return \yii\helpers\Html::a('<i class="glyphicon glyphicon-arrow-right"></i>', $shopBill->url, [
                                    'target'    => '_blank',
                                    'title'     => \Yii::t('skeeks/cms', 'Watch to site (opens new window)'),
                                    'data-pjax' => '0',
                                    'class'     => 'btn btn-default btn-sm',
                                ]);
                            },
                        ],

                    ],
                ]
            ]
        ]);

        ArrayHelper::remove($result, "create");
        ArrayHelper::remove($result, "update");
        /*ArrayHelper::remove($result, "delete");*/
        /*ArrayHelper::remove($result, "delete-multi");*/

        return $result;
    }
}
