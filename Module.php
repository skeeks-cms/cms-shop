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
class Module extends \skeeks\cms\base\Module
{
    public $controllerNamespace = 'skeeks\cms\shop\controllers';

    public function init()
    {
        parent::init();
        $this->registerTranslations();
    }

    public function registerTranslations()
    {
        \Yii::$app->i18n->translations['skeeks/company24/*'] = [
            'class' => 'yii\i18n\PhpMessageSource',
            'sourceLanguage' => 'en-US',
            'basePath' => '@skeeks/shop/messages',
            'fileMap' => [
                'skeeks/shop/app' => 'app.php',
            ],
            'on missingTranslation' => ['skeeks\cms\components\TranslationEventHandler', 'handleMissingTranslation']
        ];
    }

    public static function t($category, $message, $params = [], $language = null)
    {
        \Yii::$app->getModule('shop');
        return \Yii::t('skeeks/shop/' . $category, $message, $params, $language);
    }
}