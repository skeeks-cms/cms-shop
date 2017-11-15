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
 * @property ShopPersonType $personType
 *
 * Class AdminTaxController
 * @package skeeks\cms\shop\controllers
 */
class AdminBuyerController extends AdminModelEditorController
{
    use AdminModelEditorStandartControllerTrait;

    public $notSubmitParam = 'sx-not-submit';

    public function init()
    {
        $this->name = \Yii::t('skeeks/shop/app', 'Buyers');
        $this->modelShowAttribute = "name";
        $this->modelClassName = ShopBuyer::className();

        parent::init();
    }

    /**
     * @var ShopPersonType
     */
    protected $_personType = null;

    public function getPersonType()
    {
        if ($this->_personType !== null) {
            return $this->_personType;
        }

        if ($person_type_id = \Yii::$app->request->get('person_type_id')) {
            $this->_personType = \skeeks\cms\shop\models\ShopPersonType::findOne($person_type_id);
        }

        return $this->_personType;
    }

    /**
     * @return string
     */
    public function getPermissionName()
    {
        $permissionName = parent::getPermissionName();

        if ($this->personType) {
            return $permissionName . "-" . $this->personType->id;
        }

        return $permissionName;
    }


    /**
     * @return string
     */
    public function getUrl()
    {
        return UrlHelper::construct($this->id . '/' . $this->action->id, [
            'person_type_id' => \Yii::$app->request->get('person_type_id')
        ])->enableAdmin()->setRoute('index')->normalizeCurrentRoute()->toString();
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
        /**
         * @var CmsContentProperty $model
         */
        $model = new $modelClass();
        $model->loadDefaultValues();

        if ($post = \Yii::$app->request->post()) {
            $model->load($post);
        }

        $handler = $model->relatedPropertiesModel;

        if ($handler) {
            if ($post = \Yii::$app->request->post()) {
                $handler->load($post);
            }
        }

        if ($rr->isRequestPjaxPost()) {
            if (!\Yii::$app->request->post($this->notSubmitParam)) {
                $handler->load(\Yii::$app->request->post());

                if ($model->load(\Yii::$app->request->post())
                    && $model->validate() && $handler->validate()) {
                    $model->save();
                    $handler->save();

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
            'model' => $model,
            'handler' => $handler,
        ]);
    }


    public function update()
    {
        $rr = new RequestResponse();
        /**
         * @var $model ShopBuyer
         */
        $model = $this->model;

        if ($post = \Yii::$app->request->post()) {
            $model->load($post);
        }

        $handler = $model->relatedPropertiesModel;
        if ($handler) {
            if ($post = \Yii::$app->request->post()) {
                $handler->load($post);
            }
        }

        if ($rr->isRequestPjaxPost()) {
            if (!\Yii::$app->request->post($this->notSubmitParam)) {
                if ($rr->isRequestPjaxPost()) {
                    $handler->load(\Yii::$app->request->post());

                    if ($model->load(\Yii::$app->request->post())
                        && $model->validate() && $handler->validate()) {
                        $model->save();
                        $handler->save();

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
            'model' => $model,
            'handler' => $handler,
        ]);
    }
}
