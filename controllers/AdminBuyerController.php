<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */
namespace skeeks\cms\shop\controllers;

use skeeks\cms\components\Cms;
use skeeks\cms\grid\BooleanColumn;
use skeeks\cms\grid\SiteColumn;
use skeeks\cms\grid\UserColumnData;
use skeeks\cms\helpers\RequestResponse;
use skeeks\cms\helpers\UrlHelper;
use skeeks\cms\models\CmsAgent;
use skeeks\cms\models\CmsContent;
use skeeks\cms\modules\admin\actions\modelEditor\AdminMultiModelEditAction;
use skeeks\cms\modules\admin\actions\modelEditor\AdminOneModelRelatedPropertiesAction;
use skeeks\cms\modules\admin\controllers\AdminModelEditorController;
use skeeks\cms\modules\admin\traits\AdminModelEditorStandartControllerTrait;
use skeeks\cms\shop\models\ShopBuyer;
use skeeks\cms\shop\models\ShopOrderStatus;
use skeeks\cms\shop\models\ShopPersonType;
use skeeks\cms\shop\models\ShopTax;
use skeeks\cms\shop\models\ShopVat;
use yii\grid\DataColumn;
use yii\helpers\ArrayHelper;

/**
 * Class AdminTaxController
 * @package skeeks\cms\shop\controllers
 */
class AdminBuyerController extends AdminModelEditorController
{
    use AdminModelEditorStandartControllerTrait;

    public $notSubmitParam = 'sx-not-submit';

    public function init()
    {
        $this->name                     = \Yii::t('skeeks/shop/app', 'Buyers');
        $this->modelShowAttribute       = "name";
        $this->modelClassName           = ShopBuyer::className();

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return ArrayHelper::merge(parent::actions(),
            [
                'index' =>
                [
                    "columns"      => [
                        'name',
                        [
                            'class' => UserColumnData::className(),
                            'attribute' => 'cms_user_id'
                        ],

                        [
                            'class' => DataColumn::className(),
                            'attribute' => 'shop_person_type_id',
                            'value' => function(ShopBuyer $model)
                            {
                                return $model->shopPersonType->name;
                            }
                        ]

                    ],
                ],

                'create' =>
                [
                    'callback'         => [$this, 'create'],
                ],

                'update' =>
                [
                    'callback'         => [$this, 'update'],
                ],
            ]
        );
    }

    public function create()
    {
        $rr = new RequestResponse();

        $modelClass = $this->modelClassName;
        /**
         * @var CmsContentProperty $model
         */
        $model = new $modelClass();
        $model->loadDefaultValues();

        if ($post = \Yii::$app->request->post())
        {
            $model->load($post);
        }

        $handler = $model->handler;

        if ($handler)
        {
            if ($post = \Yii::$app->request->post())
            {
                $handler->load($post);
            }
        }

        if ($rr->isRequestPjaxPost())
        {
            if (!\Yii::$app->request->post($this->notSubmitParam))
            {
                $model->component_settings = $handler->toArray();
                $handler->load(\Yii::$app->request->post());

                if ($model->load(\Yii::$app->request->post())
                    && $model->validate() && $handler->validate())
                {
                    $model->save();

                    \Yii::$app->getSession()->setFlash('success', \Yii::t('skeeks/cms','Saved'));

                    return $this->redirect(
                        UrlHelper::constructCurrent()->setCurrentRef()->enableAdmin()->setRoute($this->modelDefaultAction)->normalizeCurrentRoute()
                            ->addData([$this->requestPkParamName => $model->{$this->modelPkAttribute}])
                            ->toString()
                    );
                } else
                {
                    \Yii::$app->getSession()->setFlash('error', \Yii::t('skeeks/cms','Could not save'));
                }
            }
        }

        return $this->render('_form', [
            'model'     => $model,
            'handler'   => $handler,
        ]);
    }


    public function update()
    {
        $rr = new RequestResponse();

        $model = $this->model;

        if ($post = \Yii::$app->request->post())
        {
            $model->load($post);
        }

        $handler = $model->handler;
        if ($handler)
        {
            if ($post = \Yii::$app->request->post())
            {
                $handler->load($post);
            }
        }

        if ($rr->isRequestPjaxPost())
        {
            if (!\Yii::$app->request->post($this->notSubmitParam))
            {
                if ($rr->isRequestPjaxPost())
                {
                    $model->component_settings = $handler->toArray();
                    $handler->load(\Yii::$app->request->post());

                    if ($model->load(\Yii::$app->request->post())
                        && $model->validate() && $handler->validate())
                    {
                        $model->save();

                        \Yii::$app->getSession()->setFlash('success', \Yii::t('skeeks/cms','Saved'));

                        if (\Yii::$app->request->post('submit-btn') == 'apply')
                        {

                        } else
                        {
                            return $this->redirect(
                                $this->indexUrl
                            );
                        }

                        $model->refresh();

                    }
                }
            }
        }

        return $this->render('_form', [
            'model'     => $model,
            'handler'   => $handler,
        ]);
    }
}
