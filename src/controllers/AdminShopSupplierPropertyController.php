<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */

namespace skeeks\cms\shop\controllers;

use skeeks\cms\backend\actions\BackendGridModelRelatedAction;
use skeeks\cms\backend\controllers\BackendModelStandartController;
use skeeks\cms\backend\grid\DefaultActionColumn;
use skeeks\cms\grid\BooleanColumn;
use skeeks\cms\models\CmsAgent;
use skeeks\cms\shop\models\ShopProduct;
use skeeks\cms\shop\models\ShopStore;
use skeeks\cms\shop\models\ShopSupplier;
use skeeks\cms\shop\models\ShopSupplierProperty;
use skeeks\cms\shop\models\ShopTypePrice;
use skeeks\cms\widgets\AjaxFileUploadWidget;
use skeeks\yii2\ckeditor\CKEditorWidget;
use skeeks\yii2\form\fields\BoolField;
use skeeks\yii2\form\fields\SelectField;
use skeeks\yii2\form\fields\WidgetField;
use yii\base\Event;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

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
            'index'  => [
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

                        'is_visible',
                    ],
                    'columns'        => [
                        'is_visible' => [
                            'class'      => BooleanColumn::class,
                        ],

                        'external_code' => [
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
        ]);
    }

    public function updateFields($action)
    {
        return [

            'shop_supplier_id'         => [
                'class'        => SelectField::class,
                'items'  => ArrayHelper::map(
                    ShopSupplier::find()->all(),
                    'id',
                    'asText'
                )
            ],

            'is_visible' => [
                'class'     => BoolField::class,
                'allowNull' => false,
            ],

            'external_code',
            'name',
        ];
    }

}
