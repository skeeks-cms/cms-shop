<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 17.05.2015
 */

namespace skeeks\cms\shop\controllers;

use skeeks\cms\helpers\RequestResponse;
use skeeks\cms\helpers\UrlHelper;
use skeeks\cms\modules\admin\controllers\AdminModelEditorController;
use skeeks\cms\shop\models\ShopPersonTypeProperty;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * Class AdminPersonTypePropertyController
 * @package skeeks\cms\shop\controllers
 */
class AdminPersonTypePropertyController extends AdminModelEditorController
{
    public $notSubmitParam = 'sx-not-submit';

    public function init()
    {
        $this->name = \Yii::t('skeeks/shop/app', 'Control of properties payer');
        $this->modelShowAttribute = "name";
        $this->modelClassName = ShopPersonTypeProperty::className();

        parent::init();

    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return ArrayHelper::merge(parent::actions(),
            [
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
                $model->component_settings = $handler->toArray();
                $handler->load(\Yii::$app->request->post());

                if ($model->load(\Yii::$app->request->post())
                    && $model->validate() && $handler->validate()
                ) {
                    $model->save();

                    \Yii::$app->getSession()->setFlash('success', \Yii::t('skeeks/cms', 'Saved'));

                    return $this->redirect(
                        UrlHelper::constructCurrent()->setCurrentRef()->enableAdmin()->setRoute($this->modelDefaultAction)->normalizeCurrentRoute()
                            ->addData([$this->requestPkParamName => $model->{$this->modelPkAttribute}])
                            ->toString()
                    );
                } else {
                    \Yii::error(Json::encode($model->errors), self::className());
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
                    $model->component_settings = $handler->toArray();
                    $handler->load(\Yii::$app->request->post());

                    if ($model->load(\Yii::$app->request->post())
                        && $model->validate() && $handler->validate()
                    ) {
                        $model->save();

                        \Yii::$app->getSession()->setFlash('success', \Yii::t('skeeks/cms', 'Saved'));

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
