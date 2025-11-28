<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 31.05.2015
 */

namespace skeeks\cms\shop\controllers;

use Mpdf\Writer\ColorWriter;
use skeeks\cms\backend\controllers\BackendModelStandartController;
use skeeks\cms\backend\grid\DefaultActionColumn;
use skeeks\cms\grid\BooleanColumn;
use skeeks\cms\grid\DateTimeColumnData;
use skeeks\cms\grid\ImageColumn2;
use skeeks\cms\grid\UserColumnData;
use skeeks\cms\helpers\Image;
use skeeks\cms\models\CmsCountry;
use skeeks\cms\queryfilters\QueryFiltersEvent;
use skeeks\cms\rbac\CmsManager;
use skeeks\cms\shop\models\ShopBrand;
use skeeks\cms\shop\models\ShopCollection;
use skeeks\cms\shop\models\ShopCollectionSticker;
use skeeks\cms\shop\models\ShopProduct;
use skeeks\cms\widgets\AjaxFileUploadWidget;
use skeeks\cms\widgets\AjaxSelectModel;
use skeeks\cms\widgets\ColorInput;
use skeeks\cms\widgets\formInputs\comboText\ComboTextInputWidget;
use skeeks\cms\widgets\GridView;
use skeeks\yii2\form\fields\BoolField;
use skeeks\yii2\form\fields\FieldSet;
use skeeks\yii2\form\fields\NumberField;
use skeeks\yii2\form\fields\SelectField;
use skeeks\yii2\form\fields\WidgetField;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class AdminShopCollectionStickerController extends BackendModelStandartController
{
    public function init()
    {
        $this->name = \Yii::t('skeeks/cms', "Стикеры коллекций");
        $this->modelShowAttribute = "name";
        $this->modelClassName = ShopCollectionSticker::class;

        $this->generateAccessActions = true;
        /*$this->permissionName = CmsManager::PERMISSION_ROLE_ADMIN_ACCESS;*/

        /*$this->accessCallback = function () {
            if (!\Yii::$app->skeeks->site->is_default) {
                return false;
            }
            return \Yii::$app->user->can(CmsManager::PERMISSION_ROLE_ADMIN_ACCESS);
        };*/


        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return ArrayHelper::merge(parent::actions(), [
            'index' => [
                "filters" => [
                    'visibleFilters' => [
                        'q',
                    ],
                ],
            'grid'  => [
                'defaultOrder'   => [
                    'created_at' => SORT_DESC,
                ],


                'visibleColumns' => [
                    'checkbox',
                    'actions',

                    'created_at',
                    'name',
                    'color',
                ],

                'columns'        => [

                    'created_at'   => [
                        'class' => DateTimeColumnData::class
                    ],
                    'created_by'   => [
                        'class' => UserColumnData::class
                    ],
                    'name'   => [
                        'class' => DefaultActionColumn::class
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
            'main' => [
                'class'  => FieldSet::class,
                'name'   => \Yii::t('skeeks/cms', 'Main'),
                'fields' => [

                    'name',
                    'color' => [
                        'class'       => WidgetField::class,
                        'widgetClass' => ColorInput::class,
                    ],


                    'description' => [
                        'class'       => WidgetField::class,
                        'widgetClass' => ComboTextInputWidget::class,
                    ],

                    'priority' => [
                        'class' => NumberField::class,
                    ],

                ],
            ],

        ];
    }
}
