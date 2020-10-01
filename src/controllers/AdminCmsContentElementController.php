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
use skeeks\cms\backend\helpers\BackendUrlHelper;
use skeeks\cms\backend\ViewBackendAction;
use skeeks\cms\backend\widgets\SelectModelDialogContentElementWidget;
use skeeks\cms\helpers\RequestResponse;
use skeeks\cms\helpers\UrlHelper;
use skeeks\cms\IHasUrl;
use skeeks\cms\models\CmsContent;
use skeeks\cms\models\CmsContentElement;
use skeeks\cms\models\CmsSite;
use skeeks\cms\modules\admin\actions\AdminAction;
use skeeks\cms\modules\admin\actions\modelEditor\AdminModelEditorAction;
use skeeks\cms\queryfilters\filters\NumberFilterField;
use skeeks\cms\queryfilters\filters\StringFilterField;
use skeeks\cms\queryfilters\QueryFiltersEvent;
use skeeks\cms\shop\assets\admin\AdminShopProductAsset;
use skeeks\cms\shop\grid\ShopProductColumn;
use skeeks\cms\shop\models\ShopCmsContentElement;
use skeeks\cms\shop\models\ShopProduct;
use skeeks\cms\shop\models\ShopProductPrice;
use skeeks\cms\shop\models\ShopProductRelation;
use skeeks\cms\shop\models\ShopStore;
use skeeks\cms\shop\models\ShopStoreProduct;
use skeeks\cms\widgets\GridView;
use skeeks\yii2\form\fields\BoolField;
use skeeks\yii2\form\fields\HtmlBlock;
use skeeks\yii2\form\fields\SelectField;
use skeeks\yii2\form\fields\TextField;
use skeeks\yii2\form\fields\WidgetField;
use yii\base\DynamicModel;
use yii\base\Event;
use yii\db\ActiveQuery;
use yii\db\Exception;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;

/**
 * @property CmsContent $content
 *
 * Class AdminCmsContentTypeController
 * @package skeeks\cms\controllers
 */
class AdminCmsContentElementController extends \skeeks\cms\controllers\AdminCmsContentElementController
{
    public $modelClassName = ShopCmsContentElement::class;
    public $modelShowAttribute = "asText";


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


                /*"relations-dettach" => [
                    'class' => BackendAction::class,
                    'accessCallback' => true,
                    'isVisible' => false,
                    'callback' => [$this, "actionRelationsDettach"],
                ],*/

