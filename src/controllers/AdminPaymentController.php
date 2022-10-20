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
use skeeks\cms\grid\UserColumnData;
use skeeks\cms\models\CmsAgent;
use skeeks\cms\shop\models\ShopOrderChange;
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
                        'shop_order_id',
                    ],
                ],

                'grid' => [
                    'defaultOrder' => [
                        'created_at' => SORT_DESC,
                    ],

                    'visibleColumns' => [
                        //'checkbox',
                        'actions',
                        'id',

                        'created_at',
                        'shop_order_id',

                        'amount',

                        'cms_user_id',

                        'shop_pay_system_id',
                        'shop_check_id',

                        'comment',

                    ],
                    'columns'        => [

                        'created_at' => [
                            'class' => DateTimeColumnData::class,
                            'view_type' => DateTimeColumnData::VIEW_DATE,
                        ],
                        'cms_user_id' => [
                            'class' => UserColumnData::class,
                        ],
                        'amount' => [
                            'value' => function(ShopPayment $shopPayment) {
                                if ($shopPayment->is_debit) {
                                    return "<span style='color: green;'>+{$shopPayment->money}</span>";
                                } else {
                                    return "<span style='color: red;'>-{$shopPayment->money}</span>";
                                }
                            }
                        ],
                        'comment' => [
                            'value' => function(ShopPayment $shopPayment) {
                                return "<span style='color: gray;'>{$shopPayment->comment}</span>";
                            }
                        ],

                        'shop_order_id' => [
                            'value'         => function(ShopPayment $shopPayment) {
                                if ($shopPayment->shopOrder) {
                                    return \skeeks\cms\backend\widgets\AjaxControllerActionsWidget::widget([
                                        'controllerId'            => '/shop/admin-order',
                                        'modelId'                 => $shopPayment->shopOrder->id,
                                        'content'                 => $shopPayment->shopOrder->asText,
                                        'isRunFirstActionOnClick' => true,
                                        'options'                 => [
                                            'class' => 'btn btn-xs btn-default',
                                            //'style' => 'cursor: pointer; border-bottom: 1px dashed;',
                                        ],
                                    ]);
                                } else {
                                    return '';
                                }
                            },
                        ],

                        'shop_check_id' => [
                            'value'         => function(ShopPayment $shopPayment) {
                                if ($shopPayment->shopCheck) {
                                    return \skeeks\cms\backend\widgets\AjaxControllerActionsWidget::widget([
                                        'controllerId'            => '/shop/admin-shop-check',
                                        'modelId'                 => $shopPayment->shopCheck->id,
                                        'content'                 => $shopPayment->shopCheck->asText,
                                        'isRunFirstActionOnClick' => true,
                                        'options'                 => [
                                            'class' => 'btn btn-xs btn-default',
                                            //'style' => 'cursor: pointer; border-bottom: 1px dashed;',
                                        ],
                                    ]);
                                } else {
                                    return '';
                                }
                            },
                        ],

                        'shop_pay_system_id' => [
                            'value' => function(ShopPayment $shopPayment) {
                                $data = [];
                                if ($shopPayment->shop_store_id) {
                                    $data[] = 'Оплата в магазине (' . $shopPayment->shopStore->name . ')';
                                    $data[] = "<span style='color: gray;'>{$shopPayment->shopStorePaymentTypeAsText}</span>";
                                    if ($shopPayment->shopCashebox) {
                                        $data[] = "<span style='color: gray;'>Касса: {$shopPayment->shopCashebox->name}</span>";
                                    }
                                    if ($shopPayment->shopCasheboxShift) {
                                        $data[] = "<span style='color: gray;'>{$shopPayment->shopCasheboxShift->asText}</span>";
                                    }


                                } else {
                                    $data[] = 'Оплата через сайт';
                                    if ($shopPayment->shopPaySystem) {
                                        $data[] = "<span style='color: gray;'>{$shopPayment->shopPaySystem->name}</span>";
                                    }
                                }

                                return implode("<br />", $data);
                            }
                        ],
                    ],
                ],
            ],
        ]);

        ArrayHelper::remove($result, "create");
        ArrayHelper::remove($result, "update");
        ArrayHelper::remove($result, "delete");
        ArrayHelper::remove($result, "delete-multi");

        return $result;
    }
}
