<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 27.08.2015
 */
namespace skeeks\cms\shop;
/**
 * Class Module
 * @package skeeks\cms\reviews2
 */
class Module extends \yii\web\Module
{
    public $controllerNamespace = 'skeeks\cms\shop\controllers';

    public static function t($category, $message, $params = [], $language = null)
    {
        return \Yii::t('skeeks/shop/' . $category, $message, $params, $language);
    }
}