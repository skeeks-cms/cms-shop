<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */

namespace skeeks\cms\shop\controllers;

use skeeks\cms\backend\controllers\BackendModelStandartController;
use skeeks\cms\shop\models\ShopPersonTypeProperty;
use skeeks\cms\shop\models\ShopPersonTypePropertyEnum;
use skeeks\yii2\form\fields\SelectField;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class AdminPersonTypePropertyEnumController extends BackendModelStandartController
{
    public function init()
    {
        $this->name = \Yii::t('skeeks/shop/app', 'Control of property values payer');
        $this->modelShowAttribute = "value";
        $this->modelClassName = ShopPersonTypePropertyEnum::class;

        parent::init();

    }

    public function actions()
    {
        return ArrayHelper::merge(parent::actions(), [
            'index'  => [
                'filters' => [
                    'visibleFilters' => [
                        'value',
                        'property_id',
                    ],
                ],
                'grid'    => [
                    'visibleColumns' => [
                        'checkbox',
                        'actions',
                        'id',
                        'value',
                        'property_id',
                        'code',
                        'priority',
                    ],
                    'columns'        => [
                        'value' => [
                            'attribute' => "value",
                            'format'    => "raw",
                            'value'     => function (ShopPersonTypePropertyEnum $model) {
                                return Html::a($model->value, "#", [
                                    'class' => "sx-trigger-action",
                                ]);
                            },
                        ],
                    ],
                ],
            ],
            'create' => [
                'fields' => [$this, 'updateFields'],
            ],
            'update' => [
                'fields' => [$this, 'updateFields'],
            ],
        ]);
    }


    public function updateFields($action)
    {
        /**
         * @var $model Form2FormProperty
         */
        $model = $action->model;
        $model->load(\Yii::$app->request->get());

        if ($property_id = \Yii::$app->request->get("property_id")) {
            $model->property_id = $property_id;
        }
        return [
            'property_id' => [
                'class' => SelectField::class,
                'items' => function () {
                    return \yii\helpers\ArrayHelper::map(
                        ShopPersonTypeProperty::find()->all(),
                        "id",
                        "asText"
                    );
                },
            ],
            'value',
            'code',
            'priority',
        ];
    }

}
