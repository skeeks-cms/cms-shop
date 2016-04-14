<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 27.08.2015
 */
return [

    'components' =>
    [
        'shop' => [
            'class'         => 'skeeks\cms\shop\components\ShopComponent',
        ],

        'i18n' => [
            'translations'  =>
            [
                'skeeks/shop/app' =>
                [
                    'class'             => 'yii\i18n\PhpMessageSource',
                    'basePath'          => '@skeeks/cms/shop/messages',
                    'fileMap' => [
                        'skeeks/shop/app' => 'app.php',
                    ],
                ]
            ],
        ],

    ],

    'modules' =>
    [
        'shop' => [
            'class'                 => 'skeeks\cms\shop\Module',
            'controllerNamespace'   => 'skeeks\cms\shop\console\controllers'
        ]
    ]
];