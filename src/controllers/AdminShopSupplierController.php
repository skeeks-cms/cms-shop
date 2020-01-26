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
use skeeks\cms\grid\ImageColumn;
use skeeks\cms\models\CmsAgent;
use skeeks\cms\shop\models\ShopDelivery;
use skeeks\cms\shop\models\ShopProduct;
use skeeks\cms\shop\models\ShopStore;
use skeeks\cms\shop\models\ShopSupplier;
use skeeks\cms\shop\models\ShopTypePrice;
use skeeks\cms\widgets\AjaxFileUploadWidget;
use skeeks\yii2\ckeditor\CKEditorWidget;
use skeeks\yii2\form\fields\BoolField;
use skeeks\yii2\form\fields\FieldSet;
use skeeks\yii2\form\fields\SelectField;
use skeeks\yii2\form\fields\TextareaField;
use skeeks\yii2\form\fields\WidgetField;
use yii\base\Event;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class AdminShopSupplierController extends BackendModelStandartController
{
    public function init()
    {
        $this->name = "Поставщики";
        $this->modelShowAttribute = "asText";
        $this->modelClassName = ShopSupplier::class;

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
                "filters" => [
                    'visibleFilters' => [
                        'id',
                        'name',
                    ],
                ],
                'grid'    => [
                    'on init'       => function (Event $e) {
                        /**
                         * @var $dataProvider ActiveDataProvider
                         * @var $query ActiveQuery
                         */
                        $query = $e->sender->dataProvider->query;
                        $dataProvider = $e->sender->dataProvider;


                        $shopTypePricesQuery = ShopTypePrice::find()->select(['count(*) as inner_count'])->where([
                            'shop_supplier_id' => new Expression(ShopSupplier::tableName().".id"),
                        ]);
                        
                        $shopStoreQuery = ShopStore::find()->select(['count(*) as inner_count'])->where([
                            'shop_supplier_id' => new Expression(ShopSupplier::tableName().".id"),
                        ]);
                        $shopProductQuery = ShopProduct::find()->select(['count(*) as inner_count'])->where([
                            'shop_supplier_id' => new Expression(ShopSupplier::tableName().".id"),
                        ]);

                        $query->groupBy(ShopSupplier::tableName() . ".id");

                        $query->select([
                            ShopSupplier::tableName() . '.*',
                            'countShopStores' => $shopStoreQuery,
                            'countShopTypePrices' => $shopTypePricesQuery,
                            'countShopProducts' => $shopProductQuery
                        ]);
                    },

                    'defaultOrder'   => [
                        'id' => SORT_DESC,
                    ],

                    'sortAttributes' => [
                        'countShopStores' => [
                            'asc' => ['countShopStores' => SORT_ASC],
                            'desc' => ['countShopStores' => SORT_DESC],
                            'label' => 'Количество складов',
                            'default' => SORT_ASC
                        ],
                        'countShopTypePrices' => [
                            'asc' => ['countShopTypePrices' => SORT_ASC],
                            'desc' => ['countShopTypePrices' => SORT_DESC],
                            'label' => 'Количество типов цен',
                            'default' => SORT_ASC
                        ],
                        'countShopProducts' => [
                            'asc' => ['countShopProducts' => SORT_ASC],
                            'desc' => ['countShopProducts' => SORT_DESC],
                            'label' => 'Количество товаров',
                            'default' => SORT_ASC
                        ]
                    ],

                    'visibleColumns' => [

                        'checkbox',
                        'actions',

                        //'id',
                        'name',

                        'countShopStores',
                        'countShopTypePrices',
                        'countShopProducts',
                        'is_active',
                    ],
                    'columns'        => [
                        'is_active' => [
                            'class'      => BooleanColumn::class,
                            'trueValue'  => 1,
                            'falseValue' => 1,
                        ],
                        
                        'name'           => [
                            'class' => DefaultActionColumn::class,
                        ],

                        'countShopProducts' => [
                            'value' => function(ShopSupplier $cmsSite) {
                                return $cmsSite->raw_row['countShopProducts'];
                            },
                            'attribute' => 'countShopProducts',
                            'label' => 'Количество товаров'
                        ],
                        'countShopStores' => [
                            'value' => function(ShopSupplier $cmsSite) {
                                return $cmsSite->raw_row['countShopStores'];
                            },
                            'attribute' => 'countShopStores',
                            'label' => 'Количество складов'
                        ],
                        'countShopTypePrices' => [
                            'value' => function(ShopSupplier $cmsSite) {
                                return $cmsSite->raw_row['countShopTypePrices'];
                            },
                            'attribute' => 'countShopTypePrices',
                            'label' => 'Количество типов цен'
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



            "stores" => [
                'class' => BackendGridModelRelatedAction::class,
                'accessCallback' => true,
                'name'            => "Склады",
                'icon'            => 'fa fa-list',
                'controllerRoute' => "/shop/admin-shop-store",
                'relation'        => ['shop_supplier_id' => 'id'],
                'priority'        => 600,
                'on gridInit'        => function($e) {
                    /**
                     * @var $action BackendGridModelRelatedAction
                     */
                    $action = $e->sender;
                    $action->relatedIndexAction->backendShowings = false;
                    $visibleColumns = $action->relatedIndexAction->grid['visibleColumns'];

                    ArrayHelper::removeValue($visibleColumns, 'shop_supplier_id');
                    $action->relatedIndexAction->grid['visibleColumns'] = $visibleColumns;

                },
            ],


            "prices" => [
                'class' => BackendGridModelRelatedAction::class,
                'accessCallback' => true,
                'name'            => "Цены",
                'icon'            => 'fa fa-list',
                'controllerRoute' => "/shop/admin-type-price",
                'relation'        => ['shop_supplier_id' => 'id'],
                'priority'        => 600,
                'on gridInit'        => function($e) {
                    /**
                     * @var $action BackendGridModelRelatedAction
                     */
                    $action = $e->sender;
                    $action->relatedIndexAction->backendShowings = false;
                    $visibleColumns = $action->relatedIndexAction->grid['visibleColumns'];

                    ArrayHelper::removeValue($visibleColumns, 'shop_supplier_id');
                    $action->relatedIndexAction->grid['visibleColumns'] = $visibleColumns;

                },
            ],

            "products" => [
                'class' => BackendGridModelRelatedAction::class,
                'accessCallback' => true,
                'name'            => "Товары",
                'icon'            => 'fa fa-list',
                'controllerRoute' => "/shop/admin-cms-content-element",
                //'relation'        => ['shopProduct.shop_supplier_id' => 'id'],
                'priority'        => 600,
                'on gridInit'        => function($e) {
                    /**
                     * @var $action BackendGridModelRelatedAction
                     */
                    $action = $e->sender;
                    
                    $action->relatedIndexAction->grid['on init'] = function (Event $e) {
                        /**
                         * @var $querAdminCmsContentElementControllery ActiveQuery
                         */
                        $query = $e->sender->dataProvider->query;
                        $query->joinWith("shopProduct as shopProduct");
                        $query->andWhere(['shopProduct.shop_supplier_id' => $this->model->id]);
                    };
                    
                    //$action->relatedIndexAction->backendShowings = false;
                    $visibleColumns = $action->relatedIndexAction->grid['visibleColumns'];

                    ArrayHelper::removeValue($visibleColumns, 'shop_supplier_id');
                    $action->relatedIndexAction->grid['visibleColumns'] = $visibleColumns;

                },
            ],

        ]);
    }

    public function updateFields($action)
    {
        return [
            'cms_image_id' => [
                'class'        => WidgetField::class,
                'widgetClass'  => AjaxFileUploadWidget::class,
                'widgetConfig' => [
                    'accept'   => 'image/*',
                    'multiple' => false,
                ],
            ],
            'is_active'  => [
                'class'      => BoolField::class,
            ],
            'name',
            'description'  => [
                'class'        => WidgetField::class,
                'widgetClass'  => CKEditorWidget::class,
                'widgetConfig' => [
                    'preset'        => false,
                    'clientOptions' => [
                        'enterMode'      => 2,
                        'height'         => 300,
                        'allowedContent' => true,
                        'extraPlugins'   => 'ckwebspeech,lineutils,dialogui',
                        'toolbar'        => [
                            ['name' => 'basicstyles', 'groups' => ['basicstyles', 'cleanup'], 'items' => ['Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat']],
                        ],
                    ],

                ],
            ],
            'description_internal'  => [
                'class'        => WidgetField::class,
                'widgetClass'  => CKEditorWidget::class,
                'widgetConfig' => [
                    'preset'        => false,
                    'clientOptions' => [
                        'enterMode'      => 2,
                        'height'         => 300,
                        'allowedContent' => true,
                        'extraPlugins'   => 'ckwebspeech,lineutils,dialogui',
                        'toolbar'        => [
                            ['name' => 'basicstyles', 'groups' => ['basicstyles', 'cleanup'], 'items' => ['Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat']],
                        ],
                    ],

                ],
            ],
            
            
            'is_main'  => [
                'class'      => BoolField::class,
                'allowNull'      => false,
            ],
            
            'external_id',


        ];
    }

}
