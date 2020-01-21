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
use skeeks\cms\models\CmsAgent;
use skeeks\cms\shop\models\ShopSupplier;
use skeeks\cms\shop\models\ShopTypePrice;
use skeeks\yii2\form\fields\HtmlBlock;
use skeeks\yii2\form\fields\SelectField;
use skeeks\yii2\form\fields\TextareaField;
use yii\helpers\ArrayHelper;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class AdminTypePriceController extends BackendModelStandartController
{

    public function init()
    {
        $this->name = \Yii::t('skeeks/shop/app', 'Types of prices');
        $this->modelShowAttribute = "asText";
        $this->modelClassName = ShopTypePrice::class;

        $this->generateAccessActions = false;

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return ArrayHelper::merge(parent::actions(), [

            "index" => [
                "filters" => [
                    "visibleFilters" => [
                        'id',
                        'name',
                    ],
                ],
                'grid'    => [
                    'defaultOrder' => [
                        'priority' => SORT_ASC,
                    ],

                    'visibleColumns' => [
                        'checkbox',
                        'actions',

                        ///'id',

                        'name',
                        'shop_supplier_id',

                        'priority',

                    ],
                    'columns'        => [
                        'name' => [
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
        /**
         * @var $model ShopTypePrice
         */
        $model = $action->model;
        
        $model->load(\Yii::$app->request->get());

        $result = [
            'shop_supplier_id' => [
                'class' => SelectField::class,
                'items' => ArrayHelper::map(
                    ShopSupplier::find()->all(),
                    'id',
                    'asText'
                )
            ],
            'name',
            'description' => [
                'class' => TextareaField::class,
            ],
            'priority',
        ];

        if ($model->isNewRecord) {
            $result[] = [
                'class'   => HtmlBlock::class,
                'content' => \yii\bootstrap\Alert::widget([
                    'options' => [
                        'class' => 'alert-info',
                    ],
                    'body'    => \skeeks\cms\shop\Module::t('app', 'After saving can be set up to whom this type available price'),
                ]),
            ];
        } else {
            $result[] = [
                'class'   => HtmlBlock::class,
                'content' => \yii\bootstrap\Alert::widget([
                        'options' => [
                            'class' => 'alert-warning',
                        ],
                        'body'    => \skeeks\cms\shop\Module::t('app',
                            '<b> Warning! </b> Permissions are stored in real time. Thus, these settings are independent of site or user.'),
                    ]).
                    \skeeks\cms\rbac\widgets\adminPermissionForRoles\AdminPermissionForRolesWidget::widget([
                        'notClosedRoles'        => [],
                        'permissionName'        => $model->viewPermissionName,
                        'permissionDescription' => \skeeks\cms\shop\Module::t('app', 'Rights to see the prices')." '{$model->name}'",
                        'label'                 => \skeeks\cms\shop\Module::t('app', 'User Groups that have permission to view this type of price'),
                    ])
                    .
                    \skeeks\cms\rbac\widgets\adminPermissionForRoles\AdminPermissionForRolesWidget::widget([
                        'permissionName'        => $model->buyPermissionName,
                        'notClosedRoles'        => [],
                        'permissionDescription' => \skeeks\cms\shop\Module::t('app',
                                'The right to buy at a price').": '{$model->name}'",
                        'label'                 => \skeeks\cms\shop\Module::t('app',
                            'Group of users who have the right to purchase on this type of price'),
                    ]),
            ];
        }
        
        return $result;
    }

}
