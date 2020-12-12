<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 27.08.2015
 */
return [
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
            ],
        ],

        'upaBackend' => [
            'menu' => [
                'data' => [
                    'shop' => [
                        'name'  => ['skeeks/shop/app', 'Orders'],
                        'url'  => ['/shop/upa-order'],
                        'icon'  => 'icon-basket',
                    ],
                    'favorites' => [
                        'name'  => "Избранное",
                        'url'  => ['/shop/upa-favorite'],
                        'icon'  => 'icon-heart',
                    ],
                ],
            ],
        ],
    ],

    'modules' => [
        'cms' => [
            'controllerMap' => [
                'content-element' => \skeeks\cms\shop\controllers\ContentElementController::class,
                'admin-cms-site' => \skeeks\cms\shop\controllers\AdminCmsSiteController::class,
            ],
        ],
    ],
];