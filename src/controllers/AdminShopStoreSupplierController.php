<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */

namespace skeeks\cms\shop\controllers;

use skeeks\cms\backend\controllers\BackendModelStandartController;
use skeeks\cms\backend\grid\DefaultActionColumn;
use skeeks\cms\grid\BooleanColumn;
use skeeks\cms\helpers\Image;
use skeeks\cms\helpers\RequestResponse;
use skeeks\cms\models\CmsAgent;
use skeeks\cms\query\CmsActiveQuery;
use skeeks\cms\shop\components\ShopComponent;
use skeeks\cms\shop\models\ShopStore;
use skeeks\cms\shop\models\ShopStoreProduct;
use skeeks\cms\shop\store\StoreUrlRule;
use skeeks\cms\widgets\AjaxFileUploadWidget;
use skeeks\cms\widgets\GridView;
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
class AdminShopStoreSupplierController extends BackendModelStandartController
{
    public function init()
    {
        $this->name = "Поставщики";
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
            'index' => [
                'on beforeRender' => function (Event $e) {

                        $backendUrl = Url::to(['update-prices']);
                        \Yii::$app->view->registerJs(<<<JS
$(".sx-import").on("click", function() {
    var jBtn = $(this);
    if (jBtn.hasClass("disabled")) {
        return false;
    }
    var Blocker = sx.block($(".sx-main-col"));
    jBtn.addClass("disabled");
    
    var AjaxQuery = sx.ajax.preparePostQuery("{$backendUrl}");
    var AjaxHandler = new sx.classes.AjaxHandlerStandartRespose(AjaxQuery);
    
    AjaxHandler.on("success", function () {
        setTimeout(function() {
            sx.notify.info("Страница сейчас будет перезагружена");
        }, 1000)
        
        setTimeout(function() {
            window.location.reload();
        }, 3000)
        
        /*Blocker.unblock();
        jBtn.removeClass("disabled");*/
    });
    AjaxHandler.on("error", function () {
        Blocker.unblock();
        jBtn.removeClass("disabled");
    });
    
    AjaxQuery.execute();
    
    return false;
});
JS
                        );

                        $btn = Html::button("<i class='fas fa-sync'></i> Обновить цены", [
                            'class' => 'btn btn-primary sx-import',
                            'title' => 'Эта кнопка запускает обновление цен товаров на сайте',
                            'data-toggle' => 'tooltip'
                        ]);

                    $e->content = Alert::widget([
                        'closeButton' => false,
                        'options'     => [
                            'class' => 'alert-default',
                        ],

                        'body' => <<<HTML
<p>{$btn}</p>
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
                         * @var $query CmsActiveQuery
                         */
                        $query = $e->sender->dataProvider->query;

                        $query->cmsSite();
                        $query->andWhere(['is_supplier' => 1]);
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

                        'panel' => [
                            'label'         => '',
                            'format'        => 'raw',
                            'headerOptions' => [
                                'style' => 'width: 120px;',
                            ],
                            'value'         => function (ShopStore $shopStore) {
                                return Html::a('Панель <i class="fas fa-external-link-alt"></i>', Url::to(['/shop/store-product', StoreUrlRule::STORE_PARAM_NAME => $shopStore->id]), [
                                    'class'       => 'btn btn-secondary',
                                    'data-pjax'   => 0,
                                    'target'      => '_blank',
                                    'title'       => 'Открыть интерфейс управления в новой вкладке',
                                    'data-toggle' => 'tooltip',
                                ]);
                            },
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

            "create" => [
                'fields' => [$this, 'updateFields'],
            ],
            "update" => [
                'fields' => [$this, 'updateFields'],
            ],


            /*"products" => [
                'class'           => BackendGridModelRelatedAction::class,
                'name'            => ['skeeks/shop/app', 'Товары'],
                'icon'            => 'fas fa-list',
                'controllerRoute' => "/shop/admin-shop-store-product",
                'relation'        => ['shop_store_id' => 'id'],
                'priority'        => 600,
                'on gridInit'     => function ($e) {
                    /**
                     * @var $action BackendGridModelRelatedAction
                    $action = $e->sender;
                    $action->relatedIndexAction->backendShowings = false;
                    $visibleColumns = $action->relatedIndexAction->grid['visibleColumns'];

                    ArrayHelper::removeValue($visibleColumns, 'shop_store_id');
                    $action->relatedIndexAction->grid['visibleColumns'] = $visibleColumns;

                },
            ],*/
        ]);
    }

    public function updateFields($action)
    {
        $action->model->load(\Yii::$app->request->get());
        $action->model->is_supplier = 1;
        \Yii::$app->view->registerCss(<<<CSS
.field-shopstore-is_supplier {
    display: none;
}
CSS
        );

        return [
            'main' => [
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


                ],
            ],
            'selling_price' => [
                'class'  => FieldSet::class,
                'name'   => \Yii::t('skeeks/shop/app', 'Формирование розничной цены'),
                'fields' => [
                    'source_selling_price' => [
                        'class' => SelectField::class,
                        'allowNull' => false,
                        'items' => [
                            'purchase_price' => 'Закупочная цена',
                            'selling_price' => 'Розничная цена',
                        ]
                    ],
                    'selling_extra_charge' => [
                        'class' => NumberField::class,
                        'append' => "%",
                        'step' => 0.01
                    ],
                ],
            ],
            'purchase_price' => [
                'class'  => FieldSet::class,
                'name'   => \Yii::t('skeeks/shop/app', 'Формирование закупочной цены'),
                'elementOptions' => [
                    'isOpen' => false
                ],
                'fields' => [
                    'source_purchase_price' => [
                        'class' => SelectField::class,
                        'allowNull' => false,
                        'items' => [
                            'purchase_price' => 'Закупочная цена',
                            'selling_price' => 'Розничная цена',
                        ]
                    ],
                    'purchase_extra_charge' => [
                        'class' => NumberField::class,
                        'append' => "%",
                        'step' => 0.01
                    ],
                ],
            ],

            'additional' => [
                'class'  => FieldSet::class,
                'name'   => \Yii::t('skeeks/shop/app', 'Дополнительно'),
                'elementOptions' => [
                    'isOpen' => false
                ],
                'fields' => [
                    'is_active'    => [
                        'class'     => BoolField::class,
                        'allowNull' => false,
                    ],
                    'is_supplier'  => [
                        'class'     => BoolField::class,
                        'allowNull' => false,
                    ],
                    'external_id',
                    'priority'    => [
                        'class' => NumberField::class,
                    ],
                ]
            ],

            'description' => [
                'class'  => FieldSet::class,
                'name'   => \Yii::t('skeeks/shop/app', 'Описание'),
                'elementOptions' => [
                    'isOpen' => false
                ],
                'fields' => [
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
                ]
            ],
            'addresses' => [
                'class'  => FieldSet::class,
                'name'   => \Yii::t('skeeks/shop/app', 'Контакты'),
                'elementOptions' => [
                    'isOpen' => false
                ],
                'fields' => [
                    'coordinates' => [
                        'class'        => WidgetField::class,
                        'widgetClass'  => YaMapInput::class,
                        'widgetConfig' => [
                            'YaMapWidgetOptions' => [
                                'options' => [
                                    'style' => 'height: 400px;',
                                ],
                            ],

                            'clientOptions' => [
                                'select' => new \yii\web\JsExpression(<<<JS
            function(e, data)
            {
                var lat = data.coords[0];
                var long = data.coords[1];
                var address = data.address;
                var phone = data.phone;
                var email = data.email;
        
                $('#shopstore-address').val(address);
                $('#shopstore-latitude').val(lat);
                $('#shopstore-longitude').val(long);
            }
JS
                                ),
                            ],
                        ],
                    ],

                    [
                        'class'   => HtmlBlock::class,
                        'content' => '<div style="display: block;">',
                    ],
                    'address',
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


        ];
    }


    /**
     * Загрузка данных поставщика на сайт
     *
     * @return RequestResponse
     */
    public function actionUpdatePrices()
    {
        $rr = new RequestResponse();
        $rr->success = true;
        $rr->message = "Данные успешно обновлены";

        try {
            ShopComponent::updateProductPrices();
        } catch (\Exception $e) {
            throw $e;
            $rr->success = false;
            $rr->message = "Ошибка загрузки данных: " . $e->getMessage();
        }

        return $rr;
    }
}
