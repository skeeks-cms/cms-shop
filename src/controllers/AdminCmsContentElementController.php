<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 15.05.2015
 */

namespace skeeks\cms\shop\controllers;

use skeeks\cms\backend\actions\BackendModelUpdateAction;
use skeeks\cms\components\Cms;
use skeeks\cms\helpers\RequestResponse;
use skeeks\cms\helpers\UrlHelper;
use skeeks\cms\IHasUrl;
use skeeks\cms\models\CmsContent;
use skeeks\cms\models\CmsContentElement;
use skeeks\cms\modules\admin\actions\AdminAction;
use skeeks\cms\modules\admin\actions\modelEditor\AdminModelEditorAction;
use skeeks\cms\modules\admin\actions\modelEditor\AdminMultiDialogModelEditAction;
use skeeks\cms\modules\admin\actions\modelEditor\AdminMultiModelEditAction;
use skeeks\cms\modules\admin\controllers\AdminModelEditorController;
use skeeks\cms\modules\admin\traits\AdminModelEditorStandartControllerTrait;
use skeeks\cms\modules\admin\widgets\GridViewStandart;
use skeeks\cms\shop\models\searchs\ShopCmsContentElementSearch;
use skeeks\cms\shop\models\ShopCmsContentElement;
use skeeks\cms\shop\models\ShopProduct;
use skeeks\cms\shop\models\ShopProductPrice;
use skeeks\cms\shop\models\ShopTypePrice;
use skeeks\yii2\form\fields\BoolField;
use yii\base\DynamicModel;
use yii\base\Event;
use yii\caching\TagDependency;
use yii\db\ActiveQuery;
use yii\db\Exception;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\Application;

/**
 * @property CmsContent $content
 *
 * Class AdminCmsContentTypeController
 * @package skeeks\cms\controllers
 */
class AdminCmsContentElementController extends AdminModelEditorController
{
    use AdminModelEditorStandartControllerTrait;

    public $notSubmitParam = 'sx-not-submit';

