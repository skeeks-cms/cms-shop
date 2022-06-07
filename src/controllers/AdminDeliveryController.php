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
use skeeks\cms\helpers\RequestResponse;
use skeeks\cms\models\CmsAgent;
use skeeks\cms\shop\components\DeliveryHandlerComponent;
use skeeks\cms\shop\deliveries\BoxberryDeliveryHandler;
use skeeks\cms\shop\models\ShopDelivery;
use skeeks\cms\widgets\AjaxFileUploadWidget;
use skeeks\yii2\form\Builder;
use skeeks\yii2\form\fields\BoolField;
use skeeks\yii2\form\fields\FieldSet;
use skeeks\yii2\form\fields\NumberField;
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
                "filters"         => false,

                'grid' => [
                    'on init'        => function (Event $e) {
                        /**
                         * @var $dataProvider ActiveDataProvider
                         * @var $query ActiveQuery
                         */
                        $query = $e->sender->dataProvider->query;

                        $query->andWhere(['cms_site_id' => \Yii::$app->skeeks->site->id]);
                    },
                    'defaultOrder'   => [
                        'is_active' => SORT_DESC,
                        'priority'  => SORT_ASC,
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
                        'name'           => [
                            'class'         => DefaultActionColumn::class,
                            'viewAttribute' => 'asText',
                        ],
                        'is_active'      => [
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
                'on beforeSave' => function (Event $e) {
                    /**
                     * @var $action BackendModelUpdateAction;
                     * @var $model CmsUser;
                     */
                    $action = $e->sender;
                    $model = $action->model;
                    $action->isSaveFormModels = false;

                    if (isset($action->formModels['handler'])) {
                        $handler = $action->formModels['handler'];
                        $model->component_config = $handler->toArray();
                    }

                    if ($model->save()) {
                        //$action->afterSaveUrl = Url::to(['update', 'pk' => $newModel->id, 'content_id' => $newModel->content_id]);
                    } else {
                        throw new Exception(print_r($model->errors, true));
                    }

                },
            ],
            "update" => [
                'fields' => [$this, 'updateFields'],
                'on beforeSave' => function (Event $e) {
                    /**
                     * @var $action BackendModelUpdateAction;
                     * @var $model CmsUser;
                     */
                    $action = $e->sender;
                    $model = $action->model;
                    $action->isSaveFormModels = false;

                    if (isset($action->formModels['handler'])) {
                        $handler = $action->formModels['handler'];
                        $model->component_config = $handler->toArray();
                    }


                    if ($model->save()) {
                        //$action->afterSaveUrl = Url::to(['update', 'pk' => $newModel->id, 'content_id' => $newModel->content_id]);
                    } else {
                        throw new Exception(print_r($model->errors, true));
                    }

                },

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
        $handlerFields = [];
        /**
         * @var $handler DeliveryHandlerComponent
         */
        if ($action->model && $action->model->handler) {
            $handler = $action->model->handler;
            $handlerFields = $handler->getConfigFormFields();
            $handlerFields = Builder::setModelToFields($handlerFields, $handler);

            $action->formModels['handler'] = $handler;
            if ($post = \Yii::$app->request->post()) {
                $handler->load($post);
            }

        }

        $result = [


            'main' => [
                'class'  => FieldSet::class,
                'name'   => \Yii::t('skeeks/shop/app', 'Main'),
                'fields' => [

                    'is_active' => [
                        'class'     => BoolField::class,
                        'allowNull' => false,
                    ],

                    'logo_id'     => [
                        'class'        => WidgetField::class,
                        'widgetClass'  => AjaxFileUploadWidget::class,
                        'widgetConfig' => [
                            'accept'   => 'image/*',
                            'multiple' => false,
                        ],
                    ],



                    'name',
                    'description' => [
                        'class' => TextareaField::class,
                        'elementOptions' => [
                            'placeholder' => 'Это описание выводится клиенту при оформлении заказа'
                        ]
                    ],



                    'price' => NumberField::class,

                    'currency_code' => [
                        'class' => SelectField::class,
                        'items' => \yii\helpers\ArrayHelper::map(\skeeks\cms\money\models\MoneyCurrency::find()->where(['is_active' => 1])->all(), 'code', 'code'),
                    ],

                    'priority' => NumberField::class,
                ],
            ],

            'filter' => [
                'class'  => FieldSet::class,
                'name'   => \Yii::t('skeeks/shop/app', 'Условия показа'),
                'fields' => [
                    'order_price_from' => NumberField::class,
                    'order_price_to' => NumberField::class,
                ]
            ],

            'additionally' => [
                'class'  => FieldSet::class,
                'name'   => \Yii::t('skeeks/shop/app', 'Additionally'),
                'fields' => [

                    'shopPaySystems' => [
                        'class'    => SelectField::class,
                        'multiple' => true,
                        'items'    => \yii\helpers\ArrayHelper::map(
                            \skeeks\cms\shop\models\ShopPaySystem::find()->active()->all(), 'id', 'name'
                        ),
                    ],

                    'component' => [
                        'class'   => SelectField::class,
                        'items'   => \Yii::$app->shop->getDeliveryHandlersForSelect(),
                        'elementOptions' => [
                            RequestResponse::DYNAMIC_RELOAD_FIELD_ELEMENT => "true",
                        ],
                    ],

                ],
            ],
        ];

        if ($handlerFields) {
            $result = ArrayHelper::merge($result, [
                'handler' => [
                    'class'  => FieldSet::class,
                    'name'   => "Настройки обработчика",
                    'fields' => $handlerFields
                ]
            ]);
        }


        return $result;
    }

}
