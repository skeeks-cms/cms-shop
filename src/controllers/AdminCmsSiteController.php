<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 15.05.2015
 */

namespace skeeks\cms\shop\controllers;

use skeeks\cms\backend\actions\BackendModelUpdateAction;
use skeeks\cms\models\CmsSiteDomain;
use skeeks\cms\modules\admin\actions\modelEditor\AdminModelEditorAction;
use skeeks\cms\shop\models\CmsSite;
use skeeks\cms\shop\models\ShopProduct;
use skeeks\cms\shop\models\ShopStore;
use skeeks\cms\shop\models\ShopTypePrice;
use skeeks\cms\widgets\formInputs\ckeditor\Ckeditor;
use skeeks\yii2\form\fields\BoolField;
use skeeks\yii2\form\fields\FieldSet;
use skeeks\yii2\form\fields\WidgetField;
use yii\base\Event;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class AdminCmsSiteController extends \skeeks\cms\controllers\AdminCmsSiteController
{

    public function actions()
    {
        $result = ArrayHelper::merge(parent::actions(), [
            "index" => [
                "grid" => [
                    'on init' => function (Event $e) {
                        /**
                         * @var $dataProvider ActiveDataProvider
                         * @var $query ActiveQuery
                         */
                        $query = $e->sender->dataProvider->query;
                        $dataProvider = $e->sender->dataProvider;

                        $query->joinWith('cmsSiteDomains as cmsSiteDomains');

                        $qCountDomains = CmsSiteDomain::find()->select(["total" => "count(*)"])->where(['cms_site_id' => new Expression(CmsSite::tableName().".id")]);

                        $query->groupBy(CmsSite::tableName().".id");



                        $shopTypePricesQuery = ShopTypePrice::find()->select(['count(*) as inner_count'])->where([
                            'cms_site_id' => new Expression(CmsSite::tableName().".id"),
                        ]);

                        $shopStoreQuery = ShopStore::find()->select(['count(*) as inner_count'])->where([
                            'cms_site_id' => new Expression(CmsSite::tableName().".id"),
                        ]);
                        $shopProductQuery = ShopProduct::find()->joinWith("cmsContentElement as cmsContentElement")->select(['count(*) as inner_count'])->where([
                            'cmsContentElement.cms_site_id' => new Expression(CmsSite::tableName().".id"),
                        ]);

                        $shopProductConnectedQuery = ShopProduct::find()->joinWith("cmsContentElement as cmsContentElement")->select(['count(*) as inner_count1'])->where([
                            'cmsContentElement.cms_site_id' => new Expression(CmsSite::tableName().".id"),
                        ])->andWhere([
                            'is not',
                            'main_pid',
                            null,
                        ]);


                        $query->select([
                            CmsSite::tableName().'.*',
                            'countDomains' => $qCountDomains,

                            'countShopStores'            => $shopStoreQuery,
                            'countShopTypePrices'        => $shopTypePricesQuery,
                            'countShopProducts'          => $shopProductQuery,
                            'countShopProductsConnected' => $shopProductConnectedQuery,
                        ]);
                    },

                    'sortAttributes' => [
                        'countShopStores'     => [
                            'asc'     => ['countShopStores' => SORT_ASC],
                            'desc'    => ['countShopStores' => SORT_DESC],
                            'label'   => 'Количество складов',
                            'default' => SORT_ASC,
                        ],
                        'countShopTypePrices' => [
                            'asc'     => ['countShopTypePrices' => SORT_ASC],
                            'desc'    => ['countShopTypePrices' => SORT_DESC],
                            'label'   => 'Количество типов цен',
                            'default' => SORT_ASC,
                        ],
                        'countShopProducts'   => [
                            'asc'     => ['countShopProducts' => SORT_ASC],
                            'desc'    => ['countShopProducts' => SORT_DESC],
                            'label'   => 'Количество товаров',
                            'default' => SORT_ASC,
                        ],
                    ],


                    'visibleColumns' => [
                        'checkbox',
                        'actions',
                        'custom',
                        //'id',
                        //'image_id',
                        'is_active',
                        'priority',
                        //'name',
                        'countDomains',
                        'is_supplier',
                        'is_receiver',

                        'countShopStores',
                        'countShopTypePrices',
                        'countShopProducts',
                    ],

                    'columns' => [
                        'is_supplier' => [
                            'label'  => 'Поставщик?',
                            'value' => function (CmsSite $model) {
                                return $model->shopSite->is_supplier ? "Да" : "";
                            },
                        ],
                        'is_receiver' => [
                            'label'  => 'Получает товары?',
                            'value' => function (CmsSite $model) {
                                return $model->shopSite->is_receiver ? "Да" : "";
                            },
                        ],


                        'countShopProducts'   => [
                            'format'    => 'raw',
                            'value'     => function (CmsSite $cmsSite) {

                                if ($cmsSite->is_default) {
                                    return $cmsSite->raw_row['countShopProducts'];
                                } else {
                                    $result = $cmsSite->raw_row['countShopProducts'];

                                    if ($cmsSite->raw_row['countShopProductsConnected']) {
                                        $result .= " (".Html::tag('b', $cmsSite->raw_row['countShopProductsConnected'], [
                                                'title' => 'Количество привязанных/продаваемых товаров',
                                                'style' => 'color: green;',
                                            ]).")";
                                    }


                                    return $result;
                                }

                            },
                            'attribute' => 'countShopProducts',
                            'label'     => 'Количество товаров',
                        ],
                        'countShopStores'     => [
                            'value'     => function (CmsSite $cmsSite) {
                                return $cmsSite->raw_row['countShopStores'];
                            },
                            'attribute' => 'countShopStores',
                            'label'     => 'Количество складов',
                        ],
                        'countShopTypePrices' => [
                            'value'     => function (CmsSite $cmsSite) {
                                return $cmsSite->raw_row['countShopTypePrices'];
                            },
                            'attribute' => 'countShopTypePrices',
                            'label'     => 'Количество типов цен',
                        ],
                    ],
                ],
            ],

            "shop" => [
                'class'             => BackendModelUpdateAction::class,
                'name'              => 'Данные магазина',
                'priority'          => 200,
                'fields'            => [$this, 'updateShopFields'],
                'on initFormModels' => function (Event $e) {
                    /**
                     * @var $model CmsSite
                     */
                    $model = $e->sender->model;
                    $shopSite = $model->shopSite;
                    $e->sender->formModels['shopSite'] = $shopSite;
                },
            ],
        ]);

        return $result;
    }

    public function updateShopFields()
    {
        return [
            'main' => [
                'class'  => FieldSet::class,
                'name'   => 'Эти поля не могут редатировать владельцы сайтов',
                'fields' => [
                    'shopSite.is_supplier'          => [
                        'class'     => BoolField::class,
                        'allowNull' => false,
                    ],
                    'shopSite.is_receiver'          => [
                        'class'     => BoolField::class,
                        'allowNull' => false,
                    ],
                    'shopSite.description_internal' => [
                        'class'       => WidgetField::class,
                        'widgetClass' => Ckeditor::class,
                    ],
                ],
            ],


        ];
    }

}