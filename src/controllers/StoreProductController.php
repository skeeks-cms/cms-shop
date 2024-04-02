<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

namespace skeeks\cms\shop\controllers;

use skeeks\cms\actions\backend\BackendModelMultiActivateAction;
use skeeks\cms\actions\backend\BackendModelMultiDeactivateAction;
use skeeks\cms\backend\actions\BackendModelAction;
use skeeks\cms\backend\controllers\BackendModelStandartController;
use skeeks\cms\backend\ViewBackendAction;
use skeeks\cms\components\Cms;
use skeeks\cms\grid\BooleanColumn;
use skeeks\cms\grid\DateTimeColumnData;
use skeeks\cms\helpers\Image;
use skeeks\cms\helpers\RequestResponse;
use skeeks\cms\models\CmsAgent;
use skeeks\cms\models\CmsContentElement;
use skeeks\cms\models\CmsContentElementProperty;
use skeeks\cms\models\CmsContentProperty;
use skeeks\cms\queryfilters\filters\NumberFilterField;
use skeeks\cms\queryfilters\QueryFiltersEvent;
use skeeks\cms\rbac\CmsManager;
use skeeks\cms\shop\models\ShopCmsContentElement;
use skeeks\cms\shop\models\ShopProduct;
use skeeks\cms\shop\models\ShopProductPrice;
use skeeks\cms\shop\models\ShopStoreProduct;
use skeeks\cms\shop\models\ShopStoreProperty;
use skeeks\cms\shop\models\ShopStorePropertyOption;
use skeeks\cms\shop\models\ShopTypePrice;
use skeeks\cms\Skeeks;
use skeeks\cms\widgets\GridView;
use skeeks\yii2\form\fields\BoolField;
use skeeks\yii2\form\fields\HtmlBlock;
use skeeks\yii2\form\fields\NumberField;
use skeeks\yii2\form\fields\TextField;
use yii\base\Event;
use yii\base\Exception;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\UnsetArrayValue;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class StoreProductController extends BackendModelStandartController
{
    public function init()
    {
        $this->name = \Yii::t('skeeks/shop/app', 'Товары');
        $this->modelShowAttribute = "name";
        $this->modelClassName = ShopStoreProduct::class;

        $this->permissionName = Cms::UPA_PERMISSION;

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return ArrayHelper::merge(parent::actions(), [
            'index' => [
                "filters"         => [
                    'visibleFilters' => [
                        'q',
                        'has_shop_product',
                        'quantity',

                        'selling_price',
                        'purchase_price',

                        'marginality_per_filter',
                        'marginality_filter',
                        'is_active',
                        //'component',
                    ],

                    'filtersModel' => [
                        'rules' => [
                            ['q', 'safe'],
                            ['has_shop_product', 'safe'],
                            ['marginality_per_filter', 'safe'],
                            ['marginality_filter', 'safe'],
                        ],

                        'attributeDefines' => [
                            'q',
                            'has_shop_product',
                            'marginality_per_filter',
                            'marginality_filter',
                        ],


                        'fields' => [

                            'q'                => [
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
                                                ['like', ShopStoreProduct::tableName().'.id', $e->field->value],
                                                ['like', ShopStoreProduct::tableName().'.name', $e->field->value],
                                                ['like', ShopStoreProduct::tableName().'.external_id', $e->field->value],
                                                ['like', ShopStoreProduct::tableName().'.external_data', $e->field->value],
                                                ['like', 'element.name', $e->field->value],
                                            ]);

                                        $query->joinWith("shopProduct as shopProduct");
                                        $query->joinWith("shopProduct.cmsContentElement as element");

                                        $query->groupBy([ShopStoreProduct::tableName().'.id']);
                                    }
                                },
                            ],
                            'is_active'        => [
                                'class'    => BoolField::class,
                                'on apply' => function (QueryFiltersEvent $e) {
                                    /**
                                     * @var $query ActiveQuery
                                     */
                                    $query = $e->dataProvider->query;

                                    if ($e->field->value == 1) {
                                        $query->andWhere(
                                            [ShopStoreProduct::tableName().'.is_active' => 1],
                                        );

                                        $query->groupBy([ShopStoreProduct::tableName().'.id']);
                                    } else if ($e->field->value == "0") {
                                        $query->andWhere(
                                            [ShopStoreProduct::tableName().'.is_active' => 0],
                                        );

                                        $query->groupBy([ShopStoreProduct::tableName().'.id']);
                                    }
                                },
                            ],
                            'has_shop_product' => [
                                'label'    => 'Оформлен?',
                                'class'    => BoolField::class,
                                'on apply' => function (QueryFiltersEvent $e) {
                                    /**
                                     * @var $query ActiveQuery
                                     */
                                    $query = $e->dataProvider->query;

                                    if ($e->field->value == 1) {
                                        $query->andWhere(
                                            ['is not', ShopStoreProduct::tableName().'.shop_product_id', null],
                                        );

                                        $query->groupBy([ShopStoreProduct::tableName().'.id']);
                                    } else if ($e->field->value == "0") {
                                        $query->andWhere(
                                            [ShopStoreProduct::tableName().'.shop_product_id' => null],
                                        );

                                        $query->groupBy([ShopStoreProduct::tableName().'.id']);
                                    }
                                },
                            ],

                            'marginality_per_filter' => [
                                'label'                   => 'Маржинальность, %',
                                'class'                   => NumberFilterField::class,
                                'field'                   => [
                                    'class' => NumberField::class,
                                ],
                                'isAddAttributeTableName' => false,
                                'beforeModeApplyCallback' => function (QueryFiltersEvent $e, NumberFilterField $field) {
                                    /**
                                     * @var $query ActiveQuery
                                     */
                                    $query = $e->dataProvider->query;

                                    if (ArrayHelper::getValue($e->field->value, "value.0") || ArrayHelper::getValue($e->field->value, "value.1")) {

                                        $field->setIsHaving();
                                        //$field->attr = 'marginality_per_filter';

                                        $query->addSelect([
                                            'marginality_per_filter' => new Expression("(selling_price - purchase_price) / selling_price * 100"),
                                        ]);
                                        $query->groupBy([ShopStoreProduct::tableName().'.id']);
                                    }


                                    return true;
                                },
                            ],

                            'marginality_filter' => [
                                'label'                   => 'Маржинальность, значение',
                                'class'                   => NumberFilterField::class,
                                'field'                   => [
                                    'class' => NumberField::class,
                                ],
                                'isAddAttributeTableName' => false,
                                'beforeModeApplyCallback' => function (QueryFiltersEvent $e, NumberFilterField $field) {
                                    /**
                                     * @var $query ActiveQuery
                                     */
                                    $query = $e->dataProvider->query;

                                    if (ArrayHelper::getValue($e->field->value, "value.0") || ArrayHelper::getValue($e->field->value, "value.1")) {

                                        $field->setIsHaving();
                                        //$field->attr = 'marginality_per_filter';

                                        $query->addSelect([
                                            'marginality_filter' => new Expression("(selling_price - purchase_price)"),
                                        ]);
                                        $query->groupBy([ShopStoreProduct::tableName().'.id']);
                                    }


                                    return true;
                                },
                            ],
                        ],
                    ],
                ],
                "backendShowings" => false,
                'grid'            => [
                    'on init'        => function (Event $e) {
                        /**
                         * @var $dataProvider ActiveDataProvider
                         * @var $query ActiveQuery
                         */
                        $query = $e->sender->dataProvider->query;
                        $query->andWhere(['shop_store_id' => \Yii::$app->shop->backendShopStore->id]);
                    },
                    'defaultOrder'   => [
                        'id' => SORT_DESC,
                    ],
                    'visibleColumns' => [

                        'checkbox',
                        'actions',

                        //'id',
                        'custom',
                        //'shop_store_id',

                        'external_id',

                        'quantity',
                        'purchase_price',
                        'selling_price',

                        'marginality_abs',
                        'marginality_per',
                    ],
                    'columns'        => [
                        'is_active'  => [
                            'class' => BooleanColumn::class,
                        ],
                        'created_at' => [
                            'class' => DateTimeColumnData::class,
                        ],
                        'updated_at' => [
                            'class' => DateTimeColumnData::class,
                        ],
                        'quantity'   => [
                            'headerOptions' => [
                                'style' => 'width: 100px;',
                            ],
                        ],

                        'external_id'    => [
                            'headerOptions' => [
                                'style' => 'width: 100px;',
                            ],
                            'value'         => function (ShopStoreProduct $shopStoreProduct) {
                                return $shopStoreProduct->external_id ? $shopStoreProduct->external_id : "";
                            },
                        ],
                        'purchase_price' => [
                            'headerOptions' => [
                                'style' => 'width: 100px;',
                            ],
                            'value'         => function (ShopStoreProduct $shopStoreProduct) {
                                return $shopStoreProduct->purchase_price ? \Yii::$app->formatter->asDecimal($shopStoreProduct->purchase_price) : "";
                            },
                        ],
                        'selling_price'  => [
                            'headerOptions' => [
                                'style' => 'width: 100px;',
                            ],
                            'value'         => function (ShopStoreProduct $shopStoreProduct) {
            //return print_r($shopStoreProduct->toArray(),true );
                                return $shopStoreProduct->selling_price ? \Yii::$app->formatter->asDecimal($shopStoreProduct->selling_price) : "";
                            },
                        ],

                        'marginality_abs' => [
                            'attribute' => 'marginality_abs',
                            'label'     => 'Маржинальность, значение',
                            'format'    => 'raw',

                            'headerOptions' => [
                                'style' => 'width: 100px;',
                            ],

                            'beforeCreateCallback' => function (GridView $grid) {
                                /**
                                 * @var $query ActiveQuery
                                 */
                                $query = $grid->dataProvider->query;

                                $query->addSelect([
                                    'marginality_abs' => new Expression("selling_price - purchase_price"),
                                ]);


                                $grid->sortAttributes["marginality_abs"] = [
                                    'asc'  => ['marginality_abs' => SORT_ASC],
                                    'desc' => ['marginality_abs' => SORT_DESC],
                                ];
                            },
                            'value'                => function (ShopStoreProduct $shopStoreProduct) {
                                $result = $shopStoreProduct->raw_row['marginality_abs'] ? \Yii::$app->formatter->asDecimal($shopStoreProduct->raw_row['marginality_abs']) : "";
                                $color = "red";
                                if ($result < 0) {
                                    $color = "red";
                                }
                                if ($result > 0) {
                                    $color = "green";
                                }

                                return Html::tag("div", $result, [
                                    'style' => "color: {$color}",
                                ]);
                            },
                        ],

                        'marginality_per' => [
                            'attribute' => 'marginality_per',
                            'label'     => 'Маржинальность, %',
                            'format'    => 'raw',

                            'headerOptions' => [
                                'style' => 'width: 50px;',
                            ],

                            'beforeCreateCallback' => function (GridView $grid) {
                                /**
                                 * @var $query ActiveQuery
                                 */
                                $query = $grid->dataProvider->query;

                                $query->addSelect([
                                    'marginality_per' => new Expression("(selling_price - purchase_price) / selling_price * 100"),
                                ]);


                                $grid->sortAttributes["marginality_per"] = [
                                    'asc'  => ['marginality_per' => SORT_ASC],
                                    'desc' => ['marginality_per' => SORT_DESC],
                                ];
                            },
                            'value'                => function (ShopStoreProduct $shopStoreProduct) {
                                $result = $shopStoreProduct->raw_row['marginality_per'] ? \Yii::$app->formatter->asDecimal($shopStoreProduct->raw_row['marginality_per']) : "";
                                $color = "red";
                                if ($result < 0) {
                                    $color = "red";
                                }
                                if ($result > 0) {
                                    $color = "green";
                                }

                                return Html::tag("div", $result, [
                                    'style' => "color: {$color}",
                                ]);

                            },
                        ],

                        'custom' => [
                            'attribute' => 'id',
                            'format'    => 'raw',
                            'value'     => function (ShopStoreProduct $model) {

                                $data = [];
                                $data[] = Html::a($model->asText, "#", ['class' => 'sx-trigger-action', 'style' => 'border-bottom: 0;']);

                                $imageSrc = Image::getCapSrc();
                                if ($model->shopProduct && $model->shopProduct->cmsContentElement->mainProductImage) {
                                    $imageSrc = $model->shopProduct->cmsContentElement->mainProductImage->src;
                                }

                                $info = implode("<br />", $data);


                                if ($model->shop_product_id) {
                                    $attched = '<div class="my-auto text-center" style="margin-right: 5px;">';
                                    $attched .= \skeeks\cms\backend\widgets\AjaxControllerActionsWidget::widget([
                                        'controllerId' => "/shop/admin-cms-content-element",
                                        'modelId'      => $model->shop_product_id,
                                        'options'      => [
                                            'style' => 'color: gray; text-align: left;',
                                            'class' => '',
                                        ],
                                        'content'      => <<<HTML

<span style="color: green; font-size: 17px;">
    <i class="fas fa-link" style="width: 20px;" data-toggle="tooltip" title="Товар оформлен {$model->asText}"></i>
</span>
HTML
                                        ,

                                    ]);
                                    $attched .= "</div>";
                                } else {
                                    $attched = <<<HTML
<div class="my-auto text-center" style="margin-right: 5px;">
    <span style="color: red; font-size: 17px;">
        <i class="fas fa-link" style="width: 20px;" data-toggle="tooltip" title="Это товар не оформлен и не показывается на сайте"></i>
    </span>
</div>
HTML;

                                }
                                return "<div class='d-flex no-gutters'>
                                            {$attched}
                                            <div class='my-auto sx-trigger-action' style='width: 30px; text-align: center;'>
                                                <a href='#' style='text-decoration: none; border-bottom: 0;'>
                                                    <img src='".($imageSrc)."' style='max-width: 30px; max-height: 30px; border-radius: 5px;' />
                                                </a>
                                            </div>
                                            <div style='margin-left: 5px; line-height: 1.1;' class='my-auto'>".$info."</div>
                                        </div>";;
                            },
                        ],

                    ],
                ],
            ],

            "view" => [
                'class'    => BackendModelAction::class,
                'priority' => 80,
                'name'     => 'Карточка',
                'icon'     => 'fas fa-info-circle',
            ],


            "join" => [
                'class'    => ViewBackendAction::class,
                'priority' => 80,
                'name'     => 'Связать',
                'icon'     => 'fas fa-link',

                'accessCallback' => function () {
                    if (\Yii::$app->user->can(CmsManager::PERMISSION_ROLE_ADMIN_ACCESS)) {
                        return true;
                    }

                    return false;
                },
            ],

            "services" => [
                'class'          => ViewBackendAction::class,
                'priority'       => 90,
                'name'           => 'Инструменты',
                'icon'           => 'fas fa-info-circle',
                'accessCallback' => function () {
                    if (\Yii::$app->user->can(CmsManager::PERMISSION_ROLE_ADMIN_ACCESS)) {
                        return true;
                    }

                    return false;
                },
            ],


            "create" => [
                'fields' => [$this, 'createFields'],

                'accessCallback' => function () {
                    if (\Yii::$app->user->can(CmsManager::PERMISSION_ROLE_ADMIN_ACCESS)) {
                        return true;
                    }

                    if (\Yii::$app->shop->backendShopStore->is_sync_external) {
                        return false;
                    }

                    return true;
                },
            ],

            "update" => new UnsetArrayValue(),
            /*"update" => [
                'fields' => [$this, 'updateFields'],
                'accessCallback' => function() {
                    if (\Yii::$app->user->can(CmsManager::PERMISSION_ROLE_ADMIN_ACCESS)) {
                        return true;
                    }

                    if (\Yii::$app->shop->backendShopStore->is_sync_external) {
                        return false;
                    }

                    return true;
                }
            ],*/

            "import" => [
                'class' => ViewBackendAction::class,
                'icon'  => 'far fa-file-excel',
                'name'  => 'Импорт',

                'accessCallback' => function () {
                    if (\Yii::$app->user->can(CmsManager::PERMISSION_ROLE_ADMIN_ACCESS)) {
                        return true;
                    }

                    return false;
                },
            ],

            "activate-multi"   => [
                'class'     => BackendModelMultiActivateAction::class,
                'value'     => '1',
                'attribute' => 'is_active',

                'accessCallback' => function () {
                    if (\Yii::$app->user->can(CmsManager::PERMISSION_ROLE_ADMIN_ACCESS)) {
                        return true;
                    }

                    return false;
                }

                /*"eachAccessCallback" => function ($model) {
                    return \Yii::$app->user->can($this->permissionName."/update", ['model' => $model]);
                },
                "accessCallback"     => function () {
                    return \Yii::$app->user->can($this->permissionName."/update");
                },*/
            ],
            "deactivate-multi" => [

                'class'     => BackendModelMultiDeactivateAction::class,
                'value'     => '0',
                'attribute' => 'is_active',

                'accessCallback' => function () {
                    if (\Yii::$app->user->can(CmsManager::PERMISSION_ROLE_ADMIN_ACCESS)) {
                        return true;
                    }

                    return false;
                }

                /*"eachAccessCallback" => function ($model) {
                    return \Yii::$app->user->can($this->permissionName."/update", ['model' => $model]);
                },
                "accessCallback"     => function () {
                    return \Yii::$app->user->can($this->permissionName."/update");
                },*/
            ],

            "delete"       => [
                'accessCallback' => function () {

                    if (\Yii::$app->user->can(CmsManager::PERMISSION_ROLE_ADMIN_ACCESS)) {
                        return true;
                    }

                    return false;
                },
            ],
            "delete-multi" => [
                'accessCallback' => function () {
                    if (\Yii::$app->user->can(CmsManager::PERMISSION_ROLE_ADMIN_ACCESS)) {
                        return true;
                    }

                    return false;
                },
            ],
        ]);
    }


    public function actionAutoCreate()
    {
        Skeeks::unlimited();

        $rr = new RequestResponse();
        if ($rr->isRequestAjaxPost()) {

            $model = new \skeeks\cms\base\DynamicModel();
            $model->addRule("is_active", "integer");
            $model->defineAttribute("is_active");
            $model->setAttributeLebel("is_active", "Показывать товары на сайте сразу?");

            $model->defineAttribute("cms_tree_id");
            $model->setAttributeLebel("cms_tree_id", "Раздел");
            $model->addRule("cms_tree_id", "integer");

            $model->load(\Yii::$app->request->post());

            $q = \Yii::$app->shop->backendShopStore->getShopStoreProducts()->andWhere(['shop_product_id' => null])->orderBy(['id' => SORT_ASC])->limit(1000);

            $result = [];

            $retailTypePrice = ShopTypePrice::find()->cmsSite()->isRetail()->one();
            $purchaseTypePrice = ShopTypePrice::find()->isPurchase()->cmsSite()->one();

            $cmsContent = \Yii::$app->shop->contentProducts;

            /**
             * @var $shopStoreProduct ShopStoreProduct
             */
            foreach ($q->each(10) as $shopStoreProduct) {
                $shopStoreProduct->name;

                $t = \Yii::$app->db->beginTransaction();
                try {
                    $element = new ShopCmsContentElement();


                    $element->content_id = $cmsContent->id;

                    $element->name = $shopStoreProduct->name;
                    $element->tree_id = $model->cms_tree_id;

                    if ($model->is_active) {
                        $element->active = "Y";
                    } else {
                        $element->active = "N";
                    }



                    $sp = new ShopProduct();

                    $shopStoreProduct->loadDataToElementProduct($element, $sp);

                    if (!$element->save()) {
                        throw new Exception(print_r($element->errors, true));
                    }

                    $sp->id = $element->id;
                    if (!$sp->save()) {
                        throw new Exception(print_r($sp->errors, true));
                    }

                    if ($purchaseTypePrice) {
                        $price2 = $element->shopProduct->getPrice($purchaseTypePrice->id);
                        if (!$price2) {
                            $price2 = new ShopProductPrice();
                            $price2->type_price_id = $purchaseTypePrice->id;
                            $price2->product_id = $element->id;
                        }

                        $price2->price = $shopStoreProduct->purchase_price;
                        if (!$price2->save()) {
                            throw new Exception(print_r($price2->errors, true));
                        }
                    }

                    //try {
                    if ($retailTypePrice) {
                        $price1 = $element->shopProduct->getPrice($retailTypePrice->id);
                        if (!$price1) {
                            $price1 = new ShopProductPrice();
                            $price1->type_price_id = $retailTypePrice->id;
                            $price1->product_id = $element->id;


                        }
                        $price1->price = $shopStoreProduct->selling_price;
                        if (!$price1->save()) {
                            throw new Exception(print_r($price1->errors, true));
                        }
                    }


                    //} catch (\Exception $exception) {

                    //}


                    $shopStoreProduct->shop_product_id = $element->id;
                    if (!$shopStoreProduct->save()) {
                        throw new Exception(print_r($shopStoreProduct->errors, true));
                    }


                    if (!$element->relatedPropertiesModel->save()) {
                        throw new Exception(print_r($element->relatedPropertiesModel->errors, true));
                    }

                    $t->commit();

                } catch (\Exception $e) {
                    $t->rollBack();
                    /*print_r($shopStoreProduct->name);die;
                    throw $e;
                    die;*/
                    continue;
                }


            }

            $rr->success = true;
        }

        return $rr;
    }
    /**
     * @return RequestResponse
     */
    public function actionSaveMain()
    {
        $rr = new RequestResponse();
        if ($rr->isRequestAjaxPost()) {
            $model = $this->model;

            $model->load(\Yii::$app->request->post());

            if (!$model->save()) {
                $rr->success = false;
                $rr->message = print_r($model->errors, true);
            }
            $rr->success = true;
        }
        return $rr;
    }

    public function updateFields($action)
    {
        $action->model->load(\Yii::$app->request->get());

        return [

            'name',
            'external_id',
            'quantity'       => [
                'class' => NumberField::class,
            ],
            'purchase_price' => [
                'class' => NumberField::class,
            ],
            'selling_price'  => [
                'class' => NumberField::class,
            ],

        ];
    }
    public function createFields($action)
    {
        $action->model->load(\Yii::$app->request->get());
        $action->model->shop_store_id = \Yii::$app->shop->backendShopStore->id;

        return [

            'name',
            'external_id',
            'quantity'       => [
                'class' => NumberField::class,
            ],
            'purchase_price' => [
                'class' => NumberField::class,
            ],
            'selling_price'  => [
                'class' => NumberField::class,
            ],
            [
                'class'   => HtmlBlock::class,
                'content' => "<div style='display: none;'>",
            ],
            'shop_store_id'  => [
                'class' => TextField::class,
            ],
            [
                'class'   => HtmlBlock::class,
                'content' => "</div>",
            ],


        ];
    }

    public function actionImportRow()
    {
        $rr = new RequestResponse();
        $matches = \Yii::$app->request->post("matches");
        $row_data = \Yii::$app->request->post("row_data");
        try {
            if (!$matches || !$row_data) {
                throw new Exception("Не хватает данных");
            }

            $data = [];
            foreach ($matches as $key => $code) {
                $data[$code] = ArrayHelper::getValue($row_data, $key);
            }

            $external_id = trim(ArrayHelper::getValue($data, 'external_id'));
            if (!$external_id) {
                throw new Exception("Уникальный код не задан");
            }

            $isUpdate = true;
            if (!$storeProduct = \Yii::$app->shop->backendShopStore->getShopStoreProducts()->andWhere(['external_id' => $external_id])->one()) {
                $storeProduct = new ShopStoreProduct();
                $storeProduct->shop_store_id = \Yii::$app->shop->backendShopStore->id;
                $storeProduct->external_id = $external_id;

                $isUpdate = false;
            }

            ArrayHelper::remove($data, "external_id");

            foreach ($data as $key => $value) {
                if (!$key) {
                    continue;
                }

                if (in_array($key, ['purchase_price', 'selling_price', 'quantity'])) {

                    $value = trim($value);
                    $value = str_replace(" ", "", $value);
                    $value = str_replace("&nbsp", "", $value);
                    $value = str_replace("руб.", "", $value);
                    $value = str_replace("руб", "", $value);
                    $value = str_replace("р.", "", $value);
                    $value = str_replace("р", "", $value);
                    $value = str_replace(",", ".", $value);

                    $value = (float)$value;

                } else {
                    $value = trim($value);
                }
                if (!$storeProduct->hasAttribute($key)) {
                    continue;
                }
                $storeProduct->{$key} = trim($value);
            }

            $message = '';
            if ($storeProduct->save()) {
                if ($isUpdate) {
                    $message = "Товар обновлен: {$storeProduct->id}";
                } else {
                    $message = "Товар создан: {$storeProduct->id}";
                }
            } else {
                throw new Exception(print_r($storeProduct->errors, true));
            }

            $rr->success = true;
            $rr->message = $message;

        } catch (\Exception $exception) {
            $rr->success = false;
            $rr->message = $exception->getMessage();
        }

        return $rr;
    }

    public function actionJoinByBarcode()
    {

        $rr = new RequestResponse();


        set_time_limit(0);
        ini_set("memory_limit", "2G");


        if ($rr->isRequestAjaxPost()) {

            $added = 0;
            /**
             * @var $shopStorePropertyVendor ShopStoreProperty
             */
            $qShopStoreProperties = \Yii::$app->shop->backendShopStore->getShopStoreProperties();
            $shopStorePropertyBarcode = $qShopStoreProperties->andWhere(['property_nature' => ShopStoreProperty::PROPERTY_NATURE_BARCODE])->one();

            if ($shopStorePropertyBarcode) {
                $rr->success = true;
                $rr->message = "Данные обновлены";

                /**
                 * @var $vendorOption ShopStorePropertyOption
                 * @var $storeProduct ShopStoreProduct
                 */
                $storeProducts = \Yii::$app->shop->backendShopStore->getShopStoreProducts()->andWhere(['shop_product_id' => null]);
                foreach ($storeProducts->each() as $storeProduct) {
                    if ($storeProduct->external_data) {
                        $externalData = [];
                        foreach ($storeProduct->external_data as $key => $val) {
                            $externalData[trim($key)] = $val;
                        }
                        $barcodeValue = ArrayHelper::getValue($externalData, trim($shopStorePropertyBarcode->external_code));

                        if ($barcodeValue) {

                            $find = ShopCmsContentElement::find()
                                ->cmsSite()
                                ->innerJoinWith("shopProduct as sp")
                                ->innerJoinWith("shopProduct.shopProductBarcodes as barcodes")
                                ->andWhere(["barcodes.value" => $barcodeValue])
                                ->groupBy([ShopCmsContentElement::tableName().".id"]);


                            if ($find->count() == 1) {
                                $infoModel = $find->one();

                                /*print_r($storeProduct->toArray());
                                print_r($infoModel->toArray());die;*/

                                if ($infoModel) {
                                    $storeProduct->shop_product_id = $infoModel->id;
                                    try {
                                        if ($storeProduct->save(false)) {
                                            $added++;
                                        }
                                    } catch (\Exception $exception) {

                                    }

                                }

                            }
                        }

                    }
                }

                if ($added > 0) {
                    $rr->message = "Связано товаров: {$added}";
                    $rr->data = [
                        'added' => $added,
                    ];
                }
            }
        }

        return $rr;
    }

    public function actionJoinByVendorV2()
    {

        $rr = new RequestResponse();


        Skeeks::unlimited();


        if ($rr->isRequestAjaxPost()) {

            $added = 0;
            /**
             * @var $shopStorePropertyBrand ShopStoreProperty
             * @var $shopStorePropertyBrandSku ShopStoreProperty
             */

            $qShopStoreProperties = \Yii::$app->shop->backendShopStore->getShopStoreProperties();
            $shopStorePropertyBrand = $qShopStoreProperties->andWhere(['property_nature' => \skeeks\cms\shop\models\ShopStoreProperty::PROPERTY_NATURE_BRAND])->one();

            $qShopStoreProperties = \Yii::$app->shop->backendShopStore->getShopStoreProperties();
            $shopStorePropertyBrandSku = $qShopStoreProperties->andWhere(['property_nature' => \skeeks\cms\shop\models\ShopStoreProperty::PROPERTY_NATURE_BRAND_SKU])->one();


            if ($shopStorePropertyBrand && $shopStorePropertyBrandSku) {
                $rr->success = true;
                $rr->message = "Данные обновлены";

                /**
                 * @var $option ShopStorePropertyOption
                 * @var $storeProduct ShopStoreProduct
                 */
                $storeProducts = \Yii::$app->shop->backendShopStore->getShopStoreProducts()
                    ->andWhere(['shop_product_id' => null])
                ;
                foreach ($storeProducts->each() as $storeProduct) {

                    if ($storeProduct->external_data) {
                        $externalData = [];
                        foreach ($storeProduct->external_data as $key => $val) {
                            $externalData[trim($key)] = $val;
                        }
                        $skuValue = ArrayHelper::getValue($externalData, trim($shopStorePropertyBrandSku->external_code));
                        $brandValue = ArrayHelper::getValue($externalData, trim($shopStorePropertyBrand->external_code));
                        $option = $shopStorePropertyBrand->getShopStorePropertyOptions()->andWhere(['name' => $brandValue])->one();

                        if ($option && $skuValue && $option->shopBrand) {
                            $find = ShopCmsContentElement::find()
                                ->cmsSite()
                                ->innerJoinWith("shopProduct as sp")
                                ->andWhere(["sp.brand_id" => $option->shopBrand->id])
                                ->andWhere(["sp.brand_sku" => $skuValue])
                                ->groupBy([ShopCmsContentElement::tableName().".id"]);

                            if ($find->count() == 1) {
                                $infoModel = $find->one();

                                /*print_r($storeProduct->toArray());
                                print_r($infoModel->toArray());die;*/

                                if ($infoModel) {

                                    /*print_r($storeProduct->toArray());
                                    print_r($infoModel->toArray());
                                    print_r($infoModel->shopProduct->toArray());
                                    die;*/

                                    $storeProduct->shop_product_id = $infoModel->id;
                                    try {
                                        if ($storeProduct->save(false)) {
                                            $added++;
                                        }
                                    } catch (\Exception $exception) {

                                    }

                                }

                            }
                        }


                        /*if ($barcodeValue) {

                            $find = ShopCmsContentElement::find()
                                ->cmsSite()
                                ->innerJoinWith("shopProduct as sp")
                                ->innerJoinWith("shopProduct.shopProductBarcodes as barcodes")
                                ->andWhere(["barcodes.value" => $barcodeValue])
                                ->groupBy([ShopCmsContentElement::tableName().".id"]);


                            if ($find->count() == 1) {
                                $infoModel = $find->one();

                                /*print_r($storeProduct->toArray());
                                print_r($infoModel->toArray());die;

                                if ($infoModel) {
                                    $storeProduct->shop_product_id = $infoModel->id;
                                    try {
                                        if ($storeProduct->save(false)) {
                                            $added++;
                                        }
                                    } catch (\Exception $exception) {

                                    }

                                }

                            }
                        }*/

                    }
                }

                if ($added > 0) {
                    $rr->message = "Связано товаров: {$added}";
                    $rr->data = [
                        'added' => $added,
                    ];
                }
            }
        }

        return $rr;
    }


    public function actionJoinByModelBarcode()
    {

        $rr = new RequestResponse();


        set_time_limit(0);
        ini_set("memory_limit", "2G");


        if ($rr->isRequestAjaxPost()) {

            $added = 0;
            /**
             * @var $shopStorePropertyVendor ShopStoreProperty
             */
            $qShopStoreProperties = \Yii::$app->shop->backendShopStore->getShopStoreProperties();
            $shopStorePropertyBarcode = $qShopStoreProperties->andWhere(['property_nature' => ShopStoreProperty::PROPERTY_NATURE_BARCODE])->one();

            if ($shopStorePropertyBarcode) {
                $rr->success = true;
                $rr->message = "Данные обновлены";

                /**
                 * @var $vendorOption ShopStorePropertyOption
                 * @var $storeProduct ShopStoreProduct
                 */
                $storeProducts = \Yii::$app->shop->backendShopStore->getShopStoreProducts()->andWhere(['shop_product_id' => null]);
                foreach ($storeProducts->each() as $storeProduct) {
                    if ($storeProduct->external_data) {
                        $externalData = [];
                        foreach ($storeProduct->external_data as $key => $val) {
                            $externalData[trim($key)] = $val;
                        }
                        $barcodeValue = ArrayHelper::getValue($externalData, trim($shopStorePropertyBarcode->external_code));

                        if ($barcodeValue) {

                            $find = ShopCmsContentElement::find()
                                ->cmsSite()
                                ->innerJoinWith("mainCmsContentElement as mainCCE")
                                ->innerJoinWith("mainCmsContentElement.shopProduct as mainCCESp")
                                ->innerJoinWith("mainCmsContentElement.shopProduct.shopProductBarcodes as mainBarcodes")
                                /*->innerJoinWith("shopProduct as sp")
                                ->innerJoinWith("shopProduct.shopProductBarcodes as barcodes")*/
                                ->andWhere(["mainBarcodes.value" => $barcodeValue])
                                ->groupBy([ShopCmsContentElement::tableName().".id"]);

                            /*$find = ShopCmsContentElement::find()
                            ->cmsSite(CmsSite::find()->default()->one())

                            ->innerJoinWith("shopProduct as sp")
                            ->innerJoinWith("shopProduct.shopProductBarcodes as barcodes")

                            ->andWhere(["barcodes.value" => $barcodeValue])

                            ->groupBy([ShopCmsContentElement::tableName().".id"]);
                            */

                            /*print_r($find->createCommand()->rawSql);die;*/

                            if ($find->count() == 1) {
                                $infoModel = $find->one();

                                /*print_r($storeProduct->toArray());
                                print_r($infoModel->toArray());die;*/

                                if ($infoModel) {
                                    $storeProduct->shop_product_id = $infoModel->id;
                                    try {
                                        if ($storeProduct->save(false)) {
                                            $added++;
                                        }
                                    } catch (\Exception $exception) {

                                    }

                                }

                            }
                        }

                    }
                }

                if ($added > 0) {
                    $rr->message = "Связано товаров: {$added}";
                    $rr->data = [
                        'added' => $added,
                    ];
                }
            }
        }

        return $rr;
    }

    public function actionJoinByVendor()
    {
        $rr = new RequestResponse();


        set_time_limit(0);
        ini_set("memory_limit", "2G");


        if ($rr->isRequestAjaxPost()) {

            $added = 0;

            $cmsContentPropertyVendor = CmsContentProperty::find()->cmsSite()
                ->andWhere(['is_vendor' => 1])
                ->one();

            $cmsContentPropertyVendorCode = CmsContentProperty::find()->cmsSite()
                ->andWhere(['is_vendor_code' => 1])
                ->one();


            $isBrand = false;
            if ($cmsContentPropertyVendor && $cmsContentPropertyVendorCode) {
                /**
                 * @var $shopStorePropertyVendor ShopStoreProperty
                 */
                $qShopStoreProperties = \Yii::$app->shop->backendShopStore->getShopStoreProperties();
                $shopStorePropertyVendor = $qShopStoreProperties->andWhere(['cms_content_property_id' => $cmsContentPropertyVendor->id])->one();

                $qShopStoreProperties = \Yii::$app->shop->backendShopStore->getShopStoreProperties();
                $shopStorePropertyVendorCode = $qShopStoreProperties->andWhere(['cms_content_property_id' => $cmsContentPropertyVendorCode->id])->one();

                if ($shopStorePropertyVendor && $shopStorePropertyVendorCode) {
                    $rr->success = true;
                    $rr->message = "Данные обновлены";

                    /**
                     * @var $vendorOption ShopStorePropertyOption
                     * @var $storeProduct ShopStoreProduct
                     */
                    $storeProducts = \Yii::$app->shop->backendShopStore->getShopStoreProducts()->andWhere(['shop_product_id' => null]);
                    foreach ($storeProducts->each() as $storeProduct) {
                        if ($storeProduct->external_data) {
                            $externalData = [];
                            foreach ($storeProduct->external_data as $key => $val) {
                                $externalData[trim($key)] = $val;
                            }
                            $vendorValue = ArrayHelper::getValue($externalData, trim($shopStorePropertyVendor->external_code));
                            $vendorCodeValue = ArrayHelper::getValue($externalData, trim($shopStorePropertyVendorCode->external_code));


                            /*print_r($storeProduct->id);
                            print_r($storeProduct->external_data);
                            print_r($shopStorePropertyVendor->external_code);
                            print_r($shopStorePropertyVendorCode->external_code);
                            die;*/
                            if ($vendorValue && $vendorCodeValue && is_string($vendorValue)) {
                                $vendorOption = $shopStorePropertyVendor->getShopStorePropertyOptions()->andWhere(['name' => trim((string) $vendorValue)])->one();

                                /*print_r($storeProduct->id);
                                print_r($vendorOption->name);
                                print_r($vendorCodeValue);
                                print_r($vendorOption->cms_content_element_id);

                                die;*/


                                $find = ShopCmsContentElement::find()
                                    ->cmsSite()
                                    ->innerJoinWith("shopProduct as sp");


                                $find1 = CmsContentElementProperty::find()->select(['element_id as id'])
                                    ->where([
                                        "value_element_id" => $vendorOption->cms_content_element_id,
                                        "property_id"      => $cmsContentPropertyVendor->id,
                                    ]);
                                $find2 = CmsContentElementProperty::find()->select(['element_id as id'])
                                    ->where([
                                        "value"       => $vendorCodeValue,
                                        "property_id" => $cmsContentPropertyVendorCode->id,
                                    ]);


                                /*if ($find2->one()) {
                                    print_r($find2->one());die;
                                }*/

                                $find->andWhere([
                                    CmsContentElement::tableName().".id" => $find1,
                                ]);

                                $find->andWhere([
                                    CmsContentElement::tableName().".id" => $find2,
                                ]);


                                if ($find->count() == 1) {
                                    $infoModel = $find->one();

                                    /*print_r($storeProduct->toArray());
                                    print_r($infoModel->toArray());die;*/

                                    if ($infoModel) {
                                        $storeProduct->shop_product_id = $infoModel->id;
                                        try {
                                            if ($storeProduct->save(false)) {
                                                $added++;
                                            }
                                        } catch (\Exception $exception) {

                                        }

                                    }

                                }
                            }

                        }
                    }

                    if ($added > 0) {
                        $rr->message = "Связано товаров: {$added}";
                        $rr->data = [
                            'added' => $added,
                        ];
                    }

                }
            }
        }

        return $rr;
    }

}
