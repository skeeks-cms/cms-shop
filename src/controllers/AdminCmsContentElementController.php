<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 15.05.2015
 */

namespace skeeks\cms\shop\controllers;

use skeeks\cms\backend\actions\BackendGridModelRelatedAction;
use skeeks\cms\backend\actions\BackendModelAction;
use skeeks\cms\backend\actions\BackendModelMultiDialogEditAction;
use skeeks\cms\backend\actions\BackendModelUpdateAction;
use skeeks\cms\backend\events\ViewRenderEvent;
use skeeks\cms\backend\widgets\SelectModelDialogContentElementWidget;
use skeeks\cms\helpers\Image;
use skeeks\cms\helpers\RequestResponse;
use skeeks\cms\helpers\UrlHelper;
use skeeks\cms\IHasUrl;
use skeeks\cms\models\CmsContent;
use skeeks\cms\models\CmsContentElement;
use skeeks\cms\modules\admin\actions\AdminAction;
use skeeks\cms\modules\admin\actions\modelEditor\AdminModelEditorAction;
use skeeks\cms\queryfilters\filters\FilterField;
use skeeks\cms\queryfilters\filters\modes\FilterModeEmpty;
use skeeks\cms\queryfilters\filters\modes\FilterModeEq;
use skeeks\cms\queryfilters\filters\modes\FilterModeNe;
use skeeks\cms\queryfilters\filters\modes\FilterModeNotEmpty;
use skeeks\cms\queryfilters\filters\NumberFilterField;
use skeeks\cms\queryfilters\QueryFiltersEvent;
use skeeks\cms\shop\models\ShopCmsContentElement;
use skeeks\cms\shop\models\ShopProduct;
use skeeks\cms\shop\models\ShopProductPrice;
use skeeks\cms\shop\models\ShopStoreProduct;
use skeeks\cms\shop\models\ShopSupplier;
use skeeks\cms\shop\models\ShopTypePrice;
use skeeks\yii2\form\fields\BoolField;
use skeeks\yii2\form\fields\HtmlBlock;
use skeeks\yii2\form\fields\SelectField;
use skeeks\yii2\form\fields\WidgetField;
use yii\base\DynamicModel;
use yii\base\Event;
use yii\db\ActiveQuery;
use yii\db\Exception;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @property CmsContent $content
 *
 * Class AdminCmsContentTypeController
 * @package skeeks\cms\controllers
 */
class AdminCmsContentElementController extends \skeeks\cms\controllers\AdminCmsContentElementController
{
    public $notSubmitParam = 'sx-reload-form';

    public $modelClassName = ShopCmsContentElement::class;
    public $modelShowAttribute = "asText";

    static public function getSorts(ActiveQuery $activeQuery)
    {
        $activeQuery->joinWith('shopProduct as sp');

        $sorts = [
            'quantity' => [
                'asc'     => ['sp.quantity' => SORT_ASC],
                'desc'    => ['sp.quantity' => SORT_DESC],
                'label'   => \Yii::t('skeeks/shop/app', 'Available quantity'),
                'default' => SORT_ASC,
            ],
        ];

        if (\Yii::$app->shop->shopTypePrices) {
            foreach (\Yii::$app->shop->shopTypePrices as $shopTypePrice) {


                /*$pricesQuery = (new \yii\db\Query())->from(ShopProductPrice::tableName())->andWhere(['type_price_id' => $shopTypePrice->id]);
                $activeQuery->leftJoin(["p{$shopTypePrice->id}" => $pricesQuery], "p{$shopTypePrice->id}.product_id = sp.id");*/
                $activeQuery->leftJoin(["p{$shopTypePrice->id}" => ShopProductPrice::tableName()], [
                    "p{$shopTypePrice->id}.product_id"    => new Expression("sp.id"),
                    "p{$shopTypePrice->id}.type_price_id" => $shopTypePrice->id,
                ]);

                $sorts['price.'.$shopTypePrice->id] = [
                    'asc'     => ["p{$shopTypePrice->id}.price" => SORT_ASC],
                    'desc'    => ["p{$shopTypePrice->id}.price" => SORT_DESC],
                    'label'   => $shopTypePrice->name,
                    'default' => SORT_ASC,
                ];
            }
        }

        return $sorts;
    }

