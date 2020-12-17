<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

namespace skeeks\cms\shop\controllers;

use skeeks\cms\backend\controllers\BackendModelStandartController;
use skeeks\cms\backend\grid\DefaultActionColumn;
use skeeks\cms\grid\BooleanColumn;
use skeeks\cms\models\CmsAgent;
use skeeks\cms\shop\models\ShopVat;
use skeeks\yii2\form\fields\BoolField;
use skeeks\yii2\form\fields\NumberField;
use yii\helpers\ArrayHelper;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class AdminVatController extends BackendModelStandartController
{
    public function init()
    {
        $this->name = \Yii::t('skeeks/shop/app', 'VAT rates');
        $this->modelShowAttribute = "name";
        $this->modelClassName = ShopVat::class;

        $this->generateAccessActions = false;

        $this->accessCallback = function () {
            if (!\Yii::$app->skeeks->site->is_default) {
                return false;
            }
            return \Yii::$app->user->can($this->permissionName);
        };

        parent::init();
    }


    /**
     * @inheritdoc
     */
    public function actions()
    {
        return ArrayHelper::merge(parent::actions(), [

            "index" => [
                "filters"         => false,
                "backendShowings" => false,

                'grid' => [

                    'defaultOrder' => [
                        'priority' => SORT_ASC,
                    ],

                    'visibleColumns' => [
                        'checkbox',
                        'actions',

                        ///'id',

                        'name',
                        'rate',
                        'priority',
                        'is_active',

                    ],
                    'columns'        => [
                        'name'      => [
                            'class' => DefaultActionColumn::class,
                        ],
                        'is_active' => [
                            'class' => BooleanColumn::class,
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
            'name',
            'rate' => [
                'class'  => NumberField::class,
                'append' => "%",
            ],
            'priority' => [
                'class'  => NumberField::class,
            ],

            'is_active' => [
                'class' => BoolField::class,
                'allowNull' => false,
            ],
        ];
    }


}
