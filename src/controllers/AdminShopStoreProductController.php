<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */

namespace skeeks\cms\shop\controllers;

use skeeks\cms\backend\controllers\BackendModelStandartController;
use skeeks\cms\helpers\Image;
use skeeks\cms\models\CmsAgent;
use skeeks\cms\queryfilters\QueryFiltersEvent;
use skeeks\cms\shop\models\ShopStoreProduct;
use skeeks\cms\widgets\AjaxFileUploadWidget;
use skeeks\cms\ya\map\widgets\YaMapInput;
use skeeks\yii2\ckeditor\CKEditorWidget;
use skeeks\yii2\form\fields\BoolField;
use skeeks\yii2\form\fields\HtmlBlock;
use skeeks\yii2\form\fields\NumberField;
use skeeks\yii2\form\fields\WidgetField;
use yii\base\Event;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class AdminShopStoreProductController extends BackendModelStandartController
{
    public function init()
    {
        $this->name = "Товары";
        $this->modelShowAttribute = "asText";
        $this->modelClassName = ShopStoreProduct::class;

        $this->generateAccessActions = false;

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
                                        $query->andWhere([
                                            'or',
                                            ['like', ShopStoreProduct::tableName().'.id', $e->field->value],
                                            ['like', ShopStoreProduct::tableName().'.name', $e->field->value],
                                            ['like', ShopStoreProduct::tableName().'.external_id', $e->field->value],
                                            ['like', ShopStoreProduct::tableName().'.external_data', $e->field->value],
                                        ]);

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
                    },
                    'defaultOrder'   => [
                        'id' => SORT_DESC,
                    ],
                    'visibleColumns' => [

                        'checkbox',
                        'actions',

                        //'id',
                        'custom',
                        'shop_store_id',

                        'external_id',

                        'quantity',
                        'purchase_price',
                        'selling_price',
                    ],
                    'columns'        => [
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
                                $data[] = Html::a($model->asText, "#", ['class' => 'sx-trigger-action']);

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
                                return "<div class='row no-gutters'>
                                            {$attched}
                                            <div class='sx-trigger-action' style='width: 50px;'>
                                                <a href='#' style='text-decoration: none; border-bottom: 0;'>
                                                    <img src='".($imageSrc)."' style='max-width: 50px; max-height: 50px; border-radius: 5px;' />
                                                </a>
                                            </div>
                                            <div style='margin-left: 5px;'>".$info."</div>
                                        </div>";;
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

}