    public function init()
    {
        $this->name = \Yii::t('skeeks/shop/app', 'Elements');
        parent::init();
    }
    /**
     * @inheritdoc
     */
    public function actions()
    {
        $actions = ArrayHelper::merge(parent::actions(), [
                /*"create" => ["callback" => [$this, 'create']],
                "update" => ["callback" => [$this, 'update']],*/


                /*"view" => [
                    'class'          => BackendModelAction::class,
                    "name"           => "Посмотреть",
                ],*/

                "connect-to-main" => [
                    /*'on afterRender' => function(ViewRenderEvent $viewRenderEvent) {
                    if (!$this->action->model->shopProduct->main_pid) {
                        $viewRenderEvent->content = <<<HTML
<div class="text-center g-ma-20">
<a href="#" class="btn btn-xxl btn-primary">Создать главный товар</a>
</div>
HTML
                            ;
                    }

                    },*/
                    'class'    => BackendModelUpdateAction::class,
                    "name"     => "Привязать к главному",
                    "icon"     => "fas fa-link",
                    'priority' => 90,

                    'on initFormModels' => function (Event $e) {
                        $model = $e->sender->model;
                        $e->sender->formModels['shopProduct'] = $model->shopProduct;
                    },


                    'fields'         => function (BackendModelUpdateAction $action) {
                        $model = $action->model;

                        $result = [
                            'shopProduct.main_pid' => [
                                'class'        => WidgetField::class,
                                'widgetClass'  => SelectModelDialogContentElementWidget::class,
                                'widgetConfig' => [
                                    'content_id'  => $model->content_id,
                                    'options'       => [
                                        'data-form-reload' => "true",
                                    ],
                                    'dialogRoute' => [
                                        '/shop/admin-cms-content-element',
                                        'w3-submit-key' => "1",
                                        'findex'        => [
                                            'shop_supplier_id' => [
                                                'mode' => 'empty',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ];

                        if (!$model->shopProduct->main_pid) {
                            $url = Url::to(['/shop/admin-cms-content-element/create', 'content_id' => $model->content_id, 'shop_sub_product_id' => $model->id]);
                            $result[] = [
                                'class' => HtmlBlock::class,
                                'content' => <<<HTML
<div class="text-center g-ma-20">
<a href="{$url}" data-pjax='0' class="btn btn-xxl btn-primary">Создать главный товар</a>
</div>
HTML

                            ];
                        }

                        return $result;
                    },
                    'accessCallback' => function (BackendModelAction $action) {

                        $model = $action->model;

                        if (!$model) {
                            return false;
                        }

                        if (!$model->shopProduct) {
                            return false;
                        }

                        if ($model->shopProduct->isSubProduct) {
                            return true;
                        }
                    },
                ],

                "copy" => [
                    'class'          => BackendModelUpdateAction::class,
                    "name"           => \Yii::t('skeeks/cms', 'Copy'),
                    "icon"           => "fas fa-copy",
                    "beforeContent"  => "Механизм создания копии текущего элемента. Укажите параметры копирования и нажмите применить.",
                    "successMessage" => "Товар успешно скопирован",

                    'on initFormModels' => function (Event $e) {
                        $model = $e->sender->model;
                        $dm = new DynamicModel(['is_copy_images', 'is_copy_files']);
                        $dm->addRule(['is_copy_images', 'is_copy_files'], 'boolean');

                        $dm->is_copy_images = true;
                        $dm->is_copy_files = true;

                        $e->sender->formModels['dm'] = $dm;
                    },

                    'on beforeSave' => function (Event $e) {
                        /**
                         * @var $action BackendModelUpdateAction;
                         */
                        $action = $e->sender;
                        $action->isSaveFormModels = false;
                        $dm = ArrayHelper::getValue($action->formModels, 'dm');

                        /**
                         * @var $newModel ShopCmsContentElement
                         * @var $model ShopCmsContentElement
                         */
                        $newModel = $action->model->copy();

                        if ($newModel) {
                            $action->afterSaveUrl = Url::to(['update', 'pk' => $newModel->id, 'content_id' => $newModel->content_id]);
                        } else {
                            throw new Exception(print_r($newModel->errors, true));
                        }

                    },

                    'fields' => function () {
                        return [
                            'dm.is_copy_images' => [
                                'class' => BoolField::class,
                                'label' => ['skeeks/cms', 'Copy images?'],
                            ],
                            'dm.is_copy_files'  => [
                                'class' => BoolField::class,
                                'label' => ['skeeks/cms', 'Copy files?'],
                            ],
                        ];
                    },
                ],


                "to-offer" => [
                    'class'        => BackendModelMultiDialogEditAction::class,
                    "name"         => "Привязать к общему",
                    "viewDialog"   => "@skeeks/cms/shop/views/admin-cms-content-element/to-offer",
                    "eachCallback" => [
                        $this,
                        'eachToOffer',
                    ],
                    'on init'      => function ($e) {
                        $action = $e->sender;
                        if (!$action) {
                            return $this;
                        }
                        if (!$this->content) {
                            return $this;
                        }
                        /**
                         * @var BackendGridModelAction $action
                         */
                        $action->url = ["/".$action->uniqueId, 'content_id' => $this->content->id];
                    },
                ],

                "shop-properties" => [
                    'class'        => BackendModelMultiDialogEditAction::class,
                    "name"         => "Свойства товара",
                    "viewDialog"   => "@skeeks/cms/shop/views/admin-cms-content-element/_shop-properties",
                    "eachCallback" => [$this, 'eachShopProperties'],
                    'on init'      => function ($e) {
                        $action = $e->sender;
                        /**
                         * @var BackendGridModelAction $action
                         */
                        if ($this->content) {
                            $action->url = ["/".$action->uniqueId, 'content_id' => $this->content->id];
                        }
                    },
                ],


                "viewed-products" => [
                    'class'           => BackendGridModelRelatedAction::class,
                    'accessCallback'  => true,
                    'name'            => ['skeeks/shop/app', 'Looked'],
                    'icon'            => 'far fa-eye',
                    'controllerRoute' => "/shop/admin-viewed-product",
                    'relation'        => ['shop_product_id' => 'id'],
                    'priority'        => 600,
                    'on gridInit'     => function ($e) {
                        /**
                         * @var $action BackendGridModelRelatedAction
                         */
                        $action = $e->sender;
                        $action->relatedIndexAction->backendShowings = false;
                        $visibleColumns = $action->relatedIndexAction->grid['visibleColumns'];

                        ArrayHelper::removeValue($visibleColumns, 'shop_product_id');
                        $action->relatedIndexAction->grid['visibleColumns'] = $visibleColumns;

                    },
                ],

                "quantity-notice-emails" => [
                    'class'           => BackendGridModelRelatedAction::class,
                    'accessCallback'  => true,
                    'name'            => ['skeeks/shop/app', 'Waiting for receipt'],
                    'icon'            => 'far fa-envelope',
                    'controllerRoute' => "/shop/admin-quantity-notice-email",
                    'relation'        => ['shop_product_id' => 'id'],
                    'priority'        => 600,
                    'on gridInit'     => function ($e) {
                        /**
                         * @var $action BackendGridModelRelatedAction
                         */
                        $action = $e->sender;
                        $action->relatedIndexAction->backendShowings = false;
                        $visibleColumns = $action->relatedIndexAction->grid['visibleColumns'];

                        ArrayHelper::removeValue($visibleColumns, 'good');
                        $action->relatedIndexAction->grid['visibleColumns'] = $visibleColumns;

                    },
                ],

                "carts" => [
                    'class'           => BackendGridModelRelatedAction::class,
                    'accessCallback'  => true,
                    'name'            => ['skeeks/shop/app', 'In baskets'],
                    'icon'            => 'fas fa-cart-arrow-down',
                    'controllerRoute' => "/shop/admin-cart",
                    'relation'        => ['shop_product_id' => 'id'],
                    'priority'        => 600,
                    'on gridInit'     => function ($e) {
                        /**
                         * @var $action BackendGridModelRelatedAction
                         */
                        $action = $e->sender;
                        $action->relatedIndexAction->backendShowings = false;
                        $visibleColumns = $action->relatedIndexAction->grid['visibleColumns'];

                        $action->relatedIndexAction->grid['on init'] = function (Event $e) {
                            /**
                             * @var $querAdminCmsContentElementControllery ActiveQuery
                             */
                            $query = $e->sender->dataProvider->query;
                            $query->joinWith("shopOrderItems as shopOrderItems");
                            $query->joinWith("shopOrderItems.shopProduct as shopProduct");
                            $query->andWhere(['shopProduct.id' => $this->model->id]);
                            $query->andWhere(['is_created' => 0]);
                        };

                        ArrayHelper::removeValue($visibleColumns, 'goods');
                        $action->relatedIndexAction->grid['visibleColumns'] = $visibleColumns;

                    },
                ],

                "orders" => [
                    'class'           => BackendGridModelRelatedAction::class,
                    'accessCallback'  => true,
                    'name'            => ['skeeks/shop/app', 'In orders'],
                    'icon'            => 'fas fa-cart-arrow-down',
                    'controllerRoute' => "/shop/admin-order",
                    'relation'        => ['shop_product_id' => 'id'],
                    'priority'        => 600,
                    'on gridInit'     => function ($e) {
                        /**
                         * @var $action BackendGridModelRelatedAction
                         */
                        $action = $e->sender;
                        $action->relatedIndexAction->backendShowings = false;
                        $visibleColumns = $action->relatedIndexAction->grid['visibleColumns'];

                        $action->relatedIndexAction->grid['on init'] = function (Event $e) {
                            /**
                             * @var $querAdminCmsContentElementControllery ActiveQuery
                             */
                            $query = $e->sender->dataProvider->query;
                            $query->joinWith("shopOrderItems as shopOrderItems");
                            $query->joinWith("shopOrderItems.shopProduct as shopProduct");
                            $query->andWhere(['shopProduct.id' => $this->model->id]);
                            $query->andWhere(['is_created' => 1]);
                        };

                        ArrayHelper::removeValue($visibleColumns, 'goods');
                        $action->relatedIndexAction->grid['visibleColumns'] = $visibleColumns;

                    },
                ],

            ]
        );

        if (isset($actions['related-properties'])) {
            unset($actions['related-properties']);
        }

        if (isset($actions['shop'])) {
            unset($actions['shop']);
        }

        return $actions;
    }

    public function eachShopProperties($model, $action)
    {
        /**
         * @var $model ShopCmsContentElement
         */
        try {
            $formData = [];
            parse_str(\Yii::$app->request->post('formData'), $formData);

            if (!$formData) {
                return false;
            }

            if (!$content_id = ArrayHelper::getValue($formData, 'content_id')) {
                return false;
            }

            if (!$fields = ArrayHelper::getValue($formData, 'fields')) {
                return false;
            }


            /**
             * @var CmsContent $content
             */
            $content = CmsContent::findOne($content_id);
            if (!$content) {
                return false;
            }


            $tmpProduct = new ShopProduct();
            $tmpProduct->load($formData);

            $shopProduct = $model->shopProduct;

            foreach ((array)ArrayHelper::getValue($formData, 'fields') as $code) {
                if ($shopProduct->hasAttribute($code)) {
                    $shopProduct->setAttribute($code, $tmpProduct->{$code});
                }
            }

            return $shopProduct->save(false);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function initGridData($action, $content)
    {
        parent::initGridData($action, $content);


        $sortAttributes = [];
        $shopColumns = [];
        $visibleColumns = [];
        $filterFields = [];
        $filterFieldsLabels = [];
        $filterFieldsRules = [];


        $shopColumns["shop.product_type"] = [
            'attribute' => "shop.product_type",
            'label'     => 'Тип товара [магазин]',
            'format'    => 'raw',
            'value'     => function ($shopCmsContentElement) {
                if ($shopCmsContentElement->shopProduct) {
                    return \yii\helpers\ArrayHelper::getValue(\skeeks\cms\shop\models\ShopProduct::possibleProductTypes(),
                        $shopCmsContentElement->shopProduct->product_type);
                }
            },
        ];

        $shopColumns["shop.shop_supplier_id"] = [
            'attribute' => "shop.shop_supplier_id",
            'label'     => 'Поставщик [магазин]',
            'format'    => 'raw',
            'value'     => function (ShopCmsContentElement $shopCmsContentElement) {
                if ($shopCmsContentElement->shopProduct && $shopCmsContentElement->shopProduct->shopSupplier) {
                    return $shopCmsContentElement->shopProduct->shopSupplier->asText;
                }
            },
        ];

        $shopColumns["shop.supplier_external_id"] = [
            'attribute' => "shop.supplier_external_id",
            'label'     => 'Идентификатор поставщика [магазин]',
            'format'    => 'raw',
            'value'     => function (ShopCmsContentElement $shopCmsContentElement) {
                if ($shopCmsContentElement->shopProduct) {
                    return $shopCmsContentElement->shopProduct->supplier_external_id;
                }
            },
        ];

        $shopColumns["shop.quantity"] = [
            'attribute' => "shop.quantity",
            'label'     => 'Количество [магазин]',
            'format'    => 'raw',
            'value'     => function (ShopCmsContentElement $shopCmsContentElement) {
                if ($shopCmsContentElement->shopProduct) {
                    $result = $shopCmsContentElement->shopProduct->quantity." ".$shopCmsContentElement->shopProduct->measure->symbol;
                    if ($shopCmsContentElement->shopProduct->shopStoreProducts) {
                        $storesQuantity = [];
                        foreach ($shopCmsContentElement->shopProduct->shopStoreProducts as $shopStoreProduct) {
                            if ($shopStoreProduct->quantity > 0) {
                                $storesQuantity[] = Html::tag('span', $shopStoreProduct->quantity, [
                                    'title' => $shopStoreProduct->shopStore->shopSupplier->name." - ".$shopStoreProduct->shopStore->name,
                                ]);
                            }

                        }

                        if ($storesQuantity) {
                            $result .= "<hr>".implode("<br>", $storesQuantity);
                        }
                    }

                    return $result;
                }
                return "—";
            },
        ];
        $sortAttributes["shop.quantity"] = [
            'asc'  => ['sp.quantity' => SORT_ASC],
            'desc' => ['sp.quantity' => SORT_DESC],
            'name' => 'Количество [магазин]',
        ];

        $sortAttributes["shop.supplier_external_id"] = [
            'asc'  => ['sp.supplier_external_id' => SORT_ASC],
            'desc' => ['sp.supplier_external_id' => SORT_DESC],
            'name' => 'Идентификатор поставщика [магазин]',
        ];

        $visibleColumns[] = "shop.product_type";
        $visibleColumns[] = "shop.quantity";

        if (\Yii::$app->shop->shopTypePrices) {

            foreach (\Yii::$app->shop->shopTypePrices as $shopTypePrice) {

                $shopColumns["shop.price{$shopTypePrice->id}"] = [
                    'label'     => $shopTypePrice->name." [магазин]",
                    'attribute' => 'shop.price'.$shopTypePrice->id,
                    'format'    => 'raw',
                    'value'     => function (\skeeks\cms\models\CmsContentElement $model) use ($shopTypePrice) {
                        $shopProduct = \skeeks\cms\shop\models\ShopProduct::getInstanceByContentElement($model);
                        if ($shopProduct) {
                            if ($shopProductPrice = $shopProduct->getShopProductPrices()
                                ->andWhere(['type_price_id' => $shopTypePrice->id])->one()
                            ) {
                                return (string)$shopProductPrice->money;
                            }
                        }

                        return null;
                    },
                ];


                /*$visibleColumns[] = 'shop.price'.$shopTypePrice->id;

                $sortAttributes['shop.price'.$shopTypePrice->id] = [
                    'asc'     => ["p{$shopTypePrice->id}.price" => SORT_ASC],
                    'desc'    => ["p{$shopTypePrice->id}.price" => SORT_DESC],
                    'label'   => $shopTypePrice->name,
                    'default' => SORT_ASC,
                ];*/
            }


            $defaultId = '';

            $shopColumns["custom"] = [
                'attribute' => 'id',
                'format'    => 'raw',
                'value'     => function (ShopCmsContentElement $model) {

                    $data = [];

                    $data[] = "<span style='max-width: 300px;'>".Html::a($model->asText, "#", [
                            'class' => 'sx-trigger-action',
                            'title' => $model->asText,
                            //'style' => 'white-space: nowrap; '
                        ])."</span>";

                    if ($model->tree_id) {
                        $data[] = '<i class="far fa-folder"></i> '.Html::a($model->cmsTree->name, $model->cmsTree->url, [
                                'data-pjax' => '0',
                                'target'    => '_blank',
                                'title'     => $model->cmsTree->fullName,
                                'style'     => 'color: #333; max-width: 200px;',
                            ]);
                    }

                    if ($model->cmsTrees) {
                        foreach ($model->cmsTrees as $cmsTree) {
                            $data[] = Html::a($cmsTree->name, $cmsTree->url, [
                                'data-pjax' => '0',
                                'target'    => '_blank',
                                'title'     => $cmsTree->fullName,
                                'style'     => 'color: #333; max-width: 200px; ',
                            ]);
                        }
                    }

                    if ($model->shopProduct && $model->shopProduct->shop_supplier_id) {
                        $data[] = '<i class="fas fa-truck" title="Поставщик"></i> '.$model->shopProduct->shopSupplier->asText;
                    }


                    if ($model->shopProduct->isSubProduct) {
                        if ($model->shopProduct->main_pid) {
                            $data[] = '<span style="color: green;"><i class="fas fa-link" title="Привязан к главному товару"></i> '.$model->shopProduct->shopMainProduct->cmsContentElement->asText."</span>";
                        } else {
                            $data[] = '<span style="color: red;"><i class="fas fa-link" title="Привязан к главному товару"></i> Не привязан к главному товару!</span>';
                        }
                    }


                    $info = implode("<br />", $data);

                    return "<div class='row no-gutters'>
                                                <div style='margin-left: 5px;'>
                                                <div class='sx-trigger-action' style='width: 50px; margin-right: 10px; float: left;'>
                                                    <a href='#' style='text-decoration: none; border-bottom: 0;'>
                                                        <img src='".($model->image ? $model->image->src : Image::getCapSrc())."' style='max-width: 50px; max-height: 50px; border-radius: 5px;' />
                                                    </a>
                                                </div>".$info."</div></div>";;
                },
            ];

            $shopColumns["shop.priceDefult"] = [
                'label'     => "Все цены [магазин]",
                'attribute' => 'shop.priceDefult',
                'format'    => 'raw',
                'value'     => function (ShopCmsContentElement $model) {
                    $result = [];
                    if (!$model->shopProduct) {
                        return "";
                    }
                    foreach ($model->shopProduct->shopTypePrices as $shopTypePrice) {
                        if ($shopTypePrice->isDefault) {
                            $defaultId = $shopTypePrice->id;
                        }
                        $shopProduct = \skeeks\cms\shop\models\ShopProduct::getInstanceByContentElement($model);
                        if ($shopProduct) {
                            if ($shopProductPrice = $shopProduct->getShopProductPrices()
                                ->andWhere(['type_price_id' => $shopTypePrice->id])->one()
                            ) {
                                $result[] = "<span title='{$shopTypePrice->name}'>".(string)$shopProductPrice->money."</span>";
                            } else {
                                $result[] = "<span title='{$shopTypePrice->name}'>"." — "."</span>";;
                            }
                        }
                    }


                    return implode("<br />", $result);
                },
            ];

            $visibleColumns[] = 'shop.priceDefult';

            if ($defaultId) {
                $sortAttributes['shop.priceDefult'] = [
                    'asc'     => ["p{$defaultId}.price" => SORT_ASC],
                    'desc'    => ["p{$defaultId}.price" => SORT_DESC],
                    //'label'   => $shopTypePrice->name,
                    'default' => SORT_ASC,
                ];

            }


        }


        $filterFields['shop_product_type'] = [
            'class'    => SelectField::class,
            'items'    => \skeeks\cms\shop\models\ShopProduct::possibleProductTypes(),
            'label'    => 'Тип товара [магазин]',
            'multiple' => true,
            'on apply' => function (QueryFiltersEvent $e) {
                /**
                 * @var $query ActiveQuery
                 */
                $query = $e->dataProvider->query;

                if ($e->field->value) {
                    $query->andWhere(['sp.product_type' => $e->field->value]);
                }
            },
        ];

        $filterFields['shop_supplier_id'] = [
            'class'           => FilterField::class,
            'field'           => [
                'class' => SelectField::class,
                'items' => function () {
                    return ArrayHelper::map(
                        ShopSupplier::find()->all(),
                        'id',
                        'asText'
                    );
                },
            ],
            'label'           => 'Поставщик',
            'filterAttribute' => 'sp.shop_supplier_id',
            'modes'           => [
                FilterModeEmpty::class,
                FilterModeNotEmpty::class,

                FilterModeEq::class,
                FilterModeNe::class,
            ],
            //'multiple' => true,
            /*'multiple' => true,
            'on apply' => function (QueryFiltersEvent $e) {
                /**
                 * @var $query ActiveQuery
                $query = $e->dataProvider->query;

                if ($e->field->value) {
                    $query->andWhere(['sp.shop_supplier_id' => $e->field->value]);
                }
            },*/
        ];

        $filterFields['shop_quantity'] = [
            'class'           => NumberFilterField::class,
            'label'           => 'Количество [магазин]',
            'filterAttribute' => 'sp.quantity',
            /*'on apply' => function (QueryFiltersEvent $e) {
                /**
                 * @var $query ActiveQuery
                $query = $e->dataProvider->query;

                if ($e->field->value) {
                    $query->andWhere(['sp.product_type' => $e->field->value]);
                }
            },*/
        ];

        //$filterFieldsLabels['shop_product_type'] = 'Тип товара [магазин]';
        //$filterFieldsLabels['shop_quantity'] = 'Количество [магазин]';
        //$filterFieldsLabels['shop_supplier_id'] = 'Поставщик [магазин]';

        $filterFieldsRules[] = ['shop_product_type', 'safe'];
        $filterFieldsRules[] = ['shop_quantity', 'safe'];
        $filterFieldsRules[] = ['shop_supplier_id', 'safe'];

        //Мерж колонок и сортировок
        if ($shopColumns) {
            $action->grid['columns'] = ArrayHelper::merge($action->grid['columns'], $shopColumns);
            $action->grid['sortAttributes'] = ArrayHelper::merge((array)ArrayHelper::getValue($action->grid, ['sortAttributes']), $sortAttributes);
            $action->grid['visibleColumns'] = ArrayHelper::merge((array)ArrayHelper::getValue($action->grid, ['visibleColumns']), $visibleColumns);

            $action->filters['filtersModel']['fields'] = ArrayHelper::merge((array)ArrayHelper::getValue($action->filters, ['filtersModel', 'fields']), $filterFields);
            $action->filters['filtersModel']['attributeDefines'] = ArrayHelper::merge((array)ArrayHelper::getValue($action->filters, ['filtersModel', 'attributeDefines']),
                array_keys($filterFields));
            $action->filters['filtersModel']['attributeLabels'] = ArrayHelper::merge((array)ArrayHelper::getValue($action->filters, ['filtersModel', 'attributeLabels']), $filterFieldsLabels);
            $action->filters['filtersModel']['rules'] = ArrayHelper::merge((array)ArrayHelper::getValue($action->filters, ['filtersModel', 'rules']), $filterFieldsRules);

            $action->filters['visibleFilters'] = ArrayHelper::merge((array)ArrayHelper::getValue($action->filters, ['visibleFilters']), array_keys($filterFieldsLabels));
        }

        //Приджоивание магазинных данных
        $action->grid['on init'] = function (Event $event) {
            /**
             * @var $query ActiveQuery
             */
            $query = $event->sender->dataProvider->query;
            if ($this->content) {
                $query->andWhere(['content_id' => $this->content->id]);
            }

            $query->joinWith('shopProduct as sp');
            $query->joinWith('shopProduct.shopSupplier as shopSupplier');

            $query->andWhere([
                'or',
                ['shopSupplier.id' => null],
                ['shopSupplier.is_main' => 1],
            ]);

            if (\Yii::$app->shop->shopTypePrices) {
                foreach (\Yii::$app->shop->shopTypePrices as $shopTypePrice) {
                    $query->leftJoin(["p{$shopTypePrice->id}" => ShopProductPrice::tableName()], [
                        "p{$shopTypePrice->id}.product_id"    => new Expression("sp.id"),
                        "p{$shopTypePrice->id}.type_price_id" => $shopTypePrice->id,
                    ]);
                }
            }
        };
    }
    /**
     * @param CmsContentElement $model
     * @param                   $action
     * @return bool
     */
    public function eachToOffer($model, $action)
    {
        try {
            $formData = [];
            parse_str(\Yii::$app->request->post('formData'), $formData);
            $model->load($formData);

            $sp = $model->shopProduct;
            $sp->product_type = ShopProduct::TYPE_OFFER;

            $model->save(false);
            $sp->save();
            return true;

        } catch (\Exception $e) {
            return false;
        }
    }

    public function create($adminAction)
    {
        $is_saved = false;
        $redirect = "";

        $productPrices = [];
        $shopStoreProducts = [];

        //Если нужно создавать товар из поддтовара
        $shopSubproductContentElement = null;
        if ($shop_sub_product_id = \Yii::$app->request->get("shop_sub_product_id")) {
            $shopSubproductContentElement = ShopCmsContentElement::find()->where(['id' => $shop_sub_product_id])->one();
        }


        /**
         * @var ShopSupplier $shopSupplier ;
         */
        if ($shopSuppliers = \skeeks\cms\shop\models\ShopSupplier::find()->all()) {
            foreach ($shopSuppliers as $key => $shopSupplier) {
                if ($shopSupplier->shopStores) {
                    foreach ($shopSupplier->shopStores as $shopStore) {
                        $shopStoreProduct = new ShopStoreProduct([
                            'shop_store_id' => $shopStore->id,
                        ]);

                        $shopStoreProducts[] = $shopStoreProduct;
                    }
                }
            }
        }


        $modelClassName = $this->modelClassName;
        $model = new $modelClassName();

        $model->loadDefaultValues();
        $model->content_id = $this->content->id;

        $relatedModel = $model->relatedPropertiesModel;
        $shopProduct = new ShopProduct();

        $shopProduct->loadDefaultValues();
        $rr = new RequestResponse();

        if (\Yii::$app->request->isAjax && !\Yii::$app->request->isPjax) {
            $model->load(\Yii::$app->request->post());
            $relatedModel->load(\Yii::$app->request->post());
            $shopProduct->load(\Yii::$app->request->post());

            return \yii\widgets\ActiveForm::validateMultiple([
                $model,
                $relatedModel,
                $shopProduct,
            ]);
        }

        if ($post = \Yii::$app->request->post()) {
            $model->load(\Yii::$app->request->post());
            $relatedModel->load(\Yii::$app->request->post());
            $shopProduct->load(\Yii::$app->request->post());
        }

        $productPrices = [];
        $productPriceQuery = ShopTypePrice::find()->andWhere(['shop_supplier_id' => null])->orderBy(['priority' => SORT_ASC]);
        if ($shopProduct->shop_supplier_id) {
            $productPriceQuery->orWhere(['in', 'shop_supplier_id', $shopProduct->shop_supplier_id]);
        }


        if ($typePrices = $productPriceQuery->all()) {
            foreach ($typePrices as $typePrice) {

                $productPrice = new ShopProductPrice([
                    'type_price_id' => $typePrice->id,
                ]);

                $productPrices[] = $productPrice;
            }
        }


        if ($rr->isRequestPjaxPost()) {
            if (!\Yii::$app->request->post($this->notSubmitParam)) {
                $model->load(\Yii::$app->request->post());
                $relatedModel->load(\Yii::$app->request->post());
                $shopProduct->load(\Yii::$app->request->post());

                if ($model->save() && $relatedModel->save()) {
                    $shopProduct->id = $model->id;
                    $shopProduct->save();


                    $savedPrice = $shopProduct->getBaseProductPrice()->one();
                    foreach ($productPrices as $productPrice) {
                        if ($savedPrice->type_price_id == $productPrice->type_price_id) {
                            $productPrice = $savedPrice;
                            $data = ArrayHelper::getValue($post, 'prices.'.$productPrice->type_price_id);
                            $productPrice->load($data, "");
                            $productPrice->save();
                        } else {
                            $data = ArrayHelper::getValue($post, 'prices.'.$productPrice->type_price_id);
                            $productPrice->load($data, "");
                            $productPrice->product_id = $shopProduct->id;
                            $productPrice->save();
                        }
                    }

                    foreach ($shopStoreProducts as $shopStoreProduct) {
                        $data = ArrayHelper::getValue($post, 'stores.'.$shopStoreProduct->shop_store_id);
                        $shopStoreProduct->load($data, "");
                        $shopStoreProduct->shop_product_id = $shopProduct->id;
                        $shopStoreProduct->save();
                    }
                    /*$shopProduct->getBaseProductPriceValue();
                    $baseProductPrice = $shopProduct->baseProductPrice;*/

                    if ($shopSubproductContentElement) {
                        $shopSubproductContentElement->shopProduct->main_pid = $shopProduct->id;
                        $shopSubproductContentElement->shopProduct->save();
                    }

                    \Yii::$app->getSession()->setFlash('success', \Yii::t('skeeks/shop/app', 'Saved'));

                    $is_saved = true;

                    if (\Yii::$app->request->post('submit-btn') == 'apply') {
                        $redirect = UrlHelper::constructCurrent()->setCurrentRef()->enableAdmin()->setRoute($this->modelDefaultAction)->normalizeCurrentRoute()
                            ->addData([$this->requestPkParamName => $model->{$this->modelPkAttribute}])
                            ->toString();
                    } else {
                        $redirect = $this->url;
                    }
                }
            }

        }

        return $this->render('_form', [
            'model'             => $model,
            'relatedModel'      => $relatedModel,
            'shopProduct'       => $shopProduct,
            'productPrices'     => $productPrices,
            'shopStoreProducts' => $shopStoreProducts,
            //'baseProductPrice' => $baseProductPrice,

            'is_saved'  => $is_saved,
            'submitBtn' => \Yii::$app->request->post('submit-btn'),
            'redirect'  => $redirect,
            'shopSubproductContentElement'  => $shopSubproductContentElement,
        ]);
    }

    public function update($adminAction)
    {
        $is_saved = false;
        $redirect = "";

        /**
         * @var $model ShopCmsContentElement
         */
        $model = $this->model;
        $relatedModel = $model->relatedPropertiesModel;
        $shopProduct = $model->shopProduct;


        try {
            if (!$shopProduct) {
                $shopProduct = new ShopProduct([
                    'id' => $model->id,
                ]);

                $shopProduct->save();
            }


            $shopStoreProducts = [];
            /**
             * @var ShopSupplier $shopSupplier ;
             */
            if ($shopSuppliers = \skeeks\cms\shop\models\ShopSupplier::find()->all()) {
                foreach ($shopSuppliers as $key => $shopSupplier) {
                    if ($shopSupplier->shopStores) {
                        foreach ($shopSupplier->shopStores as $shopStore) {

                            $shopStoreProduct = ShopStoreProduct::find()->where([
                                'shop_product_id' => $shopProduct->id,
                                'shop_store_id'   => $shopStore->id,
                            ])->one();

                            if (!$shopStoreProduct) {
                                $shopStoreProduct = new ShopStoreProduct([
                                    'shop_product_id' => $shopProduct->id,
                                    'shop_store_id'   => $shopStore->id,
                                ]);

                            }

                            if ($post = \Yii::$app->request->post()) {
                                $data = ArrayHelper::getValue($post, 'stores.'.$shopStore->id);
                                $shopStoreProduct->load($data, "");
                            }


                            $shopStoreProducts[] = $shopStoreProduct;
                        }
                    }
                }
            }


            $rr = new RequestResponse();

            if (\Yii::$app->request->isAjax && !\Yii::$app->request->isPjax) {
                $model->load(\Yii::$app->request->post());
                $relatedModel->load(\Yii::$app->request->post());
                $shopProduct->load(\Yii::$app->request->post());


                return \yii\widgets\ActiveForm::validateMultiple([
                    $model,
                    $relatedModel,
                    $shopProduct,
                ]);
            }

            if ($post = \Yii::$app->request->post()) {

                $model->load(\Yii::$app->request->post());
                $relatedModel->load(\Yii::$app->request->post());
                $shopProduct->load(\Yii::$app->request->post());
            }


            $productPrices = [];
            $productPriceQuery = ShopTypePrice::find()->andWhere(['shop_supplier_id' => null])->orderBy(['priority' => SORT_ASC]);
            if ($shopProduct->shop_supplier_id) {
                $productPriceQuery->orWhere(['in', 'shop_supplier_id', $shopProduct->shop_supplier_id]);
            }
            if ($typePrices = $productPriceQuery->all()) {
                foreach ($typePrices as $typePrice) {

                    $productPrice = ShopProductPrice::find()->where([
                        'product_id'    => $shopProduct->id,
                        'type_price_id' => $typePrice->id,
                    ])->one();

                    if (!$productPrice) {
                        $productPrice = new ShopProductPrice([
                            'product_id'    => $shopProduct->id,
                            'type_price_id' => $typePrice->id,
                        ]);
                    }

                    if ($post = \Yii::$app->request->post()) {
                        $data = ArrayHelper::getValue($post, 'prices.'.$typePrice->id);
                        $productPrice->load($data, "");
                    }

                    $productPrices[] = $productPrice;
                }
            }

            if ($rr->isRequestPjaxPost()) {
                if (!\Yii::$app->request->post($this->notSubmitParam)) {
                    $model->load(\Yii::$app->request->post());
                    $relatedModel->load(\Yii::$app->request->post());


                    if ($model->save() && $relatedModel->save() && $shopProduct->save()) {

                        /**
                         * @var $productPrice ShopProductPrice
                         */
                        foreach ($productPrices as $productPrice) {
                            if ($productPrice->save()) {

                            } else {
                                \Yii::$app->getSession()->setFlash('error',
                                    \Yii::t('skeeks/shop/app', 'Check the correctness of the prices'));
                            }

                        }
                        /**
                         * @var $productPrice ShopProductPrice
                         */
                        foreach ($shopStoreProducts as $shopStoreProduct) {
                            if ($shopStoreProduct->save()) {

                            } else {
                                \Yii::$app->getSession()->setFlash('error',
                                    \Yii::t('skeeks/shop/app', 'Check the correctness of the stores: '.print_r($shopStoreProduct->errors, true)));
                            }

                        }

                        $is_saved = true;

                        \Yii::$app->getSession()->setFlash('success', \Yii::t('skeeks/shop/app', 'Saved'));

                        if (\Yii::$app->request->post('submit-btn') == 'apply') {
                        } else {

                            $redirect = $this->url;

                        }

                        $model->refresh();

                    }
                }

            }


            if (!$shopProduct->baseProductPrice) {
                $baseProductPrice = new ShopProductPrice([
                    'type_price_id' => \Yii::$app->shop->baseTypePrice->id,
                    'currency_code' => \Yii::$app->money->currencyCode,
                    'product_id'    => $model->id,
                ]);

                $baseProductPrice->save();
            }
        } catch (\Exception $e) {
            $model->addError('name', $e->getMessage());
        }

        //return $this->render('@skeeks/cms/shop/views/admin-cms-content-element/_form', [
        return $this->render('_form', [
            'model'             => $model,
            'relatedModel'      => $relatedModel,
            'shopProduct'       => $shopProduct,
            'productPrices'     => $productPrices,
            'shopStoreProducts' => $shopStoreProducts,
            'baseProductPrice'  => $shopProduct->getBaseProductPrice()->one(),

            'is_saved'  => $is_saved,
            'submitBtn' => \Yii::$app->request->post('submit-btn'),
            'redirect'  => $redirect,
        ]);
    }


    public function beforeAction($action)
    {
        if ($content_id = \Yii::$app->request->get('content_id')) {
            $this->content = CmsContent::findOne($content_id);
        }

        if ($this->content) {
            if ($this->content->name_meny) {
                $this->name = $this->content->name_meny;
            }
        }

        return parent::beforeAction($action);
    }
    /**
     * @return string
     */
    public function getUrl()
    {
        $actions = $this->getActions();
        $index = ArrayHelper::getValue($actions, 'index');
        if ($index && $index instanceof IHasUrl) {
            return $index->url;
        }

        return parent::getUrl();
    }
    public function getActions()
    {
        /**
         * @var AdminAction $action
         */
        $actions = parent::getActions();
        if ($actions) {
            foreach ($actions as $action) {
                if ($this->content) {
                    $action->url = ArrayHelper::merge($action->urlData, ['content_id' => $this->content->id]);
                }
            }
        }

        return $actions;
    }
    public function getModelActions()
    {
        /**
         * @var AdminAction $action
         */
        $actions = parent::getModelActions();
        if ($actions) {
            foreach ($actions as $action) {
                $action->url = ArrayHelper::merge($action->urlData, ['content_id' => $this->model->cmsContent->id]);
            }
        }

        return $actions;
    }

}
