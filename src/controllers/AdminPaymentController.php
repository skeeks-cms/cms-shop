<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

namespace skeeks\cms\shop\controllers;

use skeeks\cms\backend\actions\BackendModelAction;
use skeeks\cms\backend\controllers\BackendModelStandartController;
use skeeks\cms\grid\DateTimeColumnData;
use skeeks\cms\grid\UserColumnData;
use skeeks\cms\models\CmsAgent;
use skeeks\cms\models\CmsUser;
use skeeks\cms\shop\models\ShopCashebox;
use skeeks\cms\shop\models\ShopCheck;
use skeeks\cms\shop\models\ShopOrder;
use skeeks\cms\shop\models\ShopOrderChange;
use skeeks\cms\shop\models\ShopPayment;
use skeeks\cms\shop\models\ShopPaySystem;
use skeeks\cms\shop\models\ShopStore;
use skeeks\cms\widgets\AjaxSelectModel;
use skeeks\yii2\form\fields\FieldSet;
use skeeks\yii2\form\fields\NumberField;
use skeeks\yii2\form\fields\SelectField;
use skeeks\yii2\form\fields\TextareaField;
use skeeks\yii2\form\fields\WidgetField;
use yii\base\Event;
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

            "view" => [
                'class'    => BackendModelAction::class,
                'priority' => 80,
                'name'     => 'Просмотр',
                'icon'     => 'fas fa-info-circle',
            ],

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

                    'on init' => function (Event $e) {
                        /**
                         * @var $dataProvider ActiveDataProvider
                         * @var $query ActiveQuery
                         */
                        $query = $e->sender->dataProvider->query;

                        $query->cmsSite();
                    },

                    'visibleColumns' => [
                        //'checkbox',
                        'actions',

                        'created_at',

                        'id',

                        'amount',
                        'shop_order_id',

                        'cms_user_id',

                        'shop_pay_system_id',
                        'shop_check_id',

                        'comment',

                    ],
                    'columns'        => [

                        'id'             => [
                            'value' => function(ShopPayment $model) {
                                return \yii\helpers\Html::a( ($model->is_debit ? "Поступление " : "Оплата") . " #{$model->id}", "#", [
                                    'class' => "sx-trigger-action",
                                ]);
                            }
                        ],

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

            'create' => [
                'fields'        => [$this, 'updateFields'],
                'buttons'         => ["save"],
            ]
        ]);

       /// ArrayHelper::remove($result, "create");
        ArrayHelper::remove($result, "update");
        ArrayHelper::remove($result, "delete");
        ArrayHelper::remove($result, "delete-multi");

        return $result;
    }

    public function updateFields($action)
    {
        $model = $action->model;

        $result = [
            'main'         => [
                'class'  => FieldSet::class,
                'name'   => \Yii::t('skeeks/shop/app', 'Main'),
                'fields' => [

                    'is_debit' => [
                        'class'       => SelectField::class,
                        'label' => 'Тип операции',
                        'items' => [
                            '1' => 'Нам поступили деньги/Внесли деньги в кассу',
                            '0' => 'Мы заплатили/выплатили/выдали из кассы',
                        ],
                    ],

                    'cms_user_id' => [
                        'class'        => WidgetField::class,
                        'label' => 'Контрагент',
                        'widgetClass'  => AjaxSelectModel::class,
                        'widgetConfig' => [
                            'modelClass' => CmsUser::class,
                            'searchQuery' => function($word = '') {
                                $query = CmsUser::find()->cmsSite();
                                if ($word) {
                                    $query->search($word);
                                }
                                return $query;
                            },
                        ],
                    ],

                    'amount' => [
                        'class'        => NumberField::class,
                    ],

                    'comment' => [
                        'class'        => TextareaField::class,
                    ],
                ],
            ],

            'shop'         => [
                'class'  => FieldSet::class,
                'name'   => "Связь с магазном",
                'fields' => [


                    'shop_store_id' => [
                        'class'        => WidgetField::class,
                        'widgetClass'  => AjaxSelectModel::class,
                        'widgetConfig' => [
                            'modelClass' => ShopStore::class,
                            'searchQuery' => function($word = '') {
                                $query = ShopStore::find()->isSupplier(false)->cmsSite();
                                if ($word) {
                                    $query->search($word);
                                }
                                return $query;
                            },
                        ],
                    ],

                    'shop_cashebox_id' => [
                        'class'        => WidgetField::class,
                        'widgetClass'  => AjaxSelectModel::class,
                        'widgetConfig' => [
                            'modelClass' => ShopCashebox::class,
                            'searchQuery' => function($word = '') {
                                $query = ShopCashebox::find()->cmsSite();
                                if ($word) {
                                    $query->search($word);
                                }
                                return $query;
                            },
                        ],
                    ],

                    'shop_store_payment_type' => [
                        'class'       => SelectField::class,
                        'label' => 'Тип оплаты в магазине',
                        'items' => ShopPayment::getShopStorePaymentTypes(),
                    ],

                ],
            ],

            'relations'         => [
                'class'  => FieldSet::class,
                'name'   => "Дополнительны связи",
                'fields' => [


                    'shop_order_id' => [
                        'class'        => WidgetField::class,
                        'label' => 'Заказ/Продажа/Возврат',
                        'widgetClass'  => AjaxSelectModel::class,
                        'widgetConfig' => [
                            'modelClass' => ShopOrder::class,
                            'searchQuery' => function($word = '') {
                                $query = ShopOrder::find()->cmsSite();
                                if ($word) {
                                    $query->search($word);
                                }
                                return $query;
                            },
                        ],
                    ],

                    'shop_check_id' => [
                        'class'        => WidgetField::class,
                        'label' => 'Чек',
                        'widgetClass'  => AjaxSelectModel::class,
                        'widgetConfig' => [
                            'modelClass' => ShopCheck::class,
                            'searchQuery' => function($word = '') {
                                $query = ShopCheck::find()->cmsSite();
                                if ($word) {
                                    $query->search($word);
                                }
                                return $query;
                            },
                        ],
                    ],

                    'shop_pay_system_id' => [
                        'class'        => WidgetField::class,
                        'label' => 'Способ олплаты на сайте',
                        'widgetClass'  => AjaxSelectModel::class,
                        'widgetConfig' => [
                            'modelClass' => ShopPaySystem::class,
                            'searchQuery' => function($word = '') {
                                $query = ShopPaySystem::find()->cmsSite();
                                if ($word) {
                                    $query->search($word);
                                }
                                return $query;
                            },
                        ],
                    ],
                ],
            ],

        ];

        return $result;
    }

}
