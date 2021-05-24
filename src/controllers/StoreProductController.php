<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

namespace skeeks\cms\shop\controllers;

use skeeks\cms\backend\actions\BackendModelAction;
use skeeks\cms\backend\BackendAction;
use skeeks\cms\backend\controllers\BackendModelStandartController;
use skeeks\cms\backend\ViewBackendAction;
use skeeks\cms\components\Cms;
use skeeks\cms\grid\DateColumn;
use skeeks\cms\grid\DateTimeColumnData;
use skeeks\cms\helpers\Image;
use skeeks\cms\helpers\RequestResponse;
use skeeks\cms\models\CmsAgent;
use skeeks\cms\models\CmsContentElement;
use skeeks\cms\models\CmsContentElementProperty;
use skeeks\cms\queryfilters\QueryFiltersEvent;
use skeeks\cms\shop\models\ShopBasket;
use skeeks\cms\shop\models\ShopCmsContentElement;
use skeeks\cms\shop\models\ShopStoreProduct;
use skeeks\cms\shop\models\ShopStoreProperty;
use skeeks\cms\shop\models\ShopStorePropertyOption;
use skeeks\yii2\form\fields\BoolField;
use skeeks\yii2\form\fields\NumberField;
use skeeks\yii2\form\fields\SelectField;
use yii\base\Event;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

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
            'index'  => [
                "filters"         => [
                    'visibleFilters' => [
                        'q',
                        'has_shop_product',
                        'quantity',
                        //'component',
                    ],

                    'filtersModel' => [
                        'rules' => [
                            ['q', 'safe'],
                            ['has_shop_product', 'safe'],
                        ],

                        'attributeDefines' => [
                            'q',
                            'has_shop_product',
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
                                            ])
                                        ;

                                        $query->joinWith("shopProduct as shopProduct");
                                        $query->joinWith("shopProduct.cmsContentElement as element");

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
                    ],
                    'columns'        => [
                        'created_at' => [
                            'class' => DateTimeColumnData::class
                        ],
                        'updated_at' => [
                            'class' => DateTimeColumnData::class
                        ],
                        'quantity' => [
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
                                        'content' => <<<HTML

<span style="color: green; font-size: 17px;">
    <i class="fas fa-link" style="width: 20px;" data-toggle="tooltip" title="Товар оформлен {$model->asText}"></i>
</span>
HTML

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
            ],

            "create" => [
                'fields' => [$this, 'updateFields'],
            ],

            "update" => [
                'fields' => [$this, 'updateFields'],
            ],

            "import" => [
                'class' => ViewBackendAction::class,
                'icon' => 'far fa-file-excel',
                'name' => 'Импорт из excel',
            ],
        ]);
    }

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
            'quantity' => [
                'class' => NumberField::class
            ],
            'purchase_price' => [
                'class' => NumberField::class
            ],
            'selling_price' => [
                'class' => NumberField::class
            ],

        ];
    }

    public function actionJoinByVendor()
    {
        $rr = new RequestResponse();


        set_time_limit(0);
        ini_set("memory_limit", "2G");


        if ($rr->isRequestAjaxPost()) {

            $added = 0;

            $shopCmsContentPropertyVendor = \skeeks\cms\shop\models\ShopCmsContentProperty::find()
                ->innerJoinWith('cmsContentProperty as cmsContentProperty')
                ->andWhere(['cmsContentProperty.cms_site_id' => \Yii::$app->skeeks->site->id])
                ->andWhere(['is_vendor' => 1])
                ->one();

            $shopCmsContentPropertyVendorCode = \skeeks\cms\shop\models\ShopCmsContentProperty::find()
                ->innerJoinWith('cmsContentProperty as cmsContentProperty')
                ->andWhere(['cmsContentProperty.cms_site_id' => \Yii::$app->skeeks->site->id])
                ->andWhere(['is_vendor_code' => 1])
                ->one();

            $isBrand = false;
            if ($shopCmsContentPropertyVendor && $shopCmsContentPropertyVendorCode) {
                /**
                 * @var $shopStorePropertyVendor ShopStoreProperty
                 */
                $qShopStoreProperties = \Yii::$app->shop->backendShopStore->getShopStoreProperties();
                $shopStorePropertyVendor = $qShopStoreProperties->andWhere(['cms_content_property_id' => $shopCmsContentPropertyVendor->cms_content_property_id])->one();

                $qShopStoreProperties = \Yii::$app->shop->backendShopStore->getShopStoreProperties();
                $shopStorePropertyVendorCode = $qShopStoreProperties->andWhere(['cms_content_property_id' => $shopCmsContentPropertyVendorCode->cms_content_property_id])->one();

                if ($shopStorePropertyVendor && $shopStorePropertyVendorCode) {
                    $rr->success = true;
                    $rr->message = "Данные обновлены";

                    /**
                     * @var $vendorOption ShopStorePropertyOption
                     * @var $storeProduct ShopStoreProduct
                     */
                    $storeProducts = \Yii::$app->shop->backendShopStore->getShopStoreProducts()->andWhere(['shop_product_id' => null]);
                    foreach ($storeProducts->each() as $storeProduct)
                    {
                        if ($storeProduct->external_data) {
                            $externalData = [];
                            foreach ($storeProduct->external_data as $key => $val)
                            {
                                $externalData[trim($key)] = $val;
                            }
                            $vendorValue = ArrayHelper::getValue($externalData, trim($shopStorePropertyVendor->external_code));
                            $vendorCodeValue = ArrayHelper::getValue($externalData, trim($shopStorePropertyVendorCode->external_code));


                            /*print_r($storeProduct->id);
                            print_r($storeProduct->external_data);
                            print_r($shopStorePropertyVendor->external_code);
                            print_r($shopStorePropertyVendorCode->external_code);
                            die;*/
                            if ($vendorValue && $vendorCodeValue) {
                                $vendorOption = $shopStorePropertyVendor->getShopStorePropertyOptions()->andWhere(['name' => trim($vendorValue)])->one();

                                /*print_r($storeProduct->id);
                                print_r($vendorOption->name);
                                print_r($vendorCodeValue);
                                print_r($vendorOption->cms_content_element_id);

                                die;*/


                                $find = ShopCmsContentElement::find()
                                    ->cmsSite()
                                    ->innerJoinWith("shopProduct as sp")
                                ;


                                $find1 = CmsContentElementProperty::find()->select(['element_id as id'])
                                            ->where([
                                                "value_element_id"  => $vendorOption->cms_content_element_id,
                                                "property_id" => $shopCmsContentPropertyVendor->cms_content_property_id,
                                            ]);
                                $find2 = CmsContentElementProperty::find()->select(['element_id as id'])
                                            ->where([
                                                "value"  => $vendorCodeValue,
                                                "property_id" => $shopCmsContentPropertyVendorCode->cms_content_property_id,
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
                                                $added ++;
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
                            'added' => $added
                        ];
                    }

                }
            }
        }

        return $rr;
    }

}
