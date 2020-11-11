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
use skeeks\cms\backend\controllers\BackendModelStandartController;
use skeeks\cms\backend\grid\DefaultActionColumn;
use skeeks\cms\grid\BooleanColumn;
use skeeks\cms\models\CmsAgent;
use skeeks\cms\shop\models\ShopDiscount;
use skeeks\cms\shop\models\ShopDiscountCoupon;
use skeeks\cms\widgets\AjaxSelectModel;
use skeeks\yii2\form\fields\BoolField;
use skeeks\yii2\form\fields\FieldSet;
use skeeks\yii2\form\fields\NumberField;
use skeeks\yii2\form\fields\WidgetField;
use yii\base\Event;
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

                        $query->andWhere(['shop_discount_id' => ShopDiscount::find()->cmsSite()->select(['id'])]);
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
                        'shop_discount_id',
                        'is_active',
                    ],
                    'columns'        => [
                        'coupon'    => [
                            'class' => DefaultActionColumn::class,
                        ],
                        'is_active' => [
                            'class' => BooleanColumn::class,
                        ],


                        'value' => [
                            'value' => function (\skeeks\cms\shop\models\ShopDiscount $shopDiscount) {
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
                ],
            ],


        ];
    }
}
