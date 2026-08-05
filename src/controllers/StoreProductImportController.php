<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

namespace skeeks\cms\shop\controllers;

use skeeks\cms\backend\actions\BackendModelAction;
use skeeks\cms\backend\controllers\BackendModelStandartController;
use skeeks\cms\backend\widgets\BackendEntityLink;
use skeeks\cms\components\Cms;
use skeeks\cms\helpers\Image;
use skeeks\cms\helpers\RequestResponse;
use skeeks\cms\models\CmsAgent;
use skeeks\cms\queryfilters\QueryFiltersEvent;
use skeeks\cms\shop\models\ShopBasket;
use skeeks\cms\shop\models\ShopStoreProduct;
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
class StoreProductImportController extends BackendModelStandartController
{
    public function init()
    {
        $this->name = \Yii::t('skeeks/shop/app', 'Импорт');
        $this->modelShowAttribute = "name";

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

                                $imageSrc = Image::getCapSrc();
                                if ($model->shopProduct && $model->shopProduct->cmsContentElement->mainProductImage) {
                                    $imageSrc = $model->shopProduct->cmsContentElement->mainProductImage->src;
                                }

                                if ($model->shop_product_id) {
                                    $attached = BackendEntityLink::widget([
                                        'controllerId' => '/shop/admin-cms-content-element',
                                        'modelId'      => $model->shop_product_id,
                                        'urlParams'    => [
                                            'content_id' => $model->shopProduct->cmsContentElement->content_id,
                                        ],
                                        'content'      => Html::tag('i', '', ['class' => 'fas fa-link']),
                                        'options'      => [
                                            'class'      => 'sx-preview-card__related sx-text--success',
                                            'title'      => "Товар оформлен {$model->asText}",
                                            'aria-label' => "Товар оформлен {$model->asText}",
                                        ],
                                    ]);
                                } else {
                                    $attached = Html::tag('span', Html::tag('i', '', ['class' => 'fas fa-link']), [
                                        'class'      => 'sx-preview-card__related sx-text--danger',
                                        'title'      => 'Этот товар не оформлен и не показывается на сайте',
                                        'aria-label' => 'Этот товар не оформлен и не показывается на сайте',
                                    ]);
                                }

                                $media = BackendEntityLink::widget([
                                    'controllerId' => '/shop/store-product-import',
                                    'modelId'      => $model->id,
                                    'content'      => Html::img($imageSrc, [
                                        'class' => 'sx-photo sx-img-size-small',
                                        'alt'   => '',
                                    ]),
                                    'options'      => [
                                        'class'      => 'sx-preview-card__media-link',
                                        'aria-label' => (string)$model->asText,
                                    ],
                                ]);
                                $title = BackendEntityLink::widget([
                                    'controllerId' => '/shop/store-product-import',
                                    'modelId'      => $model->id,
                                    'label'        => $model->asText,
                                    'options'      => [
                                        'class'      => 'sx-preview-card__title sx-collection-cell__primary',
                                        'aria-label' => (string)$model->asText,
                                    ],
                                ]);

                                return Html::tag('div',
                                    $attached
                                    .Html::tag('div', $media, ['class' => 'sx-preview-card__media'])
                                    .Html::tag('div', $title, [
                                        'class' => 'sx-preview-card__content sx-collection-cell sx-collection-cell--stack',
                                    ]),
                                    ['class' => 'sx-preview-card sx-preview-card--file']
                                );
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

            "create" => [
                'fields' => [$this, 'updateFields'],
            ],
            "update" => [
                'fields' => [$this, 'updateFields'],
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

}
