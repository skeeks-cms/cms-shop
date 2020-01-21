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
use skeeks\cms\shop\models\ShopSupplier;
use skeeks\cms\widgets\AjaxFileUploadWidget;
use skeeks\yii2\ckeditor\CKEditorWidget;
use skeeks\yii2\form\fields\BoolField;
use skeeks\yii2\form\fields\FieldSet;
use skeeks\yii2\form\fields\SelectField;
use skeeks\yii2\form\fields\TextareaField;
use skeeks\yii2\form\fields\WidgetField;
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
                    'defaultOrder'   => [
                        'id' => SORT_DESC,
                    ],
                    'visibleColumns' => [

                        'checkbox',
                        'actions',

                        //'id',
                        'name',

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
            
            'external_id',


        ];
    }

}
