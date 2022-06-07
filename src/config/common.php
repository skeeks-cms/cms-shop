<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 27.08.2015
 */
return [

    'bootstrap'  => ['shop'],
    'components' => [
        'shop'        => [
            'class'            => 'skeeks\cms\shop\components\ShopComponent',
            'deliveryHandlers' => [
                'pickup' => [
                    'class' => \skeeks\cms\shop\delivery\pickup\PickupDeliveryHandler::class
                ],
                'simple' => [
                    'class' => \skeeks\cms\shop\delivery\simple\SimpleDeliveryHandler::class
                ]
            ],
        ],
        'i18n'        => [
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
        'cmsAgent'    => [
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

                'shop/agents/update-product-type'                       => [
                    'class'    => \skeeks\cms\agent\CmsAgent::class,
                    'name'     => ['skeeks/shop/app', 'Обновление типа товаров'],
                    'interval' => 60 * 60,
                ],
                'shop/agents/update-product-prices-from-store-products' => [
                    'class'    => \skeeks\cms\agent\CmsAgent::class,
                    'name'     => ['skeeks/shop/app', 'Обновление цен из складских цен'],
                    'interval' => 60 * 60,
                ],
                /*'shop/agents/update-subproducts'  => [
                    'class'    => \skeeks\cms\agent\CmsAgent::class,
                    'name'     => ['skeeks/shop/app', 'Обновление данных по вложенным товарам'],
                    'interval' => 60 * 5,
                ],*/
                'shop/agents/update-auto-prices'                        => [
                    'class'    => \skeeks\cms\agent\CmsAgent::class,
                    'name'     => ['skeeks/shop/app', 'Обновление цен, которые рассчитываются автоматически'],
                    'interval' => 60 * 5,
                ],
            ],
        ],
        'authManager' => [
            'config' => [
                'roles'       => [
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
                                "shop/admin-shop-cms-content-property",

                                "shop/admin-viewed-product",
                                "shop/admin-quantity-notice-email",

                                "shop/admin-shop-import-cms-site",

                                "shop/admin-delivery",

                                "shop/admin-person-type",
                                "shop/admin-person-type-property",
                                "shop/admin-person-type-property-enum",

                                "shop/admin-shop-product-relation",

                                "shop/admin-cms-site",
                                "shop/admin-discount",

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

                    [
                        'name'  => \skeeks\cms\rbac\CmsManager::ROLE_USER,

                        //Есть доступ к системе администрирования
                        'child' => [
                            'permissions' => [
                                'shop/upa-order',
                                'shop/upa-favorite',
                            ],
                        ],
                    ],
                ],
                'permissions' => [
                    [
                        'name'        => 'shop/admin-shop-import-cms-site',
                        'description' => ['skeeks/cms', 'Поставщики'],
                    ],
                    [
                        'name'        => 'shop/admin-delivery',
                        'description' => ['skeeks/cms', 'Поставщики'],
                    ],
                    [
                        'name'        => 'shop/admin-shop-cms-content-property',
                        'description' => ['skeeks/cms', 'Свойства контента в магазине'],
                    ],
                    [
                        'name'        => 'shop/admin-cms-site',
                        'description' => 'shop/admin-cms-site',
                    ],
                    [
                        'name'        => 'shop/admin-discount',
                        'description' => 'Управление скидками',
                    ],
                    [
                        'name'        => 'shop/admin-cart',
                        'description' => 'Корзины пользователей',
                    ],
                    [
                        'name'        => 'shop/admin-shop-store',
                        'description' => 'Склады',
                    ],
                    [
                        'name'        => 'shop/admin-shop-store-supplier',
                        'description' => 'Поставщики',
                    ],
                    [
                        'name'        => 'shop/admin-shop-store-product',
                        'description' => 'Товары склада',
                    ],
                ],
            ],
        ],

        'skeeks' => [
            'siteClass' => \skeeks\cms\shop\models\CmsSite::class,
        ],

        'cmsExport' => [
            'handlers' => [
                \skeeks\cms\shop\export\ExportFacebookCsvContentHandler::class => [
                    'class' => \skeeks\cms\shop\export\ExportFacebookCsvContentHandler::class,
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