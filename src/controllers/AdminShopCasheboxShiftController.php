<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */

namespace skeeks\cms\shop\controllers;

use skeeks\cms\backend\actions\BackendGridModelAction;
use skeeks\cms\backend\actions\BackendModelAction;
use skeeks\cms\backend\BackendController;
use skeeks\cms\backend\controllers\BackendModelStandartController;
use skeeks\cms\backend\grid\DefaultActionColumn;
use skeeks\cms\grid\BooleanColumn;
use skeeks\cms\grid\DateTimeColumnData;
use skeeks\cms\grid\UserColumnData;
use skeeks\cms\models\CmsAgent;
use skeeks\cms\rbac\CmsManager;
use skeeks\cms\shop\models\queries\ShopCasheboxShiftQuery;
use skeeks\cms\shop\models\ShopCachebox;
use skeeks\cms\shop\models\ShopCasheboxShift;
use skeeks\cms\shop\models\ShopStore;
use skeeks\cms\widgets\AjaxSelectModel;
use skeeks\yii2\form\fields\BoolField;
use skeeks\yii2\form\fields\FieldSet;
use skeeks\yii2\form\fields\NumberField;
use skeeks\yii2\form\fields\WidgetField;
use yii\base\Event;
use yii\bootstrap\Alert;
use yii\helpers\ArrayHelper;
use yii\helpers\UnsetArrayValue;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class AdminShopCasheboxShiftController extends BackendModelStandartController
{
    public function init()
    {
        $this->name = "Смены";
        $this->modelShowAttribute = "asText";
        $this->modelClassName = ShopCasheboxShift::class;

        $this->generateAccessActions = false;
        $this->permissionName = CmsManager::PERMISSION_ROLE_ADMIN_ACCESS;

        parent::init();
    }


    /**
     * @inheritdoc
     */
    public function actions()
    {
        return ArrayHelper::merge(parent::actions(), [
            "view" => [
                'class'    => BackendModelAction::class,
                'priority' => 80,
                'name'     => 'Просмотр',
                'icon'     => 'fas fa-info-circle',
            ],

            'payments' => [
                'class'    => BackendModelAction::class,
                'name'     => 'Платежи',
                'priority' => 90,
                'callback' => [$this, 'payments'],
                'icon'     => 'fas fa-credit-card',
            ],

            'orders' => [
                'class'    => BackendModelAction::class,
                'name'     => 'Продажи',
                'priority' => 90,
                'callback' => [$this, 'orders'],
                'icon'     => 'fas fa-credit-card',
            ],

            'checks' => [
                'class'    => BackendModelAction::class,
                'name'     => 'Чеки',
                'priority' => 90,
                'callback' => [$this, 'checks'],
                'icon'     => 'fas fa-credit-card',
            ],

            'index' => [
                'on beforeRender' => function (Event $e) {
                    /*$e->content = Alert::widget([
                        'closeButton' => false,
                        'options'     => [
                            'class' => 'alert-default',
                        ],

                        'body' => <<<HTML
Все смены
HTML
                        ,
                    ]);*/
                },
                "filters"         => false,
                "backendShowings" => false,
                'grid'            => [
                    'on init'        => function (Event $e) {
                        /**
                         * @var $dataProvider ActiveDataProvider
                         * @var $query ShopCasheboxShiftQuery
                         */
                        $query = $e->sender->dataProvider->query;

                        $query
                            ->innerJoinWith('shopCashebox as shopCashebox')
                            ->innerJoinWith('shopCashebox.shopStore as shopStore')
                            ->andWhere(['shopStore.cms_site_id' => \Yii::$app->skeeks->site->id]);
                        //$query->andWhere(['is_supplier' => 0]);
                    },
                    'defaultOrder'   => [
                        'id' => SORT_DESC,
                    ],
                    'visibleColumns' => [

                        /*'checkbox',*/
                        'actions',

                        //'id',
                        'shift_number',

                        'created_at',
                        'closed_at',

                        'created_by',

                        'shop',
                        'custom',
                    ],
                    'columns'        => [
                        'created_at' => [
                            'value'         => function(ShopCasheboxShift $model) {
                                return $model->created_at ? \Yii::$app->formatter->asDatetime($model->created_at) : "";
                            }
                        ],
                        'closed_at' => [
                            'value'         => function(ShopCasheboxShift $model) {
                                return $model->closed_at ? \Yii::$app->formatter->asDatetime($model->closed_at) : "";
                            }
                        ],
                        'shift_number' => [
                            'class'         => DefaultActionColumn::class,
                            'viewAttribute'         => "asText"
                        ],
                        'created_by' => [
                            'class'         => UserColumnData::class
                        ],
                        'custom' => [
                            'label'         => 'Касса',
                            'attribute'         => 'shop_cashebox_id',
                            'format'         => 'raw',
                            'value' => function(ShopCasheboxShift $casheboxShift) {
                                return $casheboxShift->shopCashebox->name;
                            },
                        ],
                        'shop' => [
                            'label'         => 'Магазин',
                            'attribute'         => 'shop_cashebox_id',
                            'format'         => 'raw',
                            'value' => function(ShopCasheboxShift $casheboxShift) {
                                return $casheboxShift->shopCashebox->shopStore->name;
                            },
                        ],
                        /*'is_active' => [
                            'class'      => BooleanColumn::class,
                            'trueValue'  => 1,
                            'falseValue' => 1,
                        ],

                        'name' => [
                            'class'         => DefaultActionColumn::class,
                            'viewAttribute' => 'asText',
                        ],*/

                    ],
                ],
            ],

            "create" => new UnsetArrayValue(),
            "update" => new UnsetArrayValue(),
            "delete-multi" => new UnsetArrayValue(),
            "delete" => new UnsetArrayValue(),
        ]);
    }

    public function checks()
    {
        if ($controller = \Yii::$app->createController('/shop/admin-shop-check')) {
            /**
             * @var $controller BackendController
             * @var $indexAction BackendGridModelAction
             */
            $controller = $controller[0];
            $controller->actionsMap = [
                'index' => [
                    'configKey' => $this->action->uniqueId,
                ],
            ];

            if ($indexAction = ArrayHelper::getValue($controller->actions, 'index')) {
                $indexAction->url = $this->action->urlData;
                $indexAction->filters = false;
                $indexAction->backendShowings = false;
                $visibleColumns = $indexAction->grid['visibleColumns'];
                //ArrayHelper::removeValue($visibleColumns, 'shop_order_id');
                $indexAction->grid['visibleColumns'] = $visibleColumns;
                $indexAction->grid['columns']['actions']['isOpenNewWindow'] = true;
                $indexAction->grid['on init'] = function (Event $e) {
                    /**
                     * @var $query ActiveQuery
                     */
                    $query = $e->sender->dataProvider->query;
                    $query->andWhere([
                        'shop_cashebox_shift_id' => $this->model->id,
                    ]);
                };



                return $indexAction->run();
            }
        }

        return '1';
    }


    public function payments()
    {
        if ($controller = \Yii::$app->createController('/shop/admin-payment')) {
            /**
             * @var $controller BackendController
             * @var $indexAction BackendGridModelAction
             */
            $controller = $controller[0];
            $controller->actionsMap = [
                'index' => [
                    'configKey' => $this->action->uniqueId,
                ],
            ];

            if ($indexAction = ArrayHelper::getValue($controller->actions, 'index')) {
                $indexAction->url = $this->action->urlData;
                $indexAction->filters = false;
                $indexAction->backendShowings = false;
                $visibleColumns = $indexAction->grid['visibleColumns'];
                //ArrayHelper::removeValue($visibleColumns, 'shop_order_id');
                $indexAction->grid['visibleColumns'] = $visibleColumns;
                $indexAction->grid['columns']['actions']['isOpenNewWindow'] = true;
                $indexAction->grid['on init'] = function (Event $e) {
                    /**
                     * @var $query ActiveQuery
                     */
                    $query = $e->sender->dataProvider->query;
                    $query->andWhere([
                        'shop_cashebox_shift_id' => $this->model->id,
                    ]);
                };



                return $indexAction->run();
            }
        }

        return '1';
    }

    public function orders()
    {
        if ($controller = \Yii::$app->createController('/shop/admin-order')) {
            /**
             * @var $controller BackendController
             * @var $indexAction BackendGridModelAction
             */
            $controller = $controller[0];
            $controller->actionsMap = [
                'index' => [
                    'configKey' => $this->action->uniqueId,
                ],
            ];

            if ($indexAction = ArrayHelper::getValue($controller->actions, 'index')) {
                $indexAction->url = $this->action->urlData;
                $indexAction->filters = false;
                $indexAction->backendShowings = false;
                $visibleColumns = $indexAction->grid['visibleColumns'];
                //ArrayHelper::removeValue($visibleColumns, 'shop_order_id');
                $indexAction->grid['visibleColumns'] = $visibleColumns;
                $indexAction->grid['columns']['actions']['isOpenNewWindow'] = true;
                $indexAction->grid['on init'] = function (Event $e) {
                    /**
                     * @var $query ActiveQuery
                     */
                    $query = $e->sender->dataProvider->query;
                    $query->andWhere([
                        'shop_cashebox_shift_id' => $this->model->id,
                    ]);
                };



                return $indexAction->run();
            }
        }

        return '1';
    }
}
