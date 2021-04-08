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
use skeeks\cms\relatedProperties\propertyTypes\PropertyTypeElement;
use skeeks\cms\relatedProperties\propertyTypes\PropertyTypeList;
use skeeks\cms\shop\models\ShopStoreProduct;
use skeeks\cms\shop\models\ShopStoreProperty;
use skeeks\cms\shop\models\ShopStorePropertyOption;
use skeeks\yii2\form\fields\BoolField;
use skeeks\yii2\form\fields\FieldSet;
use skeeks\yii2\form\fields\SelectField;
use skeeks\yii2\form\fields\TextareaField;
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
                        ]);

                        $optionsConnectQuery = ShopStorePropertyOption::find()->select(['count(*) as inner_count1'])->where([
                            'shop_store_property_id' => new Expression(ShopStoreProperty::tableName().".id"),
                        ])->andWhere([
                            'or',
                            ['is not', 'cms_content_property_enum_id', null],
                            ['is not', 'cms_content_element_id', null],
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
                        'external_code',
                        /*'shop_supplier_id',*/
                        'name',

                        'property_type',
                        'cms_content_property_id',

                        'priority',
                        'is_visible',
                    ],
                    'columns'        => [
                        'is_visible' => [
                            'class' => BooleanColumn::class,
                        ],

                        'external_code'           => [
                            'class' => DefaultActionColumn::class,
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

                        'property_type' => [
                            'value' => function (ShopStoreProperty $property) {
                                $result[] = $property->propertyTypeAsText;
                                if ($property->property_type == ShopStoreProperty::PROPERTY_TYPE_LIST) {
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

        return [

            'supplier' => [
                'class'  => FieldSet::class,
                'name'   => 'От поставщика',
                'fields' => [
                    'external_code',
                ],
            ],
            'main'     => [
                'class'  => FieldSet::class,
                'name'   => 'Настройки свойства',
                'fields' => [

                    'property_type' => [
                        'class' => SelectField::class,
                        'items' => ShopStoreProperty::getPopertyTypeOptions(),
                    ],

                    'is_visible' => [
                        'class'     => BoolField::class,
                        'allowNull' => false,
                    ],


                    'name',
                    'priority',

                    'cms_content_property_id' => [
                        'class' => SelectField::class,
                        'items' => ArrayHelper::map(
                            CmsContentProperty::find()->all(),
                            'id',
                            'asText'
                        ),
                    ],


                ],
            ],

            'import' => [
                'class'  => FieldSet::class,
                'name'   => 'Настройки импорта/преобразования',
                'fields' => [
                    'import_delimetr' => [
                        'class' => TextareaField::class,
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

                if (!$properties = ShopStoreProperty::find()->andWhere(['property_type' => ShopStoreProperty::PROPERTY_TYPE_LIST])
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
                    ->andWhere(['property_type' => ShopStoreProperty::PROPERTY_TYPE_LIST])
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