                "offers" => [
                    'class'                  => BackendGridModelRelatedAction::class,
                    'name'                   => "Предложения",
                    'icon'                   => 'fa fa-list',
                    'controllerRoute'        => "/shop/admin-cms-content-element",
                    'priority'               => 150,
                    'isStandartBeforeRender' => false,
                    'on gridInit'            => function ($e) {
                        /**
                         * @var $action BackendGridModelRelatedAction
                         */
                        $action = $e->sender;
                        $controller = $action->relatedIndexAction->controller;
                        $action->relatedIndexAction->controller->initGridData($action->relatedIndexAction, $action->relatedIndexAction->controller->content);

                        $helper = new \skeeks\cms\shop\helpers\ShopOfferChooseHelper([
                            'shopProduct' => $controller->model->shopProduct,
                        ]);

                        $action->relatedIndexAction->grid['on init'] = function (Event $e) use ($helper) {

                            /**
                             * @var $querAdminCmsContentElementControllery ActiveQuery
                             */
                            $query = $e->sender->dataProvider->query;
                            $query->with("image");
                            $query->with("cmsContent");
                            $query->with("cmsTree");
                            $query->with("cmsSite");
                            $query->with("shopProduct");
                            $query->with("shopProduct.cmsContentElement.cmsContent");
                            $query->with("shopProduct.cmsContentElement");
                            $query->with("shopProduct.cmsContentElement.cmsSite");
                            $query->with("shopProduct.measure");
                            $query->joinWith("shopProduct as sp");
                            $query->andWhere(['sp.offers_pid' => $this->model->id]);
                            $query->andWhere(['in', 'sp.id', ArrayHelper::map($helper->availableOffers, 'id', 'id')]);
                        };


                        $action->relatedIndexAction->on('beforeRender', function (Event $event) use ($controller, $helper) {

                            $event->content = \Yii::$app->view->render("@skeeks/cms/shop/views/admin-cms-content-element/rp-header", [
                                'controller' => $controller,
                                'helper'     => $helper,
                            ]);

                            /*if ($createAction = ArrayHelper::getValue($controller->actions, 'create')) {

                                /**
                                 * @var $controller BackendModelController
                                 * @var $createAction BackendModelCreateAction
                                $r = new \ReflectionClass($controller->modelClassName);

                                $createAction->url = ArrayHelper::merge($createAction->urlData, [
                                    'parent_content_element_id' => $this->model->id,
                                ]);

                                $createAction->name = "Добавить предложение";

                                $event->content = ControllerActionsWidget::widget([
                                        'actions'         => [$createAction],
                                        'isOpenNewWindow' => true,
                                        'minViewCount'    => 1,
                                        'itemTag'         => 'button',
                                        'itemOptions'     => ['class' => 'btn btn-primary'],
                                    ])."<br>" . \Yii::$app->view->render("@skeeks/cms-shop/views/admin-cms-content-element/rp-header", [
                                        ''
                                    ]);
                            }*/
                        });
                        $action->relatedIndexAction->on('afterRender', function (Event $event) {
                            $event->content = '';
                        });

                        $action->relatedIndexAction->backendShowings = false;
                        $action->relatedIndexAction->filters = false;
                        $visibleColumns = $action->relatedIndexAction->grid['visibleColumns'];

                        $action->relatedIndexAction->grid['visibleColumns'] = $visibleColumns;

                    },

                    'accessCallback' => function (BackendModelAction $action) {

                        /**
                         * @var $model ShopCmsContentElement
                         */
                        $model = $action->model;

                        if (!$model) {
                            return false;
                        }

                        if (!$model->shopProduct) {
                            return false;
                        }

                        if ($model->shopProduct->isOffersProduct) {
                            return \Yii::$app->user->can($action->controller->uniqueId."__".$model->content_id."/update", [
                                'model' => $model,
                            ]);
                            return true;
                        }

                    },
                ],

