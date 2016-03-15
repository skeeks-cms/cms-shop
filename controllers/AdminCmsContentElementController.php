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

                $shopProduct->getBaseProductPriceValue()

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

}
