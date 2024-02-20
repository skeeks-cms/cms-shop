<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 24.05.2015
 */

namespace skeeks\cms\shop\urlRules;

use skeeks\cms\models\CmsContentElement;
use skeeks\cms\models\CmsContentElementProperty;
use skeeks\cms\models\CmsContentProperty;
use skeeks\cms\models\CmsSavedFilter;
use skeeks\cms\models\CmsSite;
use skeeks\cms\models\CmsTree;
use skeeks\cms\shop\models\ShopBrand;
use skeeks\cms\shop\models\ShopCollection;
use \yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\Application;

/**
 * Class UrlRuleContentElement
 * @package skeeks\cms\components\urlRules
 */
class UrlRuleCollection
    extends \yii\web\UrlRule
{

    public function init()
    {
        if ($this->name === null) {
            $this->name = __CLASS__;
        }
    }

    /**
     * //Это можно использовать только в коротких сценариях, иначе произойдет переполнение памяти
     * @var array
     */
    static public $models = [];

    /**
     * @param \yii\web\UrlManager $manager
     * @param string $route
     * @param array $params
     * @return bool|string
     */
    public function createUrl($manager, $route, $params)
    {
        if ($route == 'shop/collection/view') {

            $model = $this->_getModel($params);

            if (!$model) {
                return false;
            }

            $url = $model->code . '-c' . $model->id;


            if (strpos($url, '//') !== false) {

                $url = preg_replace('#/+#', '/', $url);
            }

            /**
             * @see parent::createUrl()
             */
            if ($url !== '') {
                $url .= ($this->suffix === null ? $manager->suffix : $this->suffix);
            }

            /**
             * @see parent::createUrl()
             */
            if (!empty($params) && ($query = http_build_query($params)) !== '') {
                $url .= '?' . $query;
            }

            return $url;
        }

        return false;
    }

    /**
     * @param $params
     * @return false|ShopCollection
     */
    protected function _getModel(&$params)
    {
        $id = (int)ArrayHelper::getValue($params, 'id');
        $model = ArrayHelper::getValue($params, 'model');

        if (!$id && !$model) {
            return false;
        }

        if ($model && $model instanceof ShopCollection) {
            if (\Yii::$app instanceof Application) {
                self::$models[$model->id] = $model;
            }
        } else {
            /**
             * @var $model ShopCollection
             */
            if (!$model = ArrayHelper::getValue(self::$models, $id)) {
                $model = ShopCollection::findOne(['id' => $id]);
                if (\Yii::$app instanceof Application) {
                    self::$models[$id] = $model;
                }
            }
        }

        ArrayHelper::remove($params, 'id');
        ArrayHelper::remove($params, 'model');

        return $model;
    }

    /**
     * @param \yii\web\UrlManager $manager
     * @param \yii\web\Request $request
     * @return array|bool
     */
    public function parseRequest($manager, $request)
    {
        if ($this->mode === self::CREATION_ONLY) {
            return false;
        }

        if (!empty($this->verb) && !in_array($request->getMethod(), $this->verb, true)) {
            return false;
        }

        $pathInfo = $request->getPathInfo();
        if ($this->host !== null) {
            $pathInfo = strtolower($request->getHostInfo()) . ($pathInfo === '' ? '' : '/' . $pathInfo);
        }


        $params = $request->getQueryParams();
        $suffix = (string)($this->suffix === null ? $manager->suffix : $this->suffix);
        $treeNode = null;

        if (!$pathInfo) {
            return false;
        }

        if ($suffix) {
            $pathInfo = substr($pathInfo, 0, strlen($pathInfo) - strlen($suffix));
        }

        if (preg_match('/\/(?<code>\S+)\-c(?<id>\d+)$/i', "/" . $pathInfo, $matches)) {
            return [
                'shop/collection/view', [
                    'id' => $matches['id'],
                    'code' => $matches['code']
                ]
            ];
        }

        return false;
    }


}
