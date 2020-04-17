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
use skeeks\cms\grid\ImageColumn;
use skeeks\cms\models\CmsAgent;
use skeeks\cms\shop\models\ShopDelivery;
use skeeks\cms\shop\models\ShopStore;
use skeeks\cms\shop\models\ShopSupplier;
use skeeks\cms\widgets\AjaxFileUploadWidget;
use skeeks\yii2\ckeditor\CKEditorWidget;
use skeeks\yii2\form\fields\BoolField;
use skeeks\yii2\form\fields\FieldSet;
use skeeks\yii2\form\fields\SelectField;
use skeeks\yii2\form\fields\TextareaField;
use skeeks\yii2\form\fields\WidgetField;
use yii\base\Event;
use yii\bootstrap\Alert;
use yii\helpers\ArrayHelper;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class AdminShopStoreController extends BackendModelStandartController
{
    public function init()
    {
        $this->name = "Склады";
        $this->modelShowAttribute = "asText";
        $this->modelClassName = ShopStore::class;

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
                'on beforeRender' => function (Event $e) {
                    $e->content = Alert::widget([
                        'closeButton' => false,
                        'options'     => [
                            'class' => 'alert-default',
                        ],

                        'body' => <<<HTML
Добавьте склады для того чтобы указывать наличие по товарам на них.
HTML
                        ,
                    ]);
                },
                "filters" => [
                    'visibleFilters' => [
                        'id',
                        'name',
                        'shop_supplier_id',
                    ],
                ],
                'grid'    => [
                    'on init' => function (Event $e) {
                        /**
                         * @var $dataProvider ActiveDataProvider
                         * @var $query ActiveQuery
                         */
                        $query = $e->sender->dataProvider->query;

                        $query->andWhere(['cms_site_id' => \Yii::$app->skeeks->site->id]);
                    },
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
                            'viewAttribute' => 'asText',
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
        $action->model->load(\Yii::$app->request->get());
        
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

            'external_id',

        ];
    }

}
