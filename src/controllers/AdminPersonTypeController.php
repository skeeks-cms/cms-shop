<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

namespace skeeks\cms\shop\controllers;

use skeeks\cms\backend\actions\BackendGridModelRelatedAction;
use skeeks\cms\backend\controllers\BackendModelStandartController;
use skeeks\cms\grid\BooleanColumn;
use skeeks\cms\grid\DateTimeColumnData;
use skeeks\cms\models\CmsAgent;
use skeeks\cms\shop\models\ShopBuyer;
use skeeks\cms\shop\models\ShopPersonType;
use skeeks\yii2\form\fields\BoolField;
use skeeks\yii2\form\fields\TextareaField;
use yii\base\Event;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class AdminPersonTypeController extends BackendModelStandartController
{
    public function init()
    {
        $this->name = \Yii::t('skeeks/shop/app', 'Types of payers');
        $this->modelShowAttribute = "name";
        $this->modelClassName = ShopPersonType::class;

        $this->generateAccessActions = false;

        $this->accessCallback = function () {
            if (!\Yii::$app->skeeks->site->is_default) {
                return false;
            }
            return \Yii::$app->user->can($this->uniqueId);
        };
        
        parent::init();
    }
    
    
    /**
     * @inheritdoc
     */
    public function actions()
    {
        return ArrayHelper::merge(parent::actions(), [

            "create" => [
                'fields' => [$this, 'updateFields'],
            ],
            "update" => [
                'fields' => [$this, 'updateFields'],
            ],

            'index' => [

                'filters' => [
                    'visibleFilters' => [
                        'name',
                    ],
                ],

                "grid" => [
                    'on init' => function (Event $e) {
                        /**
                         * @var $dataProvider ActiveDataProvider
                         * @var $query ActiveQuery
                         */
                        $query = $e->sender->dataProvider->query;

                        $sendsQuery = ShopBuyer::find()->select(['count(*)'])->where(['shop_person_type_id' => new Expression(ShopPersonType::tableName().".id")]);


                        $query->select([
                            ShopPersonType::tableName().'.*',
                            'count_buyers' => $sendsQuery,
                        ]);
                    },

                    'sortAttributes' => [
                        'count_buyers' => [
                            'asc'  => ['count_buyers' => SORT_ASC],
                            'desc' => ['count_buyers' => SORT_DESC],
                            'name' => "Количество профилей",
                        ],
                    ],

                    'defaultOrder'   => [
                        'priority' => SORT_ASC,
                    ],

                    'visibleColumns' => [
                        'checkbox',
                        'actions',

                        //'id',
                        //'created_at',

                        'customName',
                        /*'name',
                        'code',*/
                        //'phones',
                        //'countFields',
                        'priority',
                        'is_active',
                        'count_buyers',

                    ],
                    'columns'        => [
                        'customName' => [
                            'label' => "Тип профиля",
                            'format' => "raw",
                            'value' => function(ShopPersonType $form) {
                                $result = [];
                                $result[] = Html::a($form->asText, "#", [
                                    'class' => "sx-trigger-action",
                                ]);

                                /*$result[] = $form->;*/

                                return implode('<br />', $result);
                            }
                        ],

                        'created_at' => [
                            'class' => DateTimeColumnData::class,
                        ],

                        'is_active' => [
                            'class' => BooleanColumn::class,
                        ],

                        /*'countFields' => [
                            'label' => \Yii::t('skeeks/form2/app', 'Number of fields in the form'),
                            'value' => function (ShopPersonType $model) {
                                return count($model->createModelFormSend()->relatedPropertiesModel->toArray());
                            },
                        ],*/
                        'count_buyers' => [
                            'label' => "Количество покупателей",
                            'attribute' => 'count_buyers',
                            'value' => function (ShopPersonType $model) {
                                return $model->raw_row['count_buyers'];
                            },
                        ],
                    ],
                ],
            ],

            "properties" => [
                'class'           => BackendGridModelRelatedAction::class,
                'accessCallback'  => true,
                'name'            => "Свойства покупателя",
                'icon'            => 'fa fa-list',
                'controllerRoute' => "/shop/admin-person-type-property",
                'relation'        => ['shop_person_type_id' => 'id'],
                'priority'        => 600,
                'on gridInit'     => function ($e) {
                    /**
                     * @var $action BackendGridModelRelatedAction
                     */
                    $action = $e->sender;

                    $action->relatedIndexAction->backendShowings = false;
                    $visibleColumns = $action->relatedIndexAction->grid['visibleColumns'];
                    ArrayHelper::removeValue($visibleColumns, 'shop_person_type_id');
                    $action->relatedIndexAction->grid['visibleColumns'] = $visibleColumns;

                },
            ],

            /*"send" => [
                'class'           => BackendGridModelRelatedAction::class,
                'accessCallback'  => true,
                'name'            => "Сообщения",
                'icon'            => 'fa fa-list',
                'controllerRoute' => "/form2/admin-form-send",
                'relation'        => ['form_id' => 'id'],
                'priority'        => 600,
                'on gridInit'     => function ($e) {
                    /**
                     * @var $action BackendGridModelRelatedAction
                    $action = $e->sender;

                    $action->relatedIndexAction->backendShowings = false;
                    $visibleColumns = $action->relatedIndexAction->grid['visibleColumns'];
                    ArrayHelper::removeValue($visibleColumns, 'form_id');
                    $action->relatedIndexAction->grid['visibleColumns'] = $visibleColumns;

                },
            ],*/
        ]);
    }

    public function updateFields()
    {
        return [
            'is_active' => [
                'class' => BoolField::class,
                'allowNull' => false,
            ],
            'name',
            'priority',
        ];
    }
}
