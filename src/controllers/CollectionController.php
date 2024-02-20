<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 21.09.2015
 */

namespace skeeks\cms\shop\controllers;

use skeeks\cms\base\Controller;
use skeeks\cms\shop\models\ShopCollection;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;

/**
 * @property ShopCollection $model;
 *
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class CollectionController extends Controller
{
    /**
     * @var ShopCollection
     */
    public $_model = false;

    /**
     * @var string
     */
    public $modelClassName = ShopCollection::class;

    /**
     * @var string
     */
    public $editControllerRoute = "shop/admin-shop-collection";


    /**
     * @return bool|ShopCollection
     */
    public function getModel()
    {
        if ($this->_model !== false) {
            return $this->_model;
        }

        if (!$id = \Yii::$app->request->get('id')) {
            $this->_model = null;
            return false;
        }

        $modelClassName = $this->modelClassName;
        $this->_model = $modelClassName::findOne(['id' => $id]);

        return $this->_model;
    }

    /**
     * @param $model
     * @return $this
     */
    public function setModel($model)
    {
        $this->_model = $model;
        return $this;
    }

    public function beforeAction($action)
    {
        if ($this->model && \Yii::$app->cmsToolbar) {
            $controller = \Yii::$app->createController($this->editControllerRoute)[0];
            $adminControllerRoute = [
                '/'.$this->editControllerRoute.'/'.$controller->modelDefaultAction,
                $controller->requestPkParamName => $this->model->{$controller->modelPkAttribute},
            ];

            $urlEditModel = \skeeks\cms\backend\helpers\BackendUrlHelper::createByParams($adminControllerRoute)
                ->enableEmptyLayout()
                ->url;

            \Yii::$app->cmsToolbar->editUrl = $urlEditModel;
        }

        return parent::beforeAction($action);
    }

    /**
     * @return $this|string
     * @throws NotFoundHttpException
     */
    public function actionView()
    {
        if (!$this->model) {
            throw new NotFoundHttpException(\Yii::t('skeeks/cms', 'Страница не найдена: ').\Yii::$app->request->absoluteUrl);
        }
        if (!$this->model->is_active) {
            throw new NotFoundHttpException(\Yii::t('skeeks/cms', 'Страница отключена: ').\Yii::$app->request->absoluteUrl);
        }

        $model = $this->model;

        //TODO: Может быть не сбрасывать GET параметры
        if (Url::isRelative($model->url)) {

            $url = \Yii::$app->request->absoluteUrl;
            if ($pos = strpos($url, '?')) {
                $url = substr($url, 0, $pos);
            }

            if ($model->getUrl(true) != $url) {
                $url = $model->getUrl(true);
                \Yii::$app->response->redirect($url, 301);
                \Yii::$app->end();
            }
        } else {

            if ($urlData = parse_url($model->getUrl(true))) {
                $url = \Yii::$app->request->absoluteUrl;
                if ($pos = strpos($url, '?')) {
                    $url = substr($url, 0, $pos);
                }
                $requestUrlData = parse_url($url);

                if (ArrayHelper::getValue($urlData, 'path') != ArrayHelper::getValue($requestUrlData, 'path')) {
                    $url = $model->getUrl(true);
                    \Yii::$app->response->redirect($url, 301);
                    \Yii::$app->end();
                }
            }
        }


        $this->_initStandartMetaData();

        return $this->render($this->action->id, [
            'model' => $this->model,
        ]);
    }

    /**
     *
     * TODO: Вынести в seo компонент
     *
     * Установка метаданных страницы
     * @return $this
     */
    protected function _initStandartMetaData()
    {
        $model = $this->model;

        //Заголовок
        if (!$title = $model->meta_title) {
            if (isset($model->seoName)) {
                $title = $model->seoName;
            }
        }

        $this->view->title = $title;
        $this->view->registerMetaTag([
            'property' => 'og:title',
            'content'  => $title,
        ], 'og:title');

        //Ключевые слова
        if ($meta_keywords = $model->meta_keywords) {
            $this->view->registerMetaTag([
                "name"    => 'keywords',
                "content" => $meta_keywords,
            ], 'keywords');
        }


        //Описание
        if ($meta_descripption = $model->meta_description) {
            $description = $meta_descripption;
        } elseif ($model->description_short) {
            $description = $model->description_short;
        } else {
            if (isset($model->name)) {
                if ($model->name != $model->seoName) {
                    $description = $model->seoName;
                } else {
                    $description = $model->name;
                }
            }
        }

        $description = trim(strip_tags($description));

        $this->view->registerMetaTag([
            "name"    => 'description',
            "content" => $description,
        ], 'description');

        $this->view->registerMetaTag([
            'property' => 'og:description',
            'content'  => $description,
        ], 'og:description');

        //Картика
        $imageAbsoluteSrc = null;
        if ($model->cms_image_id) {
            $imageAbsoluteSrc = $model->image->absoluteSrc;
        }

        if ($imageAbsoluteSrc) {
            $this->view->registerMetaTag([
                'property' => 'og:image',
                'content'  => $imageAbsoluteSrc,
            ], 'og:image');
        }


        $this->view->registerMetaTag([
            'property' => 'og:url',
            'content'  => $model->getUrl(true),
        ], 'og:url');

        $this->view->registerMetaTag([
            'property' => 'og:type',
            'content'  => 'article',
        ], 'og:type');

        return $this;
    }
}