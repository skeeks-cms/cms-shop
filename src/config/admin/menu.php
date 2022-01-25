<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 12.03.2015
 */


/**
 * Меню контента
 * @return array
 */

function shopPersonTypes()
{
    $result = [];

    if ($personTypes = \skeeks\cms\shop\models\ShopPersonType::find()->all()) {
        /**
         * @var $personType \skeeks\cms\shop\models\ShopPersonType
         */
        foreach ($personTypes as $personType) {
            $itemData = [
                'label'          => $personType->name,
                'url'            => ["shop/admin-buyer", "person_type_id" => $personType->id],
                'activeCallback' => function (\skeeks\cms\backend\BackendMenuItem $adminMenuItem) {
                    return (bool)(\Yii::$app->controller->uniqueId == 'shop/admin-buyer' && \yii\helpers\ArrayHelper::getValue($adminMenuItem->urlData,
                            'person_type_id') == \Yii::$app->request->get('person_type_id'));
                },
            ];

            $result[] = $itemData;
        }
    }

    return $result;
}

;

return \yii\helpers\ArrayHelper::merge([

    'orders' => [
        'priority' => 200,
        "label"    => \Yii::t('skeeks/shop/app', 'Orders'),
        "url"      => ["shop/admin-order"],
        "img"      => ['\skeeks\cms\shop\assets\Asset', 'icons/orders.png'],
    ],

    'shop' => [
        'label'    => \Yii::t('skeeks/shop/app', 'Shop'),
        "img"      => ['\skeeks\cms\shop\assets\Asset', 'icons/e-commerce.png'],
        'priority' => 250,

        'items' => [
            [


                /*'priority' => 10,
                'label'    => \Yii::t('skeeks/shop/app', 'Orders'),
                "img"      => ['\skeeks\cms\shop\assets\Asset', 'icons/orders.png'],

                'items' =>
                    [
                        [
                            "label" => \Yii::t('skeeks/shop/app', 'Orders'),
                            "url"   => ["shop/admin-order"],
                            "img"   => ['\skeeks\cms\shop\assets\Asset', 'icons/orders.png'],
                        ],

                        [
                            "label" => \Yii::t('skeeks/shop/app', 'История изменения'),
                            "url"   => ["shop/admin-order-change"],
                            "img"   => ['\skeeks\cms\shop\assets\Asset', 'icons/orders.png'],
                        ],

                        [
                            "label" => \Yii::t('skeeks/shop/app', 'Счета'),
                            "url"   => ["shop/admin-bill"],
                            "img"   => ['\skeeks\cms\shop\assets\Asset', 'icons/orders.png'],
                        ],
                        [
                            "label" => \Yii::t('skeeks/shop/app', 'Платежи'),
                            "url"   => ["shop/admin-payment"],
                            "img"   => ['\skeeks\cms\shop\assets\Asset', 'icons/orders.png'],
                        ],
                    ],*/

            ],


            /*[
                'priority' => 30,
                'label'    => \Yii::t('skeeks/shop/app', 'Buyers'),
                "img"      => ['\skeeks\cms\shop\assets\Asset', 'icons/buyers.png'],

                'items' =>
                    [

                        [
                            "label" => \Yii::t('skeeks/shop/app', 'Regular customers'),
                            "url"   => ["shop/admin-buyer-user"],
                            "img"   => ['\skeeks\cms\shop\assets\Asset', 'icons/buyers.png'],
                        ],

                        [
                            "label" => \Yii::t('skeeks/shop/app', 'Buyers'),
                            //"url"       => ["shop/admin-buyer"],
                            "img"   => ['\skeeks\cms\shop\assets\Asset', 'icons/buyers.png'],

                            'items' => shopPersonTypes(),
                        ],


                        [
                            "label" => \Yii::t('skeeks/shop/app', 'Baskets'),
                            "url"   => ["shop/admin-cart"],
                            'icon'  => 'fas fa-cart-arrow-down',
                        ],

                        [
                            "label" => \Yii::t('skeeks/shop/app', 'Notify on receipt (Email)'),
                            "url"   => ["shop/admin-quantity-notice-email"],
                            'icon'  => 'far fa-envelope',

                        ],

                        [
                            "label" => \Yii::t('skeeks/shop/app', 'Viewed products'),
                            "url"   => ["shop/admin-viewed-product"],
                            'icon'  => 'far fa-eye',
                        ],
                    ],

            ],*/


            /*[
                'priority' => 50,
                'label'    => \Yii::t('skeeks/shop/app', 'Reports'),
                "img"      => ['\skeeks\cms\shop\assets\Asset', 'icons/reports.png'],

                'items' =>
                    [
                        [
                            "label" => \Yii::t('skeeks/shop/app', 'Reports on orders'),
                            "url"   => ["shop/admin-report-order"],
                        ],

                        [
                            "label" => \Yii::t('skeeks/shop/app', 'Reports on products'),
                            "url"   => ["shop/admin-report-product"],
                        ],

                    ],

            ],*/

            /*[
                "label" => \Yii::t('skeeks/shop/app', 'Поставщики'),
                "url"   => ["shop/admin-shop-import-cms-site"],
                "img"   => ['\skeeks\cms\shop\assets\Asset', 'icons/lorrygreen.png'],
            ],*/
            /*[
                "label" => \Yii::t('skeeks/shop/app', 'Поставщики'),
                "url"   => ["shop/admin-shop-supplier"],
                "img"   => ['\skeeks\cms\shop\assets\Asset', 'icons/lorrygreen.png'],
            ],*/


        ],
    ],

    "settings" => [
        "items" => [
            [
                'priority' => 600,
                "label"    => \Yii::t('skeeks/shop/app', 'Настройки магазина'),
                "img"      => ['\skeeks\cms\shop\assets\Asset', 'icons/e-commerce.png'],

                'items' =>
                    [
                        [
                            "img"   => ['skeeks\cms\assets\CmsAsset', 'images/icons/settings.png'],
                            "label" => \Yii::t('skeeks/shop/app', 'Main'),
                            "url"   => ["shop/admin-shop-site"],
                        ],

                        /*[
                            "label"          => \Yii::t('skeeks/shop/app', 'Дополнительные'),
                            "url"            => [
                                "cms/admin-settings",
                                "component" => 'skeeks\cms\shop\components\ShopComponent',
                            ],
                            "img"            => ['skeeks\cms\assets\CmsAsset', 'images/icons/settings.png'],
                            "activeCallback" => function ($adminMenuItem) {
                                return (bool)(\Yii::$app->request->getUrl() == $adminMenuItem->getUrl());
                            },
                        ],*/


                        /*[
                            "label" => \Yii::t('skeeks/shop/app', 'Склады'),
                            "url"   => ["shop/admin-shop-store"],
                            "img"   => ['\skeeks\cms\shop\assets\Asset', 'icons/store.png'],
                        ],*/

                        [
                            "label" => \Yii::t('skeeks/shop/app', 'Content settings'),
                            "url"   => ["shop/admin-content"],
                        ],

                        [
                            "label" => \Yii::t('skeeks/shop/app', 'Свойства контента'),
                            "url"   => ["shop/admin-shop-cms-content-property"],
                        ],

                        [
                            "label" => \Yii::t('skeeks/shop/app', 'Order statuses'),
                            "url"   => ["shop/admin-order-status"],
                        ],

                        /*[
                            "label" => \Yii::t('skeeks/shop/app', 'Цены'),
                            "url"   => ["shop/admin-type-price"],
                            'icon'  => "fas fa-dollar-sign",
                        ],*/

                        [
                            "label" => \Yii::t('skeeks/shop/app', 'Покупатели'),
                            "url"   => ["shop/admin-person-type"],
                            "icon"  => "fas fa-user-friends",
                        ],

                        /*[
                            "label" => \Yii::t('skeeks/shop/app', 'Способы оплаты'),
                            "url"   => ["shop/admin-pay-system"],
                            "icon"  => "fab fa-cc-visa",
                        ],

                        [
                            "label" => \Yii::t('skeeks/shop/app', 'Способы доставки'),
                            "url"   => ["shop/admin-delivery"],
                            "icon"  => "fas fa-truck",
                        ],*/

                        /*[
                            "label" => \Yii::t('skeeks/shop/app', 'Свойства поставщика'),
                            "url"   => ["shop/admin-shop-supplier-property"],
                            "img"   => ['\skeeks\cms\shop\assets\Asset', 'icons/lorrygreen.png'],
                        ],*/


                        /*[
                            "label" => \Yii::t('skeeks/cms', 'Поставщики/Сайты'),
                            "url"   => ["/shop/admin-cms-site"],
                            "img"   => ['\skeeks\cms\assets\CmsAsset', 'images/icons/www.png'],
                        ],*/

                        [
                            "label" => \Yii::t('skeeks/shop/app', 'VAT rates'),
                            "url"   => ["shop/admin-vat"],
                        ],


                        [
                            'priority' => 40,
                            'label'    => \Yii::t('skeeks/shop/app', 'Marketing'),
                            "img"      => ['\skeeks\cms\shop\assets\Asset', 'icons/marketing.png'],

                            'items' => [

                                /*[
                                    "label"     => \Yii::t('skeeks/shop/app', 'Rules for the basket'),
                                    'items' =>
                                    [
                                        [
                                            "label"     => \Yii::t('skeeks/shop/app', 'Rules for the basket'),
                                            "url"       => ["shop/admin-discount1"],
                                        ],
            
                                        [
                                            "label"     => \Yii::t('skeeks/shop/app', 'Coupons of rules work to basket'),
                                            "url"       => ["shop/admin-discsav1e"],
                                        ]
            
                                    ]
                                ],*/

                                [
                                    "label" => \Yii::t('skeeks/shop/app', 'Discounts'),
                                    "url"   => ["shop/admin-discount"],
                                ],

                                [
                                    "label" => \Yii::t('skeeks/shop/app', 'Купоны'),
                                    "url"   => ["shop/admin-discount-coupon"],
                                ],

                                /*[
                                    "label" => \Yii::t('skeeks/shop/app', 'Cumulative discounts'),
                                    "url"   => ["shop/admin-discsave"],
                                ],*/

                            ],
                        ],


                        [
                            "label" => \Yii::t('skeeks/shop/app', 'Склады'),
                            "url"   => ["shop/admin-shop-store"],
                            "img"   => ['\skeeks\cms\shop\assets\Asset', 'icons/store.png'],
                        ],
                        [
                            "label" => \Yii::t('skeeks/shop/app', 'Поставщики'),
                            "url"   => ["shop/admin-shop-store-supplier"],
                            "img"   => ['\skeeks\cms\shop\assets\Asset', 'icons/lorrygreen.png'],
                        ],

                        [
                            "label" => \Yii::t('skeeks/shop/app', 'Способы оплаты'),
                            "url"   => ["shop/admin-pay-system"],
                            "icon"  => "fab fa-cc-visa",
                        ],

                        [
                            "label" => \Yii::t('skeeks/shop/app', 'Способы доставки'),
                            "url"   => ["shop/admin-delivery"],
                            "icon"  => "fas fa-truck",
                        ],

                        [
                            "label" => \Yii::t('skeeks/shop/app', 'Цены'),
                            "url"   => ["shop/admin-type-price"],
                            'icon'  => "fas fa-dollar-sign",
                        ],

                    ],
            ],

        ],
    ],
], \skeeks\cms\shop\components\ShopComponent::getAdminShopProductsMenu());