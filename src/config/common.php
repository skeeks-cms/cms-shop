<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 27.08.2015
 */
return [

    'components' => [
        'shop' => [
            'class' => 'skeeks\cms\shop\components\ShopComponent',
        ],

        'i18n' => [
            'translations' =>
                [
                    'skeeks/shop/app' =>
                        [
                            'class'    => 'yii\i18n\PhpMessageSource',
                            'basePath' => '@skeeks/cms/shop/messages',
                            'fileMap'  => [
                                'skeeks/shop/app' => 'app.php',
                            ],
                        ],
                ],
        ],

        'cmsAgent' => [
            'commands' => [

                'shop/agents/delete-empty-carts' => [
                    'class'    => \skeeks\cms\agent\CmsAgent::class,
                    'name'     => ['skeeks/shop/app', 'Remove empty baskets'],
                    'interval' => 3600 * 6,
                ],

                'shop/flush/price-changes' => [
                    'class'    => \skeeks\cms\agent\CmsAgent::class,
                    'name'     => ['skeeks/shop/app', 'Removing the old price changes'],
                    'interval' => 3600 * 24,
                ],

                'shop/notify/quantity-emails' => [
                    'class'    => \skeeks\cms\agent\CmsAgent::class,
                    'name'     => ['skeeks/shop/app', 'Notify admission'],
                    'interval' => 60 * 10,
                ],

            ],
        ],

        'authManager' => [
            'config' => [
                'roles' => [
                    [
                        'name'  => \skeeks\cms\rbac\CmsManager::ROLE_ADMIN,
                        'child' => [
                            //Есть доступ к системе администрирования
                            'permissions' => [
                                "shop/admin-order",
                                "shop/admin-cart",
                                
                                "shop/admin-order-status",
                                "shop/admin-type-price",
                            ],
                        ],
                    ],
                    [
                        'name'  => \skeeks\cms\rbac\CmsManager::ROLE_MANGER,
                        'child' => [
                            //Есть доступ к системе администрирования
                            'permissions' => [
                                "shop/admin-order",
                                "shop/admin-cart",
                            ],
                        ],
                    ],
                ],
            ],
        ],
        
    ],

    'modules' => [
        'shop' => [
            'class' => 'skeeks\cms\shop\Module',
        ],
    ],
];