    protected $_modelClassName = ShopCmsContentElement::class;
    protected $_modelShowAttribute = "name";
    /**
     * @var CmsContent
     */
    protected $_content = null;
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
    /**
     * @param CmsContent $model
     * @return array
     */
    static public function getColumns($cmsContent = null, $dataProvider = null)
    {
        return \yii\helpers\ArrayHelper::merge(
            static::getDefaultColumns($cmsContent),
            static::getColumnsByContent($cmsContent, $dataProvider)
        );
    }
    /**
     * @param CmsContent $cmsContent
     * @return array
     */
    static public function getDefaultColumns($cmsContent = null)
    {
        $columns = [

            [
                'class' => \skeeks\cms\grid\ImageColumn2::className(),
            ],

            'name',
            ['class' => \skeeks\cms\grid\UpdatedAtColumn::className()],

            [
                'class'     => \yii\grid\DataColumn::className(),
                'value'     => function (\skeeks\cms\models\CmsContentElement $model) {
                    if (!$model->cmsTree) {
                        return null;
                    }

                    $path = [];

                    if ($model->cmsTree->parents) {
                        foreach ($model->cmsTree->parents as $parent) {
                            if ($parent->isRoot()) {
                                $path[] = "[".$parent->site->name."] ".$parent->name;
                            } else {
                                $path[] = $parent->name;
                            }
                        }
                    }
                    $path = implode(" / ", $path);
                    return "<small><a href='{$model->cmsTree->url}' target='_blank' data-pjax='0'>{$path} / {$model->cmsTree->name}</a></small>";
                },
                'format'    => 'raw',
                'filter'    => false,
                //'filter' => \skeeks\cms\helpers\TreeOptions::getAllMultiOptions(),
                'attribute' => 'tree_id',
            ],

            [
                'class'  => \yii\grid\DataColumn::className(),
                'value'  => function (\skeeks\cms\models\CmsContentElement $model) {
                    $result = [];

                    if ($model->cmsContentElementTrees) {
                        foreach ($model->cmsContentElementTrees as $contentElementTree) {
                            $site = $contentElementTree->tree->site;
                            $result[] = "<small><a href='{$contentElementTree->tree->url}' target='_blank' data-pjax='0'>[{$site->name}]/.../{$contentElementTree->tree->name}</a></small>";

                        }
                    }

                    return implode('<br />', $result);

                },
                'format' => 'raw',
                'label'  => \Yii::t('skeeks/shop/app', 'Advanced Topics'),
            ],

            [
                'attribute' => 'active',
                'class'     => \skeeks\cms\grid\BooleanColumn::className(),
            ],


            /*[
                'label' => \Yii::t('skeeks/shop/app', 'Base price'),
                'class' => \yii\grid\DataColumn::className(),
                'attribute' => 'baseProductPrice',
                'value' => function(\skeeks\cms\models\CmsContentElement $model)
                {
                    $shopProduct = \skeeks\cms\shop\models\ShopProduct::getInstanceByContentElement($model);
                    if ($shopProduct && $shopProduct->baseProductPrice)
                    {
                        return \Yii::$app->money->intlFormatter()->format($shopProduct->baseProductPrice->money);
                    }

                    return null;
                }
            ],*/

            [
                'class'  => \yii\grid\DataColumn::className(),
                'value'  => function (\skeeks\cms\models\CmsContentElement $model) {

                    return \yii\helpers\Html::a('<i class="glyphicon glyphicon-arrow-right"></i>', $model->absoluteUrl,
                        [
                            'target'    => '_blank',
                            'title'     => \Yii::t('skeeks/shop/app', 'View online (opens new window)'),
                            'data-pjax' => '0',
                            'class'     => 'btn btn-default btn-sm',
                        ]);

                },
                'format' => 'raw',
            ],
        ];

        if (\Yii::$app->shop->shopTypePrices) {
            foreach (\Yii::$app->shop->shopTypePrices as $shopTypePrice) {
                $columns[] = [
                    'label'     => $shopTypePrice->name,
                    'class'     => \yii\grid\DataColumn::className(),
                    'attribute' => 'price.'.$shopTypePrice->id,
                    'value'     => function (\skeeks\cms\models\CmsContentElement $model) use ($shopTypePrice) {
                        $shopProduct = \skeeks\cms\shop\models\ShopProduct::getInstanceByContentElement($model);
                        if ($shopProduct) {
                            if ($shopProductPrice = $shopProduct->getShopProductPrices()
                                ->andWhere(['type_price_id' => $shopTypePrice->id])->one()
                            ) {
                                return (string) $shopProductPrice->money;
                            }
                        }

                        return null;
                    },
                ];
            }
        }

        $typeColumn = //TODO: показывать только для контента с предложениями
            [
                'class' => \yii\grid\DataColumn::className(),
                'label' => 'Тип товара',
                'value' => function (\skeeks\cms\shop\models\ShopCmsContentElement $shopCmsContentElement) {
                    if ($shopCmsContentElement->shopProduct) {
                        return \yii\helpers\ArrayHelper::getValue(\skeeks\cms\shop\models\ShopProduct::possibleProductTypes(),
                            $shopCmsContentElement->shopProduct->product_type);
                    }
                },
            ];

        if ($cmsContent) {
            /**
             * @var $shopContent \skeeks\cms\shop\models\ShopContent
             */
            $shopContent = \skeeks\cms\shop\models\ShopContent::findOne(['content_id' => $cmsContent->id]);
            if ($shopContent) {
                if ($shopContent->childrenContent) {
                    $columns = \yii\helpers\ArrayHelper::merge([$typeColumn], $columns);
                }
            }

        }
        return $columns;
    }
    /**
     * @param CmsContent $cmsContent
     * @return array
     */
    static public function getColumnsByContent($cmsContent = null, $dataProvider = null)
    {
        $autoColumns = [];

        if (!$cmsContent) {
            return [];
        }

        $model = null;
        //$model = CmsContentElement::find()->where(['content_id' => $cmsContent->id])->limit(1)->one();

        if (!$model) {
            $model = new CmsContentElement([
                'content_id' => $cmsContent->id,
            ]);
        }

        if (is_array($model) || is_object($model)) {
            foreach ($model->toArray() as $name => $value) {
                $autoColumns[] = [
                    'attribute' => $name,
                    'visible'   => false,
                    'format'    => 'raw',
                    'class'     => \yii\grid\DataColumn::className(),
                    'value'     => function ($model, $key, $index) use ($name) {
                        if (is_array($model->{$name})) {
                            return implode(",", $model->{$name});
                        } else {
                            return $model->{$name};
                        }
                    },
                ];
            }

            $searchRelatedPropertiesModel = new \skeeks\cms\models\searchs\SearchRelatedPropertiesModel();
            $searchRelatedPropertiesModel->initProperties($cmsContent->cmsContentProperties);
            $searchRelatedPropertiesModel->load(\Yii::$app->request->get());
            if ($dataProvider) {
                $searchRelatedPropertiesModel->search($dataProvider);
            }

            /**
             * @var $model \skeeks\cms\models\CmsContentElement
             */
            if ($model->relatedPropertiesModel) {
                $autoColumns = ArrayHelper::merge($autoColumns,
                    GridViewStandart::getColumnsByRelatedPropertiesModel($model->relatedPropertiesModel,
                        $searchRelatedPropertiesModel));
            }
        }

        return $autoColumns;
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
        $actions = ArrayHelper::merge(parent::actions(),
            [
                'index' =>
                    [
                        "modelSearchClassName" => ShopCmsContentElementSearch::className(),
                    ],

                "create" => ["callback" => [$this, 'create']],
                "update" => ["callback" => [$this, 'update']],


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

                "activate-multi" =>
                    [
                        'class'        => AdminMultiModelEditAction::className(),
                        "name"         => \Yii::t('skeeks/shop/app', 'Activate'),
                        "eachCallback" => [$this, 'eachMultiActivate'],
                    ],

                "inActivate-multi" =>
                    [
                        'class'        => AdminMultiModelEditAction::className(),
                        "name"         => \Yii::t('skeeks/shop/app', 'Deactivate'),
                        "eachCallback" => [$this, 'eachMultiInActivate'],
                    ],

                "change-tree-multi" =>
                    [
                        'class'        => AdminMultiDialogModelEditAction::class,
                        "name"         => \Yii::t('skeeks/shop/app', 'The main section'),
                        "viewDialog"   => "@skeeks/cms/views/admin-cms-content-element/change-tree-form",
                        "eachCallback" => [
                            \Yii::$app->createController('/cms/admin-cms-content-element')[0],
                            'eachMultiChangeTree',
                        ],
                    ],

                "change-trees-multi" =>
                    [
                        'class'        => AdminMultiDialogModelEditAction::class,
                        "name"         => \Yii::t('skeeks/shop/app', 'Related topics'),
                        "viewDialog"   => "@skeeks/cms/views/admin-cms-content-element/change-trees-form",
                        "eachCallback" => [
                            \Yii::$app->createController('/cms/admin-cms-content-element')[0],
                            'eachMultiChangeTrees',
                        ],
                    ],

                "rp" =>
                    [
                        'class'        => AdminMultiDialogModelEditAction::class,
                        "name"         => \Yii::t('skeeks/shop/app', 'Properties'),
                        "viewDialog"   => "@skeeks/cms/views/admin-cms-content-element/multi-rp",
                        "eachCallback" => [
                            \Yii::$app->createController('/cms/admin-cms-content-element')[0],
                            'eachRelatedProperties',
                        ],
                    ],

                "to-offer" =>
                    [
                        'class'        => AdminMultiDialogModelEditAction::class,
                        "name"         => "Привязать к общему",
                        "viewDialog"   => "@skeeks/cms/shop/views/admin-cms-content-element/to-offer",
                        "eachCallback" => [
                            $this,
                            'eachToOffer',
                        ],
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
        $productPrices = [];

        $modelClassName = $this->modelClassName;
        $model = new $modelClassName();

        $model->loadDefaultValues();

        if ($content_id = \Yii::$app->request->get("content_id")) {
            $contentModel = \skeeks\cms\models\CmsContent::findOne($content_id);
            $model->content_id = $content_id;
        }

        $relatedModel = $model->relatedPropertiesModel;
        $shopProduct = new ShopProduct();

        $shopProduct->loadDefaultValues();

        $baseProductPrice = new ShopProductPrice([
            'type_price_id' => \Yii::$app->shop->baseTypePrice->id,
            'currency_code' => \Yii::$app->money->currencyCode,
        ]);

        $shopProduct->baseProductPriceCurrency = \Yii::$app->money->currencyCode;

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


        if ($rr->isRequestPjaxPost()) {
            if (!\Yii::$app->request->post($this->notSubmitParam)) {
                $model->load(\Yii::$app->request->post());
                $relatedModel->load(\Yii::$app->request->post());
                $shopProduct->load(\Yii::$app->request->post());

                if ($model->save() && $relatedModel->save()) {
                    $shopProduct->id = $model->id;
                    $shopProduct->save();

                    $shopProduct->getBaseProductPriceValue();

                    $baseProductPrice = $shopProduct->baseProductPrice;

                    \Yii::$app->getSession()->setFlash('success', \Yii::t('skeeks/shop/app', 'Saved'));

                    if (\Yii::$app->request->post('submit-btn') == 'apply') {
                        return $this->redirect(
                            UrlHelper::constructCurrent()->setCurrentRef()->enableAdmin()->setRoute($this->modelDefaultAction)->normalizeCurrentRoute()
                                ->addData([$this->requestPkParamName => $model->{$this->modelPkAttribute}])
                                ->toString()
                        );
                    } else {
                        return $this->redirect(
                            $this->url
                        );
                    }

                }
            }

        }

        return $this->render('_form', [
            'model'            => $model,
            'relatedModel'     => $relatedModel,
            'shopProduct'      => $shopProduct,
            'productPrices'    => $productPrices,
            'baseProductPrice' => $baseProductPrice,
        ]);
    }
    public function update($adminAction)
    {
        /**
         * @var $model CmsContentElement
         */
        $model = $this->model;
        $relatedModel = $model->relatedPropertiesModel;
        $shopProduct = ShopProduct::find()->where(['id' => $model->id])->one();

        $productPrices = [];

        if (!$shopProduct) {

            $shopProduct = new ShopProduct([
                'id' => $model->id,
            ]);

            $shopProduct->save();

        } else {
            if ($typePrices = ShopTypePrice::find()
                ->where(['!=', 'def', Cms::BOOL_Y])
                ->orderBy(['priority' => SORT_ASC])->all()
            ) {
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

        if ($rr->isRequestPjaxPost()) {
            if (!\Yii::$app->request->post($this->notSubmitParam)) {
                $model->load(\Yii::$app->request->post());
                $relatedModel->load(\Yii::$app->request->post());
                $shopProduct->load(\Yii::$app->request->post());

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


                    \Yii::$app->getSession()->setFlash('success', \Yii::t('skeeks/shop/app', 'Saved'));

                    if (\Yii::$app->request->post('submit-btn') == 'apply') {
                    } else {

                        return $this->redirect(
                            $this->url
                        );
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


        return $this->render('_form', [
            'model'            => $model,
            'relatedModel'     => $relatedModel,
            'shopProduct'      => $shopProduct,
            'productPrices'    => $productPrices,
            'baseProductPrice' => $shopProduct->getBaseProductPrice()->one(),
        ]);
    }
    /**
     * @return string
     */
    public function getPermissionName()
    {
        $unique = parent::getPermissionName();

        if ($content = $this->content) {
            $unique = $unique."__".$content->id;
        }

        return $unique;
    }
    /**
     * @return CmsContent|static
     */
    public function getContent()
    {
        if ($this->_content === null) {
            if ($this->model) {
                $this->_content = $this->model->cmsContent;
                return $this->_content;
            }

            if (\Yii::$app instanceof Application && \Yii::$app->request->get('content_id')) {
                $content_id = \Yii::$app->request->get('content_id');

                $dependency = new TagDependency([
                    'tags' =>
                        [
                            (new CmsContent())->getTableCacheTag(),
                        ],
                ]);

                $this->_content = CmsContent::getDb()->cache(function ($db) use ($content_id) {
                    return CmsContent::find()->where([
                        "id" => $content_id,
                    ])->one();
                }, null, $dependency);

                return $this->_content;
            }
        }

        return $this->_content;
    }
    /**
     * @param $content
     * @return $this
     */
    public function setContent($content)
    {
        $this->_content = $content;
        return $this;
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
