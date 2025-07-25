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
use skeeks\cms\backend\BackendAction;
use skeeks\cms\backend\helpers\BackendUrlHelper;
use skeeks\cms\backend\ViewBackendAction;
use skeeks\cms\backend\widgets\SelectModelDialogContentElementWidget;
use skeeks\cms\helpers\RequestResponse;
use skeeks\cms\helpers\UrlHelper;
use skeeks\cms\IHasUrl;
use skeeks\cms\models\CmsContent;
use skeeks\cms\models\CmsContentElement;
use skeeks\cms\models\CmsCountry;
use skeeks\cms\models\CmsSite;
use skeeks\cms\modules\admin\actions\AdminAction;
use skeeks\cms\modules\admin\actions\modelEditor\AdminModelEditorAction;
use skeeks\cms\money\Money;
use skeeks\cms\queryfilters\filters\modes\FilterModeEmpty;
use skeeks\cms\queryfilters\filters\modes\FilterModeEq;
use skeeks\cms\queryfilters\filters\modes\FilterModeNotEmpty;
use skeeks\cms\queryfilters\filters\NumberFilterField;
use skeeks\cms\queryfilters\filters\StringFilterField;
use skeeks\cms\queryfilters\QueryFiltersEvent;
use skeeks\cms\rbac\CmsManager;
use skeeks\cms\shop\assets\admin\AdminShopProductAsset;
use skeeks\cms\shop\components\ShopComponent;
use skeeks\cms\shop\grid\ShopProductColumn;
use skeeks\cms\shop\models\ShopBrand;
use skeeks\cms\shop\models\ShopCmsContentElement;
use skeeks\cms\shop\models\ShopProduct;
use skeeks\cms\shop\models\ShopProductBarcode;
use skeeks\cms\shop\models\ShopProductPrice;
use skeeks\cms\shop\models\ShopProductRelation;
use skeeks\cms\shop\models\ShopStore;
use skeeks\cms\shop\models\ShopStoreDocMove;
use skeeks\cms\shop\models\ShopStoreProduct;
use skeeks\cms\shop\models\ShopStoreProductMove;
use skeeks\cms\widgets\AjaxSelectModel;
use skeeks\cms\widgets\GridView;
use skeeks\yii2\form\fields\BoolField;
use skeeks\yii2\form\fields\HtmlBlock;
use skeeks\yii2\form\fields\NumberField;
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
use yii\widgets\ActiveForm;

/**
 * @property CmsContent $content
 *
 * Class AdminCmsContentTypeController
 * @package skeeks\cms\controllers
 */
class AdminCmsContentElementController extends \skeeks\cms\controllers\AdminCmsContentElementController
{
    public $modelClassName = ShopCmsContentElement::class;
    public $modelShowAttribute = "name";

    public $editForm = '@skeeks/cms/shop/views/admin-cms-content-element/_form';

    public function init()
    {
        $this->modelDefaultAction = 'view';
        $this->name = "Товары и услуги";

        $this->generateAccessActions = false;

        if ($this->permissionName === null) {
            $this->permissionName = "shop/admin-product";
        }
        
        if ($this->content) {
            $this->name = \Yii::t('skeeks/cms', $this->content->name);
        }

        parent::init();
    }

    public function setContent($content)
    {
        //$this->permissionName = $this->uniqueId . "__" . $content->id;
        $this->permissionName = "shop/admin-product";
        $this->_content = $content;
        return $this;
    }


    /**
     * @return bool
     */
    public function isProductGroup()
    {
        //var_dump(\Yii::$app->session->get("isProductGroup", 1));die;
        return (bool)\Yii::$app->session->get("isProductGroup", 1);
    }
    /**
     * @inheritdoc
     */
    public function actions()
    {
        $actions = ArrayHelper::merge(parent::actions(), [

                "index" => [
                    /*'backendShowings' => false,*/
                    'on beforeRender' => function (Event $e) {
                        $urlHelper = new BackendUrlHelper();
                        $urlHelper->setBackendParamsByCurrentRequest();
                        if ($urlHelper->getBackenParam("sx-to-main") || $urlHelper->getBackenParam("all-items")) {

                        } else {
                            $isGroup = $this->isProductGroup();

                            if ($postValue = \Yii::$app->request->post()) {
                                $isGroup = \Yii::$app->request->post("is_group");
                                \Yii::$app->session->set("isProductGroup", (int)$isGroup);
                            }
                            $e->content = $e->content = $this->renderPartial("_index_btns", [
                                'isGroup' => $isGroup,
                            ]);
                        }

                    },
                ],

                "view" => [
                    'class'    => BackendModelAction::class,
                    'priority' => 80,
                    'name'     => 'Карточка',
                    'icon'     => 'fas fa-info-circle',
                    'accessCallback' => function (BackendModelAction $action) {
                        return \Yii::$app->user->can($this->permissionName."");
                    },
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

                        /*if ($model->main_cce_id) {
                            return false;
                        }*/

                        return \Yii::$app->user->can($this->permissionName."/update", ['model' => $action->model]);
                    },
                ],


                "offers" => [
                    'class'                  => BackendGridModelRelatedAction::class,
                    'name'                   => "Модификации",
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
                            'shopProduct'           => $controller->model->shopProduct,
                            'is_filter_by_quantity' => false,
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

                            $query->joinWith('shopProduct as sp');

                            if (\Yii::$app->skeeks->site->shopTypePrices) {
                                foreach (\Yii::$app->skeeks->site->shopTypePrices as $shopTypePrice) {
                                    $query->leftJoin(["p{$shopTypePrice->id}" => ShopProductPrice::tableName()], [
                                        "p{$shopTypePrice->id}.product_id"    => new Expression("sp.id"),
                                        "p{$shopTypePrice->id}.type_price_id" => $shopTypePrice->id,
                                    ]);
                                }
                            }

                            $this->initGridColumns($e->sender, $this->content);
                        };


                        $action->relatedIndexAction->on('beforeRender', function (Event $event) use ($controller, $helper) {

                            $event->content = \Yii::$app->view->render("@skeeks/cms/shop/views/admin-cms-content-element/rp-header", [
                                'controller' => $controller,
                                'helper'     => $helper,
                            ]);


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
                            return \Yii::$app->user->can($this->permissionName."/update", ['model' => $model]);
                        }

                        return false;

                    },


                ],


                "joins" => [
                    'class'    => BackendModelAction::class,
                    'name'     => "Объединение",
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

                        if ($model->main_cce_id) {
                            return false;
                        }


                        /**
                         * @var $site \skeeks\cms\shop\models\CmsSite
                         */
                        $site = $model->cmsSite;

                        return \Yii::$app->user->can($this->permissionName."/join", ['model' => $model]);

                    },
                ],

