<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 15.05.2015
 */
namespace skeeks\cms\shop\controllers;

use skeeks\cms\components\Cms;
use skeeks\cms\helpers\RequestResponse;
use skeeks\cms\helpers\UrlHelper;
use skeeks\cms\models\CmsContent;
use skeeks\cms\models\CmsContentElement;
use skeeks\cms\models\CmsContentType;
use skeeks\cms\modules\admin\actions\AdminAction;
use skeeks\cms\modules\admin\actions\modelEditor\AdminModelEditorAction;
use skeeks\cms\modules\admin\actions\modelEditor\AdminModelEditorCreateAction;
use skeeks\cms\modules\admin\actions\modelEditor\AdminMultiDialogModelEditAction;
use skeeks\cms\modules\admin\actions\modelEditor\AdminMultiModelEditAction;
use skeeks\cms\modules\admin\actions\modelEditor\AdminOneModelEditAction;
use skeeks\cms\modules\admin\controllers\AdminController;
use skeeks\cms\modules\admin\controllers\AdminModelEditorController;
use skeeks\cms\modules\admin\traits\AdminModelEditorStandartControllerTrait;
use skeeks\cms\shop\models\ShopCmsContentElement;
use skeeks\cms\shop\models\ShopProduct;
use skeeks\cms\shop\models\ShopProductPrice;
use skeeks\cms\shop\models\ShopTypePrice;
use Yii;
use skeeks\cms\models\User;
use skeeks\cms\models\searchs\User as UserSearch;
use yii\base\ActionEvent;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;

/**
 * Class AdminCmsContentTypeController
 * @package skeeks\cms\controllers
 */
class AdminCmsContentElementController extends AdminModelEditorController
{
    use AdminModelEditorStandartControllerTrait;

    public function init()
    {
        $this->name                     = \skeeks\cms\shop\Module::t('app', 'Elements');
        $this->modelShowAttribute       = "name";
        $this->modelClassName           = ShopCmsContentElement::className();

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
                    "dataProviderCallback" => function(ActiveDataProvider $dataProvider)
                    {
                        $query = $dataProvider->query;
                        /**
                         * @var ActiveQuery $query
                         */
                        //$query->select(['app_company.*', 'count(`app_company_officer_user`.`id`) as countOfficer']);

                        $query->with('image');
                        $query->with('cmsTree');
                        $query->with('cmsContentElementTrees');
                        $query->with('cmsContent');
                        $query->with('relatedProperties');
                        $query->with('relatedElementProperties');
                        $query->with('cmsContentElementTrees.tree');

                        $query->with('shopProduct');
                        $query->with('shopProduct.baseProductPrice');
                        //$query->with('shopProduct.minProductPrice');
                    },
                ],

                "create" =>
                [
                    'class'         => AdminModelEditorCreateAction::className(),
                    "callback"      => [$this, 'create'],
                ],

                "update" =>
                [
                    'class'         => AdminOneModelEditAction::className(),
                    "callback"      => [$this, 'update'],
                ],

                /*'settings' =>
                [
                    'class'         => AdminModelEditorAction::className(),
                    'name'          => \skeeks\cms\shop\Module::t('app', 'Settings'),
                    "icon"          => "glyphicon glyphicon-cog",
                ],*/

                "activate-multi" =>
                [
                    'class' => AdminMultiModelEditAction::className(),
                    "name" => \skeeks\cms\shop\Module::t('app', 'Activate'),
                    //"icon"              => "glyphicon glyphicon-trash",
                    "eachCallback" => [$this, 'eachMultiActivate'],
                ],

                "inActivate-multi" =>
                [
                    'class' => AdminMultiModelEditAction::className(),
                    "name" => \skeeks\cms\shop\Module::t('app', 'Deactivate'),
                    //"icon"              => "glyphicon glyphicon-trash",
                    "eachCallback" => [$this, 'eachMultiInActivate'],
                ],


