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

                'shop/agents/update-quantity' => [
                    'class'    => \skeeks\cms\agent\CmsAgent::class,
                    'name'     => ['skeeks/shop/app', 'Обновление количества'],
                    'interval' => 60 * 5,
                ],

                'shop/agents/update-product-type' => [
                    'class'    => \skeeks\cms\agent\CmsAgent::class,
                    'name'     => ['skeeks/shop/app', 'Обновление типа товаров'],
                    'interval' => 60 * 5,
                ],
                'shop/agents/update-subproducts' => [
                    'class'    => \skeeks\cms\agent\CmsAgent::class,
                    'name'     => ['skeeks/shop/app', 'Обновление данных по вложенным товарам'],
                    'interval' => 60 * 5,
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

                                "shop/admin-shop-supplier-property",
                                "shop/admin-shop-supplier",
                                "shop/admin-shop-store",
                                
                                "shop/admin-content",

                                "shop/admin-viewed-product",
                                "shop/admin-quantity-notice-email",

                                "shop/admin-shop-import-cms-site",
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

                                "shop/admin-viewed-product",
                                "shop/admin-quantity-notice-email",
                            ],
                        ],
                    ],

                    'roles' => [
                         [
                            'name'        => \skeeks\cms\rbac\CmsManager::ROLE_USER,

                            //Есть доступ к системе администрирования
                            'child'       => [
                                'permissions' => [
                                    'shop/upa-order'
                                ],
                            ],

                        ],
                    ]
                ],
            ],
        ],
        
        'skeeks' => [
            'siteClass' => \skeeks\cms\shop\models\CmsSite::class,
        ],
    ],

    'modules' => [
        'shop' => [
            'class' => 'skeeks\cms\shop\Module',
        ],
    ],
];