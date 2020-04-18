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
use skeeks\yii2\form\fields\BoolField;
use skeeks\yii2\form\fields\HtmlBlock;
use skeeks\yii2\form\fields\SelectField;
use skeeks\yii2\form\fields\TextareaField;
use yii\base\Event;
use yii\bootstrap\Alert;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

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
                'on beforeRender' => function (Event $e) {
                    $e->content = Alert::widget([
                        'closeButton' => false,
                        'options'     => [
                            'class' => 'alert-default',
                        ],

                        'body' => <<<HTML
Настройте какие цены будут доступны на сайте.
HTML
                        ,
                    ]);
                },

                "filters" => [
                    "visibleFilters" => [
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

                        $query->andWhere(['cms_site_id' => \Yii::$app->skeeks->site->id]);
                    },

                    'defaultOrder' => [
                        'priority' => SORT_ASC,
                    ],

                    'visibleColumns' => [
                        'checkbox',
                        'actions',

                        ///'id',

                        'custom',

                        'priority',

                    ],
                    'columns'        => [
                        'custom'       => [
                            'attribute' => 'name',
                            'format' => 'raw',
                            'value' => function (ShopTypePrice $model) {

                                $data = [];
                                $data[] = ($model->is_default ? '<span class="fa fa-check text-success" title="Цена по умолчанию"></span> ' : '').Html::a($model->asText, "#", ['class' => 'sx-trigger-action']);
                                $data[] = $model->description;

                                return implode("<br />", $data);
                            }
                        ]

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

            'is_default' => [
                'class' => BoolField::class,
                'formElement' => BoolField::ELEMENT_CHECKBOX,
                'allowNull' => false,
            ],
            'name',
            'description'      => [
                'class' => TextareaField::class,
            ],
            'priority',
            'external_id',
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