                "relations" => [
                    'class'    => BackendModelAction::class,
                    'name'     => "Так же покупают",
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

                        if ($model->main_cce_id) {
                            return false;
                        }


                        /**
                         * @var $site \skeeks\cms\shop\models\CmsSite
                         */
                        $site = $model->cmsSite;

                        return \Yii::$app->user->can($this->permissionName."/join", ['model' => $model]);

                    },
                ],


                "connect-to-main" => [

                    'class'    => BackendModelUpdateAction::class,
                    "name"     => "Инфо карточка",
                    "icon"     => "fas fa-link",
                    'priority' => 90,


                    'on initFormModels' => function (Event $e) {
                        $model = $e->sender->model;
                        $e->sender->formModels['shopProduct'] = $model->shopProduct;
                    },


                    'fields'         => function (BackendModelUpdateAction $action) {
                        $model = $action->model;

                        $result = [
                            'main_cce_id' => [
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

                        if (!$model->main_cce_id) {
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
    <a href="{$url}" data-pjax='0' class="btn btn-xxl btn-primary">Создать информационную карточку</a>
    
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
                        if (!$site->shopSite->is_receiver) {
                            return false;
                        }

                        if (!$model->shopProduct->isSubProduct) {
                            return false;
                        }

                        return \Yii::$app->user->can($this->permissionName."/create");
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


                    'accessCallback' => function (BackendModelAction $action) {
                        return \Yii::$app->user->can($this->permissionName."/create");
                    },
                ],

                /*"to-offer" => [
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
                        $action->url = ["/".$action->uniqueId, 'content_id' => $this->content->id];
                    },

                    "eachAccessCallback" => function ($model) {
                        return \Yii::$app->user->can($this->permissionName."/update", ['model' => $model]);
                    },
                    "accessCallback"     => function () {
                        return \Yii::$app->user->can($this->permissionName."/update");
                    },
                ],*/

                "shop-properties" => [
                    'class'        => BackendModelMultiDialogEditAction::class,
                    "name"         => "Цены",
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
                        return (\Yii::$app->user->can($this->permissionName."/update"));
                    },
                ],

                "price-tags" => [
                    'class'      => BackendModelMultiDialogEditAction::class,
                    "name"       => "Ценники",
                    "viewDialog" => "@skeeks/cms/shop/views/admin-cms-content-element/_price-tags",
                    'on init'    => function ($e) {
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
                    'name'            => ['skeeks/shop/app', 'Кто посмотрел?'],
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
                    'accessCallback'  => function (BackendModelAction $action) {
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

                        return \Yii::$app->user->can($this->permissionName."/orders", ['model' => $model]);
                    },
                ],

                "quantity-notice-emails" => [
                    'class'          => BackendGridModelRelatedAction::class,
                    'accessCallback' => function (BackendModelAction $action) {
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

                        return \Yii::$app->user->can($this->permissionName."/orders", ['model' => $model]);
                    },

                    'name'            => ['skeeks/shop/app', 'Кто ждет?'],
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

                        return \Yii::$app->user->can($this->permissionName."/orders", ['model' => $model]);
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
                    'generateAccess'  => true,
                    'class'           => BackendGridModelRelatedAction::class,
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
                    'accessCallback'  => function (BackendModelAction $action) {
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

                        return \Yii::$app->user->can($this->permissionName."/orders", ['model' => $model]);
                    },
                ],




                "store-moves" => [
                    'class'           => BackendGridModelRelatedAction::class,
                    'name'            => 'Движение товара',
                    'icon'            => 'fas fa-truck',
                    'controllerRoute' => "/shop/admin-shop-store-product-move",
                    'relation'        => ['shop_store_product_id' => 'id'],
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
                            $query->joinWith("shopStoreProduct as shopStoreProduct");
                            $query->joinWith("shopStoreProduct.shopProduct as shopProduct");
                            $query->andWhere([ShopStoreProductMove::tableName().".is_active" => 1]);

                            $query->andWhere(['shopProduct.id' => $this->model->id]);
                        };

                        /*ArrayHelper::removeValue($visibleColumns, 'goods');
                        $action->relatedIndexAction->grid['visibleColumns'] = $visibleColumns;*/

                    },
                    /*'accessCallback'  => function (BackendModelAction $action) {
                        $model = $action->model;
                        if (!$model) {
                            return false;
                        }

                        if (!$model->shopProduct) {
                            return false;
                        }

                        /**
                         * @var $site \skeeks\cms\shop\models\CmsSite
                        $site = $model->cmsSite;


                        return \Yii::$app->user->can($this->permissionName."/orders", ['model' => $model]);
                    },*/
                    'accessCallback' => function (BackendModelAction $action) {
                        $model = $action->model;
                        if (!$model) {
                            return false;
                        }
                        if (!$model->shopProduct) {
                            return false;
                        }
                        return \Yii::$app->user->can($this->permissionName."/index");
                    },
                ],


                "update-data" => [
                    'class'          => ViewBackendAction::class,
                    'icon'           => 'fas fa-sync',
                    'name'           => 'Обновление данных',
                ],

                "create" => [

                    'accessCallback' => function (BackendAction $action) {
                        return \Yii::$app->user->can($this->permissionName."/create");
                    },
                ],



                "update-attribute" => [

                    'class'     => BackendModelAction::class,
                    'isVisible' => false,
                    'callback'  => [$this, 'actionUpdateAttribute'],

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

                        return \Yii::$app->user->can($this->permissionName."/update", ['model' => $action->model]);
                    },
                ],


                'duplicates' => [
                    'class'          => ViewBackendAction::class,
                    'name'           => 'Дубли',
                    'icon'           => 'fas fa-copy',
                    'priority'       => 500,
                ],

            ]
        );

        if (isset($actions['related-properties'])) {
            unset($actions['related-properties']);
        }

        if (isset($actions['shop'])) {
            unset($actions['shop']);
        }
        if (isset($actions['connect-to-main'])) {
            unset($actions['connect-to-main']);
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

    /**
     * @param $grid GridView
     * @param $content
     * @return \skeeks\cms\controllers\AdminCmsContentElementController|void
     */
    public function initGridColumns($grid, $content)
    {
        parent::initGridColumns($grid, $content);

        $shopColumns = [];

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

        $shopColumns["shop.barcodes"] = [
            'attribute' => "shop.barcodes",
            'label'     => 'Штрихкод',
            'format'    => 'raw',

            'value' => function (ShopCmsContentElement $shopCmsContentElement) {
                return implode("<br />", ArrayHelper::map((array)$shopCmsContentElement->shopProduct->shopProductBarcodes, "value", 'value'));
            },
        ];

        /*$shopColumns["shop.product_type"] = [
            'attribute' => "shop.product_type",
            'label'     => 'Тип товара',
            'format'    => 'raw',
            'value'     => function ($shopCmsContentElement) {
                if ($shopCmsContentElement->shopProduct) {
                    return \yii\helpers\ArrayHelper::getValue(\skeeks\cms\shop\models\ShopProduct::possibleProductTypes(),
                        $shopCmsContentElement->shopProduct->product_type);
                }
            },
        ];*/


        $shopColumns["custom"] = [
            'attribute'     => 'id',
            'label'         => 'Товар/Услуга',
            'headerOptions' => [
                'style' => 'min-width: 300px;',
            ],
            'class'         => ShopProductColumn::class,
        ];


        if (\Yii::$app->shop->shopTypePrices) {

            foreach (\Yii::$app->shop->shopTypePrices as $shopTypePrice) {

                $shopColumns["shop.price{$shopTypePrice->id}"] = [
                    'label'     => $shopTypePrice->name,
                    'attribute' => 'shop.price'.$shopTypePrice->id,
                    'format'    => 'raw',
                    'value'     => function (ShopCmsContentElement $model) use ($shopTypePrice) {
                        $shopProduct = \skeeks\cms\shop\models\ShopProduct::getInstanceByContentElement($model);
                        if ($shopProduct && !$shopProduct->isOffersProduct) {
                            if ($shopProductPrice = $shopProduct->getShopProductPrices()
                                ->andWhere(['type_price_id' => $shopTypePrice->id])->one()
                            ) {
                                return (string)$shopProductPrice->money;
                            }
                        }

                        return "";
                    },
                ];

                /*$shopColumns["shop.net_cost"] = [
                    'label'     => "Себестоимость",
                    //'attribute' => 'shop.price'.$shopTypePrice->id,
                    'format'    => 'raw',
                    'value'     => function (ShopCmsContentElement $model) use ($shopTypePrice) {
                        $shopProduct = \skeeks\cms\shop\models\ShopProduct::getInstanceByContentElement($model);
                        if ($shopProduct && $shopProduct->shopStoreProducts) {
                            $netCostData = $shopProduct->getShopStoreProducts()->select(['net_cost' => new Expression("sum(purchase_price)/count(id)")])->asArray()->one();
                            return print_r($netCostData, true);
                        }

                        return "";
                    },
                ];*/


                $visibleColumns[] = 'shop.price'.$shopTypePrice->id;

                $sortAttributes['shop.price'.$shopTypePrice->id] = [
                    'asc'     => ["p{$shopTypePrice->id}.price" => SORT_ASC],
                    'desc'    => ["p{$shopTypePrice->id}.price" => SORT_DESC],
                    'label'   => $shopTypePrice->name,
                    'default' => SORT_ASC,
                ];
            }
        }

        $purchaseTypePrice = \Yii::$app->skeeks->site->getShopTypePrices()->andWhere(['is_purchase' => 1])->one();
        $defaultTypePrice = \Yii::$app->skeeks->site->getShopTypePrices()->andWhere(['is_default' => 1])->one();

        if ($purchaseTypePrice && $defaultTypePrice) {
            $baseMoney = new Money("0", \Yii::$app->money->currencyCode);
            $shopColumns["shop.marginality_per"] = [
                'attribute' => "shop.marginality_per",
                'label'     => "Маржинальность, %",
                'format'    => 'raw',

                'beforeCreateCallback' => function (GridView $grid) use ($purchaseTypePrice, $defaultTypePrice) {
                    /**
                     * @var $query ActiveQuery
                     */
                    $query = $grid->dataProvider->query;


                    $query->addSelect([
                        'marginality_purchase' => "p{$purchaseTypePrice->id}.price",
                    ]);


                    $query->addSelect([
                        'marginality_selling' => "p{$defaultTypePrice->id}.price",
                    ]);

                    $query->addSelect([
                        'marginality_per' => new Expression("if (p{$defaultTypePrice->id}.price > 0 AND p{$purchaseTypePrice->id}.price > 0, ((p{$defaultTypePrice->id}.price - p{$purchaseTypePrice->id}.price) / p{$defaultTypePrice->id}.price * 100), 0)"),
                    ]);

                    $grid->sortAttributes["shop.marginality_per"] = [
                        'asc'  => ['marginality_per' => SORT_ASC],
                        'desc' => ['marginality_per' => SORT_DESC],
                    ];
                },

                'value' => function (ShopCmsContentElement $shopCmsContentElement) {
                    if ($shopCmsContentElement->shopProduct && !$shopCmsContentElement->shopProduct->isOffersProduct) {

                        $result = $shopCmsContentElement->raw_row['marginality_per'] ? \Yii::$app->formatter->asDecimal($shopCmsContentElement->raw_row['marginality_per'])." %" : "";
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
                    }
                    return "";
                },
            ];


            $shopColumns["shop.marginality_abs"] = [
                'attribute' => "shop.marginality_abs",
                'label'     => "Маржинальность, ".$baseMoney->currency->symbol,
                'format'    => 'raw',

                'beforeCreateCallback' => function (GridView $grid) use ($purchaseTypePrice, $defaultTypePrice) {
                    /**
                     * @var $query ActiveQuery
                     */
                    $query = $grid->dataProvider->query;


                    $query->addSelect([
                        'marginality_abs' => new Expression("if (p{$defaultTypePrice->id}.price > 0 AND p{$purchaseTypePrice->id}.price > 0, ((p{$defaultTypePrice->id}.price - p{$purchaseTypePrice->id}.price)), 0)"),
                    ]);

                    $grid->sortAttributes["shop.marginality_abs"] = [
                        'asc'  => ['marginality_abs' => SORT_ASC],
                        'desc' => ['marginality_abs' => SORT_DESC],
                    ];
                },

                'value' => function (ShopCmsContentElement $shopCmsContentElement) use ($baseMoney) {
                    if ($shopCmsContentElement->shopProduct && !$shopCmsContentElement->shopProduct->isOffersProduct) {

                        $result = $shopCmsContentElement->raw_row['marginality_abs'] ? \Yii::$app->formatter->asDecimal($shopCmsContentElement->raw_row['marginality_abs'])." ".$baseMoney->currency->symbol : "";

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
                    }
                    return "";
                },
            ];
            $visibleColumns[] = "shop.marginality_per";
            $visibleColumns[] = "shop.marginality_abs";


            $shopColumns["shop.cost_price"] = [
                'attribute' => "shop.cost_price",
                'label'     => "Себестоимость",
                'format'    => 'raw',

                'beforeCreateCallback' => function (GridView $grid) use ($purchaseTypePrice, $defaultTypePrice) {
                    /**
                     * @var $query ActiveQuery
                     */
                    $query = $grid->dataProvider->query;

                    $q = ShopStoreProductMove::find()
                        ->from([
                            'sspm' => ShopStoreProductMove::tableName(),
                        ])
                        ->joinWith("shopStoreDocMove as shopStoreDocMove")
                        ->joinWith("shopStoreProduct as shopStoreProduct")
                        ->andWhere(['shopStoreProduct.shop_product_id' => new Expression("sp.id")])
                        ->andWhere(['shopStoreDocMove.doc_type' => ShopStoreDocMove::DOCTYPE_POSTING])
                        ->addSelect([
                            "cost_price" => new Expression("sum(sspm.price * sspm.quantity) / sum(sspm.quantity)"),
                        ]);;


                    $query->addSelect([
                        'cost_price' => $q,
                    ]);

                    $grid->sortAttributes["shop.cost_price"] = [
                        'asc'  => ['cost_price' => SORT_ASC],
                        'desc' => ['cost_price' => SORT_DESC],
                    ];
                },

                'value' => function (ShopCmsContentElement $shopCmsContentElement) {

                    $result = $shopCmsContentElement->raw_row['cost_price'] ? (float)round($shopCmsContentElement->raw_row['cost_price']) : "";

                    return Html::tag("div", $result, [
                    ]);

                    return "";
                },
            ];
        }


        //$visibleColumns[] = "shop.brand_id";
        $shopColumns["shop.brand_id"] = [
            'attribute' => "shop.brand_id",
            'label'     => "Бренд",
            'format'    => 'raw',

            'beforeCreateCallback' => function (GridView $grid) use ($purchaseTypePrice, $defaultTypePrice) {
                /**
                 * @var $query ActiveQuery
                 */
                $query = $grid->dataProvider->query;


                $query->addSelect([
                    'brand_id' => "sp.brand_id",
                ]);

                $grid->sortAttributes["shop.brand_id"] = [
                    'asc'  => ['brand_id' => SORT_ASC],
                    'desc' => ['brand_id' => SORT_DESC],
                ];
            },

            'value' => function (ShopCmsContentElement $shopCmsContentElement) {


                if ($shopCmsContentElement->shopProduct->brand_id) {
                    return Html::tag("div", $shopCmsContentElement->shopProduct->brand->name);
                } else {

                    return "";
                }

            },
        ];
        $shopColumns["shop.brand_sku"] = [
            'attribute' => "shop.brand_sku",
            'label'     => "Артикул бренда",
            'format'    => 'raw',

            'beforeCreateCallback' => function (GridView $grid) use ($purchaseTypePrice, $defaultTypePrice) {
                /**
                 * @var $query ActiveQuery
                 */
                $query = $grid->dataProvider->query;


                $query->addSelect([
                    'brand_sku' => "sp.brand_sku",
                ]);

                $grid->sortAttributes["shop.brand_sku"] = [
                    'asc'  => ['brand_sku' => SORT_ASC],
                    'desc' => ['brand_sku' => SORT_DESC],
                ];
            },

            'value' => function (ShopCmsContentElement $shopCmsContentElement) {


                if ($shopCmsContentElement->shopProduct->brand_sku) {
                    return Html::tag("div", $shopCmsContentElement->shopProduct->brand_sku);
                } else {

                    return "";
                }

            },
        ];
        $shopColumns["shop.dimentions"] = [
            'attribute' => "shop.dimentions",
            'label'     => "Габариты",
            'format'    => 'raw',

            'beforeCreateCallback' => function (GridView $grid) use ($purchaseTypePrice, $defaultTypePrice) {
                /**
                 * @var $query ActiveQuery
                 */
                $query = $grid->dataProvider->query;

            },

            'value' => function (ShopCmsContentElement $shopCmsContentElement) {


                if ($shopCmsContentElement->shopProduct->dimensionsFormated) {
                    return Html::tag("div", $shopCmsContentElement->shopProduct->dimensionsFormated);
                } else {

                    return "";
                }

            },
        ];

        $shopColumns["shop.brand_sku"] = [
            'attribute' => "shop.brand_sku",
            'label'     => "Артикул бренда",
            'format'    => 'raw',

            'beforeCreateCallback' => function (GridView $grid) use ($purchaseTypePrice, $defaultTypePrice) {
                /**
                 * @var $query ActiveQuery
                 */
                $query = $grid->dataProvider->query;


                $query->addSelect([
                    'brand_sku' => "sp.brand_sku",
                ]);

                $grid->sortAttributes["shop.brand_sku"] = [
                    'asc'  => ['brand_sku' => SORT_ASC],
                    'desc' => ['brand_sku' => SORT_DESC],
                ];
            },

            'value' => function (ShopCmsContentElement $shopCmsContentElement) {


                if ($shopCmsContentElement->shopProduct->brand_sku) {
                    return Html::tag("div", $shopCmsContentElement->shopProduct->brand_sku);
                } else {

                    return "";
                }

            },
        ];


        $shopColumns["shop.country_alpha2"] = [
            'attribute' => "shop.country_alpha2",
            'label'     => "Страна",
            'format'    => 'raw',

            'beforeCreateCallback' => function (GridView $grid) use ($purchaseTypePrice, $defaultTypePrice) {
                /**
                 * @var $query ActiveQuery
                 */
                $query = $grid->dataProvider->query;


                $query->addSelect([
                    'country_alpha2' => "sp.country_alpha2",
                ]);

                $grid->sortAttributes["shop.country_alpha2"] = [
                    'asc'  => ['country_alpha2' => SORT_ASC],
                    'desc' => ['country_alpha2' => SORT_DESC],
                ];
            },

            'value' => function (ShopCmsContentElement $shopCmsContentElement) {


                if ($shopCmsContentElement->shopProduct->country_alpha2) {
                    return Html::tag("div", $shopCmsContentElement->shopProduct->country->name);
                } else {

                    return "";
                }

            },
        ];


        /**
         * @var ShopStore[] $stores
         */
        if ($stores = ShopStore::find()->cmsSite()->all()) {
            foreach ($stores as $store) {
                $shopColumns["shop.quantity_".$store->id] = [
                    'attribute' => "shop.quantity_".$store->id,
                    'label'     => $store->name,
                    'format'    => 'raw',

                    'beforeCreateCallback' => function (GridView $grid) use ($store) {
                        /**
                         * @var $query ActiveQuery
                         */
                        $query = $grid->dataProvider->query;

                        $subQuery = ShopStoreProduct::find()->select(["quantity"])->andWhere(
                            ['shop_product_id' => new Expression("sp.id")],
                        )->andWhere(['shop_store_id' => $store->id]);

                        $query->addSelect([
                            'quantity_'.$store->id => $subQuery,
                        ]);


                        $grid->sortAttributes["shop.quantity_".$store->id] = [
                            'asc'  => ['quantity_'.$store->id => SORT_ASC],
                            'desc' => ['quantity_'.$store->id => SORT_DESC],
                        ];
                    },

                    'value' => function (ShopCmsContentElement $shopCmsContentElement) use ($store) {
                        if ($shopCmsContentElement->shopProduct) {
                            return (float)$shopCmsContentElement->raw_row['quantity_'.$store->id]." ".$shopCmsContentElement->shopProduct->measure->symbol;
                        }
                        return "";
                    },

                ];

                if (!$store->is_supplier) {
                    //$visibleColumns[] = "shop.quantity_" . $store->id;
                }
            }
        }

        if (ShopStore::find()->isSupplier(false)->cmsSite()->exists()) {
            $shopColumns["shop.quantity_our"] = [
                'attribute' => "shop.quantity_our",
                'label'     => "В наличии",
                'format'    => 'raw',

                'beforeCreateCallback' => function (GridView $grid) {
                    /**
                     * @var $query ActiveQuery
                     */
                    $query = $grid->dataProvider->query;

                    $subQuery = ShopStoreProduct::find()->select(["quantity" => new Expression("sum(quantity)")])->andWhere(
                        ['shop_product_id' => new Expression("sp.id")],
                    )->andWhere(['shop_store_id' => ShopStore::find()->isSupplier(false)->cmsSite()->select(['id'])]);

                    $query->addSelect([
                        'quantity_our' => $subQuery,
                    ]);


                    $grid->sortAttributes["shop.quantity_our"] = [
                        'asc'  => ['quantity_our' => SORT_ASC],
                        'desc' => ['quantity_our' => SORT_DESC],
                    ];
                },

                'value' => function (ShopCmsContentElement $shopCmsContentElement) {
                    if ($shopCmsContentElement->shopProduct && !$shopCmsContentElement->shopProduct->isOffersProduct) {

                        return (float)$shopCmsContentElement->raw_row['quantity_our']." ".$shopCmsContentElement->shopProduct->measure->symbol;
                    }
                    return "";
                },
            ];
            $visibleColumns[] = "shop.quantity_our";
        }


        if (ShopStore::find()->isSupplier(true)->cmsSite()->exists()) {
            $shopColumns["shop.quantity_suppliers"] = [
                'attribute' => "shop.quantity_suppliers",
                'label'     => "У поставщиков",
                'format'    => 'raw',

                'beforeCreateCallback' => function (GridView $grid) {
                    /**
                     * @var $query ActiveQuery
                     */
                    $query = $grid->dataProvider->query;

                    $subQuery = ShopStoreProduct::find()->select(["quantity" => new Expression("sum(quantity)")])->andWhere(
                        ['shop_product_id' => new Expression("sp.id")],
                    )->andWhere(['shop_store_id' => ShopStore::find()->isSupplier(true)->cmsSite()->select(['id'])]);

                    $query->addSelect([
                        'quantity_suppliers' => $subQuery,
                    ]);


                    $grid->sortAttributes["shop.quantity_suppliers"] = [
                        'asc'  => ['quantity_suppliers' => SORT_ASC],
                        'desc' => ['quantity_suppliers' => SORT_DESC],
                    ];
                },

                'value' => function (ShopCmsContentElement $shopCmsContentElement) {
                    if ($shopCmsContentElement->shopProduct && !$shopCmsContentElement->shopProduct->isOffersProduct) {
                        return (float)$shopCmsContentElement->raw_row['quantity_suppliers']." ".$shopCmsContentElement->shopProduct->measure->symbol;
                    }
                    return "";
                },
            ];
            $visibleColumns[] = "shop.quantity_suppliers";
        }


        $shopColumns["shop.measure_code"] = [
            'attribute' => "shop.measure_code",
            'label'     => "Единица продаж",
            'format'    => 'raw',

            'beforeCreateCallback' => function (GridView $grid) {
                /**
                 * @var $query ActiveQuery
                 */
                $query = $grid->dataProvider->query;

                $query->addSelect([
                    'sp_measure_code' => 'sp.measure_code',
                ]);


                $grid->sortAttributes["shop.measure_code"] = [
                    'asc'  => ['sp.measure_code' => SORT_ASC],
                    'desc' => ['sp.measure_code' => SORT_DESC],
                ];
            },

            'value' => function (ShopCmsContentElement $shopCmsContentElement) {
                return (string)$shopCmsContentElement->shopProduct->measure->asShortText;
            },
        ];
        $visibleColumns[] = "shop.measure_code";


        $visibleColumns[] = "shop.barcodes";


        if ($shopColumns) {
            ArrayHelper::remove($grid->columns, 'custom');
            $grid->columns = ArrayHelper::merge($grid->columns, $shopColumns);

            $visibleColumns = ArrayHelper::merge([
                'checkbox',
                'actions',
                'custom',
            ], $visibleColumns);
            $visibleColumns = ArrayHelper::merge($visibleColumns, ['active', 'view']);
            $grid->visibleColumns = $visibleColumns;
            $grid->sortAttributes = ArrayHelper::merge($grid->sortAttributes, $sortAttributes);
        }
    }


    public function initGridData($action, $content)
    {
        parent::initGridData($action, $content);

        $filterFields = [];
        $filterFieldsLabels = [];
        $filterFieldsRules = [];


        if ($is_quantity_our_filter = ShopStore::find()->isSupplier(false)->cmsSite()->exists()) {
            $filterFields['quantity_our_filter'] = [
                'class'                   => NumberFilterField::class,
                'label'                   => 'В наличии',
                'isAddAttributeTableName' => false,
                'field'                   => [
                    'class' => NumberField::class,
                ],
                'beforeModeApplyCallback' => function (QueryFiltersEvent $e, NumberFilterField $field) {
                    /**
                     * @var $query ActiveQuery
                     */
                    $query = $e->dataProvider->query;

                    if (ArrayHelper::getValue($e->field->value, "mode") == FilterModeEmpty::ID || ArrayHelper::getValue($e->field->value, "mode") == FilterModeNotEmpty::ID || ArrayHelper::getValue($e->field->value,
                            "value.0") || ArrayHelper::getValue($e->field->value, "value.1") || (string)ArrayHelper::getValue($e->field->value, "value.0") == '0') {

                        $field->setIsHaving();

                        $subQuery = ShopStoreProduct::find()->select(["quantity" => new Expression("sum(quantity)")])->andWhere(
                            ['shop_product_id' => new Expression("sp.id")],
                        )->andWhere(['shop_store_id' => ShopStore::find()->isSupplier(false)->cmsSite()->select(['id'])]);

                        $query->addSelect([
                            'quantity_our_filter' => $subQuery,
                        ]);
                    }


                    return true;
                },
            ];
        }

        if ($is_quantity_supplier_filter = ShopStore::find()->isSupplier(true)->cmsSite()->exists()) {
            $filterFields['quantity_supplier_filter'] = [
                'class'                   => NumberFilterField::class,
                'label'                   => 'Количество у поставщиков',
                'isAddAttributeTableName' => false,
                'field'                   => [
                    'class' => NumberField::class,
                ],
                'beforeModeApplyCallback' => function (QueryFiltersEvent $e, NumberFilterField $field) {
                    /**
                     * @var $query ActiveQuery
                     */
                    $query = $e->dataProvider->query;

                    if (ArrayHelper::getValue($e->field->value, "value.0") || ArrayHelper::getValue($e->field->value, "value.1") || (string)ArrayHelper::getValue($e->field->value, "value.0") == '0') {

                        $field->setIsHaving();

                        $subQuery = ShopStoreProduct::find()->select(["quantity" => new Expression("sum(quantity)")])->andWhere(
                            ['shop_product_id' => new Expression("sp.id")],
                        )->andWhere(['shop_store_id' => ShopStore::find()->isSupplier(true)->cmsSite()->select(['id'])]);

                        $query->addSelect([
                            'quantity_supplier_filter' => $subQuery,
                        ]);
                    }


                    return true;
                },


            ];
        }

        if ($is_quantity_our_filter || $is_quantity_supplier_filter) {
            $filterFields['filter_other'] = [
                'class'    => SelectField::class,
                'items'    => [
                    'not_tied' => 'Не связаны с магазинами и поставщиками',
                    'tied'     => 'Связаны с магазинами и поставщиками',
                ],
                'label'    => 'Связка и оформление',
                'on apply' => function (QueryFiltersEvent $e) {
                    /**
                     * @var $query ActiveQuery
                     */
                    $query = $e->dataProvider->query;

                    if ($e->field->value == 'tied') {
                        //$query->joinWith("childrenContentElements as child");
                        //$query->joinWith("childrenContentElements.parentContentElement as parent");

                        $subQuery = ShopStoreProduct::find()->select(["total" => new Expression("count(id)")])->andWhere(
                            ['shop_product_id' => new Expression("sp.id")],
                        );

                        $query->addSelect([
                            'total_tied' => $subQuery,
                        ]);

                        $query->andHaving([
                            '>',
                            'total_tied',
                            0,
                        ]);
                    } elseif ($e->field->value == 'not_tied') {
                        $subQuery = ShopStoreProduct::find()->select(["total" => new Expression("count(id)")])->andWhere(
                            ['shop_product_id' => new Expression("sp.id")],
                        );

                        $query->addSelect([
                            'total_tied' => $subQuery,
                        ]);

                        $query->andHaving([
                            '<=',
                            'total_tied',
                            0,
                        ]);
                    }
                },
            ];
        }


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

        $filterFields['barcodes'] = [
            'class'             => StringFilterField::class,
            'field'             => [
                'class' => TextField::class,
            ],
            "isAllowChangeMode" => false,
            "defaultMode"       => FilterModeEq::ID,
            /*'elementOptions' => [
                'placeholder' => 'Штрихкод',
            ],*/
            'label'             => 'Штрихкод',
            'filterAttribute'   => 'barcodes.value',
            'on apply'          => function (QueryFiltersEvent $e) {


                if (ArrayHelper::getValue($e->field->value, "value.0")) {
                    /**
                     * @var $query ActiveQuery
                     */
                    $query = $e->dataProvider->query;
                    $query->joinWith("shopProduct as shopProduct");
                    $query->joinWith("shopProduct.shopProductBarcodes as barcodes");
                    $query->groupBy([
                        ShopCmsContentElement::tableName().".id",
                    ]);

                    //$query->andWhere(['barcodes.value' => ArrayHelper::getValue($e->field->value, "value.0")]);
                }
            },
        ];


        $filterFields['stores'] = [
            'class'    => StringFilterField::class,
            'label'    => 'Магазин/склад',
            //'filterAttribute' => 'shopStoreProducts.shop_store_id',
            'on apply' => function (QueryFiltersEvent $e) {
                /**
                 * @var $query ActiveQuery
                 */
                $query = $e->dataProvider->query;
                $query->joinWith("shopProduct.shopStoreProducts as shopStoreProducts");
                if ($e->field->value) {
                    $query->andWhere(['shopStoreProducts.shop_store_id' => $e->field->value]);
                }

                /*if ($e->field->value) {
                    $query->andWhere(['barcodes.value' => $e->field->value]);
                }*/
            },

            'class'    => SelectField::class,
            'items'    => ArrayHelper::map(ShopStore::find()->cmsSite()->all(), 'id', 'asText'),
            'multiple' => true,

        ];

        $filterFields['brand_id'] = [
            'class'    => StringFilterField::class,
            'label'    => 'Бренд',
            //'filterAttribute' => 'shopStoreProducts.shop_store_id',
            'on apply' => function (QueryFiltersEvent $e) {
                /**
                 * @var $query ActiveQuery
                 */
                $query = $e->dataProvider->query;
                if ($e->field->value) {
                    $query->andWhere(['sp.brand_id' => $e->field->value]);
                }

            },

            'class'        => WidgetField::class,
            'widgetClass'  => AjaxSelectModel::class,
            'widgetConfig' => [
                'modelClass' => ShopBrand::class,
                'multiple'   => true,
            ],
            //

        ];

        $filterFields['brand_sku'] = [
            'class'    => StringFilterField::class,
            'label'    => 'Артикул бренда',
            //'filterAttribute' => 'shopStoreProducts.shop_store_id',
            'on apply' => function (QueryFiltersEvent $e) {
                /**
                 * @var $query ActiveQuery
                 */
                $query = $e->dataProvider->query;
                if ($e->field->value) {
                    $query->andWhere(['sp.brand_sku' => $e->field->value]);
                }

            },
            'class'        => TextField::class,
        ];

        $filterFields['q'] = [
            'label'          => 'Поиск',
            'elementOptions' => [
                'placeholder' => 'Поиск (название, описание)',
            ],
            'on apply'       => function (QueryFiltersEvent $e) {
                /**
                 * @var $query ActiveQuery
                 */
                $query = $e->dataProvider->query;

                if ($e->field->value) {
                    //$query->joinWith("childrenContentElements as child");
                    //$query->joinWith("childrenContentElements.parentContentElement as parent");

                    $query->joinWith("shopProduct.brand as brand");

                    $q = CmsContentElement::find()
                        ->select(['parent_id' => 'parent_content_element_id'])
                        ->where([
                            'or',
                            ['like', CmsContentElement::tableName().'.id', $e->field->value],
                            ['like', CmsContentElement::tableName().'.name', $e->field->value],
                            ['like', CmsContentElement::tableName().'.description_short', $e->field->value],
                            ['like', CmsContentElement::tableName().'.description_full', $e->field->value],
                            ['like', CmsContentElement::tableName().'.external_id', $e->field->value],
                            //['like', 'brand.name', $e->field->value],
                        ]);

                    $query->leftJoin(['p' => $q], ['p.parent_id' => new Expression(CmsContentElement::tableName().".id")]);

                    $query->andWhere([
                        'or',
                        ['like', CmsContentElement::tableName().'.id', $e->field->value],
                        ['like', CmsContentElement::tableName().'.name', $e->field->value],
                        ['like', CmsContentElement::tableName().'.description_short', $e->field->value],
                        ['like', CmsContentElement::tableName().'.description_full', $e->field->value],
                        ['like', CmsContentElement::tableName().'.external_id', $e->field->value],
                        ['like', 'shopProduct.brand_sku', $e->field->value],
                        ['like', 'brand.name', $e->field->value],
                        ['is not', 'p.parent_id', null],
                    ]);
                }
            },
        ];

        $filterFields['country_alpha2'] = [
            'class'    => StringFilterField::class,
            'label'    => 'Страна',
            //'filterAttribute' => 'shopStoreProducts.shop_store_id',
            'on apply' => function (QueryFiltersEvent $e) {
                /**
                 * @var $query ActiveQuery
                 */
                $query = $e->dataProvider->query;
                if ($e->field->value) {
                    $query->andWhere(['sp.country_alpha2' => $e->field->value]);
                }

                /*if ($e->field->value) {
                    $query->andWhere(['barcodes.value' => $e->field->value]);
                }*/
            },

            'class'        => WidgetField::class,
            'widgetClass'  => AjaxSelectModel::class,
            'widgetConfig' => [
                'modelClass'       => CmsCountry::class,
                'modelPkAttribute' => "alpha2",
                'multiple'         => true,
            ],
            //

        ];

        $filterFields['empty'] = [
            'class'    => StringFilterField::class,
            'label'    => 'Не заполнено',
            //'filterAttribute' => 'shopStoreProducts.shop_store_id',
            'on apply' => function (QueryFiltersEvent $e) {
                /**
                 * @var $query ActiveQuery
                 */
                $query = $e->dataProvider->query;
                if ($e->field->value == 'dimentions') {
                    $query->andWhere([
                        'or',
                        ['sp.length' => ""],
                        ['sp.width' => ""],
                        ['sp.height' => ""],
                    ]);
                } elseif ($e->field->value == 'weight') {
                    $query->andWhere([
                        'or',
                        ['sp.weight' => ""],
                    ]);
                } elseif ($e->field->value == 'brand') {
                    $query->andWhere([
                        'or',
                        ['sp.brand_id' => null],
                    ]);
                }

                /*if ($e->field->value) {
                    $query->andWhere(['barcodes.value' => $e->field->value]);
                }*/
            },

            'class' => SelectField::class,
            'items' => [
                'dimentions' => 'Габариты',
                'weight'     => 'Вес',
                'brand'      => 'Бренд',
            ]
            //

        ];

        $purchaseTypePrice = \Yii::$app->skeeks->site->getShopTypePrices()->andWhere(['is_purchase' => 1])->one();
        $defaultTypePrice = \Yii::$app->skeeks->site->getShopTypePrices()->andWhere(['is_default' => 1])->one();

        if ($purchaseTypePrice && $defaultTypePrice) {
            $baseMoney = new Money("0", \Yii::$app->money->currencyCode);

            $filterFields["marginality_per_filter"] = [
                'attribute' => "marginality_per_filter",
                'label'     => "Маржинальность, %",
                'class'     => NumberFilterField::class,
                'field'     => [
                    'class' => NumberField::class,
                ],

                'isAddAttributeTableName' => false,
                'beforeModeApplyCallback' => function (QueryFiltersEvent $e, NumberFilterField $field) use ($defaultTypePrice, $purchaseTypePrice) {
                    /**
                     * @var $query ActiveQuery
                     */
                    $query = $e->dataProvider->query;

                    if (ArrayHelper::getValue($e->field->value, "value.0") || ArrayHelper::getValue($e->field->value, "value.1") || (string)ArrayHelper::getValue($e->field->value, "value.0") == "0") {

                        $field->setIsHaving();

                        $query->addSelect([
                            'marginality_per_filter' => new Expression("(p{$defaultTypePrice->id}.price - p{$purchaseTypePrice->id}.price) / p{$defaultTypePrice->id}.price * 100"),
                        ]);
                    }
                    return true;
                },
            ];

            $filterFields["marginality_abs_filter"] = [
                'attribute' => "marginality_abs_filter",
                'label'     => "Маржинальность, ".$baseMoney->currency->symbol,
                'class'     => NumberFilterField::class,
                'field'     => [
                    'class' => NumberField::class,
                ],

                'isAddAttributeTableName' => false,
                'beforeModeApplyCallback' => function (QueryFiltersEvent $e, NumberFilterField $field) use ($defaultTypePrice, $purchaseTypePrice) {
                    /**
                     * @var $query ActiveQuery
                     */
                    $query = $e->dataProvider->query;

                    if (ArrayHelper::getValue($e->field->value, "value.0") || ArrayHelper::getValue($e->field->value, "value.1") || (string)ArrayHelper::getValue($e->field->value, "value.0") == "0") {

                        $field->setIsHaving();

                        $query->addSelect([
                            'marginality_per_filter' => new Expression("(p{$defaultTypePrice->id}.price - p{$purchaseTypePrice->id}.price)"),
                        ]);
                    }
                    return true;
                },
            ];

            $filterFields["marginality_abs_filter"] = [
                'attribute' => "marginality_abs_filter",
                'label'     => "Маржинальность, ".$baseMoney->currency->symbol,
                'class'     => NumberFilterField::class,
                'field'     => [
                    'class' => NumberField::class,
                ],

                'isAddAttributeTableName' => false,
                'beforeModeApplyCallback' => function (QueryFiltersEvent $e, NumberFilterField $field) use ($defaultTypePrice, $purchaseTypePrice) {
                    /**
                     * @var $query ActiveQuery
                     */
                    $query = $e->dataProvider->query;

                    if (ArrayHelper::getValue($e->field->value, "value.0") || ArrayHelper::getValue($e->field->value, "value.1") || (string)ArrayHelper::getValue($e->field->value, "value.0") == "0") {

                        $field->setIsHaving();

                        $query->addSelect([
                            'marginality_per_filter' => new Expression("(p{$defaultTypePrice->id}.price - p{$purchaseTypePrice->id}.price)"),
                        ]);
                    }
                    return true;
                },
            ];
        }


        $filterFields["product_weight"] = [
            'filterAttribute' => 'sp.weight',
            'attribute'       => "product_weight",
            'label'           => "Вес товара с упаковкой",
            'class'           => NumberFilterField::class,
            'field'           => [
                'class' => NumberField::class,
            ],

        ];

        if ($defaultTypePrice) {
            $filterFields["retail_price_filter"] = [
                'attribute' => "retail_price_filter",
                'label'     => $defaultTypePrice->name,
                'class'     => NumberFilterField::class,
                'field'     => [
                    'class' => NumberField::class,
                ],

                'isAddAttributeTableName' => false,
                'beforeModeApplyCallback' => function (QueryFiltersEvent $e, NumberFilterField $field) use ($defaultTypePrice) {
                    /**
                     * @var $query ActiveQuery
                     */
                    $query = $e->dataProvider->query;

                    if (ArrayHelper::getValue($e->field->value, "mode") == FilterModeNotEmpty::ID || ArrayHelper::getValue($e->field->value, "mode") == FilterModeEmpty::ID || ArrayHelper::getValue($e->field->value,
                            "value.0") || ArrayHelper::getValue($e->field->value, "value.1") || (string)ArrayHelper::getValue($e->field->value, "value.0") == "0") {

                        $field->setIsHaving();

                        $query->addSelect([
                            'retail_price_filter' => new Expression("(p{$defaultTypePrice->id}.price)"),
                        ]);
                    }
                    return true;
                },
            ];
        }


        if ($purchaseTypePrice) {
            $filterFields["purchase_price_filter"] = [
                'attribute' => "purchase_price_filter",
                'label'     => $defaultTypePrice->name,
                'class'     => NumberFilterField::class,
                'field'     => [
                    'class' => NumberField::class,
                ],

                'isAddAttributeTableName' => false,
                'beforeModeApplyCallback' => function (QueryFiltersEvent $e, NumberFilterField $field) use ($purchaseTypePrice) {
                    /**
                     * @var $query ActiveQuery
                     */
                    $query = $e->dataProvider->query;

                    if (ArrayHelper::getValue($e->field->value, "value.0") || ArrayHelper::getValue($e->field->value, "value.1") || (string)ArrayHelper::getValue($e->field->value, "value.0") == "0") {

                        $field->setIsHaving();

                        $query->addSelect([
                            'purchase_price_filter' => new Expression("(p{$purchaseTypePrice->id}.price)"),
                        ]);
                    }
                    return true;
                },
            ];
        }


        //Только для сайта поставщика
        if (\Yii::$app->skeeks->site->shopSite->is_receiver) {
            $filterFields['is_ready'] = [
                'class'    => SelectField::class,
                'items'    => [
                    'on'  => 'Привязан',
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
                            $query->andWhere(['is not', 'main_cce_id', null]);
                        } else {
                            $query->andWhere(['main_cce_id' => null]);
                        }

                    }
                },
            ];

            $filterFieldsLabels['is_ready'] = 'Привязка';
            $filterFieldsRules[] = ['is_ready', 'safe'];

        }

        /*if (\Yii::$app->skeeks->site->is_default) {
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
        }*/

        $visibleFilters = [
            'barcodes', 'brand_id', 'brand_sku',
        ];

        if ($is_quantity_our_filter) {
            $visibleFilters[] = 'quantity_our_filter';
        }
        if ($is_quantity_supplier_filter) {
            $visibleFilters[] = 'quantity_supplier_filter';
        }
        if ($purchaseTypePrice) {
            $visibleFilters[] = 'purchase_price_filter';
        }
        if ($defaultTypePrice) {
            $visibleFilters[] = 'retail_price_filter';
        }

        $visibleFilters[] = 'product_weight';

        if ($purchaseTypePrice && $defaultTypePrice) {
            $visibleFilters[] = 'marginality_abs_filter';
            $visibleFilters[] = 'marginality_per_filter';
        }


        $filterFieldsLabels['shop_product_type'] = 'Тип товара';
        $filterFieldsLabels['barcodes'] = 'Штрихкод';
        $filterFieldsLabels['stores'] = 'Магазин/склад';
        $filterFieldsLabels['brand_id'] = 'Бренд';
        $filterFieldsLabels['brand_sku'] = 'Артикул бренд';
        $filterFieldsLabels['country_alpha2'] = 'Страна';
        $filterFieldsLabels['empty'] = 'Не заполнено';
        $filterFieldsLabels['supplier_external_jsondata'] = 'Данные поставщика';
        $filterFieldsLabels['quantity_our_filter'] = "В наличии";
        $filterFieldsLabels['quantity_supplier_filter'] = "Количество у поставщиков";
        $filterFieldsLabels['marginality_abs_filter'] = "Маржинальность, значение";
        $filterFieldsLabels['marginality_per_filter'] = "Маржинальность, %";
        $filterFieldsLabels['retail_price_filter'] = "";
        $filterFieldsLabels['product_weight'] = "Вес товара с упаковкой";
        $filterFieldsLabels['purchase_price_filter'] = "";
        $filterFieldsLabels['filter_other'] = "Связка и оформление";


        $filterFieldsRules[] = ['shop_product_type', 'safe'];
        $filterFieldsRules[] = ['supplier_external_jsondata', 'safe'];
        $filterFieldsRules[] = ['quantity_our_filter', 'safe'];
        $filterFieldsRules[] = ['quantity_supplier_filter', 'safe'];
        $filterFieldsRules[] = ['barcodes', 'string'];
        $filterFieldsRules[] = ['stores', 'safe'];
        $filterFieldsRules[] = ['brand_id', 'safe'];
        $filterFieldsRules[] = ['brand_sku', 'safe'];
        $filterFieldsRules[] = ['empty', 'safe'];
        $filterFieldsRules[] = ['country_alpha2', 'safe'];
        $filterFieldsRules[] = ['marginality_abs_filter', 'safe'];
        $filterFieldsRules[] = ['marginality_per_filter', 'safe'];
        $filterFieldsRules[] = ['retail_price_filter', 'safe'];
        $filterFieldsRules[] = ['product_weight', 'safe'];
        $filterFieldsRules[] = ['purchase_price_filter', 'safe'];
        $filterFieldsRules[] = ['filter_other', 'safe'];


        //Мерж колонок и сортировок
        if ($filterFields) {

            $action->filters['filtersModel']['fields'] = ArrayHelper::merge((array)ArrayHelper::getValue($action->filters, ['filtersModel', 'fields']), $filterFields);
            $action->filters['filtersModel']['attributeDefines'] = ArrayHelper::merge((array)ArrayHelper::getValue($action->filters, ['filtersModel', 'attributeDefines']),
                array_keys($filterFields));
            $action->filters['filtersModel']['attributeLabels'] = ArrayHelper::merge((array)ArrayHelper::getValue($action->filters, ['filtersModel', 'attributeLabels']), $filterFieldsLabels);
            $action->filters['filtersModel']['rules'] = ArrayHelper::merge((array)ArrayHelper::getValue($action->filters, ['filtersModel', 'rules']), $filterFieldsRules);

            $action->filters['visibleFilters'] = ArrayHelper::merge((array)ArrayHelper::getValue($action->filters, ['visibleFilters']), $visibleFilters);
        }

        //Приджоивание магазинных данных
        $action->grid['on init'] = function (Event $event) {
            /**
             * @var $query ActiveQuery
             */
            $query = $event->sender->dataProvider->query;

            $query->select([
                ShopCmsContentElement::tableName().".*",
            ]);

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

            if ($urlHelper->getBackenParam("sx-to-main") || $urlHelper->getBackenParam("all-items")) {

                /*$siteClass = \Yii::$app->skeeks->siteClass;
                $site = $siteClass::find()->where(['is_default' => 1])->one();
                $site_id = $site->id;*/
                $site_id = \Yii::$app->skeeks->site->id;
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

                if ($this->isProductGroup()) {
                    $query->andWhere([
                        'in',
                        'sp.product_type',
                        [
                            ShopProduct::TYPE_SIMPLE,
                            ShopProduct::TYPE_OFFERS,
                        ],
                    ]);
                } else {
                    $query->andWhere([
                        'in',
                        'sp.product_type',
                        [
                            ShopProduct::TYPE_SIMPLE,
                            ShopProduct::TYPE_OFFER,
                        ],
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
        //echo "Технические работы";die;
        $is_saved = false;
        $redirect = "";

        $productPrices = [];
        $shopStoreProducts = [];


        /**
         * if ($shopStores = ShopStore::find()->where(['cms_site_id' => \Yii::$app->skeeks->site->id])->all()) {
         * foreach ($shopStores as $shopStore) {
         * $ssp = new ShopStoreProduct([
         * 'shop_store_id' => $shopStore->id,
         * ]);
         *
         * $shopStoreProducts[] = $ssp;
         * }
         * }*/


        $modelClassName = $this->modelClassName;
        $model = new $modelClassName();
        $model->cms_site_id = \Yii::$app->skeeks->site->id;


        $model->loadDefaultValues();
        $model->content_id = $this->content->id;


        $shopProduct = new ShopProduct();
        $shopProduct->loadDefaultValues();
        $shopProduct->validate();

        $rr = new RequestResponse();


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


        //Сначала правильно подгружаем данные выбранные в форме
        if ($post = \Yii::$app->request->post()) {
            $model->load(\Yii::$app->request->post());
            $relatedModel = $model->relatedPropertiesModel;
            $relatedModel->load(\Yii::$app->request->post());
        }

        //Затем пытаем автоматически заполнить данные из товара поставщика
        $shopStoreProduct = null;
        if ($store_product_id = \Yii::$app->request->get("store_product_id")) {
            /**
             * @var $shopStoreProduct ShopStoreProduct
             */
            $shopStoreProduct = ShopStoreProduct::find()->where(['id' => $store_product_id])->one();

            if ($shopStoreProduct) {
                $shopStoreProduct->loadDataToElementProduct($model, $shopProduct);
            }
        }

        //Перезатераем тем что выбранно в форме
        if ($post = \Yii::$app->request->post()) {
            $model->load(\Yii::$app->request->post());
            $relatedModel = $model->relatedPropertiesModel;
            //print_r($relatedModel->toArray());die;
            $relatedModel->load(\Yii::$app->request->post());

            /*print_r($model->tree_id);
            print_r($relatedModel->toArray());
            die;*/

            $shopProduct->load(\Yii::$app->request->post());

            foreach ($productPrices as $productPrice) {
                $data = ArrayHelper::getValue($post, 'prices.'.$productPrice->type_price_id);
                $productPrice->load($data, "");
            }

        } else {
            $relatedModel = $model->relatedPropertiesModel;
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

                        /*foreach ($shopStoreProducts as $ssp) {
                            $data = ArrayHelper::getValue($post, 'stores.'.$ssp->shop_store_id);
                            $ssp->load($data, "");
                            $ssp->shop_product_id = $shopProduct->id;
                            $ssp->save();
                        }*/
                        /*$shopProduct->getBaseProductPriceValue();
                        $baseProductPrice = $shopProduct->baseProductPrice;*/

                        /*if ($shopSubproductContentElement) {
                            $shopSubproductContentElement->main_cce_id = $shopProduct->id;
                            $shopSubproductContentElement->save();
                        }*/
                        if ($shopStoreProduct) {
                            $shopStoreProduct->shop_product_id = $shopProduct->id;
                            $shopStoreProduct->save();
                        }

                        $t->commit();

                        //\Yii::$app->getSession()->setFlash('success', \Yii::t('skeeks/shop/app', 'Saved'));

                        $is_saved = true;
                        \Yii::$app->getSession()->setFlash('success', $this->action->successMessage);

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

        } elseif ($rr->isRequestAjaxPost()) {

            $t = \Yii::$app->db->beginTransaction();

            try {

                $site = \Yii::$app->skeeks->site;
                $siteClass = \Yii::$app->skeeks->siteClass;
                \Yii::$app->skeeks->site = $siteClass::find()->where(['is_default' => 1])->one();

                $model->load(\Yii::$app->request->post());
                $relatedModel->load(\Yii::$app->request->post());


                if (!$model->errors && !$relatedModel->errors && !$shopProduct->errors) {
                    if (!$model->save()) {
                        throw new Exception("Ошибка сохранения данных: ".print_r($model->errors, true));
                    }

                    if (!$relatedModel->save()) {
                        throw new Exception("Ошибка сохранения дополнительных данных: ".print_r($relatedModel->errors, true));
                    }

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

                    if ($shopStoreProduct) {
                        $shopStoreProduct->shop_product_id = $shopProduct->id;
                        $shopStoreProduct->save();
                    }

                    $t->commit();

                    $rr->message = '✓ Сохранено';
                    $rr->data = [
                        'type' => 'create',
                    ];
                    $rr->success = true;
                } else {
                    $rr->success = false;
                    $rr->data = [
                        'validation' => ArrayHelper::merge(
                            ActiveForm::validate($model),
                            ActiveForm::validate($shopProduct),
                            ActiveForm::validate($relatedModel),
                        ),
                    ];
                }
            } catch (\Exception $exception) {
                $t->rollBack();
                $rr->success = false;
                if ($shopProduct->errors) {

                } else {
                    $rr->message = $exception->getMessage();
                }


                $rr->data = [
                    'validation' => ArrayHelper::merge(
                        ActiveForm::validate($model),
                        ActiveForm::validate($shopProduct),
                        ActiveForm::validate($relatedModel),
                    ),
                ];
            }


            return $rr;
        }


        return $this->render($this->editForm, [
            'model'             => $model,
            'relatedModel'      => $relatedModel,
            'shopProduct'       => $shopProduct,
            'productPrices'     => $productPrices,
            'shopStoreProducts' => $shopStoreProducts,
            'is_create'         => true,
            //'baseProductPrice' => $baseProductPrice,

            'is_saved'         => $is_saved,
            'submitBtn'        => \Yii::$app->request->post('submit-btn'),
            'redirect'         => $redirect,
            //'shopSubproductContentElement' => $shopSubproductContentElement,
            'shopStoreProduct' => $shopStoreProduct,
        ]);
    }

    public function update($adminAction)
    {
        //echo "Технические работы";die;

        $is_saved = false;
        $redirect = "";

        /**
         * @var $model ShopCmsContentElement
         */
        $model = $this->model;

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

            /*if (\Yii::$app->request->isAjax && !\Yii::$app->request->isPjax) {
                $model->load(\Yii::$app->request->post());
                $relatedModel->load(\Yii::$app->request->post());
                $shopProduct->load(\Yii::$app->request->post());


                return \yii\widgets\ActiveForm::validateMultiple([
                    $model,
                    $relatedModel,
                    $shopProduct,
                ]);
            }*/

            if ($post = \Yii::$app->request->post()) {

                $model->load(\Yii::$app->request->post());
                $relatedModel = $model->relatedPropertiesModel;

                $relatedModel->load(\Yii::$app->request->post());
                $shopProduct->load(\Yii::$app->request->post());
            } else {
                $relatedModel = $model->relatedPropertiesModel;
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
                try {


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

                            \Yii::$app->getSession()->setFlash('success', $this->action->successMessage);
                            ///\Yii::$app->getSession()->setFlash('success', \Yii::t('skeeks/shop/app', 'Saved'));

                            if (\Yii::$app->request->post('submit-btn') == 'apply') {

                            } else {

                                $redirect = $this->url;

                            }

                            $model->refresh();

                        }
                    }
                } catch (\Exception $exception) {
                    print_r($exception->getMessage());
                    die;
                }


            } elseif ($rr->isRequestAjaxPost()) {

                try {
                    $model->load(\Yii::$app->request->post());
                    $shopProduct->load(\Yii::$app->request->post());
                    $relatedModel->load(\Yii::$app->request->post());

                    $shopProduct->validate();
                    $model->validate();
                    $relatedModel->validate();

                    if (!$model->errors && !$relatedModel->errors && !$shopProduct->errors) {
                        if (!$model->save()) {
                            throw new Exception("Ошибка сохранения данных");
                        }

                        if (!$relatedModel->save()) {
                            throw new Exception("Ошибка сохранения дополнительных данных: ".print_r($relatedModel->errors, true));
                        }

                        if (!$shopProduct->save()) {
                            throw new Exception("Ошибка сохранения товарных данных");
                        }

                        /**
                         * @var $productPrice ShopProductPrice
                         */
                        foreach ($productPrices as $productPrice) {
                            if ($productPrice->save()) {

                            } else {
                                throw new Exception("Ошибка сохранения цены");
                            }

                        }


                        /**
                         * @var $productPrice ShopProductPrice
                         */
                        foreach ($shopStoreProducts as $shopStoreProduct) {
                            if ($shopStoreProduct->save()) {

                            } else {
                                throw new Exception('Check the correctness of the stores: '.print_r($shopStoreProduct->errors, true));
                            }

                        }


                        $rr->message = '✓ Сохранено';
                        $rr->success = true;
                        $rr->data = [
                            'type' => 'update',
                        ];
                    } else {
                        $rr->success = false;
                        $rr->data = [
                            'validation' => ArrayHelper::merge(
                                ActiveForm::validate($model),
                                ActiveForm::validate($relatedModel),
                                ActiveForm::validate($shopProduct),
                            ),
                        ];
                    }
                } catch (\Exception $exception) {
                    $rr->success = false;
                    $rr->message = $exception->getMessage();
                }

                return $rr;
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
            if ($this->content->name_meny && $this->content->name_meny != "Элементы") {
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
            //\Yii::$app->shop->updateAllSubproducts();
            //\Yii::$app->shop->updateAllQuantities();
            \Yii::$app->shop->updateAllTypes();
            ShopComponent::updateProductPrices(\Yii::$app->skeeks->site);
            //\Yii::$app->shop->updateOffersPrice();
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

    /**
     * @return RequestResponse
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionJoinsDettach()
    {
        $rr = new RequestResponse();
        if ($rr->isRequestAjaxPost()) {
            $product_id = (int)\Yii::$app->request->post('product_id');

            if ($product_id) {
                $sp = ShopProduct::findOne($product_id);
                $spModel = $sp->shopProductModel;
                if (!$spModel) {
                    return $rr;
                }

                $t = \Yii::$app->db->beginTransaction();

                try {

                    $sp->shop_product_model_id = null;
                    if (!$sp->save(false, ['shop_product_model_id'])) {
                        throw new \yii\base\Exception(print_r($sp->errors, true));
                    }

                    if ($spModel->getShopProducts()->count() <= 1) {
                        $spModel->delete();
                    }



                    $t->commit();

                    $rr->success = true;

                } catch (\Exception $exception) {
                    $t->rollBack();
                    throw $exception;
                }


            }

        }

        return $rr;
    }

    /**
     *
     */
    public function actionUpdateAttribute()
    {
        $rr = new RequestResponse();
        /**
         * @var $model ShopCmsContentElement
         */
        $model = $this->model;

        if ($rr->isRequestAjaxPost()) {

            $attribute = \Yii::$app->request->post("attribute");
            $value = \Yii::$app->request->post("value");

            try {

                if (\Yii::$app->request->post("act") == "update-price") {

                    $productPrice = $model->shopProduct->savePrice((int)\Yii::$app->request->post("shop_type_price_id"), (float)\Yii::$app->request->post("price_value"), \Yii::$app->request->post("price_currency_code"));
                    $productPrice->is_fixed = (int)\Yii::$app->request->post("is_fixed");
                    $productPrice->save();

                } elseif (\Yii::$app->request->post("act") == "update-store") {
                    $model->shopProduct->saveStoreQuantity((int)\Yii::$app->request->post("shop_store_id"), (float)\Yii::$app->request->post("store_quantity"));
                } else {
                    $model->load(\Yii::$app->request->post());

                    if (\Yii::$app->request->post("ShopProduct")) {

                        $model->shopProduct->load(\Yii::$app->request->post());
                        $dirtyAttrs = $model->shopProduct->getDirtyAttributes();

                        if (!$model->shopProduct->save(true, array_keys($dirtyAttrs))) {
                            throw new \yii\base\Exception("Ошибка сохранения товарных данных: ".print_r($model->shopProduct->errors, true));
                        }
                    }

                    if (!$model->save()) {
                        throw new \yii\base\Exception("Ошибка сохранения: ".print_r($model->errors, true));
                    }
                }

                $rr->message = "Обновлено";
                $rr->success = true;

            } catch (\Exception $exception) {
                $rr->message = $exception->getMessage();
                $rr->success = false;
            }
        }

        return $rr;
    }


    public function actionGenerateBarcode()
    {
        $rr = new RequestResponse();

        /**
         * @var $model ShopCmsContentElement
         */
        $model = $this->model;

        if ($model) {

            //Если у товара нет штрихкда, сгенерировать его
            if (!$model->shopProduct->shopProductBarcodes) {
                $lastBarcode = ShopProductBarcode::find()->orderBy(['id' => SORT_DESC])->limit(1)->one();
                $lastId = 1;
                if ($lastBarcode) {
                    $lastId = $lastBarcode->id + 1;
                }

                $new_barcode = $this->_genBarcode($lastId);

                $barcode = new ShopProductBarcode();
                $barcode->shop_product_id = $model->id;
                $barcode->value = $new_barcode;
                if (!$barcode->save()) {
                    $rr->success = false;
                    $rr->message = print_r($barcode->errors, true);
                } else {
                    $rr->success = true;
                }
            }
        }

        return $rr;
    }

    private function _genBarcode($num)
    {
        $num = (string)$num;

        $result = 0;
        $s = "2".str_pad($num, 11, '0', STR_PAD_LEFT);
        $s = trim($s);
        $sArr = str_split($s);
        $len_num = strlen($s);
        /*print_r($sArr);
        print_r($len_num);
        die;*/
        for ($i = $len_num - 1; $i >= 0; $i--) {
            $result = $result + (int)($sArr[$i]) * (1 + (2 * ($i % 2)));
        }
        $last_num = (10 - ($result % 10)) % 10;

        return $s.$last_num;
    }
}

