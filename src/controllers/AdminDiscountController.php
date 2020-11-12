<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */

namespace skeeks\cms\shop\controllers;

use skeeks\cms\actions\backend\BackendModelMultiActivateAction;
use skeeks\cms\actions\backend\BackendModelMultiDeactivateAction;
use skeeks\cms\backend\actions\BackendGridModelRelatedAction;
use skeeks\cms\backend\controllers\BackendModelStandartController;
use skeeks\cms\backend\grid\DefaultActionColumn;
use skeeks\cms\grid\BooleanColumn;
use skeeks\cms\models\CmsAgent;
use skeeks\cms\shop\models\ShopDiscount;
use skeeks\cms\shop\models\ShopDiscountCoupon;
use skeeks\cms\widgets\GridView;
use yii\base\Event;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class AdminDiscountController extends BackendModelStandartController
{
    public $notSubmitParam = 'sx-not-submit';

    public function init()
    {
        $this->name = \Yii::t('skeeks/shop/app', 'Discount goods');
        $this->modelShowAttribute = "asText";
        $this->modelClassName = ShopDiscount::class;

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
                        'name',
                    ],

                    'filtersModel' => [
                        'fields' => [
                            'name' => [
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
                        $query->cmsSite();
                    },

                    'defaultOrder'   => [
                        'is_active' => SORT_DESC,
                        'priority'  => SORT_ASC,
                    ],
                    'visibleColumns' => [
                        'checkbox',
                        'actions',

                        'name',

                        'value',

                        'is_active',

                        'priority',
                        'countCoupons',
                    ],
                    'columns'        => [
                        'name'      => [
                            'class' => DefaultActionColumn::class,
                        ],
                        'is_active' => [
                            'class' => BooleanColumn::class,
                        ],
                        'is_last'   => [
                            'class' => BooleanColumn::class,
                        ],
                        'priority'  => [
                            'headerOptions' => [
                                'style' => 'width: 100px;',
                            ],
                        ],

                        'countCoupons' => [
                            'value'                => function (ShopDiscount $cmsSite) {
                                return $cmsSite->raw_row['countCoupons'];
                            },
                            'attribute'            => 'countCoupons',
                            'label'                => 'Количество купонов',
                            'headerOptions' => [
                                'style' => 'width: 100px;',
                            ],
                            'beforeCreateCallback' => function (GridView $gridView) {
                                $query = $gridView->dataProvider->query;

                                $qCount = ShopDiscountCoupon::find()->from([
                                        'sdc' => ShopDiscountCoupon::tableName(),
                                    ])
                                    ->select(["total" => "count(*)"])
                                    ->where(['sdc.shop_discount_id' => new Expression(ShopDiscount::tableName().".id")]);

                                $query->groupBy(ShopDiscount::tableName().".id");
                                $query->addSelect([
                                    'countCoupons' => $qCount,
                                ]);

                                $gridView->sortAttributes['countCoupons'] = [
                                    'asc'     => ['countCoupons' => SORT_ASC],
                                    'desc'    => ['countCoupons' => SORT_DESC],
                                    'label'   => 'Количество купонов',
                                    'default' => SORT_ASC,
                                ];
                            },
                        ],

                        'value' => [
                            'headerOptions' => [
                                'style' => 'width: 100px;',
                            ],
                            'value'         => function (\skeeks\cms\shop\models\ShopDiscount $shopDiscount) {
                                if ($shopDiscount->value_type == \skeeks\cms\shop\models\ShopDiscount::VALUE_TYPE_P) {
                                    return \Yii::$app->formatter->asPercent($shopDiscount->value / 100);
                                } else {
                                    $money = new \skeeks\cms\money\Money((string)$shopDiscount->value, $shopDiscount->currency_code);
                                    return (string)$money;
                                }
                            },
                        ],
                    ],
                ],
            ],

            "coupons" => [
                'class'           => BackendGridModelRelatedAction::class,
                'accessCallback'  => true,
                'name'            => "Купоны",
                'icon'            => 'fa fa-list',
                'controllerRoute' => "/shop/admin-discount-coupon",
                'relation'        => ['shop_discount_id' => 'id'],
                'priority'        => 600,
                'on gridInit'     => function ($e) {
                    /**
                     * @var $action BackendGridModelRelatedAction
                     */
                    $action = $e->sender;
                    $action->relatedIndexAction->backendShowings = false;
                    $visibleColumns = $action->relatedIndexAction->grid['visibleColumns'];

                    ArrayHelper::removeValue($visibleColumns, 'shop_discount_id');
                    $action->relatedIndexAction->grid['visibleColumns'] = $visibleColumns;

                },
            ],

            /*"create" => [
                'fields' => [$this, 'updateFields'],
            ],

            "update" => [
                'fields' => [$this, 'updateFields'],
            ],*/

            "activate-multi" => [
                'class' => BackendModelMultiActivateAction::class,
            ],

            "deactivate-multi" => [
                'class' => BackendModelMultiDeactivateAction::class,
            ],
        ]);
    }

}
