<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 15.05.2015
 */

namespace skeeks\cms\shop\controllers;

use skeeks\cms\backend\actions\BackendModelMultiDialogEditAction;
use skeeks\cms\backend\actions\BackendModelUpdateAction;
use skeeks\cms\components\Cms;
use skeeks\cms\helpers\RequestResponse;
use skeeks\cms\helpers\UrlHelper;
use skeeks\cms\IHasUrl;
use skeeks\cms\models\CmsContent;
use skeeks\cms\models\CmsContentElement;
use skeeks\cms\modules\admin\actions\AdminAction;
use skeeks\cms\modules\admin\actions\modelEditor\AdminModelEditorAction;
use skeeks\cms\queryfilters\filters\FilterField;
use skeeks\cms\queryfilters\filters\NumberFilterField;
use skeeks\cms\queryfilters\QueryFiltersEvent;
use skeeks\cms\shop\models\ShopCmsContentElement;
use skeeks\cms\shop\models\ShopProduct;
use skeeks\cms\shop\models\ShopProductPrice;
use skeeks\cms\shop\models\ShopTypePrice;
use skeeks\yii2\form\fields\BoolField;
use skeeks\yii2\form\fields\SelectField;
use yii\base\DynamicModel;
use yii\base\Event;
use yii\db\ActiveQuery;
use yii\db\Exception;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/**
 * @property CmsContent $content
 *
 * Class AdminCmsContentTypeController
 * @package skeeks\cms\controllers
 */
class AdminCmsContentElementController extends \skeeks\cms\controllers\AdminCmsContentElementController
{
    public $notSubmitParam = 'sx-not-submit';

    public $modelClassName = ShopCmsContentElement::class;
    public $modelShowAttribute = "name";

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

        $shopColumns["shop.quantity"] = [
            'attribute' => "shop.quantity",
            'label'     => 'Количество [магазин]',
            'format'    => 'raw',
            'value'     => function (ShopCmsContentElement $shopCmsContentElement) {
                if ($shopCmsContentElement->shopProduct) {
                    return $shopCmsContentElement->shopProduct->quantity." ".$shopCmsContentElement->shopProduct->measure->symbol_rus;
                }
                return "—";
            },
        ];
        $sortAttributes["shop.quantity"] = [
            'asc'  => ['sp.quantity' => SORT_ASC],
            'desc' => ['sp.quantity' => SORT_DESC],
            'name' => 'Количество [магазин]',
        ];

        $visibleColumns[] = "shop.product_type";
        $visibleColumns[] = "shop.quantity";

        if (\Yii::$app->shop->shopTypePrices) {

            foreach (\Yii::$app->shop->shopTypePrices as $shopTypePrice) {

                $shopColumns["shop.price{$shopTypePrice->id}"] = [
                    'label'     => $shopTypePrice->name." [магазин]",
                    'attribute' => 'shop.price'.$shopTypePrice->id,
                    'format' => 'raw',
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

            $shopColumns["shop.priceDefult"] = [
                'label'     => "Все цены [магазин]",
                'attribute' => 'shop.priceDefult',
                'format' => 'raw',
                'value'     => function (\skeeks\cms\models\CmsContentElement $model) {
                    $result = [];
                    foreach (\Yii::$app->shop->shopTypePrices as $shopTypePrice)
                    {
                        if ($shopTypePrice->isDefault) {
                            $defaultId = $shopTypePrice->id;
                        }
                        $shopProduct = \skeeks\cms\shop\models\ShopProduct::getInstanceByContentElement($model);
                        if ($shopProduct) {
                            if ($shopProductPrice = $shopProduct->getShopProductPrices()
                                ->andWhere(['type_price_id' => $shopTypePrice->id])->one()
                            ) {
                                $result[] = "<span title='{$shopTypePrice->name}'>" . (string)$shopProductPrice->money . "</span>";
                            } else {
                                $result[] = "<span title='{$shopTypePrice->name}'>" . " — " . "</span>";;
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

        $filterFields['shop_quantity'] = [
            'class'    => NumberFilterField::class,
            'label'    => 'Количество [магазин]',
            'filterAttribute'    => 'sp.quantity',
            /*'on apply' => function (QueryFiltersEvent $e) {
                /**
                 * @var $query ActiveQuery
                $query = $e->dataProvider->query;

                if ($e->field->value) {
                    $query->andWhere(['sp.product_type' => $e->field->value]);
                }
            },*/
        ];

        $filterFieldsLabels['shop_product_type'] = 'Тип товара [магазин]';
        $filterFieldsLabels['shop_quantity'] = 'Количество [магазин]';

        $filterFieldsRules[] = ['shop_product_type', 'safe'];
        $filterFieldsRules[] = ['shop_quantity', 'safe'];

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
            'model'            => $model,
            'relatedModel'     => $relatedModel,
            'shopProduct'      => $shopProduct,
            'productPrices'    => $productPrices,
            'baseProductPrice' => $baseProductPrice,

            'is_saved' => $is_saved,
            'submitBtn' => \Yii::$app->request->post('submit-btn'),
            'redirect' => $redirect,
        ]);
    }
    public function update($adminAction)
    {
        $is_saved = false;
        $redirect = "";

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
                //->where(['!=', 'def', Cms::BOOL_Y])
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


        return $this->render('_form', [
            'model'            => $model,
            'relatedModel'     => $relatedModel,
            'shopProduct'      => $shopProduct,
            'productPrices'    => $productPrices,
            'baseProductPrice' => $shopProduct->getBaseProductPrice()->one(),

            'is_saved' => $is_saved,
            'submitBtn' => \Yii::$app->request->post('submit-btn'),
            'redirect' => $redirect,
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