                "relations" => [
                    'class'    => BackendModelAction::class,
                    'name'     => "Связанные товары",
                    'icon'     => 'fa fa-list',
                    'priority' => 190,

                    'accessCallback' => function (BackendModelAction $action) {

                        /**
                         * @var $model ShopCmsContentElement
                         */
                        $model = $action->model;

                        if (!$model) {
                            return false;
                        }

                        if (!$model->shopProduct) {
                            return false;
                        }

                        if ($model->shopProduct->offers_pid) {
                            return false;
                        }

                        return true;

                    },
                ],

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
                                    'options'     => [
                                        'data-form-reload' => "true",
                                    ],
                                    'dialogRoute' => [
                                        '/shop/admin-cms-content-element',
                                        BackendUrlHelper::BACKEND_PARAM_NAME => [
                                            'sx-to-main' => "true",
                                        ],
                                        'w3-submit-key'                      => "1",
                                        'findex'                             => [
                                            'shop_supplier_id' => [
                                                'mode' => 'empty',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ];

                        if (!$model->shopProduct->main_pid) {
                            $siteClass = \Yii::$app->skeeks->siteClass;
                            /**
                             * @var $defaultSite CmsSite
                             */
                            $defaultSite = $siteClass::find()->where(['is_default' => 1])->one();
                            if ($defaultSite) {

                                //$hostInfo = \Yii::$app->urlManager->hostInfo;
                                //\Yii::$app->urlManager->hostInfo = $defaultSite->url;
                                $url = Url::to(['/shop/admin-cms-content-element/create', 'content_id' => $model->content_id, 'shop_sub_product_id' => $model->id], true);
                                //\Yii::$app->urlManager->hostInfo = $hostInfo;
                                
                                $result[] = [
                                    'class'   => HtmlBlock::class,
                                    'content' => <<<HTML
<div class="text-center g-ma-20">
<a href="{$url}" data-pjax='0' class="btn btn-xxl btn-primary">Создать главный товар</a>
</div>
HTML
                                    ,

                                ];
                            }

                        }

                        return $result;
                    },
                    'accessCallback' => function (BackendModelAction $action) {

                        /**
                         * @var $model ShopCmsContentElement
                         */
                        $model = $action->model;

                        if (!$model) {
                            return false;
                        }

                        if (!$model->shopProduct) {
                            return false;
                        }

                        /**
                         * @var $site \skeeks\cms\shop\models\CmsSite
                         */
                        $site = $model->cmsSite;
                        //Показываем только для сайтов которые являются поставщиками
                        if (!$site->shopSite->is_supplier) {
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
                        try {
                            $newModel = $action->model->copy();
                        } catch (\Exception $e) {
                            print_r($e->getMessage());
                            die;
                        }


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

                    "eachAccessCallback" => function ($model) {
                        return \Yii::$app->user->can($this->permissionName."/update", ['model' => $model]);
                    },
                    "accessCallback"     => function () {
                        return \Yii::$app->user->can($this->permissionName."/update");
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

                    "eachAccessCallback" => function ($model) {
                        return \Yii::$app->user->can($this->permissionName."/update", ['model' => $model]);
                    },
                    "accessCallback"     => function () {
                        return \Yii::$app->user->can($this->permissionName."/update");
                    },
                ],


                "viewed-products" => [
                    'class'           => BackendGridModelRelatedAction::class,
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
                    'class'          => BackendGridModelRelatedAction::class,
                    'accessCallback' => function (BackendModelAction $action) {
                        $model = $action->model;
                        return \Yii::$app->user->can($this->permissionName."/viewed-products", ['model' => $model]);
                    },

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
                    'accessCallback'  => function (BackendModelAction $action) {
                        $model = $action->model;
                        return \Yii::$app->user->can($this->permissionName."/viewed-products", ['model' => $model]);
                    },
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
                    'accessCallback'  => function (BackendModelAction $action) {
                        $model = $action->model;
                        return \Yii::$app->user->can($this->permissionName."/viewed-products", ['model' => $model]);
                    },
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

                "update-data" => [
                    'class' => ViewBackendAction::class,
                    'icon'  => 'fas fa-sync',
                    'name'  => 'Обновление данных',
                ],

                "update" => [
                    'accessCallback' => function (BackendModelAction $action) {

                        /**
                         * @var $model ShopCmsContentElement
                         */
                        $model = $action->model;

                        if (!$model) {
                            return false;
                        }

                        if (!$model->shopProduct) {
                            return false;
                        }

                        if ($model->cmsSite->shopSite->is_supplier) {
                            return false;
                        }
                        
                        if ($model->cmsSite->shopSite->is_receiver) {
                            return false;
                        }
                        /*if ($model->shopProduct->main_pid) {
                            return false;
                        }*/

                        return true;
                        //return \Yii::$app->user->can($this->permissionName . "/" . $this->action->id, ['model' => $action->model]);
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

            $prices = [];
            foreach ($fields as $fieldName) {
                if (strpos($fieldName, "price-") !== false) {
                    $prices[] = (int)str_replace("price-", "", $fieldName);
                }
            }


            /**
             * @var CmsContent $content
             */
            $content = CmsContent::findOne((int)$content_id);
            if (!$content) {
                return false;
            }


            if ($prices) {
                foreach ($prices as $key => $typePriceId) {
                    $priceData = ArrayHelper::getValue($formData, 'price.'.$typePriceId);

                    $model->shopProduct->savePrice(
                        $typePriceId,
                        (float)ArrayHelper::getValue($priceData, "value"),
                        (string)ArrayHelper::getValue($priceData, "currency")
                    );
                }
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


        $shopColumns["shop.relations_products"] = [
            'attribute'            => "shop.relations_products",
            'label'                => 'Количество связанных товаров',
            'format'               => 'raw',
            'beforeCreateCallback' => function (GridView $grid) {
                /**
                 * @var $query ActiveQuery
                 */
                $query = $grid->dataProvider->query;

                $subQuery = ShopProductRelation::find()->select([new Expression("count(1)")])->where([
                    'or',
                    ['shop_product1_id' => new Expression("sp.id")],
                    ['shop_product2_id' => new Expression("sp.id")],
                ]);

                $query->addSelect([
                    'countRelations' => $subQuery,
                ]);


                $grid->sortAttributes["shop.relations_products"] = [
                    'asc'  => ['countRelations' => SORT_ASC],
                    'desc' => ['countRelations' => SORT_DESC],
                ];
            },
            'value'                => function (ShopCmsContentElement $shopCmsContentElement) {
                return $shopCmsContentElement->raw_row['countRelations'];
            },
        ];

        $shopColumns["shop.product_type"] = [
            'attribute' => "shop.product_type",
            'label'     => 'Тип товара',
            'format'    => 'raw',
            'value'     => function ($shopCmsContentElement) {
                if ($shopCmsContentElement->shopProduct) {
                    return \yii\helpers\ArrayHelper::getValue(\skeeks\cms\shop\models\ShopProduct::possibleProductTypes(),
                        $shopCmsContentElement->shopProduct->product_type);
                }
            },
        ];


        $shopColumns["shop.quantity"] = [
            'attribute' => "shop.quantity",
            'label'     => 'Количество',
            'format'    => 'raw',
            'value'     => function (ShopCmsContentElement $shopCmsContentElement) {
                if ($shopCmsContentElement->shopProduct) {
                    $result = $shopCmsContentElement->shopProduct->quantity." ".$shopCmsContentElement->shopProduct->measure->symbol;

                    if (count($shopCmsContentElement->shopProduct->shopStoreProducts) > 1) {
                        $storesQuantity = [];
                        foreach ($shopCmsContentElement->shopProduct->shopStoreProducts as $shopStoreProduct) {
                            if ($shopStoreProduct->quantity > 0) {
                                $storesQuantity[] = Html::tag('small', $shopStoreProduct->quantity, [
                                    'title' => $shopStoreProduct->shopStore->name,
                                    'style' => 'white-space: nowrap; color: gray;',
                                ]);
                            }

                        }

                        if ($storesQuantity) {
                            $result .= "<hr>".implode("<br>", $storesQuantity);
                        }
                    }

                    if ($shopSupplierProducts = $shopCmsContentElement->shopProduct->shopSupplierProducts) {

                        $storesQuantity = [];
                        foreach ($shopCmsContentElement->shopProduct->shopSupplierProducts as $shopStoreProduct) {
                            $storesQuantity[] = Html::tag('small', $shopStoreProduct->quantity." - ".$shopStoreProduct->cmsContentElement->cmsSite->name, [
                                'title' => $shopStoreProduct->cmsContentElement->cmsSite->name,
                                'style' => 'white-space: nowrap; color: gray;',
                            ]);

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
        ];
        $sortAttributes["shop.product_type"] = [
            'asc'  => ['sp.product_type' => SORT_ASC],
            'desc' => ['sp.product_type' => SORT_DESC],
        ];


        $visibleColumns[] = "shop.product_type";
        $visibleColumns[] = "shop.quantity";

        if (\Yii::$app->shop->shopTypePrices) {

            foreach (\Yii::$app->shop->shopTypePrices as $shopTypePrice) {

                $shopColumns["shop.price{$shopTypePrice->id}"] = [
                    'label'     => $shopTypePrice->name,
                    'attribute' => 'shop.price'.$shopTypePrice->id,
                    'format'    => 'raw',
                    'value'     => function (ShopCmsContentElement $model) use ($shopTypePrice) {
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


                $visibleColumns[] = 'shop.price'.$shopTypePrice->id;

                $sortAttributes['shop.price'.$shopTypePrice->id] = [
                    'asc'     => ["p{$shopTypePrice->id}.price" => SORT_ASC],
                    'desc'    => ["p{$shopTypePrice->id}.price" => SORT_DESC],
                    'label'   => $shopTypePrice->name,
                    'default' => SORT_ASC,
                ];
            }


            $defaultId = \Yii::$app->shop->baseTypePrice ? \Yii::$app->shop->baseTypePrice->id : '';

            $shopColumns["custom"] = [
                'attribute' => 'id',
                'class'     => ShopProductColumn::class,
            ];
        }


        $filterFields['shop_product_type'] = [
            'class'    => SelectField::class,
            'items'    => \skeeks\cms\shop\models\ShopProduct::possibleProductTypes(),
            'label'    => 'Тип товара',
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

        $filterFields['all_ids'] = [
            'class'    => TextField::class,
            'label'    => 'ID + вложенные',
            'on apply' => function (QueryFiltersEvent $e) {
                /**
                 * @var $query ActiveQuery
                 **/
                $query = $e->dataProvider->query;

                if ($e->field->value) {

                    $q = ShopProduct::find()
                        ->select(['parent_id' => 'cmsContentElement.parent_content_element_id'])
                        ->joinWith('cmsContentElement as cmsContentElement')
                        ->where([ShopProduct::tableName().'.id' => $e->field->value])//->andWhere(['is not', 'cmsContentElement.parent_content_element_id', null])
                    ;

                    //print_R($q->createCommand()->rawSql);die;


                    $query->leftJoin(['p' => $q], ['p.parent_id' => new Expression(ShopCmsContentElement::tableName().".id")]);

                    $query->andWhere([
                        'or',
                        [ShopCmsContentElement::tableName().'.id' => $e->field->value],
                        ['is not', 'p.parent_id', null],
                    ]);
                }
            },
        ];


        $filterFields['shop_quantity'] = [
            'class'           => NumberFilterField::class,
            'label'           => 'Количество',
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

        $filterFields['supplier_external_jsondata'] = [
            'class'           => StringFilterField::class,
            'label'           => 'Данные поставщика',
            'filterAttribute' => 'sp.supplier_external_jsondata',
            /*'on apply' => function (QueryFiltersEvent $e) {
                /**
                 * @var $query ActiveQuery
                $query = $e->dataProvider->query;

                if ($e->field->value) {
                    $query->andWhere(['sp.product_type' => $e->field->value]);
                }
            },*/
        ];


        //Только для сайта поставщика
        if (\Yii::$app->skeeks->site->shopSite->is_supplier || \Yii::$app->skeeks->site->shopSite->is_receiver) {
            $filterFields['is_ready'] = [
                'class'    => SelectField::class,
                'items'    => [
                    'on'  => 'Готов',
                    'off' => 'Не привязан',
                ],
                'label'    => 'Привязка',
                'multiple' => false,
                'on apply' => function (QueryFiltersEvent $e) {
                    /**
                     * @var $query ActiveQuery
                     */
                    $query = $e->dataProvider->query;
                    $query->joinWith('shopProduct as sp');

                    if ($e->field->value) {
                        if ($e->field->value == 'on') {
                            $query->andWhere(['is not', 'sp.main_pid', null]);
                        } else {
                            $query->andWhere(['sp.main_pid' => null]);
                        }

                    }
                },
            ];
            $filterFields['is_error'] = [
                'class'    => SelectField::class,
                'items'    => [
                    'yes' => 'Ошибочно привязан',
                ],
                'label'    => 'Ошибка привязки',
                'multiple' => false,
                'on apply' => function (QueryFiltersEvent $e) {
                    /**
                     * @var $query ActiveQuery
                     */
                    $query = $e->dataProvider->query;

                    if ($e->field->value) {
                        if ($e->field->value == 'yes') {
                            $query->joinWith('shopProduct.shopMainProduct as shopMainProduct');
                            $query->andWhere(['shopMainProduct.product_type' => ShopProduct::TYPE_OFFERS]);
                            $query->groupBy(ShopCmsContentElement::tableName().".id");
                        }

                    }
                },
            ];

            $filterFieldsLabels['is_ready'] = 'Привязка';
            $filterFieldsRules[] = ['is_ready', 'safe'];

            $filterFieldsLabels['is_error'] = 'Ошибочно привязан';
            $filterFieldsRules[] = ['is_error', 'safe'];
        }

        if (\Yii::$app->skeeks->site->is_default) {
            $filterFields['is_suppliers'] = [
                'class'    => SelectField::class,
                'items'    => [
                    'yes' => 'Да',
                    'no'  => 'Нет',
                ],
                'label'    => 'Привязаны поставщики?',
                'multiple' => false,
                'on apply' => function (QueryFiltersEvent $e) {
                    /**
                     * @var $query ActiveQuery
                     */
                    $query = $e->dataProvider->query;

                    if ($e->field->value) {
                        if ($e->field->value == 'no') {
                            $query->joinWith('shopProduct.shopAttachedProducts as shopAttachedProducts');
                            $query->andWhere(['shopAttachedProducts.id' => null]);
                            $query->groupBy(ShopCmsContentElement::tableName().".id");
                        } elseif ($e->field->value == 'yes') {
                            $query->joinWith('shopProduct.shopAttachedProducts as shopAttachedProducts');
                            $query->andWhere(['is not', 'shopAttachedProducts.id', null]);
                            $query->groupBy(ShopCmsContentElement::tableName().".id");
                        }

                    }
                },
            ];

            $filterFieldsLabels['is_suppliers'] = 'Привязаны поставщики?';
            $filterFieldsRules[] = ['is_suppliers', 'safe'];
        }


        $filterFieldsLabels['shop_product_type'] = 'Тип товара';

        $filterFieldsLabels['shop_quantity'] = 'Количество';
        $filterFieldsLabels['all_ids'] = 'ID + вложенные';
        $filterFieldsLabels['supplier_external_jsondata'] = 'Данные поставщика';


        $filterFieldsRules[] = ['shop_product_type', 'safe'];
        $filterFieldsRules[] = ['shop_quantity', 'safe'];
        $filterFieldsRules[] = ['supplier_external_jsondata', 'safe'];
        $filterFieldsRules[] = ['all_ids', 'safe'];

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

            //$action->filters['visibleFilters'] = ArrayHelper::merge((array)ArrayHelper::getValue($action->filters, ['visibleFilters']), array_keys($filterFieldsLabels));
        }

        //Приджоивание магазинных данных
        $action->grid['on init'] = function (Event $event) {
            /**
             * @var $query ActiveQuery
             */
            $query = $event->sender->dataProvider->query;
            $query->select([ShopCmsContentElement::tableName().".*"]);

            $query->with("image");
            $query->with("cmsContent");
            $query->with("cmsTree");
            $query->with("cmsSite");
            $query->with("shopProduct");
            $query->with("shopProduct.measure");
            $query->with("shopProduct");
            $query->with("shopProduct.cmsContentElement.cmsContent");
            $query->with("shopProduct.cmsContentElement");
            $query->with("shopProduct.cmsContentElement.cmsSite");

            if ($this->content) {
                $query->andWhere([CmsContentElement::tableName().'.content_id' => $this->content->id]);
            }

            $query->joinWith('shopProduct as sp');

            if (\Yii::$app->skeeks->site->shopTypePrices) {
                foreach (\Yii::$app->skeeks->site->shopTypePrices as $shopTypePrice) {
                    $query->leftJoin(["p{$shopTypePrice->id}" => ShopProductPrice::tableName()], [
                        "p{$shopTypePrice->id}.product_id"    => new Expression("sp.id"),
                        "p{$shopTypePrice->id}.type_price_id" => $shopTypePrice->id,
                    ]);
                }
            }

            $urlHelper = new BackendUrlHelper();
            $urlHelper->setBackendParamsByCurrentRequest();

            if ($urlHelper->getBackenParam("sx-to-main")) {

                $siteClass = \Yii::$app->skeeks->siteClass;
                $site = $siteClass::find()->where(['is_default' => 1])->one();
                $site_id = $site->id;
                $query->andWhere([
                    'in',
                    'sp.product_type',
                    [
                        ShopProduct::TYPE_SIMPLE,
                        ShopProduct::TYPE_OFFER,
                    ],
                ]);

                $query->andWhere([CmsContentElement::tableName().".cms_site_id" => $site_id]);
            } else {
                $site_id = \Yii::$app->skeeks->site->id;
                $query->andWhere(['cms_site_id' => $site_id]);
                $query->andWhere([
                    'in',
                    'sp.product_type',
                    [
                        ShopProduct::TYPE_SIMPLE,
                        ShopProduct::TYPE_OFFERS,
                    ],
                ]);
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
            //$model->load($formData);

            $sp = $model->shopProduct;
            $sp->load($formData);
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


        if ($shopStores = ShopStore::find()->where(['cms_site_id' => \Yii::$app->skeeks->site->id])->all()) {
            foreach ($shopStores as $shopStore) {
                $shopStoreProduct = new ShopStoreProduct([
                    'shop_store_id' => $shopStore->id,
                ]);

                $shopStoreProducts[] = $shopStoreProduct;
            }
        }


        $modelClassName = $this->modelClassName;
        $model = new $modelClassName();
        $model->cms_site_id = \Yii::$app->skeeks->site->id;


        $model->loadDefaultValues();
        $model->content_id = $this->content->id;

        $relatedModel = $model->relatedPropertiesModel;
        $shopProduct = new ShopProduct();

        $shopProduct->loadDefaultValues();
        $rr = new RequestResponse();


        //Если нужно создавать товар из поддтовара
        $shopSubproductContentElement = null;
        if ($shop_sub_product_id = \Yii::$app->request->get("shop_sub_product_id")) {
            /**
             * @var $shopSubproductContentElement ShopCmsContentElement
             */
            $shopSubproductContentElement = ShopCmsContentElement::find()->where(['id' => $shop_sub_product_id])->one();

            if ($shopSubproductContentElement) {
                $subShopProduct = $shopSubproductContentElement->shopProduct;
                $shopSubproductContentElement->loadDataToMainModel($model);
                $siteClass = \Yii::$app->skeeks->siteClass;
                if (!$defaultSite = $siteClass::find()->andWhere(['is_default' => 1])->one()) {
                    throw new Exception("Нет сайта по умолчанию");
                }
                $model->cms_site_id = $defaultSite->id;

                $shopProduct->measure_code = $subShopProduct->measure_code;
                $shopProduct->measure_ratio = $subShopProduct->measure_ratio;
                $shopProduct->height = $subShopProduct->height;
                $shopProduct->width = $subShopProduct->width;
                $shopProduct->length = $subShopProduct->length;
                $shopProduct->width = $subShopProduct->width;
            }
        }


        //Если создаем товар предложение
        if ($parent_content_element_id = \Yii::$app->request->get("parent_content_element_id")) {
            $parent = \skeeks\cms\shop\models\ShopCmsContentElement::findOne($parent_content_element_id);

            $data = $parent->toArray();
            \yii\helpers\ArrayHelper::remove($data, 'image_id');
            \yii\helpers\ArrayHelper::remove($data, 'image_full_id');
            \yii\helpers\ArrayHelper::remove($data, 'imageIds');
            \yii\helpers\ArrayHelper::remove($data, 'fileIds');
            \yii\helpers\ArrayHelper::remove($data, 'code');
            \yii\helpers\ArrayHelper::remove($data, 'id');
            $model->setAttributes($data);
            $model->relatedPropertiesModel->setAttributes($parent->relatedPropertiesModel->toArray());
            $shopProduct->offers_pid = $parent_content_element_id;
            $shopProduct->product_type = \skeeks\cms\shop\models\ShopProduct::TYPE_OFFER;
            $model->tree_id = $parent->tree_id;

            \Yii::$app->view->registerCss(<<<CSS
    .field-shopcmscontentelement-tree_id,
    .field-shopproduct-offers_pid {
        display: none;
    }
CSS
            );
        }

        $productPrices = [];
        $typePrices = \Yii::$app->skeeks->site->shopTypePrices;
        if ($typePrices) {
            foreach ($typePrices as $typePrice) {

                $productPrice = new ShopProductPrice([
                    'type_price_id' => $typePrice->id,
                ]);

                $productPrices[] = $productPrice;
            }
        }

        if ($post = \Yii::$app->request->post()) {
            $model->load(\Yii::$app->request->post());
            $relatedModel->load(\Yii::$app->request->post());
            $shopProduct->load(\Yii::$app->request->post());

            foreach ($productPrices as $productPrice) {
                $data = ArrayHelper::getValue($post, 'prices.'.$productPrice->type_price_id);
                $productPrice->load($data, "");
            }

        }


        if ($rr->isRequestPjaxPost()) {
            if (!\Yii::$app->request->post(RequestResponse::DYNAMIC_RELOAD_NOT_SUBMIT)) {

                $t = \Yii::$app->db->beginTransaction();

                try {
                    $site = \Yii::$app->skeeks->site;
                    $siteClass = \Yii::$app->skeeks->siteClass;
                    \Yii::$app->skeeks->site = $siteClass::find()->where(['is_default' => 1])->one();
                    if ($model->save() && $relatedModel->save()) {
                        
                        \Yii::$app->skeeks->site = $site;
                            
                        $shopProduct->id = $model->id;
                        if (!$shopProduct->save()) {
                            throw new \yii\base\Exception("Товар не сохранен: ".print_r($shopProduct->errors, true));
                        }

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

                        $t->commit();

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
                } catch (\Exception $e) {
                    $t->rollBack();
                    throw $e;
                    $is_saved = false;
                    \Yii::$app->getSession()->setFlash('error', \Yii::t('skeeks/shop/app', 'Данные не сохранены: '.$e->getMessage()));
                }

            }

        }


        return $this->render($this->editForm, [
            'model'             => $model,
            'relatedModel'      => $relatedModel,
            'shopProduct'       => $shopProduct,
            'productPrices'     => $productPrices,
            'shopStoreProducts' => $shopStoreProducts,
            //'baseProductPrice' => $baseProductPrice,

            'is_saved'                     => $is_saved,
            'submitBtn'                    => \Yii::$app->request->post('submit-btn'),
            'redirect'                     => $redirect,
            'shopSubproductContentElement' => $shopSubproductContentElement,
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
            $typePrices = $shopProduct->shopTypePrices;
            if ($typePrices) {
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
                if (!\Yii::$app->request->post(RequestResponse::DYNAMIC_RELOAD_NOT_SUBMIT)) {
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
            throw $e;
        }

                        //die('111');
                        
        //return $this->render('@skeeks/cms/shop/views/admin-cms-content-element/_form', [
        return $this->render($this->editForm, [
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

        AdminShopProductAsset::register(\Yii::$app->view);
        $data = [];
        $json = Json::encode($data);
        \Yii::$app->view->registerJs(<<<JS
                sx.ProductList = new sx.classes.ProductList({$json});
JS
        );

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

    /**
     * @throws Exception
     */
    public function actionUpdateAllData()
    {
        $rr = new RequestResponse();
        $rr->success = true;
        $rr->message = "Данные успешно загружены";

        try {
            \Yii::$app->shop->updateAllSubproducts();
            \Yii::$app->shop->updateAllQuantities();
            \Yii::$app->shop->updateAllTypes();
            \Yii::$app->shop->updateOffersPrice();
        } catch (\Exception $e) {
            $rr->success = false;
            $rr->message = "Ошибка загрузки данных: ".$e->getMessage();
        }

        return $rr;

    }

    /**
     * @return RequestResponse
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionRelationsDettach()
    {
        $rr = new RequestResponse();
        if ($rr->isRequestAjaxPost()) {
            $product1_id = \Yii::$app->request->post('product1_id');
            $product2_id = \Yii::$app->request->post('product2_id');

            if ($product1_id && $product2_id) {
                $relation = ShopProductRelation::find()
                    ->where([
                        'shop_product1_id' => $product1_id,
                    ])
                    ->andWhere([
                        'shop_product2_id' => $product2_id,
                    ])
                    ->one();

                if ($relation) {
                    $relation->delete();
                }

                $relation = ShopProductRelation::find()
                    ->where([
                        'shop_product2_id' => $product1_id,
                    ])
                    ->andWhere([
                        'shop_product1_id' => $product2_id,
                    ])
                    ->one();

                if ($relation) {
                    $relation->delete();
                }
            }

        }

        return $rr;
    }
}
