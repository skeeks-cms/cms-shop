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
            'paysystemHandlers' => [
                'banktransfer' => [
                    'class' => \skeeks\cms\shop\paysystem\BankTransferPaysystemHandler::class,
                ],
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

                'shop/agents/update-auto-prices'                        => [
                    'class'    => \skeeks\cms\agent\CmsAgent::class,
                    'name'     => ['skeeks/shop/app', 'Обновление цен, которые рассчитываются автоматически'],
                    'interval' => 60 * 5,
                ],
                'shop/agents/update-product-rating'                        => [
                    'class'    => \skeeks\cms\agent\CmsAgent::class,
                    'name'     => ['skeeks/shop/app', 'Обновление рейтинга, которые рассчитываются автоматически'],
                    'interval' => 3600,
                ],

                /**
                 * SkeekS GPD
                 */
                'shop/skeeks-suppliers/update-products' => [
                    'class'    => \skeeks\cms\agent\CmsAgent::class,
                    'name'     => ['app', 'SkeekS GPD - полное обновление товаров'],
                    'interval' => 3600*24*7,
                ],
                'shop/skeeks-suppliers/update-store-items' => [
                    'class'    => \skeeks\cms\agent\CmsAgent::class,
                    'name'     => ['app', 'SkeekS GPD - обновить цены и наличие'],
                    'interval' => 3600*23*6,
                ],
                'shop/skeeks-suppliers/update-products --product_new_info=1' => [
                    'class'    => \skeeks\cms\agent\CmsAgent::class,
                    'name'     => ['app', 'SkeekS GPD - обновить недавно измененные товары'],
                    'interval' => 60*10,
                ],
                'shop/skeeks-suppliers/update-store-items --store_new_prices=1' => [
                    'class'    => \skeeks\cms\agent\CmsAgent::class,
                    'name'     => ['app', 'SkeekS GPD - обновить недавно измененные цены и наличие'],
                    'interval' => 60*8,
                ],
                

            ],
        ],
        'authManager' => [
            'config' => [
                'roles'       => [

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

                    [
                        'name'        => \skeeks\cms\rbac\CmsManager::ROLE_EDITOR,
                        'child' => [

                            //Есть доступ к системе администрирования
                            'permissions' => [

                                "shop/admin-product",
                                "shop/admin-product/index",
                                "shop/admin-product/create",
                                "shop/admin-product/update/own",
                                "shop/admin-product/join/own",
                                "shop/admin-product/delete/own",
                                            
                                "shop/admin-shop-brand",
                                "shop/admin-shop-brand/index",
                                "shop/admin-shop-brand/create",
                                "shop/admin-shop-brand/update/own",
                                "shop/admin-shop-brand/delete/own",

                                "shop/admin-shop-collection",
                                "shop/admin-shop-collection/index",
                                "shop/admin-shop-collection/create",
                                "shop/admin-shop-collection/update/own",
                                "shop/admin-shop-collection/delete/own",

                            ],
                        ],
                    ],

                    [
                        'name'  => \skeeks\cms\rbac\CmsManager::ROLE_MAIN_EDITOR,
                        'child' => [
                            //Есть доступ к системе администрирования
                            'permissions' => [

                                "shop/admin-product",
                                "shop/admin-product/index",
                                "shop/admin-product/create",
                                "shop/admin-product/update",
                                "shop/admin-product/join",
                                "shop/admin-product/delete/own",
                                            
                                "shop/admin-shop-brand",
                                "shop/admin-shop-brand/index",
                                "shop/admin-shop-brand/create",
                                "shop/admin-shop-brand/update",
                                "shop/admin-shop-brand/delete/own",

                                "shop/admin-shop-collection",
                                "shop/admin-shop-collection/index",
                                "shop/admin-shop-collection/create",
                                "shop/admin-shop-collection/update",
                                "shop/admin-shop-collection/delete/own",

                            ],
                        ],
                    ],

                    [
                        'name'  => \skeeks\cms\rbac\CmsManager::ROLE_MANGER,
                        'child' => [
                            //Есть доступ к системе администрирования
                            'permissions' => [

                                "shop/admin-product",
                                "shop/admin-product/index",
                                "shop/admin-product/join",
                                "shop/admin-product/orders",

                                "shop/admin-shop-check",
                                "shop/admin-order",
                                "shop/admin-payment",
                            ],
                        ],
                    ],

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

                                "shop/admin-discount",

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
                        'name'        => 'shop/admin-discount',
                        'description' => 'Скидки',
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
                    
                    
                    
                    /**
                     * Доступ к элементам
                     */
                    [
                        'name'        => 'shop/admin-product',
                        'description' => ['skeeks/cms', 'Товары и услуги'],
                    ],
            
                    [
                        'name'        => 'shop/admin-product/index',
                        'description' => ['skeeks/cms', 'Товары и услуги | Список'],
                    ],
            
                    [
                        'name'        => 'shop/admin-product/create',
                        'description' => ['skeeks/cms', 'Товары и услуги | Добавить'],
                    ],
            
                    [
                        'name'        => 'shop/admin-product/update',
                        'description' => ['skeeks/cms', 'Товары и услуги | Редактировать'],
                    ],
            
                    [
                        'name'        => 'shop/admin-product/update/own',
                        'description' => ['skeeks/cms', 'Товары и услуги | Редактировать (только свои)'],
                        'child' => [
                            'permissions' => [
                                'shop/admin-product/update',
                            ],
                        ],
                        'ruleName' => \skeeks\cms\rbac\AuthorRule::NAME
                    ],

                    [
                        'name'        => 'shop/admin-product/join',
                        'description' => ['skeeks/cms', 'Товары и услуги | Объединение, связка'],
                    ],

                    [
                        'name'        => 'shop/admin-product/join/own',
                        'description' => ['skeeks/cms', 'Товары и услуги | Объединение, связка (только свои)'],
                        'child' => [
                            'permissions' => [
                                'shop/admin-product/join',
                            ],
                        ],
                        'ruleName' => \skeeks\cms\rbac\AuthorRule::NAME
                    ],

                    [
                        'name'        => 'shop/admin-product/orders',
                        'description' => ['skeeks/cms', 'Товары и услуги | Кто посмотрел, заказал, положил в корзину'],
                    ],

                    /*[
                        'name'        => 'shop/admin-product/orders/own',
                        'description' => ['skeeks/cms', 'Товары и услуги | Кто посмотрел, заказал, положил в корзину (только свои)'],
                        'child' => [
                            'permissions' => [
                                'shop/admin-product/orders',
                            ],
                        ],
                        'ruleName' => \skeeks\cms\rbac\AuthorRule::NAME
                    ],*/

                    [
                        'name'        => 'shop/admin-product/delete',
                        'description' => ['skeeks/cms', 'Товары и услуги | Удалить'],
                    ],
            
            
                    [
                        'name'        => 'shop/admin-product/delete/own',
                        'description' => ['skeeks/cms', 'Товары и услуги | Удалить (только свои)'],
                        'child' => [
                            'permissions' => [
                                'shop/admin-product/delete',
                            ],
                        ],
                        'ruleName' => \skeeks\cms\rbac\AuthorRule::NAME
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

        'skeeksSuppliersApi' => [
            'class' => \skeeks\cms\shop\components\SkeeksSuppliersApiComponent::class,
            'api_key' => \yii\helpers\ArrayHelper::getValue($params, "skeeksSuppliersApi.api_key"),
            'timeout' => \yii\helpers\ArrayHelper::getValue($params, "skeeksSuppliersApi.timeout", 20),
        ],
    ],

    'modules' => [
        'shop' => [
            'class' => 'skeeks\cms\shop\Module',
        ],
    ],
];