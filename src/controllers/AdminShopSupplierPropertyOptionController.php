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
use skeeks\cms\helpers\RequestResponse;
use skeeks\cms\models\CmsAgent;
use skeeks\cms\models\CmsContentElement;
use skeeks\cms\models\CmsContentProperty;
use skeeks\cms\relatedProperties\propertyTypes\PropertyTypeElement;
use skeeks\cms\relatedProperties\propertyTypes\PropertyTypeList;
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
                        'connect',
                    ],
                    'columns'        => [
                        
                        'connect' => [
                            'format' => 'raw',
                            'label' => 'Связь',
                            'value' => function (ShopSupplierPropertyOption $property) {
                                if ($property->cms_content_element_id) {
                                    return $property->cmsContentElement->asText;
                                }
                                if ($property->cms_content_property_enum_id) {
                                    return $property->cmsContentPropertyEnum->asText;
                                }

                                return '';
                            },
                        ],
                        'property_type' => [
                            'value' => function (ShopSupplierPropertyOption $property) {
                                return $property->propertyTypeAsText;
                            },
                        ],

                        'name' => [
                            'class' => DefaultActionColumn::class,
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
         * @var $model ShopSupplierPropertyOption
         */
        $model = $action->model;

        $connect = [];

        if ($model->shopSupplierProperty) {

            $property = $model->shopSupplierProperty;
            if ($property->cmsContentProperty) {

                $contentProperty = $property->cmsContentProperty;
                if ($contentProperty->handler instanceof PropertyTypeList) {

                    $connect = [
                        'conncet' => [
                            'class'  => FieldSet::class,
                            'name'   => 'Связь с опциями cms',
                            'fields' => [
                                'cms_content_property_enum_id' => [
                                    'class' => SelectField::class,
                                    'items' => ArrayHelper::map(
                                        $contentProperty->getEnums()->all(),
                                        'id',
                                        'asText'
                                    ),
                                ],
                            ],
                        ],
                    ];

                } elseif ($contentProperty->handler instanceof PropertyTypeElement) {
                    $content_id = $property->cmsContentProperty->handler->content_id;

                    $connect = [
                        'conncet' => [
                            'class'  => FieldSet::class,
                            'name'   => 'Связь с опциями cms',
                            'fields' => [
                                'cms_content_element_id' => [
                                    'class' => SelectField::class,
                                    'items' => ArrayHelper::map(
                                        CmsContentElement::find()->andWhere(['content_id' => $content_id])->all(),
                                        'id',
                                        'asText'
                                    ),
                                ],
                            ],
                        ],
                    ];
                }


            }

        }


        $result = [

            'supplier' => [
                'class'  => FieldSet::class,
                'name'   => 'От поставщика',
                'fields' => [
                    'shop_supplier_property_id' => [
                        'class' => SelectField::class,
                        'elementOptions' => [
                            RequestResponse::DYNAMIC_RELOAD_FIELD_ELEMENT => 'true'
                        ],
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

        return ArrayHelper::merge($result, $connect);
    }

}
