<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */

namespace skeeks\cms\shop\controllers;

use skeeks\cms\backend\actions\BackendGridModelRelatedAction;
use skeeks\cms\backend\actions\BackendModelAction;
use skeeks\cms\backend\controllers\BackendModelStandartController;
use skeeks\cms\backend\grid\DefaultActionColumn;
use skeeks\cms\backend\ViewBackendAction;
use skeeks\cms\grid\BooleanColumn;
use skeeks\cms\helpers\Image;
use skeeks\cms\models\CmsAgent;
use skeeks\cms\models\CmsUser;
use skeeks\cms\rbac\CmsManager;
use skeeks\cms\shop\models\ShopStore;
use skeeks\cms\shop\models\ShopStoreProduct;
use skeeks\cms\shop\store\StoreUrlRule;
use skeeks\cms\widgets\AjaxFileUploadWidget;
use skeeks\cms\widgets\AjaxSelectModel;
use skeeks\cms\widgets\GridView;
use skeeks\cms\ya\map\widgets\YaMapDecodeInput;
use skeeks\cms\ya\map\widgets\YaMapInput;
use skeeks\yii2\ckeditor\CKEditorWidget;
use skeeks\yii2\form\fields\BoolField;
use skeeks\yii2\form\fields\FieldSet;
use skeeks\yii2\form\fields\HtmlBlock;
use skeeks\yii2\form\fields\NumberField;
use skeeks\yii2\form\fields\SelectField;
use skeeks\yii2\form\fields\WidgetField;
use yii\base\Event;
use yii\bootstrap\Alert;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;

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

        $this->permissionName = CmsManager::PERMISSION_ROLE_ADMIN_ACCESS;
        $this->generateAccessActions = false;

        parent::init();
    }


    /**
     * @inheritdoc
     */
    public function actions()
    {
        return ArrayHelper::merge(parent::actions(), [
            'index' => [
                'on beforeRender' => function (Event $e) {
                    $e->content = Alert::widget([
                        'closeButton' => false,
                        'options'     => [
                            'class' => 'alert-default',
                        ],

                        'body' => <<<HTML
Добавьте Ваш склад или магазин, для того чтобы указывать наличие по товарам.
HTML
                        ,
                    ]);
                },
                "filters"         => false,
                "backendShowings" => false,
                'grid'            => [
                    'on init'        => function (Event $e) {
                        /**
                         * @var $dataProvider ActiveDataProvider
                         * @var $query ActiveQuery
                         */
                        $query = $e->sender->dataProvider->query;

                        $query->cmsSite();
                        $query->andWhere(['is_supplier' => 0]);
                    },
                    'defaultOrder'   => [
                        'priority' => SORT_ASC,
                    ],
                    'visibleColumns' => [

                        'checkbox',
                        'actions',

                        //'id',
                        'custom',

                        'priority',

                        'is_active',

                        'countProducts',
                        'countReadyProducts',

                        'panel',
                    ],
                    'columns'        => [

                        'panel' => [
                            'label'         => '',
                            'format'        => 'raw',
                            'headerOptions' => [
                                'style' => 'width: 320px;',
                            ],
                            'value'         => function (ShopStore $shopStore) {
                                return Html::a('Панель <i class="fas fa-external-link-alt"></i>', Url::to(['/shop/store-product', StoreUrlRule::STORE_PARAM_NAME => $shopStore->id]), [
                                        'class'       => 'btn btn-secondary',
                                        'data-pjax'   => 0,
                                        'target'      => '_blank',
                                        'title'       => 'Открыть интерфейс управления в новой вкладке',
                                        'data-toggle' => 'tooltip',
                                    ]).Html::a('Интерфейс кассира <i class="fas fa-external-link-alt"></i>', Url::to(['/shop/cashier', StoreUrlRule::STORE_PARAM_NAME => $shopStore->id]), [
                                        'class'       => 'btn btn-secondary',
                                        'data-pjax'   => 0,
                                        'target'      => '_blank',
                                        'style'       => 'margin-left: 20px; ',
                                        'title'       => 'Открыть интерфейс кассира',
                                        'data-toggle' => 'tooltip',
                                    ]);
                            },
                        ],


                        'priority'  => [
                            'headerOptions' => [
                                'style' => 'width: 100px;',
                            ],
                        ],
                        'is_active' => [
                            'class'      => BooleanColumn::class,
                            'trueValue'  => 1,
                            'falseValue' => 1,
                        ],

                        'name' => [
                            'class'         => DefaultActionColumn::class,
                            'viewAttribute' => 'asText',
                        ],

                        'custom' => [
                            'attribute' => 'name',
                            'format'    => 'raw',
                            'value'     => function (ShopStore $model) {

                                $data = [];
                                $data[] = Html::a($model->asText, "#", ['class' => 'sx-trigger-action']);

                                if ($model->address) {
                                    $data[] = $model->address;
                                }
                                $info = implode("<br />", $data);

                                return "<div class='d-flex no-gutters'>
                                                <div class='sx-trigger-action my-auto' style='width: 50px;'>
                                                    <a href='#' style='text-decoration: none; border-bottom: 0;'>
                                                        <img src='".($model->cmsImage ? $model->cmsImage->src : Image::getCapSrc())."' style='max-width: 40px; max-height: 40px; border-radius: 5px;' />
                                                    </a>
                                                </div>
                                                <div style='margin-left: 5px;' class='my-auto'>".$info."</div></div>";;
                            },
                        ],

                        'countProducts' => [
                            'headerOptions' => [
                                'style' => 'width: 100px;',
                            ],
                            'value'         => function (ShopStore $shopStore) {
                                return $shopStore->raw_row['countProducts'];
                            },
                            'attribute'     => 'countProducts',
                            'label'         => 'Количество товаров',

                            'beforeCreateCallback' => function (GridView $grid) {
                                /**
                                 * @var $query ActiveQuery
                                 */
                                $query = $grid->dataProvider->query;

                                $subQuery = ShopStoreProduct::find()->select([new Expression("count(1)")])->where([
                                    'shop_store_id' => new Expression(ShopStore::tableName().".id"),
                                ]);

                                $query->addSelect([
                                    'countProducts' => $subQuery,
                                ]);

                                $grid->sortAttributes["countProducts"] = [
                                    'asc'  => ['countProducts' => SORT_ASC],
                                    'desc' => ['countProducts' => SORT_DESC],
                                ];
                            },
                        ],

                        'countReadyProducts' => [
                            'headerOptions' => [
                                'style' => 'width: 100px;',
                            ],
                            'value'         => function (ShopStore $shopStore) {
                                return "<div style='color: green;'>".$shopStore->raw_row['countReadyProducts']."</div>";
                            },
                            'format'        => 'raw',
                            'attribute'     => 'countProducts',
                            'label'         => 'Количество оформленных товаров',

                            'beforeCreateCallback' => function (GridView $grid) {
                                /**
                                 * @var $query ActiveQuery
                                 */
                                $query = $grid->dataProvider->query;

                                $subQuery = ShopStoreProduct::find()->select([new Expression("count(1)")])
                                    ->where([
                                        'shop_store_id' => new Expression(ShopStore::tableName().".id"),
                                    ])
                                    ->andWhere([
                                        'is not',
                                        'shop_product_id',
                                        null,
                                    ]);

                                $query->addSelect([
                                    'countReadyProducts' => $subQuery,
                                ]);

                                $grid->sortAttributes["countReadyProducts"] = [
                                    'asc'  => ['countReadyProducts' => SORT_ASC],
                                    'desc' => ['countReadyProducts' => SORT_DESC],
                                ];
                            },
                        ],
                    ],
                ],
            ],

            'orders' => [
                'class'    => BackendGridModelRelatedAction::class,
                'name'     => 'Продажи',
                'priority' => 400,
                'callback' => [$this, 'shift'],
                'icon'     => 'fas fa-credit-card',

                'controllerRoute' => "/shop/admin-order",
                'relation'        => ['shop_store_id' => 'id'],
                'on gridInit'     => function ($e) {
                    /**
                     * @var $action BackendGridModelRelatedAction
                     */
                    $action = $e->sender;
                    $action->relatedIndexAction->backendShowings = false;
                    $action->relatedIndexAction->filters = false;
                    $visibleColumns = $action->relatedIndexAction->grid['visibleColumns'];

                    ArrayHelper::removeValue($visibleColumns, 'shop_store_id');
                    $action->relatedIndexAction->grid['visibleColumns'] = $visibleColumns;

                },
            ],

            'cashebox' => [
                'class'    => BackendGridModelRelatedAction::class,
                'name'     => 'Кассы',
                'priority' => 400,
                'callback' => [$this, 'shift'],
                'icon'     => 'fas fa-credit-card',

                'controllerRoute' => "/shop/admin-shop-cashebox",
                'relation'        => ['shop_store_id' => 'id'],
                'on gridInit'     => function ($e) {
                    /**
                     * @var $action BackendGridModelRelatedAction
                     */
                    $action = $e->sender;
                    $action->relatedIndexAction->backendShowings = false;
                    $action->relatedIndexAction->filters = false;
                    $visibleColumns = $action->relatedIndexAction->grid['visibleColumns'];

                    ArrayHelper::removeValue($visibleColumns, 'shop_store_id');
                    $action->relatedIndexAction->grid['visibleColumns'] = $visibleColumns;

                },
            ],

            'stat' => [
                'class'    => BackendModelAction::class,
                'name'     => 'Статистика',
                'priority' => 500,
                'icon'     => 'fas fa-credit-card',
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
        $action->model->is_supplier = 0;
        \Yii::$app->view->registerCss(<<<CSS
.field-shopstore-is_supplier {
    display: none;
}
CSS
        );

        return [
            'main'           => [
                'class'  => FieldSet::class,
                'name'   => \Yii::t('skeeks/shop/app', 'Main'),
                'fields' => [
                    'cms_image_id' => [
                        'class'        => WidgetField::class,
                        'widgetClass'  => AjaxFileUploadWidget::class,
                        'widgetConfig' => [
                            'accept'   => 'image/*',
                            'multiple' => false,
                        ],
                    ],

                    'name',


                    'is_sync_external' => [
                        'class'     => BoolField::class,
                        'allowNull' => false,
                    ],

                    'is_personal_price' => [
                        'class'     => BoolField::class,
                        'allowNull' => false,
                    ]
                ],
            ],
            'selling_price'  => [
                'class'  => FieldSet::class,
                'name'   => \Yii::t('skeeks/shop/app', 'Формирование розничной цены'),
                'fields' => [
                    'source_selling_price' => [
                        'class'     => SelectField::class,
                        'allowNull' => false,
                        'items'     => [
                            'purchase_price' => 'Закупочная цена',
                            'selling_price'  => 'Розничная цена',
                        ],
                    ],
                    'selling_extra_charge' => [
                        'class'  => NumberField::class,
                        'append' => "%",
                        'step'   => 0.01,
                    ],
                ],
            ],
            'purchase_price' => [
                'class'          => FieldSet::class,
                'name'           => \Yii::t('skeeks/shop/app', 'Формирование закупочной цены'),
                'elementOptions' => [
                    'isOpen' => false,
                ],
                'fields'         => [
                    'source_purchase_price' => [
                        'class'     => SelectField::class,
                        'allowNull' => false,
                        'items'     => [
                            'purchase_price' => 'Закупочная цена',
                            'selling_price'  => 'Розничная цена',
                        ],
                    ],
                    'purchase_extra_charge' => [
                        'class'  => NumberField::class,
                        'append' => "%",
                        'step'   => 0.01,
                    ],
                ],
            ],

            'additional' => [
                'class'          => FieldSet::class,
                'name'           => \Yii::t('skeeks/shop/app', 'Дополнительно'),
                'elementOptions' => [
                    'isOpen' => false,
                ],
                'fields'         => [
                    'is_active'   => [
                        'class'     => BoolField::class,
                        'allowNull' => false,
                    ],
                    'is_supplier' => [
                        'class'     => BoolField::class,
                        'allowNull' => false,
                    ],
                    'external_id',
                    'priority'    => [
                        'class' => NumberField::class,
                    ],
                ],
            ],

            'description' => [
                'class'          => FieldSet::class,
                'name'           => \Yii::t('skeeks/shop/app', 'Описание'),
                'elementOptions' => [
                    'isOpen' => false,
                ],
                'fields'         => [
                    'description' => [
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
                ],
            ],
            'addresses'   => [
                'class'          => FieldSet::class,
                'name'           => \Yii::t('skeeks/shop/app', 'Контакты'),
                'elementOptions' => [
                    'isOpen' => false,
                ],
                'fields'         => [
                    'address' => [
                        'class'        => WidgetField::class,
                        'widgetClass'  => YaMapDecodeInput::class,
                        'widgetConfig' => [
                            'modelLatitudeAttr'  => 'latitude',
                            'modelLongitudeAttr' => 'longitude',
                        ],
                    ],

                    [
                        'class'   => HtmlBlock::class,
                        'content' => '<div style="display: none;">',
                    ],
                    'latitude',
                    'longitude',

                    [
                        'class'   => HtmlBlock::class,
                        'content' => '</div>',
                    ],

                    'work_time' => [
                        'class'       => WidgetField::class,
                        'widgetClass' => \skeeks\yii2\scheduleInputWidget\ScheduleInputWidget::class,
                    ],
                ],
            ],
            'cashier'     => [
                'class'          => FieldSet::class,
                'name'           => \Yii::t('skeeks/shop/app', 'Работа на кассе'),
                'elementOptions' => [
                    'isOpen' => false,
                ],
                'fields'         => [
                    'is_allow_no_check' => [
                        'class'     => BoolField::class,
                        'allowNull' => false,
                    ],
                    'cashier_is_allow_sell_out_of_stock' => [
                        'class'     => BoolField::class,
                        'allowNull' => false,
                    ],
                    'cashier_is_show_out_of_stock'       => [
                        'class'     => BoolField::class,
                        'allowNull' => false,
                    ],
                    'cashier_is_show_only_inner_products'       => [
                        'class'     => BoolField::class,
                        'allowNull' => false,
                    ],
                    'cashier_default_cms_user_id'       => [
                        'class'        => WidgetField::class,
                        'widgetClass'  => AjaxSelectModel::class,
                        'widgetConfig' => [
                            'modelClass' => CmsUser::class,
                            'searchQuery' => function($word = '') {
                                $query = CmsUser::find()->cmsSite();
                                if ($word) {
                                    $query->search($word);
                                }
                                return $query;
                            },
                        ],
                    ],
                ],
            ],


        ];
    }


}
