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
use skeeks\cms\helpers\RequestResponse;
use skeeks\cms\models\CmsAgent;
use skeeks\cms\shop\models\ShopPaySystem;
use skeeks\cms\shop\paysystem\PaysystemHandler;
use skeeks\yii2\form\Builder;
use skeeks\yii2\form\fields\BoolField;
use skeeks\yii2\form\fields\FieldSet;
use skeeks\yii2\form\fields\NumberField;
use skeeks\yii2\form\fields\SelectField;
use skeeks\yii2\form\fields\TextareaField;
use yii\base\Event;
use yii\base\Exception;
use yii\grid\DataColumn;
use yii\helpers\ArrayHelper;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class AdminPaySystemController extends BackendModelStandartController
{
    public $notSubmitParam = 'sx-not-submit';

    public function init()
    {
        $this->name = \Yii::t('skeeks/shop/app', 'Payment systems');
        $this->modelShowAttribute = "name";
        $this->modelClassName = ShopPaySystem::class;

        $this->generateAccessActions = false;

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return ArrayHelper::merge(parent::actions(),
            [
                'index' => [
                        "backendShowings" => false,
                        "filters"         => false,

                        'grid' => [
                            'on init' => function (Event $e) {
                                /**
                                 * @var $dataProvider ActiveDataProvider
                                 * @var $query ActiveQuery
                                 */
                                $query = $e->sender->dataProvider->query;

                                $query->andWhere(['cms_site_id' => \Yii::$app->skeeks->site->id]);
                            },

                            'defaultOrder' => [
                                'priority' => SORT_ASC,
                            ],

                            'visibleColumns' => [
                                'checkbox',
                                'actions',
                                'name',
                                'is_active',
                                'priority',
                            ],

                            "columns" => [
                                'name' => [
                                    'class'         => DefaultActionColumn::class,
                                    'viewAttribute' => 'asText',
                                ],
                                'priority',

                                [
                                    'class'     => DataColumn::class,
                                    'attribute' => "personTypeIds",
                                    'filter'    => false,
                                    'value'     => function (ShopPaySystem $model) {
                                        return implode(", ", ArrayHelper::map($model->personTypes, 'id', 'name'));
                                    },
                                ],

                                'is_active' => [
                                    'class'     => BooleanColumn::class,
                                    'attribute' => "is_active",
                                ],
                            ],
                        ],
                    ],

                "create" => [
                    'fields'        => [$this, 'updateFields'],
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
                    'fields'        => [$this, 'updateFields'],
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
            ]
        );
    }


    public function updateFields($action)
    {
        $handlerFields = [];
        /**
         * @var $handler PaysystemHandler
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
            'main'         => [
                'class'  => FieldSet::class,
                'name'   => \Yii::t('skeeks/shop/app', 'Main'),
                'fields' => [

                    'name',

                    'description' => [
                        'class' => TextareaField::class,
                    ],

                    'is_active' => [
                        'class'     => BoolField::class,
                        'allowNull' => false,
                    ],

                    /*'personTypeIds' => [
                        'class'    => SelectField::class,
                        'multiple' => true,
                        'items'    => \yii\helpers\ArrayHelper::map(\skeeks\cms\shop\models\ShopPersonType::find()->all(), 'id', 'name'),
                    ],*/


                    'priority' => [
                        'class' => NumberField::class
                    ],

                    'component' => [
                        'class'   => SelectField::class,
                        'items'   => \Yii::$app->shop->getPaysystemHandlersForSelect(),
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
                    'name'   => "Настройки платежной системы",
                    'fields' => $handlerFields,
                ],
            ]);
        }


        return $result;
    }

}