                "change-tree-multi" =>
                [
                    'class'             => AdminMultiDialogModelEditAction::className(),
                    "name"              => \skeeks\cms\shop\Module::t('app', 'The main section'),
                    "viewDialog"        => "@skeeks/cms/views/admin-cms-content-element/change-tree-form",
                    "eachCallback"      => [\Yii::$app->createController('/cms/admin-cms-content-element')[0], 'eachMultiChangeTree'],
                ],

                "change-trees-multi" =>
                [
                    'class'             => AdminMultiDialogModelEditAction::className(),
                    "name"              => \skeeks\cms\shop\Module::t('app', 'Related topics'),
                    "viewDialog"        => "@skeeks/cms/views/admin-cms-content-element/change-trees-form",
                    "eachCallback"      => [\Yii::$app->createController('/cms/admin-cms-content-element')[0], 'eachMultiChangeTrees'],
                ],
            ]
        );

        if (isset($actions['related-properties']))
        {
            unset($actions['related-properties']);
        }

        if (isset($actions['shop']))
        {
            unset($actions['shop']);
        }

        return $actions;
    }





    public function create(AdminAction $adminAction)
    {
        $modelClassName = $this->modelClassName;
        $model          = new $modelClassName();


        if ($content_id = \Yii::$app->request->get("content_id"))
        {
            $contentModel       = \skeeks\cms\models\CmsContent::findOne($content_id);
            $model->content_id  = $content_id;
        }

        $relatedModel = $model->relatedPropertiesModel;
        $shopProduct = new ShopProduct();

        $baseProductPrice = new ShopProductPrice([
            'type_price_id' => \Yii::$app->shop->baseTypePrice->id,
            'currency_code' => \Yii::$app->money->currencyCode
        ]);

        $shopProduct->baseProductPriceCurrency = \Yii::$app->money->currencyCode;

        $rr = new RequestResponse();

        if (\Yii::$app->request->isAjax && !\Yii::$app->request->isPjax)
        {
            $model->load(\Yii::$app->request->post());
            $relatedModel->load(\Yii::$app->request->post());
            $shopProduct->load(\Yii::$app->request->post());

            return \yii\widgets\ActiveForm::validateMultiple([
                $model, $relatedModel, $shopProduct
            ]);
        }


        if ($rr->isRequestPjaxPost())
        {
            $model->load(\Yii::$app->request->post());
            $relatedModel->load(\Yii::$app->request->post());
            $shopProduct->load(\Yii::$app->request->post());

            if ($model->save() && $relatedModel->save())
            {
                $shopProduct->id = $model->id;
                $shopProduct->save();

                $shopProduct->getBaseProductPriceValue();

                $baseProductPrice = $shopProduct->baseProductPrice;

                \Yii::$app->getSession()->setFlash('success', \Yii::t('app','Saved'));

                if (\Yii::$app->request->post('submit-btn') == 'apply')
                {
                    return $this->redirect(
                        UrlHelper::constructCurrent()->setCurrentRef()->enableAdmin()->setRoute($this->modelDefaultAction)->normalizeCurrentRoute()
                            ->addData([$this->requestPkParamName => $model->{$this->modelPkAttribute}])
                            ->toString()
                    );
                } else
                {
                    return $this->redirect(
                        $this->indexUrl
                    );
                }

            } else
            {
                \Yii::$app->getSession()->setFlash('error', \Yii::t('app','Could not save'));
            }
        }

        return $this->render('_form', [
            'model'             => $model,
            'relatedModel'      => $relatedModel,
            'shopProduct'       => $shopProduct,
            'productPrices'     => $productPrices,
            'baseProductPrice'  => $baseProductPrice
        ]);
    }

    public function update(AdminAction $adminAction)
    {
        /**
         * @var $model CmsContentElement
         */
        $model                              = $this->model;
        $relatedModel                       = $model->relatedPropertiesModel;
        $shopProduct                        = ShopProduct::find()->where(['id' => $model->id])->one();

        $productPrices = [];
        if (!$shopProduct)
        {
            $shopProduct = new ShopProduct([
                'id' => $model->id
            ]);
        } else
        {
            if ($typePrices = ShopTypePrice::find()->where(['!=', 'def', Cms::BOOL_Y])->all())
            {
                foreach ($typePrices as $typePrice)
                {
                    $productPrice = ShopProductPrice::find()->where([
                        'product_id'    => $shopProduct->id,
                        'type_price_id' => $typePrice->id
                    ])->one();

                    if (!$productPrice)
                    {
                        $productPrice = new ShopProductPrice([
                            'product_id'    => $shopProduct->id,
                            'type_price_id' => $typePrice->id
                        ]);
                    }

                    if ($post = \Yii::$app->request->post())
                    {
                        $data = ArrayHelper::getValue($post, 'prices.' . $typePrice->id);
                        $productPrice->load($data, "");
                    }

                    $productPrices[] = $productPrice;
                }
            }
        }



        $rr = new RequestResponse();

        if (\Yii::$app->request->isAjax && !\Yii::$app->request->isPjax)
        {
            $model->load(\Yii::$app->request->post());
            $relatedModel->load(\Yii::$app->request->post());
            $shopProduct->load(\Yii::$app->request->post());

            return \yii\widgets\ActiveForm::validateMultiple([
                $model, $relatedModel, $shopProduct
            ]);
        }

        if ($rr->isRequestPjaxPost())
        {
            $model->load(\Yii::$app->request->post());
            $relatedModel->load(\Yii::$app->request->post());
            $shopProduct->load(\Yii::$app->request->post());

            /**
             * @var $productPrice ShopProductPrice
             */
            foreach ($productPrices as $productPrice)
            {
                if ($productPrice->save())
                {

                } else
                {
                    \Yii::$app->getSession()->setFlash('error', \skeeks\cms\shop\Module::t('app', 'Check the correctness of the prices'));
                }

            }

            if ($model->save() && $relatedModel->save() && $shopProduct->save())
            {
                \Yii::$app->getSession()->setFlash('success', \Yii::t('app','Saved'));

                if (\Yii::$app->request->post('submit-btn') == 'apply')
                {

                } else
                {
                    return $this->redirect(
                        $this->indexUrl
                    );
                }

                $model->refresh();

            } else
            {
                $errors = [];

                if ($model->getErrors())
                {
                    foreach ($model->getErrors() as $error)
                    {
                        $errors[] = implode(', ', $error);
                    }
                }

                \Yii::$app->getSession()->setFlash('error', \Yii::t('app','Could not save') . $errors);
            }
        }

        return $this->render('_form', [
            'model'           => $model,
            'relatedModel'    => $relatedModel,
            'shopProduct'     => $shopProduct,
            'productPrices'   => $productPrices,
            'baseProductPrice'  => $shopProduct->baseProductPrice
        ]);
    }



    public $content;

    /**
     * @return string
     */
    public function getPermissionName()
    {
        if ($this->content)
        {
            return $this->content->adminPermissionName;
        }

        return parent::getPermissionName();
    }

    public function beforeAction($action)
    {
        if ($content_id = \Yii::$app->request->get('content_id'))
        {
            $this->content = CmsContent::findOne($content_id);
        }

        if ($this->content)
        {
            if ($this->content->name_meny)
            {
                $this->name = $this->content->name_meny;
            }
        }

        return parent::beforeAction($action);
    }


    /**
     * @return string
     */
    public function getIndexUrl()
    {
        return UrlHelper::construct($this->id . '/' . $this->action->id, [
            'content_id' => \Yii::$app->request->get('content_id')
        ])->enableAdmin()->setRoute('index')->normalizeCurrentRoute()->toString();
    }


    /**
     * @param CmsContent $cmsContent
     * @return array
     */
    static public function getColumnsByContent($cmsContent = null, $dataProvider = null)
    {
        $autoColumns = [];

        if (!$cmsContent)
        {
            return [];
        }

        $model = CmsContentElement::find()->where(['content_id' => $cmsContent->id])->one();

        if (!$model)
        {
            $model = new CmsContentElement([
                'content_id' => $cmsContent->id
            ]);
        }

        if (is_array($model) || is_object($model))
        {
            foreach ($model as $name => $value) {
                $autoColumns[] = [
                    'attribute' => $name,
                    'visible' => false,
                    'format' => 'raw',
                    'class' => \yii\grid\DataColumn::className(),
                    'value' => function($model, $key, $index) use ($name)
                    {
                        if (is_array($model->{$name}))
                        {
                            return implode(",", $model->{$name});
                        } else
                        {
                            return $model->{$name};
                        }
                    },
                ];
            }

            $searchRelatedPropertiesModel = new \skeeks\cms\models\searchs\SearchRelatedPropertiesModel();
            $searchRelatedPropertiesModel->initCmsContent($cmsContent);
            $searchRelatedPropertiesModel->load(\Yii::$app->request->get());
            if ($dataProvider)
            {
                $searchRelatedPropertiesModel->search($dataProvider);
            }

             /**
             * @var $model \skeeks\cms\models\CmsContentElement
             */
            if ($model->relatedPropertiesModel)
            {
                foreach ($model->relatedPropertiesModel->toArray($model->relatedPropertiesModel->attributes()) as $name => $value) {


                    $property = $model->relatedPropertiesModel->getRelatedProperty($name);
                    $filter = '';

                    if ($property->property_type == \skeeks\cms\relatedProperties\PropertyType::CODE_ELEMENT)
                    {
                        $propertyType = $property->createPropertyType();
                            $options = \skeeks\cms\models\CmsContentElement::find()->active()->andWhere([
                                'content_id' => $propertyType->content_id
                            ])->all();

                            $items = \yii\helpers\ArrayHelper::merge(['' => ''], \yii\helpers\ArrayHelper::map(
                                $options, 'id', 'name'
                            ));

                        $filter = \yii\helpers\Html::activeDropDownList($searchRelatedPropertiesModel, $name, $items, ['class' => 'form-control']);

                    } else if ($property->property_type == \skeeks\cms\relatedProperties\PropertyType::CODE_LIST)
                    {
                        $items = \yii\helpers\ArrayHelper::merge(['' => ''], \yii\helpers\ArrayHelper::map(
                            $property->enums, 'id', 'value'
                        ));

                        $filter = \yii\helpers\Html::activeDropDownList($searchRelatedPropertiesModel, $name, $items, ['class' => 'form-control']);

                    } else if ($property->property_type == \skeeks\cms\relatedProperties\PropertyType::CODE_STRING)
                    {
                        $filter = \yii\helpers\Html::activeTextInput($searchRelatedPropertiesModel, $name, [
                            'class' => 'form-control'
                        ]);
                    }
                    else if ($property->property_type == \skeeks\cms\relatedProperties\PropertyType::CODE_NUMBER)
                    {
                        $filter = "<div class='row'><div class='col-md-6'>" . \yii\helpers\Html::activeTextInput($searchRelatedPropertiesModel, $searchRelatedPropertiesModel->getAttributeNameRangeFrom($name), [
                                        'class' => 'form-control',
                                        'placeholder' => 'от'
                                    ]) . "</div><div class='col-md-6'>" .
                                        \yii\helpers\Html::activeTextInput($searchRelatedPropertiesModel, $searchRelatedPropertiesModel->getAttributeNameRangeTo($name), [
                                        'class' => 'form-control',
                                        'placeholder' => 'до'
                                    ]) . "</div></div>"
                                ;
                    }

                    $autoColumns[] = [
                        'attribute' => $name,
                        'label' => \yii\helpers\ArrayHelper::getValue($model->relatedPropertiesModel->attributeLabels(), $name),
                        'visible' => false,
                        'format' => 'raw',
                        'filter' => $filter,
                        'class' => \yii\grid\DataColumn::className(),
                        'value' => function($model, $key, $index) use ($name)
                        {
                            /**
                             * @var $model \skeeks\cms\models\CmsContentElement
                             */
                            $value = $model->relatedPropertiesModel->getSmartAttribute($name);
                            if (is_array($value))
                            {
                                return implode(",", $value);
                            } else
                            {
                                return $value;
                            }
                        },
                    ];
                }
            }
        }

        return $autoColumns;
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
                'value'     => function(\skeeks\cms\models\CmsContentElement $model)
                {
                    if (!$model->cmsTree)
                    {
                        return null;
                    }

                    $path = [];

                    if ($model->cmsTree->parents)
                    {
                        foreach ($model->cmsTree->parents as $parent)
                        {
                            if ($parent->isRoot())
                            {
                                $path[] =  "[" . $parent->site->name . "] " . $parent->name;
                            } else
                            {
                                $path[] =  $parent->name;
                            }
                        }
                    }
                    $path = implode(" / ", $path);
                    return "<small><a href='{$model->cmsTree->url}' target='_blank' data-pjax='0'>{$path} / {$model->cmsTree->name}</a></small>";
                },
                'format'    => 'raw',
                'filter' => \skeeks\cms\helpers\TreeOptions::getAllMultiOptions(),
                'attribute' => 'tree_id'
            ],

            [
                'class'     => \yii\grid\DataColumn::className(),
                'value'     => function(\skeeks\cms\models\CmsContentElement $model)
                {
                    $result = [];

                    if ($model->cmsContentElementTrees)
                    {
                        foreach ($model->cmsContentElementTrees as $contentElementTree)
                        {
                            $site = $contentElementTree->tree->root->site;
                            $result[] = "<small><a href='{$contentElementTree->tree->url}' target='_blank' data-pjax='0'>[{$site->name}]/.../{$contentElementTree->tree->name}</a></small>";

                        }
                    }

                    return implode('<br />', $result);

                },
                'format' => 'raw',
                'label' => \skeeks\cms\shop\Module::t('app', 'Advanced Topics'),
            ],

            [
                'attribute' => 'active',
                'class' => \skeeks\cms\grid\BooleanColumn::className()
            ],


            [
                'label' => \skeeks\cms\shop\Module::t('app', 'Base price'),
                'class' => \yii\grid\DataColumn::className(),
                'value' => function(\skeeks\cms\models\CmsContentElement $model)
                {
                    $shopProduct = \skeeks\cms\shop\models\ShopProduct::getInstanceByContentElement($model);
                    if ($shopProduct)
                    {
                        return \Yii::$app->money->intlFormatter()->format($shopProduct->baseProductPrice->money);
                    }

                    return null;
                }
            ],

            [
                'class'     => \yii\grid\DataColumn::className(),
                'value'     => function(\skeeks\cms\models\CmsContentElement $model)
                {

                    return \yii\helpers\Html::a('<i class="glyphicon glyphicon-arrow-right"></i>', $model->absoluteUrl, [
                        'target' => '_blank',
                        'title' => \skeeks\cms\shop\Module::t('app', 'View online (opens new window)'),
                        'data-pjax' => '0',
                        'class' => 'btn btn-default btn-sm'
                    ]);

                },
                'format' => 'raw'
            ]
        ];

        $typeColumn = //TODO: показывать только для контента с предложениями
        [
            'class'     => \yii\grid\DataColumn::className(),
            'label'     => 'Тип товара',
            'value'     => function(\skeeks\cms\shop\models\ShopCmsContentElement $shopCmsContentElement)
            {
                if ($shopCmsContentElement->shopProduct)
                {
                    return \yii\helpers\ArrayHelper::getValue(\skeeks\cms\shop\models\ShopProduct::possibleProductTypes(), $shopCmsContentElement->shopProduct->product_type);
                }
            }
        ];

        if ($cmsContent)
        {
            /**
             * @var $shopContent \skeeks\cms\shop\models\ShopContent
             */
            $shopContent = \skeeks\cms\shop\models\ShopContent::findOne(['content_id' => $cmsContent->id]);
            if ($shopContent)
            {
                if ($shopContent->childrenContent)
                {
                    $columns = \yii\helpers\ArrayHelper::merge([$typeColumn], $columns);
                }
            }

        }
        return $columns;
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

}
