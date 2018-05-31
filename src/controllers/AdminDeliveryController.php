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
use skeeks\cms\grid\BooleanColumn;
use skeeks\cms\grid\ImageColumn;
use skeeks\cms\grid\ImageColumn2;
use skeeks\cms\models\CmsAgent;
use skeeks\cms\shop\models\ShopDelivery;
use skeeks\cms\widgets\AjaxFileUploadWidget;
use skeeks\yii2\form\fields\BoolField;
use skeeks\yii2\form\fields\FieldSet;
use skeeks\yii2\form\fields\SelectField;
use skeeks\yii2\form\fields\TextareaField;
use skeeks\yii2\form\fields\WidgetField;
use yii\helpers\ArrayHelper;

/**
 * Class AdminTaxController
 * @package skeeks\cms\shop\controllers
 */
class AdminDeliveryController extends BackendModelStandartController
{
    public function init()
    {
        $this->name = \Yii::t('skeeks/shop/app', 'Delivery services');
        $this->modelShowAttribute = "name";
        $this->modelClassName = ShopDelivery::class;

        parent::init();
    }


    /**
     * @inheritdoc
     */
    public function actions()
    {
        return ArrayHelper::merge(parent::actions(), [
            'index'  => [
                "filters" => [
                    'visibleFilters' => [
                        'id',
                        'name',
                    ],
                ],
                'grid'    => [
                    'defaultOrder'   => [
                        'active'   => SORT_DESC,
                        'priority' => SORT_ASC,
                    ],
                    'visibleColumns' => [
                        'checkbox',
                        'actions',
                        'id',
                        'logo_id',
                        'name',
                        'price',
                        'active',
                        'priority',
                        'shopPaySystems',
                    ],
                    'columns'        => [
                        'active'  => [
                            'class' => BooleanColumn::class,
                        ],
                        'logo_id' => [
                            'relationName' => 'logo',
                            'class' => ImageColumn::class,
                        ],
                        'shopPaySystems' => [
                            'label' => 'Платежные системы',
                            'value' => function (\skeeks\cms\shop\models\ShopDelivery $model) {
                                if ($model->shopPaySystems) {
                                    return implode(", ", \yii\helpers\ArrayHelper::map($model->shopPaySystems, 'id', 'name'));
                                } else {
                                    return 'Все';
                                }
                            }
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
        return [
            'main' => [
                'class'  => FieldSet::class,
                'name'   => \Yii::t('skeeks/shop/app', 'Main'),
                'fields' => [
                    'logo_id' => [
                        'class'        => WidgetField::class,
                        'widgetClass'  => AjaxFileUploadWidget::class,
                        'widgetConfig' => [
                            'accept'   => 'image/*',
                            'multiple' => false,
                        ],
                    ],
                    'active'  => [
                        'class'      => BoolField::class,
                        'trueValue'  => "Y",
                        'falseValue' => "N",
                    ],

                    'site_id' => [
                        'class' => SelectField::class,
                        'items' => \yii\helpers\ArrayHelper::map(
                            \skeeks\cms\models\CmsSite::find()->all(), 'id', 'name'
                        ),
                    ],

                    'name',
                    'priority',


                    'price',

                    'currency_code' => [
                        'class' => SelectField::class,
                        'items' => \yii\helpers\ArrayHelper::map(\skeeks\cms\money\models\MoneyCurrency::find()->where(['is_active' => 1])->all(), 'code', 'code'),
                    ],

                ],
            ],

            'additionally' => [
                'class'  => FieldSet::class,
                'name'   => \Yii::t('skeeks/shop/app', 'Additionally'),
                'fields' => [


                    'period_from',
                    'period_to',

                    'period_type' => [
                        'class' => SelectField::class,
                        'items' => [
                            'D' => 'день',
                            'H' => 'час',
                            'M' => 'месяц',
                        ],
                    ],


                    'weight_from',
                    'weight_to',


                    'order_price_from',
                    'order_price_to',

                    'order_currency_code' => [
                        'class' => SelectField::class,
                        'items' => \yii\helpers\ArrayHelper::map(\skeeks\cms\money\models\MoneyCurrency::find()->where(['is_active' => 1])->all(), 'code', 'code'),
                    ],


                    'description' => [
                        'class' => TextareaField::class,
                    ],

                    'store',

                    'shopPaySystems' => [
                        'class' => SelectField::class,
                        'hint' => \Yii::t('skeeks/shop/app', 'if nothing is selected, it means all'),
                        'multiple' => true,
                        'items' => \yii\helpers\ArrayHelper::map(
                            \skeeks\cms\shop\models\ShopPaySystem::find()->active()->all(), 'id', 'name'
                        ),
                    ],
                ],
            ],

        ];
    }

}
