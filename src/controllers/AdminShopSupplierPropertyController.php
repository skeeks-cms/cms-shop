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
use skeeks\cms\models\CmsContentProperty;
use skeeks\cms\shop\models\ShopSupplier;
use skeeks\cms\shop\models\ShopSupplierProperty;
use skeeks\cms\shop\models\ShopSupplierPropertyOption;
use skeeks\yii2\form\fields\BoolField;
use skeeks\yii2\form\fields\FieldSet;
use skeeks\yii2\form\fields\SelectField;
use skeeks\yii2\form\fields\TextareaField;
use yii\base\Event;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class AdminShopSupplierPropertyController extends BackendModelStandartController
{
    public function init()
    {
        $this->name = "Свойства поставщика";
        $this->modelShowAttribute = "asText";
        $this->modelClassName = ShopSupplierProperty::class;

        $this->accessCallback = function () {
            //Если это сайт по умолчанию, этот раздел не показываем
            if (\Yii::$app->skeeks->site->is_default) {
                return false;
            }

            /**
             * @var ShopSite $shopSite
             */
            $shopSite = \Yii::$app->skeeks->site->shopSite;
            if (!$shopSite) {
                return false;
            }

          
            /*if (!$shopSite->is_receiver) {
                return false;
            }*/

            return true;
        };

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

                        $optionsQuery = ShopSupplierPropertyOption::find()->select(['count(*) as inner_count'])->where([
                            'shop_supplier_property_id' => new Expression(ShopSupplierProperty::tableName().".id"),
                        ]);

                        $optionsNotConnectQuery = ShopSupplierPropertyOption::find()->select(['count(*) as inner_count1'])->where([
                            'shop_supplier_property_id' => new Expression(ShopSupplierProperty::tableName().".id"),
                        ])->andWhere([
                            'and',
                            ['cms_content_property_enum_id' => null],
                            ['cms_content_element_id' => null],
                        ]);

                        $optionsConnectQuery = ShopSupplierPropertyOption::find()->select(['count(*) as inner_count1'])->where([
                            'shop_supplier_property_id' => new Expression(ShopSupplierProperty::tableName().".id"),
                        ])->andWhere([
                            'or',
                            ['is not', 'cms_content_property_enum_id', null],
                            ['is not', 'cms_content_element_id', null],
                        ]);

                        $query->groupBy(ShopSupplierProperty::tableName().".id");

                        $query->select([
                            ShopSupplierProperty::tableName().'.*',
                            'countOptions'           => $optionsQuery,
                            'countConnectOptions' => $optionsConnectQuery,
                            'countNotConnectOptions' => $optionsNotConnectQuery,
                        ]);

                        $query->andWhere(['cms_site_id' => \Yii::$app->skeeks->site->id]);

                    },

                    'defaultOrder' => [
                        'is_visible' => SORT_DESC,
                        'priority'   => SORT_ASC,
                    ],

                    'visibleColumns' => [

                        'checkbox',
                        'actions',

                        //'id',
                        'external_code',
                        'shop_supplier_id',
                        'name',

                        'property_type',
                        'cms_property',

                        'priority',
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
                                $result[] = $property->propertyTypeAsText;
                                if ($property->property_type == ShopSupplierProperty::PROPERTY_TYPE_LIST) {
                                    $countOptions = ArrayHelper::getValue($property->raw_row, 'countOptions');
                                    $countConnectOptions = ArrayHelper::getValue($property->raw_row, 'countConnectOptions');
                                    $countNotConnectOptions = ArrayHelper::getValue($property->raw_row, 'countNotConnectOptions');

                                    if ($countNotConnectOptions == 0) {
                                        $result[] = "<span title='Всего опций'> {$countOptions}</span> (<span style='color:green;' title='Все привязаны!'><span class='fa fa-check'></span></span>)";
                                    } else {
                                        $result[] = "<span title='Всего опций'> {$countOptions}</span> (<span style='color:red;' title='Не привязанных'>{$countNotConnectOptions}</span>)";
                                    }

                                }

                                return implode(" ", $result);
                            },
                        ],

                        'cms_property' => [
                            'label' => 'Свойство сайта',
                            'value' => function (ShopSupplierProperty $property) {
                                $result = "";

                                if ($property->cms_content_property_id) {
                                    $result = $property->cmsContentProperty->asText;
                                }

                                return $result;
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

            "options" => [
                'class'           => BackendGridModelRelatedAction::class,
                'accessCallback'  => true,
                'name'            => "Опции",
                'icon'            => 'fa fa-list',
                'controllerRoute' => "/shop/admin-shop-supplier-property-option",
                'relation'        => ['shop_supplier_property_id' => 'id'],
                'priority'        => 600,
                'on gridInit'     => function ($e) {
                    /**
                     * @var $action BackendGridModelRelatedAction
                     */
                    $action = $e->sender;
                    $action->relatedIndexAction->backendShowings = false;
                    $visibleColumns = $action->relatedIndexAction->grid['visibleColumns'];

                    ArrayHelper::removeValue($visibleColumns, 'shop_supplier_property_id');
                    $action->relatedIndexAction->grid['visibleColumns'] = $visibleColumns;

                },
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

                    'cms_content_property_id' => [
                        'class' => SelectField::class,
                        'items' => ArrayHelper::map(
                            CmsContentProperty::find()->all(),
                            'id',
                            'asText'
                        ),
                    ],


                ],
            ],

            'import' => [
                'class'  => FieldSet::class,
                'name'   => 'Настройки импорта/преобразования',
                'fields' => [
                    'import_delimetr' => [
                        'class' => TextareaField::class,
                    ],
                    'import_replace'  => [
                        'class' => TextareaField::class,
                    ],
                    'import_miltiple' => [
                        'class' => TextareaField::class,
                    ],
                ],
            ],


        ];
    }

}