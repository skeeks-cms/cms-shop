<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 31.05.2015
 */

namespace skeeks\cms\shop\controllers;

use skeeks\cms\backend\controllers\BackendModelStandartController;
use skeeks\cms\grid\BooleanColumn;
use skeeks\cms\grid\DateTimeColumnData;
use skeeks\cms\grid\ImageColumn2;
use skeeks\cms\grid\UserColumnData;
use skeeks\cms\helpers\Image;
use skeeks\cms\models\CmsCountry;
use skeeks\cms\queryfilters\QueryFiltersEvent;
use skeeks\cms\rbac\CmsManager;
use skeeks\cms\shop\models\ShopBrand;
use skeeks\cms\shop\models\ShopCollection;
use skeeks\cms\shop\models\ShopProduct;
use skeeks\cms\widgets\AjaxFileUploadWidget;
use skeeks\cms\widgets\AjaxSelectModel;
use skeeks\cms\widgets\formInputs\comboText\ComboTextInputWidget;
use skeeks\cms\widgets\GridView;
use skeeks\yii2\form\fields\BoolField;
use skeeks\yii2\form\fields\FieldSet;
use skeeks\yii2\form\fields\NumberField;
use skeeks\yii2\form\fields\SelectField;
use skeeks\yii2\form\fields\WidgetField;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class AdminShopCollectionController extends BackendModelStandartController
{
    public function init()
    {
        $this->name = \Yii::t('skeeks/cms', "Коллекции");
        $this->modelShowAttribute = "name";
        $this->modelClassName = ShopCollection::class;

        $this->generateAccessActions = true;
        /*$this->permissionName = CmsManager::PERMISSION_ROLE_ADMIN_ACCESS;*/

        /*$this->accessCallback = function () {
            if (!\Yii::$app->skeeks->site->is_default) {
                return false;
            }
            return \Yii::$app->user->can(CmsManager::PERMISSION_ROLE_ADMIN_ACCESS);
        };*/


        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return ArrayHelper::merge(parent::actions(), [
            'index' => [
                "filters" => [
                    'visibleFilters' => [
                        'q',
                        'brand',
                    ],
                    "filtersModel"   => [
                        'rules'            => [
                            ['brand', 'safe'],
                            ['q', 'safe'],
                        ],
                        'attributeDefines' => [
                            'q',
                            'brand',
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
                                        $query->andWhere([
                                            'or',
                                            ['like', ShopCollection::tableName().'.description_full', $e->field->value],
                                            ['like', ShopCollection::tableName().'.description_short', $e->field->value],
                                            ['like', ShopCollection::tableName().'.name', $e->field->value],
                                        ]);
                                    }
                                },
                            ],
                            
                            'brand' => [
                                'class'    => WidgetField::class,
                                'widgetClass'    => AjaxSelectModel::class,
                                'widgetConfig'    => [
                                    'modelClass'    => ShopBrand::class,
                                    'multiple' => true,
                                ],
                                'label'    => \Yii::t('skeeks/cms', 'Бренд'),
                                'on apply' => function (QueryFiltersEvent $e) {
                                    /**
                                     * @var $query ActiveQuery
                                     */
                                    $query = $e->dataProvider->query;
                                    if ($e->field->value) {
                                        $query->andFilterWhere([
                                            'shop_brand_id' => $e->field->value,
                                        ]);
                                    }

                                },
                            ],
                        ],
                    ],
                ],
            'grid'  => [
                'defaultOrder'   => [
                    'created_at' => SORT_DESC,
                ],
                /*'sortAttributes' => [
                    'countProducts'   => [
                        'asc'     => ['countProducts' => SORT_ASC],
                        'desc'    => ['countProducts' => SORT_DESC],
                        'label'   => 'Количество товаров',
                        'default' => SORT_ASC,
                    ],
                ],*/

                'visibleColumns' => [
                    'checkbox',
                    'actions',

                    'created_at',
                    'custom',

                    'brand',

                    'countProducts',
                    'is_active',
                    'created_by',
                    'view',
                ],

                'columns'        => [

                    'created_at'   => [
                        'class' => DateTimeColumnData::class
                    ],
                    'updated_at'   => [
                        'class' => DateTimeColumnData::class
                    ],
                    'created_by'   => [
                        'class' => UserColumnData::class
                    ],

                    'countProducts'   => [
                        'format'    => 'raw',
                        'value'     => function (ShopCollection $shopBrand) {
                            return $shopBrand->raw_row['countProducts'];
                        },
                        'attribute' => 'countProducts',
                        'label'     => 'Количество товаров',
                        'beforeCreateCallback' => function (GridView $gridView) {
                            $query = $gridView->dataProvider->query;

                            $countProductsQuery = ShopProduct::find()
                                ->joinWith("collections as collections")
                                ->select(["total" => new \yii\db\Expression("count(1)"),])
                                ->andWhere([
                                    'shop_product2collection.shop_collection_id' => new Expression(ShopCollection::tableName().".id"),
                                ]);

                            $query->addSelect([
                                'countProducts' => $countProductsQuery,
                            ]);

                            $gridView->sortAttributes['countProducts'] = [
                                'asc'     => ['countProducts' => SORT_ASC],
                                'desc'    => ['countProducts' => SORT_DESC],
                                'label'   => '',
                                'default' => SORT_ASC,
                            ];
                        },

                    ],

                    'brand' => [
                        'attribute' => 'shop_brand_id',
                        'value' => function (ShopCollection $model) {
                            return (string)$model->brand ? $model->brand->name : "";
                        },
                    ],
                    'is_active'      => [
                        'class' => BooleanColumn::class,
                    ],

                    'custom' => [
                        'attribute' => 'name',
                        'format'    => 'raw',
                        'value'     => function (ShopCollection $model) {

                            $data = [];
                            $name = $model->asText;
                            if ($model->sx_id) {
                                $data[] = Html::a($name . " <small data-toggle='tooltip' title='SkeekS Suppliers ID: {$model->sx_id}'><i class='fas fa-link'></i></small>", "#", ['class' => 'sx-trigger-action']);
                            } else {
                                $data[] = Html::a($model->asText, "#", ['class' => 'sx-trigger-action']);
                            }

                            $info = implode("<br />", $data);

                            return "<div class='row no-gutters'>
                                            <div class='sx-trigger-action' style='width: 50px;'>
                                                <a href='#' style='text-decoration: none; border-bottom: 0;'>
                                                    <img src='".($model->image ? $model->image->src : Image::getCapSrc())."' style='max-width: 50px; max-height: 50px; border-radius: 5px;' />
                                                </a>
                                            </div>
                                            <div style='margin: auto 5px;'>".$info."</div>
                                        </div>";;
                        },
                    ],

                    'flag_image_id' => [
                        'class' => ImageColumn2::class,
                    ],
                    
                    'view' => [
                        'value'          => function (ShopCollection $model) {
                            return \yii\helpers\Html::a('<i class="fas fa-external-link-alt"></i>', $model->absoluteUrl,
                                [
                                    'target'    => '_blank',
                                    'title'     => \Yii::t('skeeks/cms', 'Watch to site (opens new window)'),
                                    'data-pjax' => '0',
                                    'class'     => 'btn btn-sm',
                                ]);
                        },
                        'format'         => 'raw',
                        /*'label'  => "Смотреть",*/
                        'headerOptions'  => [
                            'style' => 'max-width: 40px; width: 40px;',
                        ],
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
        return [
            'main' => [
                'class'  => FieldSet::class,
                'name'   => \Yii::t('skeeks/cms', 'Main'),
                'fields' => [

                    'is_active' => [
                        'class'     => BoolField::class,
                        'allowNull' => false,
                    ],
                    'name',

                    'shop_brand_id'    => [
                        'class'        => WidgetField::class,
                        'widgetClass'  => AjaxSelectModel::class,
                        'widgetConfig' => [
                            'modelClass'       => ShopBrand::class,
                            'searchQuery'      => function ($word = '') {
                                $query = ShopBrand::find();
                                if ($word) {
                                    $query->search($word);
                                }
                                return $query;
                            },
                        ],

                    ],

                    'cms_image_id'     => [
                        'class'        => WidgetField::class,
                        'widgetClass'  => \skeeks\cms\widgets\AjaxFileUploadWidget::class,
                        'widgetConfig' => [
                            'accept'   => 'image/*',
                            'multiple' => false,
                        ],
                    ],

                    'imageIds'     => [
                        'class'        => WidgetField::class,
                        'widgetClass'  => AjaxFileUploadWidget::class,
                        'widgetConfig' => [
                            'accept'   => 'image/*',
                            'multiple' => true,
                        ],
                    ],
                    'description_short' => [
                        'class'       => WidgetField::class,
                        'widgetClass' => ComboTextInputWidget::class,
                    ],
                    'description_full'  => [
                        'class'       => WidgetField::class,
                        'widgetClass' => ComboTextInputWidget::class,
                    ],
                ],
            ],

            'seo' => [
                'class'          => FieldSet::class,
                'name'           => \Yii::t('skeeks/cms', 'SEO'),
                'elementOptions' => [
                    'isOpen' => false,
                ],
                'fields'         => [
                    'seo_h1',
                    'meta_title',
                    'meta_description',
                    'meta_keywords',
                    'code',
                ],
            ],

            'additional' => [
                'class'          => FieldSet::class,
                'elementOptions' => [
                    'isOpen' => false,
                ],
                'name'           => \Yii::t('skeeks/cms', 'Дополнительно'),
                'fields'         => [
                    'priority' => [
                        'class' => NumberField::class,
                    ],
                    'external_id',
                ],
            ],

        ];
    }
}
