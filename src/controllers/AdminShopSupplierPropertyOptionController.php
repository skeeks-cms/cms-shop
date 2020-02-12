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
use skeeks\cms\models\CmsContentProperty;
use skeeks\cms\shop\models\ShopSupplier;
use skeeks\cms\shop\models\ShopSupplierProperty;
use skeeks\cms\shop\models\ShopSupplierPropertyOption;
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
class AdminShopSupplierPropertyOptionController extends BackendModelStandartController
{
    public function init()
    {
        $this->name = "Опции поставщиков";
        $this->modelShowAttribute = "asText";
        $this->modelClassName = ShopSupplierPropertyOption::class;

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
                        'shop_supplier_property_id',
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
                        'shop_supplier_property_id',
                        'name',
                    ],
                    'columns'        => [
                        
                        'property_type' => [
                            'value' => function (ShopSupplierPropertyOption $property) {
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
                    'shop_supplier_property_id' => [
                        'class' => SelectField::class,
                        'items' => ArrayHelper::map(
                            ShopSupplierProperty::find()->all(),
                            'id',
                            'asText'
                        ),
                    ],

                    'name',
                ],
            ],
        ];
    }

}
