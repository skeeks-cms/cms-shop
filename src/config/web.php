<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 27.08.2015
 */
return [
    'bootstrap' => ['storeBackend', 'cacheboxBackend'],

    'components' => [
        'admin' => [
            'dashboards' => [
                'Shop' => [
                    'skeeks\cms\shop\dashboards\ReportOrderDashboard',
                ],
            ],
        ],


        'urlManager' => [
            'rules' => [
                '~shop-favorite'                => 'shop/favorite',
                '~shop-cart'                    => 'shop/cart',
                '~shop-<_a:(checkout|payment)>' => 'shop/cart/<_a>',
                '~shop-<_a:(finish)>'           => 'shop/order/<_a>',
                '~shop-order/<_a>'              => 'shop/order/<_a>',
                '~spc'                         => 'shop/coupon',

                '~bill-<_a:(view|pdf)>'           => 'shop/shop-bill/<_a>',
            ],
        ],

        'upaBackend' => [
            'menu' => [
                'data' => [
                    'shop'      => [
                        'name' => ['skeeks/shop/app', 'Orders'],
                        'url'  => ['/shop/upa-order'],
                        'icon' => 'icon-basket',
                    ],
                    'favorites' => [
                        'name' => "Избранное",
                        'url'  => ['/shop/upa-favorite'],
                        'icon' => 'icon-heart',
                    ],
                ],
            ],
        ],

        'storeBackend' => [
            'id'    => 'storeBackend',
            'class' => \skeeks\cms\shop\store\StoreBackendComponent::class,
            'menu'  => [
                'data' => [
                    'products' => [
                        'name' => ['skeeks/shop/app', 'Товары'],
                        'url'  => ['/shop/store-product'],
                        'icon' => 'icon-list',
                    ],
                    'property' => [
                        'name' => ['skeeks/shop/app', 'Характеристики'],
                        'url'  => ['/shop/store-property'],
                        'icon' => 'icon-list',
                    ],
                ],
            ],
        ],

        'cacheboxBackend' => [
            'id'    => 'cacheboxBackend',
            'class' => \skeeks\cms\shop\cashier\CashierBackendComponent::class
        ],
    ],

    'modules' => [
        'cms' => [
            'controllerMap' => [
                'content-element' => \skeeks\cms\shop\controllers\ContentElementController::class,
                'admin-cms-site'  => \skeeks\cms\shop\controllers\AdminCmsSiteController::class,
            ],
        ],
    ],
];