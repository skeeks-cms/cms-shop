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
use skeeks\cms\grid\BooleanColumn;
use skeeks\cms\grid\ImageColumn;
use skeeks\cms\models\CmsAgent;
use skeeks\cms\shop\models\ShopDelivery;
use skeeks\cms\shop\models\ShopSupplier;
use skeeks\cms\widgets\AjaxFileUploadWidget;
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
        $this->modelShowAttribute = "name";
        $this->modelClassName = ShopSupplier::class;

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
                    ],
                ],
                'grid'    => [
                    'defaultOrder'   => [
                        'id' => SORT_DESC,
                    ],
                    'visibleColumns' => [

                        'checkbox',
                        'actions',

                        'id',
                        'name',

                        'is_active',
                    ],
                    'columns'        => [
                        'is_active' => [
                            'class'      => BooleanColumn::class,
                            'trueValue'  => 1,
                            'falseValue' => 1,
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
                'class'      => TextareaField::class,
            ],

        ];
    }

}
