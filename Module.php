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

    static public $isRegisteredTranslations = false;

    public function init()
    {
        parent::init();
        self::registerTranslations();
    }

    static public function registerTranslations()
    {
        if (self::$isRegisteredTranslations === false)
        {
            \Yii::$app->i18n->translations['skeeks/shop/app'] = [
                'class' => 'yii\i18n\PhpMessageSource',
                'sourceLanguage' => 'en-US',
                'basePath' => '@skeeks/cms/shop/messages',
                'fileMap' => [
                    'skeeks/shop/app' => 'app.php',
                ],
                'on missingTranslation' => ['skeeks\cms\components\TranslationEventHandler', 'handleMissingTranslation']
            ];

            self::$isRegisteredTranslations = true;
        }
    }

    public static function t($category, $message, $params = [], $language = null)
    {
        self::registerTranslations();
        return \Yii::t('skeeks/shop/' . $category, $message, $params, $language);
    }
}