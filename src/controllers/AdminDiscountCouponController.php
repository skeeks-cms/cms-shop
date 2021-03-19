<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

namespace skeeks\cms\shop\controllers;

use skeeks\cms\actions\backend\BackendModelMultiActivateAction;
use skeeks\cms\actions\backend\BackendModelMultiDeactivateAction;
use skeeks\cms\backend\actions\BackendGridModelRelatedAction;
use skeeks\cms\backend\controllers\BackendModelStandartController;
use skeeks\cms\backend\grid\DefaultActionColumn;
use skeeks\cms\grid\BooleanColumn;
use skeeks\cms\grid\UserColumnData;
use skeeks\cms\models\CmsAgent;
use skeeks\cms\models\CmsUser;
use skeeks\cms\shop\models\ShopDiscount;
use skeeks\cms\shop\models\ShopDiscountCoupon;
use skeeks\cms\shop\models\ShopOrder;
use skeeks\cms\widgets\AjaxSelectModel;
use skeeks\cms\widgets\GridView;
use skeeks\yii2\form\fields\BoolField;
use skeeks\yii2\form\fields\FieldSet;
use skeeks\yii2\form\fields\NumberField;
use skeeks\yii2\form\fields\WidgetField;
use yii\base\Event;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class AdminDiscountCouponController extends BackendModelStandartController
{
    public function init()
    {
        $this->name = \Yii::t('skeeks/shop/app', 'Discount coupons');
        $this->modelShowAttribute = "asText";
        $this->modelClassName = ShopDiscountCoupon::class;
        $this->permissionName = 'shop/admin-discount';

        parent::init();
    }


    /**
     * @inheritdoc
     */
    public function actions()
    {


        return ArrayHelper::merge(parent::actions(), [

            'index' => [
                'backendShowings' => false,

                "filters" => [
                    'visibleFilters' => [
                        //'id',
                        'coupon',
                        'shop_discount_id',
                    ],

                    'filtersModel' => [
                        'fields' => [
                            'coupon' => [
                                'isAllowChangeMode' => false,
                            ],
                        ],
                    ],
                ],

                "grid" => [
                    'on init'        => function (Event $e) {
                        /**
                         * @var $dataProvider ActiveDataProvider
                         * @var $query ActiveQuery
                         */
                        $query = $e->sender->dataProvider->query;

                        $query->andWhere([ShopDiscountCoupon::tableName() . '.shop_discount_id' => ShopDiscount::find()->from(['d' => ShopDiscount::tableName()])->cmsSite()->select(['d.id'])]);
                    },

                    /*'sortAttributes' => [
                        'countDomains' => [
                            'asc' => ['countDomains' => SORT_ASC],
                            'desc' => ['countDomains' => SORT_DESC],
                            'label' => 'Количество доменов',
                            'default' => SORT_ASC
                        ]
                    ],*/
                    'defaultOrder'   => [
                        'priority' => SORT_ASC,
                    ],
                    'visibleColumns' => [
                        'checkbox',
                        'actions',

                        'coupon',
                        //'shop_discount_id',
                        'cms_user_id',

                        'is_active',
                        'countOrders',
                    ],
                    'columns'        => [
                        /*'coupon'    => [
                            'class' => DefaultActionColumn::class,
                        ],*/
                        'is_active' => [
                            'class' => BooleanColumn::class,
                        ],
                        'cms_user_id' => [
                            'class' => UserColumnData::class,
                        ],

                        'coupon' => [
                            'value'                => function (ShopDiscountCoupon $model) {
                                return \yii\helpers\Html::a($model->coupon, "#", [
                                    'class' => "sx-trigger-action",
                                ]) . "<br /><small>{$model->shopDiscount->name}</small>" ;
                            },
                        ],
                        'countOrders' => [
                            'value'                => function (ShopDiscountCoupon $cmsSite) {
                                return $cmsSite->raw_row['countOrders'];
                            },
                            'attribute'            => 'countOrders',
                            'label'                => 'Использован, раз',
                            'headerOptions' => [
                                'style' => 'width: 100px;',
                            ],
                            'beforeCreateCallback' => function (GridView $gridView) {
                                $query = $gridView->dataProvider->query;

                                $qCount = ShopOrder::find()->from([
                                        'order' => ShopOrder::tableName(),
                                    ])
                                    ->isCreated()
                                    ->joinWith("shopOrder2discountCoupons as shopOrder2discountCoupons")
                                    ->joinWith("shopOrder2discountCoupons.discountCoupon as shopDiscountCoupons")
                                    ->select(["total" => "count(*)"])
                                    ->andWhere(['shopDiscountCoupons.id' => new Expression(ShopDiscountCoupon::tableName().".id")]);

                                $query->groupBy(ShopDiscountCoupon::tableName().".id");
                                $query->addSelect([
                                    'countOrders' => $qCount,
                                ]);

                                $gridView->sortAttributes['countOrders'] = [
                                    'asc'     => ['countOrders' => SORT_ASC],
                                    'desc'    => ['countOrders' => SORT_DESC],
                                    'label'   => 'Использован, раз',
                                    'default' => SORT_ASC,
                                ];
                            },
                        ],
                    ],
                ],
            ],

            "orders" => [
                'class'           => BackendGridModelRelatedAction::class,
                'accessCallback'  => true,
                'name'            => "Заказы",
                'icon'            => 'fa fa-list',
                'controllerRoute' => "/shop/admin-order",
                'priority'        => 600,

                'on gridInit'     => function ($e) {
                    /**
                     * @var $action BackendGridModelRelatedAction
                     */
                    $action = $e->sender;
                    $action->relatedIndexAction->backendShowings = false;
                    $visibleColumns = $action->relatedIndexAction->grid['visibleColumns'];


                    $action->relatedIndexAction->grid['on init'] = function (Event $e) {
                        /**
                         * @var $query ActiveQuery
                         *
                         */
                        $query = $e->sender->dataProvider->query;
                        $query->cmsSite()->isCreated()
                             ->joinWith("shopOrder2discountCoupons as shopOrder2discountCoupons")
                            ->andWhere(['shopOrder2discountCoupons.discount_coupon_id' => $this->model->id]);
                        ;

                    };


                    $action->relatedIndexAction->grid['visibleColumns'] = $visibleColumns;

                },
            ],


            "create" => [
                'fields' => [$this, 'updateFields'],
            ],

            "update" => [
                'fields' => [$this, 'updateFields'],
            ],

            "activate-multi" => [
                'class' => BackendModelMultiActivateAction::class,
            ],

            "deactivate-multi" => [
                'class' => BackendModelMultiDeactivateAction::class,
            ],
        ]);
    }


    public function updateFields($action)
    {
        $model = $action->model;

        $options = [];
        if (!$model->isNewRecord) {
            $options = [
                'disabled' => 'disabled'
            ];
        }

        $model->load(\Yii::$app->request->get());

        if ($model->isNewRecord && $model->shop_discount_id) {
            \Yii::$app->view->registerCss(<<<CSS
.field-shopdiscountcoupon-shop_discount_id {
    display: none;
}
CSS
            );
        }

        return [
            'main' => [
                'class'  => FieldSet::class,
                'name'   => 'Основное',
                'fields' => [
                    'is_active' => [
                        'class' => BoolField::class,
                        'allowNull' => false,
                    ],

                    'shop_discount_id' => [
                        'class'        => WidgetField::class,
                        'widgetClass'  => AjaxSelectModel::class,
                        'widgetConfig' => [
                            'modelClass' => ShopDiscount::class,
                            'options' => $options
                        ],

                    ],
                    'coupon' => [
                        'elementOptions' => $options
                    ],
                    'description',
                    'max_use'          => [
                        'class' => NumberField::class,
                    ],
                    'cms_user_id'          => [
                        'class' => WidgetField::class,
                        'widgetClass' => AjaxSelectModel::class,
                        'widgetConfig' => [
                            'modelClass' => \Yii::$app->user->identityClass,
                            'searchQuery' => function($word = '') {
                                $identityClass = \Yii::$app->user->identityClass;
                                $query = $identityClass::find();
                                if ($word) {
                                    $query->search($word);
                                }
                                return $query;
                            },
                        ]
                    ],
                ],
            ],


        ];
    }
}
