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
use skeeks\cms\backend\controllers\BackendModelStandartController;
use skeeks\cms\backend\grid\DefaultActionColumn;
use skeeks\cms\grid\BooleanColumn;
use skeeks\cms\models\CmsAgent;
use skeeks\cms\shop\models\ShopSupplier;
use skeeks\cms\shop\models\ShopSupplierProperty;
use skeeks\yii2\form\fields\BoolField;
use skeeks\yii2\form\fields\FieldSet;
use skeeks\yii2\form\fields\SelectField;
use yii\base\Event;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class AdminShopSupplierPropertyController extends BackendModelStandartController
{
    public function init()
    {
        $this->name = "Свойства поставщиков";
        $this->modelShowAttribute = "asText";
        $this->modelClassName = ShopSupplierProperty::class;

        $this->generateAccessActions = false;

        parent::init();
    }


    /**
     * @inheritdoc
     */
    public function actions()
    {
        return ArrayHelper::merge(parent::actions(), [
            'index'         => [
                "filters" => [
                    'visibleFilters' => [
                        'id',
                        'name',
                    ],
                ],
                'grid'    => [
                    'on init' => function (Event $e) {
                        /**
                         * @var $dataProvider ActiveDataProvider
                         * @var $query ActiveQuery
                         */
                        $query = $e->sender->dataProvider->query;
                        $dataProvider = $e->sender->dataProvider;
                    },

                    'defaultOrder' => [
                        'id' => SORT_DESC,
                    ],

                    'visibleColumns' => [

                        'checkbox',
                        'actions',

                        //'id',
                        'external_code',
                        'shop_supplier_id',
                        'name',

                        'priority',
                        'property_type',
                        'is_visible',
                    ],
                    'columns'        => [
                        'is_visible' => [
                            'class' => BooleanColumn::class,
                        ],

                        'external_code' => [
                            'class' => DefaultActionColumn::class,
                        ],
                        'property_type' => [
                            'value' => function (ShopSupplierProperty $property) {
                                return $property->propertyTypeAsText;
                            },
                        ],
                    ],
                ],
            ],
            "create"        => [
                'fields' => [$this, 'updateFields'],
            ],
            "update"        => [
                'fields' => [$this, 'updateFields'],
            ],
            "is-visible"    => [
                'class'     => BackendModelMultiActivateAction::class,
                'name'      => 'Сделать видимыми',
                'attribute' => 'is_visible',
                'value'     => 1,
            ],
            "is-un-visible" => [
                'class'     => BackendModelMultiDeactivateAction::class,
                'attribute' => 'is_visible',
                'name'      => 'Скрыть',
                'value'     => 0,
            ],
        ]);
    }

    public function updateFields($action)
    {
        /**
         * @var $model ShopSupplierProperty
         */
        $model = $action->model;

        return [

            'supplier' => [
                'class'  => FieldSet::class,
                'name'   => 'От поставщика',
                'fields' => [
                    'shop_supplier_id' => [
                        'class' => SelectField::class,
                        'items' => ArrayHelper::map(
                            ShopSupplier::find()->all(),
                            'id',
                            'asText'
                        ),
                    ],

                    'external_code',
                ],
            ],
            'main'     => [
                'class'  => FieldSet::class,
                'name'   => 'Настройки свойства',
                'fields' => [

                    'property_type' => [
                        'class' => SelectField::class,
                        'items' => ShopSupplierProperty::getPopertyTypeOptions(),
                    ],

                    'is_visible' => [
                        'class'     => BoolField::class,
                        'allowNull' => false,
                    ],


                    'name',
                    'priority',


                ],
            ],


        ];
    }

}
