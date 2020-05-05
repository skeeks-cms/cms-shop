<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

namespace skeeks\cms\shop\controllers;

use skeeks\cms\backend\controllers\BackendModelStandartController;
use skeeks\cms\backend\grid\DefaultActionColumn;
use skeeks\cms\grid\BooleanColumn;
use skeeks\cms\helpers\RequestResponse;
use skeeks\cms\helpers\UrlHelper;
use skeeks\cms\models\CmsAgent;
use skeeks\cms\shop\models\ShopPaySystem;
use yii\base\Event;
use yii\grid\DataColumn;
use yii\helpers\ArrayHelper;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class AdminPaySystemController extends BackendModelStandartController
{
    public $notSubmitParam = 'sx-not-submit';

    public function init()
    {
        $this->name = \Yii::t('skeeks/shop/app', 'Payment systems');
        $this->modelShowAttribute = "name";
        $this->modelClassName = ShopPaySystem::class;

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
                        "filters" => [
                            'visibleFilters' => [
                                'name',
                            ],
                        ],

                        'grid' => [
                            'on init' => function (Event $e) {
                                /**
                                 * @var $dataProvider ActiveDataProvider
                                 * @var $query ActiveQuery
                                 */
                                $query = $e->sender->dataProvider->query;
        
                                $query->andWhere(['cms_site_id' => \Yii::$app->skeeks->site->id]);
                            },
                                    
                            'defaultOrder' => [
                                'priority' => SORT_ASC,
                            ],

                            'visibleColumns' => [
                                'checkbox',
                                'actions',
                                'name',
                                'is_active',
                                'priority',
                            ],

                            "columns" => [
                                'name'         => [
                                    'class' => DefaultActionColumn::class,
                                    'viewAttribute' => 'asText',
                                ],
                                'priority',

                                [
                                    'class'     => DataColumn::class,
                                    'attribute' => "personTypeIds",
                                    'filter'    => false,
                                    'value'     => function (ShopPaySystem $model) {
                                        return implode(", ", ArrayHelper::map($model->personTypes, 'id', 'name'));
                                    },
                                ],

                                'is_active' => [
                                    'class'     => BooleanColumn::class,
                                    'attribute' => "is_active",
                                ],
                            ],
                        ],
                    ],

                'create' =>
                    [
                        'callback' => [$this, 'create'],
                    ],

                'update' =>
                    [
                        'callback' => [$this, 'update'],
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

        if ($post = \Yii::$app->request->post()) {
            $model->load($post);
        }

        $handler = $model->handler;

        if ($handler) {
            if ($post = \Yii::$app->request->post()) {
                $handler->load($post);
            }
        }

        if ($rr->isRequestPjaxPost()) {
            if (!\Yii::$app->request->post($this->notSubmitParam)) {
                $handlerValid = true;
                if ($handler) {
                    $model->component_settings = $handler->toArray();
                    $handler->load(\Yii::$app->request->post());

                    $handlerValid = $handler->validate();
                }


                if ($model->load(\Yii::$app->request->post())
                    && $model->validate() && $handlerValid
                ) {
                    $model->save();

                    \Yii::$app->getSession()->setFlash('success', \Yii::t('skeeks/cms', 'Saved'));

                    return $this->redirect(
                        UrlHelper::constructCurrent()->setCurrentRef()->enableAdmin()->setRoute($this->modelDefaultAction)->normalizeCurrentRoute()
                            ->addData([$this->requestPkParamName => $model->{$this->modelPkAttribute}])
                            ->toString()
                    );
                } else {
                    \Yii::$app->getSession()->setFlash('error', \Yii::t('skeeks/cms', 'Could not save'));
                }
            }
        }

        return $this->render('_form', [
            'model'   => $model,
            'handler' => $handler,
        ]);
    }


    public function update()
    {
        $rr = new RequestResponse();

        $model = $this->model;

        if ($post = \Yii::$app->request->post()) {
            $model->load($post);
        }

        $handler = $model->handler;
        if ($handler) {
            if ($post = \Yii::$app->request->post()) {
                $handler->load($post);
            }
        }

        if ($rr->isRequestPjaxPost()) {
            if (!\Yii::$app->request->post($this->notSubmitParam)) {
                if ($rr->isRequestPjaxPost()) {
                    $handlerValid = true;
                    if ($handler) {
                        $model->component_settings = $handler->toArray();
                        $handler->load(\Yii::$app->request->post());

                        $handlerValid = $handler->validate();
                    }

                    if ($model->load(\Yii::$app->request->post())
                        && $model->validate() && $handlerValid
                    ) {
                        $model->save();

                        \Yii::$app->getSession()->setFlash('success', \Yii::t('app', 'Saved'));

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
        }

        return $this->render('_form', [
            'model'   => $model,
            'handler' => $handler,
        ]);
    }

}
