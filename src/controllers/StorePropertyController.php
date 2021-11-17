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
use skeeks\cms\backend\actions\BackendGridModelRelatedAction;
use skeeks\cms\backend\controllers\BackendModelStandartController;
use skeeks\cms\backend\grid\DefaultActionColumn;
use skeeks\cms\components\Cms;
use skeeks\cms\grid\BooleanColumn;
use skeeks\cms\helpers\RequestResponse;
use skeeks\cms\models\CmsAgent;
use skeeks\cms\models\CmsContentElement;
use skeeks\cms\models\CmsContentProperty;
use skeeks\cms\models\CmsContentPropertyEnum;
use skeeks\cms\queryfilters\QueryFiltersEvent;
use skeeks\cms\relatedProperties\propertyTypes\PropertyTypeElement;
use skeeks\cms\relatedProperties\propertyTypes\PropertyTypeList;
use skeeks\cms\shop\models\ShopStoreProduct;
use skeeks\cms\shop\models\ShopStoreProperty;
use skeeks\cms\shop\models\ShopStorePropertyOption;
use skeeks\yii2\form\fields\BoolField;
use skeeks\yii2\form\fields\FieldSet;
use skeeks\yii2\form\fields\NumberField;
use skeeks\yii2\form\fields\SelectField;
use skeeks\yii2\form\fields\TextareaField;
use skeeks\yii2\form\fields\TextField;
use yii\base\Event;
use yii\base\Exception;
use yii\bootstrap\Alert;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\helpers\UnsetArrayValue;
use yii\helpers\Url;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class StorePropertyController extends BackendModelStandartController
{
    public function init()
    {
        $this->name = "Характеристики";
        $this->modelShowAttribute = "asText";
        $this->modelClassName = ShopStoreProperty::class;

        $this->permissionName = Cms::UPA_PERMISSION;

        parent::init();
    }


    /**
     * @inheritdoc
     */
    public function actions()
    {
        return ArrayHelper::merge(parent::actions(), [
            'index'         => [
                'on beforeRender' => function (Event $e) {

                    $url = Url::to(['update-properties']);
                    $urlOptions = Url::to(['update-options']);
                    $urlConnectOptions = Url::to(['connect-options']);

                    \Yii::$app->view->registerJs(<<<JS
$(".sx-update-properties").on("click", function() {
    
    var ajaxQuery = new sx.ajax.preparePostQuery('{$url}');
    
    new sx.classes.AjaxHandlerStandartRespose(ajaxQuery, {
        'blockerSelector' : 'body',
        'enableBlocker' : true,
    }).on("success", function() {
        setTimeout(function() {
            window.location.reload();
        }, 1000);
    });
    
    ajaxQuery.execute();
    
    return false;
});

$(".sx-update-options").on("click", function() {
    
    var ajaxQuery = new sx.ajax.preparePostQuery('{$urlOptions}');
    
    new sx.classes.AjaxHandlerStandartRespose(ajaxQuery, {
        'blockerSelector' : 'body',
        'enableBlocker' : true,
    }).on("success", function() {
        setTimeout(function() {
            window.location.reload();
        }, 1000);
    });
    
    ajaxQuery.execute();
    
    return false;
});


$(".sx-connect-options").on("click", function() {
    
    var ajaxQuery = new sx.ajax.preparePostQuery('{$urlConnectOptions}');
    
    new sx.classes.AjaxHandlerStandartRespose(ajaxQuery, {
        'blockerSelector' : 'body',
        'enableBlocker' : true,
    }).on("success", function() {
        setTimeout(function() {
            window.location.reload();
        }, 1000);
    });
    
    ajaxQuery.execute();
    
    return false;
});
JS
                    );
                    $e->content = Alert::widget([
                        'closeButton' => false,
                        'options'     => [
                            'class' => 'alert-default',
                        ],

                        'body' => <<<HTML
<a href="#" class="btn btn-secondary sx-update-properties" 
title="Эта кнопка запускает процесс сбора характеристик со всех товаров. Процесс может занимать длительное время." 
data-toggle="tooltip"><i class="icon-refresh"></i> Обновить характеристики</a>

<a href="#" class="btn btn-secondary sx-update-options" 
title="Эта кнопка запускает процесс сбора опций к характеристикам с типом список со всех товаров. Процесс может занимать длительное время." 
data-toggle="tooltip"><i class="icon-refresh"></i> Обновить опции</a>

<a href="#" class="btn btn-secondary sx-connect-options" 
title="Эта кнопка запускает процесс связи опций к характеристикам с типом список со всех товаров. Процесс может занимать длительное время." 
data-toggle="tooltip"><i class="icon-refresh"></i> Связать опции</a>
HTML
                        ,
                    ]);
                },

                "filters" => [
                    'visibleFilters' => [
                        'q',
                    ],

                    'filtersModel' => [
                        'rules' => [
                            ['q', 'safe'],
                        ],

                        'attributeDefines' => [
                            'q',
                        ],


                        'fields' => [

                            'q' => [
                                'label'          => 'Поиск',
                                'elementOptions' => [
                                    'placeholder' => 'Поиск',
                                ],
                                'on apply'       => function (QueryFiltersEvent $e) {
                                    /**
                                     * @var $query ActiveQuery
                                     */
                                    $query = $e->dataProvider->query;

                                    if ($e->field->value) {
                                        $query
                                            ->andWhere([
                                                'or',
                                                ['like', ShopStoreProperty::tableName().'.id', $e->field->value],
                                                ['like', ShopStoreProperty::tableName().'.name', $e->field->value],
                                                ['like', ShopStoreProperty::tableName().'.external_code', $e->field->value],
                                            ]);

                                        $query->groupBy([ShopStoreProperty::tableName().'.id']);
                                    }
                                },
                            ],

                        ],
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

                        $optionsQuery = ShopStorePropertyOption::find()->select(['count(*) as inner_count'])->where([
                            'shop_store_property_id' => new Expression(ShopStoreProperty::tableName().".id"),
                        ]);

                        $optionsNotConnectQuery = ShopStorePropertyOption::find()->select(['count(*) as inner_count1'])->where([
                            'shop_store_property_id' => new Expression(ShopStoreProperty::tableName().".id"),
                        ])->andWhere([
                            'and',
                            ['cms_content_property_enum_id' => null],
                            ['cms_content_element_id' => null],
                            ['cms_tree_id' => null],
                        ]);

                        $optionsConnectQuery = ShopStorePropertyOption::find()->select(['count(*) as inner_count1'])->where([
                            'shop_store_property_id' => new Expression(ShopStoreProperty::tableName().".id"),
                        ])->andWhere([
                            'or',
                            ['is not', 'cms_content_property_enum_id', null],
                            ['is not', 'cms_content_element_id', null],
                            ['is not', 'cms_tree_id', null],
                        ]);

                        $query->groupBy(ShopStoreProperty::tableName().".id");

                        $query->select([
                            ShopStoreProperty::tableName().'.*',
                            'countOptions'           => $optionsQuery,
                            'countConnectOptions'    => $optionsConnectQuery,
                            'countNotConnectOptions' => $optionsNotConnectQuery,
                        ]);

                        $query->andWhere(['shop_store_id' => \Yii::$app->shop->backendShopStore->id]);

                    },

                    'defaultOrder' => [
                        'is_visible' => SORT_DESC,
                        'priority'   => SORT_ASC,
                    ],

                    'visibleColumns' => [

                        'checkbox',
                        'actions',

                        //'id',
                        'custom',

                        /*'shop_supplier_id',*/

                        //'property_type',
                        'cms_property',

                        'priority',
                        'is_visible',
                    ],
                    'columns'        => [
                        'is_visible' => [
                            'class' => BooleanColumn::class,
                        ],
                        'priority'   => [
                            'headerOptions' => [
                                'style' => 'width: 60px;',
                            ],
                        ],

                        'external_code'           => [
                            'class' => DefaultActionColumn::class,
                        ],
                        'custom'                  => [
                            'format'    => 'raw',
                            'label'     => 'Характеристика',
                            'attribute' => 'external_code',
                            'value'     => function (ShopStoreProperty $property) {
                                $result = [];
                                $result[] = \yii\helpers\Html::a($property->external_code, "#", [
                                    'class' => "sx-trigger-action",
                                ]);;

                                if ($property->name) {
                                    $result[] = $property->name;
                                }

                                return implode("<br />", $result);
                            },
                        ],
                        'name'                    => [
                            'value' => function (ShopStoreProperty $property) {
                                return $property->name ? $property->name : '';
                            },
                        ],
                        'cms_content_property_id' => [
                            'value' => function (ShopStoreProperty $property) {
                                return $property->cms_content_property_id ? $property->cmsContentProperty->asText : '';
                            },
                        ],

                        'cms_property' => [
                            'label'  => 'Связь с сайтом',
                            'format' => 'raw',
                            'value'  => function (ShopStoreProperty $property, $key) {

                                $isRed = false;
                                $isGreen = false;
                                \Yii::$app->view->registerCss(<<<CSS
                                    tr.sx-tr-green, tr.sx-tr-green:nth-of-type(odd), tr.sx-tr-green td
                                    {
                                    background: #d9fbd9;
                                    }
                                    tr.sx-tr-red, tr.sx-tr-red:nth-of-type(odd), tr.sx-tr-red td
                                    {
                                    background: #fbf3f3;
                                    }
                                    tr.sx-tr-inactive, tr.sx-tr-inactive:nth-of-type(odd), tr.sx-tr-inactive td
                                    {
                                    opacity: 0.6;
                                    }
CSS
                                );

                                $result = [];
                                if ($property->property_nature == ShopStoreProperty::PROPERTY_NATURE_EAV && $property->cms_content_property_id) {
                                    $result[] = $property->cmsContentProperty->asText;
                                } else if ($property->property_nature) {
                                    $result[] = $property->propertyNatureAsText;
                                    $isGreen = true;
                                }

                                $propertyType = '';
                                /*if ($property->propertyTypeAsText) {
                                    $propertyType = "<small style='color: gray;'>".$property->propertyTypeAsText."</small>";
                                }*/
                                

                                if ($property->is_options) {
                                    $countOptions = ArrayHelper::getValue($property->raw_row, 'countOptions');
                                    $countConnectOptions = ArrayHelper::getValue($property->raw_row, 'countConnectOptions');
                                    $countNotConnectOptions = ArrayHelper::getValue($property->raw_row, 'countNotConnectOptions');

                                    if ($countNotConnectOptions == 0) {
                                        $isGreen = true;
                                        $propertyType .= "<small>Опций: <span title='Всего опций'> {$countOptions}</span> (<span style='color:green;' title='Все привязаны!'><span class='fa fa-check'></span></span>)</small>";
                                    } else {
                                        $isRed = true;
                                        $propertyType .= "<small>Опций: <span title='Всего опций'> {$countOptions}</span> (<span style='color:red;' title='Не привязанных'>{$countNotConnectOptions}</span>)</small>";
                                    }

                                }

                                if ($isRed) {
                                    \Yii::$app->view->registerJs(<<<JS
                                    $('tr[data-key={$key}]').addClass('sx-tr-red');
JS
                                    );
                                }
                                
                                if ($isGreen) {
                                    \Yii::$app->view->registerJs(<<<JS
                                    $('tr[data-key={$key}]').addClass('sx-tr-green');
JS
                                    );
                                }
                                
                                if (!$property->is_visible) {
                                    \Yii::$app->view->registerJs(<<<JS
                                    $('tr[data-key={$key}]').addClass('sx-tr-inactive');
JS
                                    );
                                }

                                if ($propertyType) {
                                    $result[] = $propertyType;
                                }

                                return implode(" / ", $result);
                            },
                        ],

                        'property_type' => [
                            'value' => function (ShopStoreProperty $property) {
                                //$result[] = $property->propertyTypeAsText;
                                if ($property->is_options) {
                                    $countOptions = ArrayHelper::getValue($property->raw_row, 'countOptions');
                                    $countConnectOptions = ArrayHelper::getValue($property->raw_row, 'countConnectOptions');
                                    $countNotConnectOptions = ArrayHelper::getValue($property->raw_row, 'countNotConnectOptions');

                                    if ($countNotConnectOptions == 0) {
                                        $result[] = "<span title='Всего опций'> {$countOptions}</span> (<span style='color:green;' title='Все привязаны!'><span class='fa fa-check'></span></span>)";
                                    } else {
                                        $result[] = "<span title='Всего опций'> {$countOptions}</span> (<span style='color:red;' title='Не привязанных'>{$countNotConnectOptions}</span>)";
                                    }

                                }

                                return implode(" ", $result);
                            },
                        ],
                    ],
                ],
            ],
            "create"        => new UnsetArrayValue(),
            "update"        => [
                'fields' => [$this, 'updateFields'],

                /*'activeFormConfig' => [
                    'fieldClass' => ActiveField::class
                ]*/
            ],
            "is-visible"    => [
                'class'     => BackendModelMultiActivateAction::class,
                'name'      => 'Сделать видимыми',
                'attribute' => 'is_visible',
                'value'     => 1,
            ],
            "is-un-visible" => [
                'class'     => BackendModelMultiDeactivateAction::class,
                'attribute' => 'is_visible',
                'name'      => 'Скрыть',
                'value'     => 0,
            ],

            "options" => [
                'class'           => BackendGridModelRelatedAction::class,
                'accessCallback'  => true,
                'name'            => "Опции",
                'icon'            => 'fa fa-list',
                'controllerRoute' => "/shop/store-property-option",
                'relation'        => ['shop_store_property_id' => 'id'],
                'priority'        => 600,
                'on gridInit'     => function ($e) {
                    /**
                     * @var $action BackendGridModelRelatedAction
                     */
                    $action = $e->sender;
                    $action->relatedIndexAction->backendShowings = false;
                    $visibleColumns = $action->relatedIndexAction->grid['visibleColumns'];

                    ArrayHelper::removeValue($visibleColumns, 'shop_store_property_id');
                    $action->relatedIndexAction->grid['visibleColumns'] = $visibleColumns;

                },
            ],

        ]);
    }

    public function updateFields($action)
    {
        /**
         * @var $model ShopStoreProperty
         */
        $model = $action->model;

        if ($model->property_nature == ShopStoreProperty::PROPERTY_NATURE_EAV) {
            $cms_content_property_id = [
                'class' => SelectField::class,
                'items' => ArrayHelper::map(
                    CmsContentProperty::find()->cmsSite()->all(),
                    'id',
                    'asText'
                ),
            ];
        } else {
            $cms_content_property_id = new UnsetArrayValue();
        }


        return [

            'supplier' => [
                'class'  => FieldSet::class,
                'name'   => 'Название и описание характеристики',
                'fields' => [
                    'external_code' => [
                        'class'          => TextField::class,
                        'elementOptions' => [
                            'disabled' => 'disabled',
                        ],
                    ],

                    'name',


                ],
            ],
            'main'     => [
                'class'  => FieldSet::class,
                'name'   => 'Связь данных поставщика с данными сайта.',
                'fields' => [

                    'property_nature' => [
                        'class'          => SelectField::class,
                        'items'          => ShopStoreProperty::getPropertyNatureOptions(),
                        'elementOptions' => [
                            'data-form-reload' => 'true',
                        ],

                    ],

                    'cms_content_property_id' => $cms_content_property_id,
                ],
            ],

            'delimetr' => [
                'class'  => FieldSet::class,
                'name'   => 'Разделители значений',
                'fields' => [
                    'import_delimetr' => [
                        'class' => TextField::class,
                    ],

                ],
            ],

            'import' => [
                'class'  => FieldSet::class,
                'name'   => 'Преобразование значений',
                'fields' => [
                    'import_multiply' => [
                        'class' => NumberField::class,
                        'step'  => 0.000000001,
                    ],

                ],
            ],
            'other'  => [
                'class'  => FieldSet::class,
                'name'   => 'Дополнительные настройки',
                'fields' => [
                    /*'property_type' => [
                        'class' => SelectField::class,
                        'items' => ShopStoreProperty::getPopertyTypeOptions(),
                    ],*/

                    'is_options' => [
                        'class'     => BoolField::class,
                        'allowNull' => false,
                    ],
                    'is_visible' => [
                        'class'     => BoolField::class,
                        'allowNull' => false,
                    ],
                    'priority' => [
                        'class' => NumberField::class
                    ],
                ],
            ],


        ];
    }

    /**
     *
     */
    public function actionUpdateProperties()
    {
        set_time_limit(0);

        $rr = new RequestResponse();

        if ($rr->isRequestAjaxPost()) {

            $shopProductsQuery = ShopStoreProduct::find()->andWhere(['shop_store_id' => \Yii::$app->shop->backendShopStore->id]);

            if (!$shopProductsQuery->count()) {
                $rr->message = 'Товаров нет';
                $rr->success = true;
                return $rr;
            }

            try {
                /**
                 * @var $shopStoreProduct ShopStoreProduct
                 */
                foreach ($shopProductsQuery->each(10) as $shopStoreProduct) {
                    if ($shopStoreProduct->external_data) {
                        foreach ($shopStoreProduct->external_data as $key => $value) {
                            $key = trim($key);
                            if (!ShopStoreProperty::find()->andWhere(['shop_store_id' => \Yii::$app->shop->backendShopStore->id])->andWhere(['external_code' => $key])->one()) {
                                $shopStoreProperty = new ShopStoreProperty();
                                $shopStoreProperty->shop_store_id = \Yii::$app->shop->backendShopStore->id;
                                $shopStoreProperty->external_code = $key;

                                if ($shopStoreProperty->save()) {
                                    //$this->stdout("Создано: {$key}\n", Console::FG_GREEN);
                                } else {
                                    //$this->stdout("Не сохранено: {$key} ".print_r($shopSupplier->errors, true)."\n", Console::FG_RED);
                                }
                            }
                        }
                    }
                }

                $rr->message = 'Данные обновлены';
                $rr->success = true;

            } catch (\Exception $exception) {

                $rr->message = $exception->getMessage();
                $rr->success = false;
            }

        }

        return $rr;
    }


    /**
     *
     */
    public function actionUpdateOptions()
    {
        set_time_limit(0);
        ini_set("memory_limit", "2G");

        $rr = new RequestResponse();

        if ($rr->isRequestAjaxPost()) {

            /**
             * @var $model ShopStoreProperty
             */
            $model = $this->model;

            /*if ($model->property_type != ShopStoreProperty::PROPERTY_TYPE_LIST) {
                $rr->message = 'Это свойство не явялется списком';
                $rr->success = false;
                return $rr;
            }*/

            try {
                $shopProductsQuery = ShopStoreProduct::find()->andWhere(['shop_store_id' => \Yii::$app->shop->backendShopStore->id]);

                if (!$shopProductsQuery->count()) {
                    throw new Exception('Товаров нет');
                }

                if (!$properties = ShopStoreProperty::find()->andWhere(['is_options' => 1])
                    ->andWhere(['shop_store_id' => \Yii::$app->shop->backendShopStore->id])->all()) {
                    throw new Exception('Нет свойств типа список');
                }

                /**
                 * @var $shopStoreProduct ShopStoreProduct
                 */
                foreach ($shopProductsQuery->each(10) as $shopStoreProduct) {
                    if ($shopStoreProduct->external_data) {
                        foreach ($properties as $property) {
                            $value = ArrayHelper::getValue($shopStoreProduct->external_data, $property->external_code);
                            if (is_string($value)) {
                                if ($property->import_delimetr) {
                                    $value = explode($property->import_delimetr, $value);
                                } else {
                                    $value = [trim($value)];
                                }
                            } elseif (is_array($value)) {
                                $value = (array)$value;
                            } else {
                                continue;
                            }

                            foreach ($value as $val) {
                                $val = trim($val);

                                if (!$val) {
                                    continue;
                                }

                                if (!$option = $property->getShopStorePropertyOptions()->andWhere(['name' => $val])->one()) {
                                    $option = new ShopStorePropertyOption();
                                    $option->name = $val;
                                    $option->shop_store_property_id = $property->id;
                                    if (!$option->save()) {
                                        throw new Exception("Option not save! ".print_r($option->errors, true));
                                    } else {
                                        //$this->stdout("added option: {$val} \n");
                                    }
                                }
                            }


                        }
                    }
                }


                $rr->message = 'Данные обновлены';
                $rr->success = true;

            } catch (\Exception $exception) {

                $rr->message = $exception->getMessage();
                $rr->success = false;
            }

        }

        return $rr;
    }


    /**
     * Связывает опции поставщика и опции cms
     *
     * @param int $is_auto_create будет создавать опции в cms или нет?
     * @return bool
     * @throws Exception
     */
    public function actionConnectOptions($is_auto_create = 0)
    {
        set_time_limit(0);
        ini_set("memory_limit", "2G");

        $rr = new RequestResponse();

        if ($rr->isRequestAjaxPost()) {

            /**
             * @var $model ShopStoreProperty
             */
            $model = $this->model;


            try {
                $shopProductsQuery = ShopStoreProduct::find()->andWhere(['shop_store_id' => \Yii::$app->shop->backendShopStore->id]);

                if (!$shopProductsQuery->count()) {
                    throw new Exception('Товаров нет');
                }

                if (!$properties = ShopStoreProperty::find()
                    ->andWhere(['is_options' => 1])
                    ->andWhere(['is not', 'cms_content_property_id', null])
                    ->andWhere(['shop_store_id' => \Yii::$app->shop->backendShopStore->id])->all()) {

                    throw new Exception('Нет свойств типа список');
                }

                foreach ($properties as $property) {

                    $queryOptions = $property->getShopStorePropertyOptions()->where([
                        'and',
                        ['cms_content_property_enum_id' => null],
                        ['cms_content_element_id' => null],
                    ]);
                    $count = $queryOptions->count();
                    if (!$count) {
                        //$this->stdout("\tВсе опции связаны\n");
                        continue;
                    }
                    //$this->stdout("\tНе связанных опций: {$count}\n");

                    $content_id = null;
                    $contentProperty = $property->cmsContentProperty;
                    if ($property->cmsContentProperty->handler instanceof PropertyTypeList) {

                    } elseif ($property->cmsContentProperty->handler instanceof PropertyTypeElement) {
                        $content_id = $property->cmsContentProperty->handler->content_id;
                    }

                    foreach ($queryOptions->each(10) as $option) {
                        //$this->stdout("\tОпция: {$option->asText}\n");

                        if ($content_id) {
                            if ($element = CmsContentElement::find()->andWhere(['content_id' => $content_id])->andWhere(['name' => $option->name])->one()) {
                                $option->cms_content_element_id = $element->id;
                                if ($option->save()) {
                                    //$this->stdout("\t\tСвязана\n", Console::FG_GREEN);
                                } else {
                                    //$this->stdout("\t\tНе связана ".print_r($option->errors, true)."\n", Console::FG_RED);
                                }
                            }
                        } else {
                            if ($enum = $contentProperty->getEnums()->andWhere(['value' => $option->name])->one()) {
                                $option->cms_content_property_enum_id = $enum->id;
                                if ($option->save()) {
                                    //$this->stdout("\t\tСвязана\n", Console::FG_GREEN);
                                } else {
                                    //$this->stdout("\t\tНе связана ".print_r($option->errors, true)."\n", Console::FG_RED);
                                }
                            } else {
                                if ($is_auto_create) {
                                    $enum = new CmsContentPropertyEnum();
                                    $enum->value = $option->name;
                                    $enum->property_id = $contentProperty->id;

                                    if (!$enum->save()) {
                                        throw new Exception("Не создалась опция: ".print_r($enum->errors, true));
                                    }

                                    //$this->stdout("\t\tСоздана характеристика\n", Console::FG_GREEN);

                                    $option->cms_content_property_enum_id = $enum->id;
                                    if ($option->save()) {
                                        //$this->stdout("\t\tСвязана\n", Console::FG_GREEN);
                                    } else {
                                        //$this->stdout("\t\tНе связана ".print_r($option->errors, true)."\n", Console::FG_RED);
                                    }
                                }
                            }
                        }
                    }

                }


                $rr->message = 'Данные обновлены';
                $rr->success = true;

            } catch (\Exception $exception) {

                $rr->message = $exception->getMessage();
                $rr->success = false;
            }

        }

        return $rr;


    }

}