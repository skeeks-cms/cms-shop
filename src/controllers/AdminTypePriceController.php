<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

namespace skeeks\cms\shop\controllers;

use skeeks\cms\backend\controllers\BackendModelStandartController;
use skeeks\cms\models\CmsAgent;
use skeeks\cms\rbac\CmsManager;
use skeeks\cms\shop\models\ShopTypePrice;
use skeeks\yii2\form\fields\BoolField;
use skeeks\yii2\form\fields\FieldSet;
use skeeks\yii2\form\fields\NumberField;
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
        $this->permissionName = CmsManager::PERMISSION_ROLE_ADMIN_ACCESS;

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

                "filters"         => false,
                "backendShowings" => false,

                'grid' => [
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
                        'custom' => [
                            'attribute' => 'name',
                            'format'    => 'raw',
                            'value'     => function (ShopTypePrice $model) {

                                $data = [];
                                $name = '';
                                if ($model->is_default) {
                                    $name = '<span class="fas fa-lock" title="Базовая розничная цена" style="margin-right: 5px;"></span>';
                                }
                                if ($model->is_purchase) {
                                    $name = '<span class="fas fa-lock" title="Закупочная цена" style="margin-right: 5px;"></span>';
                                }
                                $data[] = $name.Html::a($model->asText, "#", ['class' => 'sx-trigger-action']);
                                if ($model->description) {
                                    $data[] = $model->description;
                                }
                                if ($model->is_auto) {
                                    $data[] = "<small style='color: gray;'>Цена рассчитывается автоматически от цены: ".$model->baseAutoShopTypePrice->asText."</small>";
                                }

                                return implode("<br />", $data);
                            },
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

        if (ShopTypePrice::find()->cmsSite()->andWhere(['is_default' => 1])->exists()) {
            \Yii::$app->view->registerCss(<<<CSS
.field-shoptypeprice-is_default {
    display: none;
}
CSS
            );
        }

        if (ShopTypePrice::find()->cmsSite()->andWhere(['is_purchase' => 1])->exists()) {
            \Yii::$app->view->registerCss(<<<CSS
.field-shoptypeprice-is_purchase {
    display: none;
}
CSS
            );
        }

        if ($model->isNewRecord) {

        }

        $result = [
            'main' => [
                'class'  => FieldSet::class,
                'name'   => 'Основное',
                'fields' => [
                    'is_default'  => [
                        'class'     => BoolField::class,
                        'allowNull' => true,
                    ],
                    'is_purchase' => [
                        'class'     => BoolField::class,
                        'allowNull' => true,
                    ],
                    'name',
                    'description' => [
                        'class' => TextareaField::class,
                    ],

                ],
            ],

            'permissions' => [
                'class'  => FieldSet::class,
                'name'   => 'Права доступа',
                'fields' => [
                    'cmsUserRoles'     => [
                        'class'    => SelectField::class,
                        'multiple' => true,
                        'items'    => \yii\helpers\ArrayHelper::map(
                            \Yii::$app->authManager->getAvailableRoles(), 'name', 'description'
                        ),
                    ],
                    'viewCmsUserRoles' => [
                        'class'    => SelectField::class,
                        'multiple' => true,
                        'items'    => \yii\helpers\ArrayHelper::map(
                            \Yii::$app->authManager->getAvailableRoles(), 'name', 'description'
                        ),
                    ],
                ],
            ],

            'other' => [
                'class'  => FieldSet::class,
                'name'   => 'Прочее',
                'fields' => [
                    'priority' => [
                        'class' => NumberField::class,
                    ],
                    'external_id',
                    'is_auto'  => [
                        'class'          => BoolField::class,
                        'formElement'    => BoolField::ELEMENT_CHECKBOX,
                        'allowNull'      => false,
                        'elementOptions' => [
                            'data-form-reload' => 'true',
                        ],
                    ],
                ],
            ],
        ];

        if ($model->is_auto) {
            $q = ShopTypePrice::find()->cmsSite();
            if (!$model->isNewRecord) {
                $q->andWhere(['!=', 'id', $model->id]);
            }
            $result['other']['fields'] = ArrayHelper::merge($result['other']['fields'], [
                'base_auto_shop_type_price_id' => [
                    'class' => SelectField::class,
                    'items' => ArrayHelper::map(
                        $q->all(),
                        'id',
                        'asText'
                    ),
                ],
                "auto_extra_charge"            => [
                    'class'  => NumberField::class,
                    'append' => "%",
                ],
            ]);
        }


        /*if ($model->isNewRecord) {
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
        }*/

        return $result;
    }

}
