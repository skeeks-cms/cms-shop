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
        'gpd' => ['class' => \skeeks\cms\shop\components\GpdComponent::class],
        'gpdReceiver' => ['class' => \skeeks\cms\shop\gpd\ReceiverComponent::class],
        'jobQueueFactory' => ['queues' => ['gpd-receive' => [], 'gpd-apply' => [], 'gpd-offers' => []]],
        'jobRegistry' => [
            'types' => [
                'shop.gpd.dictionaries.sync' => [
                    'type'=>'shop.gpd.dictionaries.sync','title'=>'GPD: страны и единицы измерения',
                    'handler'=>\skeeks\cms\shop\jobs\GpdStableReferencesJobHandler::class,
                    'queue'=>'gpd-apply','timeout'=>600,'leaseSeconds'=>120,'maxAttempts'=>3,
                    'idempotent'=>true,'overlapPolicy'=>'skip',
                    'permission'=>\skeeks\cms\rbac\CmsManager::PERMISSION_ROLE_ADMIN_ACCESS,
                    'resourceKey'=>static function(){return 'shop:gpd:apply';},
                    'dedupKey'=>static function(){return 'shop:gpd:dictionaries';},
                ],
                'shop.gpd.offers.sync' => [
                    'type'=>'shop.gpd.offers.sync','title'=>'GPD: синхронизация цен и остатков',
                    'handler'=>\skeeks\cms\shop\jobs\GpdOffersJobHandler::class,
                    'queue'=>'gpd-offers','timeout'=>600,'leaseSeconds'=>120,'maxAttempts'=>3,
                    'idempotent'=>true,'overlapPolicy'=>'skip',
                    'permission'=>\skeeks\cms\rbac\CmsManager::PERMISSION_ROLE_ADMIN_ACCESS,
                    'resourceKey'=>static function(){return 'shop:gpd:apply';},
                    'dedupKey'=>static function(){return 'shop:gpd:offers';},
                ],
                'shop.gpd.references.sync' => [
                    'type'=>'shop.gpd.references.sync','title'=>'GPD: синхронизация справочников',
                    'handler'=>\skeeks\cms\shop\jobs\GpdReferencesJobHandler::class,
                    'queue'=>'gpd-apply','timeout'=>600,'leaseSeconds'=>120,'maxAttempts'=>3,
                    'idempotent'=>true,'overlapPolicy'=>'skip',
                    'permission'=>\skeeks\cms\rbac\CmsManager::PERMISSION_ROLE_ADMIN_ACCESS,
                    'resourceKey'=>static function(){return 'shop:gpd:apply';},
                    'dedupKey'=>static function(){return 'shop:gpd:references';},
                ],
                'shop.gpd.catalog.apply' => [
                    'type'=>'shop.gpd.catalog.apply', 'title'=>'GPD: применение изменений каталога',
                    'handler'=>\skeeks\cms\shop\jobs\GpdCatalogApplyJobHandler::class,
                    'queue'=>'gpd-apply', 'timeout'=>600, 'leaseSeconds'=>120, 'maxAttempts'=>3,
                    'idempotent'=>true, 'overlapPolicy'=>'skip',
                    'permission'=>\skeeks\cms\rbac\CmsManager::PERMISSION_ROLE_ADMIN_ACCESS,
                    'resourceKey'=>static function(){return 'shop:gpd:apply';},
                    'dedupKey'=>static function(){return 'shop:gpd:apply';},
                ],
                'shop.gpd.catalog.receive' => [
                    'type' => 'shop.gpd.catalog.receive',
                    'title' => 'GPD: приём журнала каталога (без применения)',
                    'handler' => \skeeks\cms\shop\jobs\GpdCatalogReceiveJobHandler::class,
                    'queue' => 'gpd-receive',
                    'timeout' => 180,
                    'leaseSeconds' => 120,
                    'maxAttempts' => 3,
                    'idempotent' => true,
                    'overlapPolicy' => 'skip',
                    'permission' => \skeeks\cms\rbac\CmsManager::PERMISSION_ROLE_ADMIN_ACCESS,
                    'resourceKey' => static function (array $payload) { return 'shop:gpd:receive'; },
                    'dedupKey' => static function (array $payload) { return 'shop:gpd:receive'; },
                ],
                'shop.update-subproducts' => [
                    'type'=>'shop.update-subproducts', 'title'=>'Обновление данных по вложенным товарам',
                    'handler'=>\skeeks\cms\shop\jobs\UpdateSubproductsJobHandler::class,
                    'queue'=>'maintenance', 'timeout'=>7200, 'leaseSeconds'=>120,
                    'maxAttempts'=>1, 'idempotent'=>false, 'overlapPolicy'=>'skip',
                    'permission'=>\skeeks\cms\rbac\CmsManager::PERMISSION_ROLE_ADMIN_ACCESS,
                    'resourceKey'=>static function(){return 'shop:catalog';},
                    'dedupKey'=>static function(){return 'shop:update-subproducts';},
                ],
                'shop.quantity-emails' => [
                    'type'=>'shop.quantity-emails', 'title'=>'Уведомить о поступлении',
                    'handler'=>\skeeks\cms\shop\jobs\QuantityEmailsJobHandler::class,
                    'queue'=>'maintenance', 'timeout'=>60, 'leaseSeconds'=>120,
                    'maxAttempts'=>1, 'idempotent'=>false, 'overlapPolicy'=>'skip',
                    'permission'=>\skeeks\cms\rbac\CmsManager::PERMISSION_ROLE_ADMIN_ACCESS,
                    'resourceKey'=>static function(){return 'shop:quantity-emails';},
                ],
                'shop.delete-empty-carts' => [
                    'type' => 'shop.delete-empty-carts',
                    'title' => 'Удаление старых корзин',
                    'handler' => \skeeks\cms\shop\jobs\DeleteEmptyCartsJobHandler::class,
                    'queue' => 'maintenance',
                    'timeout' => 7200,
                    'leaseSeconds' => 120,
                    'maxAttempts' => 1,
                    'idempotent' => false,
                    'overlapPolicy' => 'skip',
                    'permission' => \skeeks\cms\rbac\CmsManager::PERMISSION_ROLE_ADMIN_ACCESS,
                    'resourceKey' => static function () { return 'shop:carts'; },
                    'dedupKey' => static function () { return 'shop:delete-empty-carts'; },
                ],
                'shop.delete-price-changes' => [
                    'type' => 'shop.delete-price-changes',
                    'title' => 'Удаление старых изменений цен',
                    'handler' => \skeeks\cms\shop\jobs\DeletePriceChangesJobHandler::class,
                    'queue' => 'maintenance',
                    'timeout' => 7200,
                    'leaseSeconds' => 120,
                    'maxAttempts' => 1,
                    'idempotent' => false,
                    'overlapPolicy' => 'skip',
                    'permission' => \skeeks\cms\rbac\CmsManager::PERMISSION_ROLE_ADMIN_ACCESS,
                    'resourceKey' => static function () { return 'shop:price-changes'; },
                    'dedupKey' => static function () { return 'shop:delete-price-changes'; },
                ],
                'shop.update-product-type' => [
                    'type' => 'shop.update-product-type',
                    'title' => 'Обновление типа товаров',
                    'handler' => \skeeks\cms\shop\jobs\UpdateProductTypeJobHandler::class,
                    'queue' => 'maintenance',
                    'timeout' => 7200,
                    'leaseSeconds' => 120,
                    'maxAttempts' => 1,
                    'idempotent' => false,
                    'overlapPolicy' => 'skip',
                    'permission' => \skeeks\cms\rbac\CmsManager::PERMISSION_ROLE_ADMIN_ACCESS,
                    'resourceKey' => static function () { return 'shop:catalog'; },
                    'dedupKey' => static function () { return 'shop:update-product-type'; },
                ],
                'shop.update-store-prices' => [
                    'type' => 'shop.update-store-prices',
                    'title' => 'Обновление цен из складских цен',
                    'handler' => \skeeks\cms\shop\jobs\UpdateStorePricesJobHandler::class,
                    'queue' => 'maintenance',
                    'timeout' => 7200,
                    'leaseSeconds' => 120,
                    'maxAttempts' => 1,
                    'idempotent' => false,
                    'overlapPolicy' => 'skip',
                    'permission' => \skeeks\cms\rbac\CmsManager::PERMISSION_ROLE_ADMIN_ACCESS,
                    'resourceKey' => static function () { return 'shop:catalog'; },
                    'dedupKey' => static function () { return 'shop:update-store-prices'; },
                ],
                'shop.update-auto-prices' => [
                    'type' => 'shop.update-auto-prices',
                    'title' => 'Обновление автоматических цен',
                    'handler' => \skeeks\cms\shop\jobs\UpdateAutoPricesJobHandler::class,
                    'queue' => 'maintenance',
                    'timeout' => 7200,
                    'leaseSeconds' => 120,
                    'maxAttempts' => 1,
                    'idempotent' => false,
                    'overlapPolicy' => 'skip',
                    'permission' => \skeeks\cms\rbac\CmsManager::PERMISSION_ROLE_ADMIN_ACCESS,
                    'resourceKey' => static function () { return 'shop:catalog'; },
                    'dedupKey' => static function () { return 'shop:update-auto-prices'; },
                ],
                'shop.update-product-rating' => [
                    'type' => 'shop.update-product-rating',
                    'title' => 'Обновление рейтинга товаров',
                    'handler' => \skeeks\cms\shop\jobs\UpdateProductRatingJobHandler::class,
                    'queue' => 'maintenance',
                    'timeout' => 7200,
                    'leaseSeconds' => 120,
                    'maxAttempts' => 1,
                    'idempotent' => false,
                    'overlapPolicy' => 'skip',
                    'permission' => \skeeks\cms\rbac\CmsManager::PERMISSION_ROLE_ADMIN_ACCESS,
                    'resourceKey' => static function () { return 'shop:catalog'; },
                    'dedupKey' => static function () { return 'shop:update-product-rating'; },
                ],
            ],
        ],
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
            'jobs' => [
                'shop.gpd.catalog.receive' => ['jobType'=>'shop.gpd.catalog.receive','name'=>'GPD: получение изменений товаров','interval'=>60],
                'shop.gpd.catalog.apply' => ['jobType'=>'shop.gpd.catalog.apply','name'=>'GPD: применение изменений товаров','interval'=>60],
                'shop.gpd.references.sync' => ['jobType'=>'shop.gpd.references.sync','name'=>'GPD: синхронизация справочников','interval'=>60],
                'shop.gpd.dictionaries.sync' => ['jobType'=>'shop.gpd.dictionaries.sync','name'=>'GPD: страны и единицы измерения','interval'=>86400],
                'shop.gpd.offers.sync' => ['jobType'=>'shop.gpd.offers.sync','name'=>'GPD: цены, остатки и склады','interval'=>60],
            ],
            'commands' => [

                'shop/agents/update-subproducts' => [
                    'jobType'=>'shop.update-subproducts',
                    'class'=>\skeeks\cms\agent\CmsAgent::class,
                    'name'=>'Обновление данных по вложенным товарам', 'interval'=>300,
                ],
                'shop/notify/quantity-emails' => [
                    'jobType'=>'shop.quantity-emails',
                    'class'=>\skeeks\cms\agent\CmsAgent::class,
                    'name'=>'Уведомить о поступлении', 'interval'=>600,
                ],
                'shop/agents/delete-empty-carts' => [
                    'jobType' => 'shop.delete-empty-carts',
                    'class'    => \skeeks\cms\agent\CmsAgent::class,
                    'name'     => ['skeeks/shop/app', 'Remove empty baskets'],
                    'interval' => 3600 * 6,
                ],


                'shop/flush/price-changes' => [
                    'jobType' => 'shop.delete-price-changes',
                    'class'    => \skeeks\cms\agent\CmsAgent::class,
                    'name'     => ['skeeks/shop/app', 'Removing the old price changes'],
                    'interval' => 3600 * 24,
                ],

                /*'shop/agents/update-quantity' => [
                    'class'    => \skeeks\cms\agent\CmsAgent::class,
                    'name'     => ['skeeks/shop/app', 'Обновление количества'],
                    'interval' => 60 * 5,
                ],*/

                'shop/agents/update-product-type'                       => [
                    'jobType' => 'shop.update-product-type',
                    'class'    => \skeeks\cms\agent\CmsAgent::class,
                    'name'     => ['skeeks/shop/app', 'Обновление типа товаров'],
                    'interval' => 60 * 60,
                ],
                'shop/agents/update-product-prices-from-store-products' => [
                    'jobType' => 'shop.update-store-prices',
                    'class'    => \skeeks\cms\agent\CmsAgent::class,
                    'name'     => ['skeeks/shop/app', 'Обновление цен из складских цен'],
                    'interval' => 60 * 60,
                ],

                'shop/agents/update-auto-prices'                        => [
                    'jobType' => 'shop.update-auto-prices',
                    'class'    => \skeeks\cms\agent\CmsAgent::class,
                    'name'     => ['skeeks/shop/app', 'Обновление цен, которые рассчитываются автоматически'],
                    'interval' => 60 * 5,
                ],
                'shop/agents/update-product-rating'                        => [
                    'jobType' => 'shop.update-product-rating',
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
                                "shop/admin-bonus-transaction",
                                "shop/admin-partner-payout",
                            ],
                        ],
                    ],

                    [
                        'name'  => \skeeks\cms\rbac\CmsManager::ROLE_MARKETER,
                        'child' => [
                            'permissions' => [
                                "shop/admin-partner-payout",
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
                                "shop/admin-bonus-transaction",
                                "shop/admin-partner-payout",

                            ],
                        ],
                    ],



                ],
                'permissions' => [
                    [
                        'name' => 'shop/admin-bonus-transaction',
                        'description' => 'Движение бонусов',
                    ],
                    [
                        'name' => 'shop/admin-partner-payout',
                        'description' => 'Заявки партнёров на вывод бонусов',
                    ],
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
            'market_url' => \yii\helpers\ArrayHelper::getValue($params, "skeeksSuppliersApi.market_url", "https://skeeks-market.ru"),
            'timeout' => \yii\helpers\ArrayHelper::getValue($params, "skeeksSuppliersApi.timeout", 20),
        ],
    ],

    'modules' => [
        'shop' => [
            'class' => 'skeeks\cms\shop\Module',
        ],
    ],
];
