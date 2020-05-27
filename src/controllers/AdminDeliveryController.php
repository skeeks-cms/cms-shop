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
use skeeks\cms\grid\ImageColumn;
use skeeks\cms\models\CmsAgent;
use skeeks\cms\shop\models\ShopDelivery;
use skeeks\cms\widgets\AjaxFileUploadWidget;
use skeeks\yii2\form\fields\BoolField;
use skeeks\yii2\form\fields\FieldSet;
use skeeks\yii2\form\fields\SelectField;
use skeeks\yii2\form\fields\TextareaField;
use skeeks\yii2\form\fields\WidgetField;
use yii\base\Event;
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

        $this->generateAccessActions = false;

        parent::init();
    }


    /**
     * @inheritdoc
     */
    public function actions()
    {
        return ArrayHelper::merge(parent::actions(), [
            'index'  => [
                "backendShowings" => false,
                "filters" => false,

                'grid'    => [
                    'on init' => function (Event $e) {
                        /**
                         * @var $dataProvider ActiveDataProvider
                         * @var $query ActiveQuery
                         */
                        $query = $e->sender->dataProvider->query;

                        $query->andWhere(['cms_site_id' => \Yii::$app->skeeks->site->id]);
                    },
                    'defaultOrder'   => [
                        'is_active'   => SORT_DESC,
                        'priority' => SORT_ASC,
                    ],
                    'visibleColumns' => [
                        'checkbox',
                        'actions',
                        'name',
                        'price',
                        'is_active',
                        'priority',
                        'shopPaySystems',
                    ],
                    'columns'        => [
                        'name'         => [
                            'class' => DefaultActionColumn::class,
                            'viewAttribute' => 'asText',
                        ],
                        'is_active'         => [
                            'class' => BooleanColumn::class,
                        ],
                        'logo_id'        => [
                            'relationName' => 'logo',
                            'class'        => ImageColumn::class,
                        ],
                        'shopPaySystems' => [
                            'label' => 'Платежные системы',
                            'value' => function (\skeeks\cms\shop\models\ShopDelivery $model) {
                                if ($model->shopPaySystems) {
                                    return implode(", ", \yii\helpers\ArrayHelper::map($model->shopPaySystems, 'id', 'name'));
                                } else {
                                    return 'Все';
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
                    'is_active'  => [
                        'class'      => BoolField::class,
                        'allowNull'      => false,
                    ],

                    'name',
                    'description' => [
                        'class' => TextareaField::class,
                    ],
                    
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


                    'weight_from',
                    'weight_to',


                    'order_price_from',
                    'order_price_to',

                    'order_currency_code' => [
                        'class' => SelectField::class,
                        'items' => \yii\helpers\ArrayHelper::map(\skeeks\cms\money\models\MoneyCurrency::find()->where(['is_active' => 1])->all(), 'code', 'code'),
                    ],


                    


                    'shopPaySystems' => [
                        'class'    => SelectField::class,
                        'hint'     => \Yii::t('skeeks/shop/app', 'if nothing is selected, it means all'),
                        'multiple' => true,
                        'items'    => \yii\helpers\ArrayHelper::map(
                            \skeeks\cms\shop\models\ShopPaySystem::find()->active()->all(), 'id', 'name'
                        ),
                    ],
                ],
            ],

        ];
    }

}
