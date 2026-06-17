<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 12.03.2015
 */



return \yii\helpers\ArrayHelper::merge([

    /*'shop' =>
        [

            'items' => [

                [
                    "label" => "Бренды",
                    "url"   => ["shop/admin-shop-brand"],
                    //"img"   => ['\skeeks\cms\assets\CmsAsset', 'images/icons/storage_file.png'],
                ],
                [
                    "label" => "Коллекции",
                    "url"   => ["shop/admin-shop-collection"],
                    //"img"   => ['\skeeks\cms\assets\CmsAsset', 'images/icons/storage_file.png'],
                ],

            ],
        ],*/

    'crm' => [
        'items' => [
            [
                'priority' => 210,
                "label" => \Yii::t('skeeks/shop/app', 'Платежи'),
                "url"   => ["shop/admin-payment"],
                "img"   => ['\skeeks\cms\assets\CmsAsset', 'images/icons/admin-menu/payment.svg'],
            ],

            [
                'priority' => 220,
                "label" => \Yii::t('skeeks/shop/app', 'Чеки'),
                "url"   => ["shop/admin-shop-check"],
                "img"   => ['\skeeks\cms\assets\CmsAsset', 'images/icons/admin-menu/cashbox.svg'],
            ],

            /*"shop-payment" => [
                "label" => \Yii::t('skeeks/shop/app', 'Движение денег'),

                "img"      => ['\skeeks\cms\shop\assets\Asset', 'icons/business-color_money-coins_icon.svg'],
                'priority' => 271,
                'items'    => [



                ],
            ],*/
        ]
    ],

    'shop' => [
        'label'    => \Yii::t('skeeks/cms', 'Магазин'),
        'priority' => 200,
        "img"      => ['\skeeks\cms\assets\CmsAsset', 'images/icons/admin-menu/shop.svg'],

        'items' => \yii\helpers\ArrayHelper::merge(\skeeks\cms\shop\components\ShopComponent::getAdminShopProductsMenu(), [
            'orders' => [
                'priority' => 260,
                "label"    => \Yii::t('skeeks/shop/app', 'Продажи и заказы'),
                "url"      => ["shop/admin-order"],
                "img"      => ['\skeeks\cms\assets\CmsAsset', 'images/icons/admin-menu/receipt.svg'],
            ],

            "shop-doc-move" => [
                "label"    => \Yii::t('skeeks/shop/app', 'Движение товара'),
                "url"      => ["shop/admin-shop-store-doc-move"],
                "img"      => ['\skeeks\cms\assets\CmsAsset', 'images/icons/admin-menu/inventory.svg'],
                'priority' => 270,
            ],

            "shop-store" => [
                "label"    => \Yii::t('skeeks/shop/app', 'Магазины и склады'),
                "url"      => ["shop/admin-shop-store"],
                "img"      => ['\skeeks\cms\assets\CmsAsset', 'images/icons/admin-menu/warehouse.svg'],
                'priority' => 279,
            ],

            "shop-supplier" => [
                "label"    => \Yii::t('skeeks/shop/app', 'Поставщики'),
                "url"      => ["shop/admin-shop-store-supplier"],
                "img"      => ['\skeeks\cms\assets\CmsAsset', 'images/icons/admin-menu/supplier.svg'],
                'priority' => 280,
            ],
            "shop-cashebox" => [
                "label" => \Yii::t('skeeks/shop/app', 'Кассы и смены'),

                "img"      => ['\skeeks\cms\assets\CmsAsset', 'images/icons/admin-menu/cashbox.svg'],
                'priority' => 271,
                'items'    => [

                    [
                        "label" => \Yii::t('skeeks/shop/app', 'Смены'),
                        "url"   => ["shop/admin-shop-cashebox-shift"],
                        "img"   => ['\skeeks\cms\assets\CmsAsset', 'images/icons/admin-menu/cashbox.svg'],
                    ],

                    [
                        "label" => \Yii::t('skeeks/shop/app', 'Кассы'),
                        "url"   => ["shop/admin-shop-cashebox"],
                        "img"   => ['\skeeks\cms\assets\CmsAsset', 'images/icons/admin-menu/cashbox.svg'],
                    ],


                    [
                        "label" => \Yii::t('skeeks/shop/app', 'Облачные кассы'),
                        "url"   => ["shop/admin-shop-cloudkassa"],
                        "img"   => ['\skeeks\cms\assets\CmsAsset', 'images/icons/admin-menu/cashbox.svg'],
                    ],

                ],
            ],
            
            [
                "label" => "Бренды",
                "url"   => ["shop/admin-shop-brand"],
                "img"   => ['\skeeks\cms\assets\CmsAsset', 'images/icons/admin-menu/brand.svg'],
                'priority' => 350,
            ],

            [
                "label" => "Коллекции",
                "url"   => ["shop/admin-shop-collection"],
                "img"   => ['\skeeks\cms\assets\CmsAsset', 'images/icons/admin-menu/collection.svg'],
                'priority' => 350,

                'items'    => [

                    [
                        "label" => "Коллекции",
                        "url"   => ["shop/admin-shop-collection"],
                        "img"   => ['\skeeks\cms\assets\CmsAsset', 'images/icons/admin-menu/collection.svg'],
                    ],
                    [
                        "label" => \Yii::t('skeeks/shop/app', 'Стикеры'),
                        "url"   => ["shop/admin-shop-collection-sticker"],
                        "img"   => ['\skeeks\cms\assets\CmsAsset', 'images/icons/admin-menu/coupon.svg'],
                    ],
                ],


            ],
        ])
    ],



    "settings" => [
        "items" => [
            [
                'priority' => 600,
                "label"    => \Yii::t('skeeks/shop/app', 'Магазин'),
                "img"      => ['\skeeks\cms\assets\CmsAsset', 'images/icons/admin-menu/shop.svg'],

                'items' =>
                    [
                        [
                            "img"   => ['\skeeks\cms\assets\CmsAsset', 'images/icons/admin-menu/settings.svg'],
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

                        /*[
                            "label" => \Yii::t('skeeks/shop/app', 'Content settings'),
                            "url"   => ["shop/admin-content"],
                        ],*/

                        /*[
                            "label" => \Yii::t('skeeks/shop/app', 'Свойства контента'),
                            "url"   => ["shop/admin-shop-cms-content-property"],
                        ],*/

                        [
                            "label" => \Yii::t('skeeks/shop/app', 'Order statuses'),
                            "url"   => ["shop/admin-order-status"],
                            "img"   => ['\skeeks\cms\assets\CmsAsset', 'images/icons/admin-menu/directory.svg'],
                        ],

                        /*[
                            "label" => \Yii::t('skeeks/shop/app', 'Цены'),
                            "url"   => ["shop/admin-type-price"],
                            'icon'  => "fas fa-dollar-sign",
                        ],*/

                        /*[
                            "label" => \Yii::t('skeeks/shop/app', 'Покупатели'),
                            "url"   => ["shop/admin-person-type"],
                            "icon"  => "fas fa-user-friends",
                        ],*/

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
                            "img"   => ['\skeeks\cms\assets\CmsAsset', 'images/icons/admin-menu/vat.svg'],
                        ],





                        [
                            "label" => \Yii::t('skeeks/shop/app', 'Способы оплаты'),
                            "url"   => ["shop/admin-pay-system"],
                            "img"   => ['\skeeks\cms\assets\CmsAsset', 'images/icons/admin-menu/payment.svg'],
                        ],

                        [
                            "label" => \Yii::t('skeeks/shop/app', 'Способы доставки'),
                            "url"   => ["shop/admin-delivery"],
                            "img"   => ['\skeeks\cms\assets\CmsAsset', 'images/icons/admin-menu/delivery.svg'],
                        ],

                        [
                            "label" => \Yii::t('skeeks/shop/app', 'Цены'),
                            "url"   => ["shop/admin-type-price"],
                            "img"   => ['\skeeks\cms\assets\CmsAsset', 'images/icons/admin-menu/price.svg'],
                        ],

                    ],
            ],

        ],
    ],







    "shop-marketing" => [
        //'priority' => 40,
        'label' => \Yii::t('skeeks/shop/app', 'Marketing'),
        "img"   => ['\skeeks\cms\assets\CmsAsset', 'images/icons/admin-menu/marketing.svg'],
        'priority' => 278,

        'items' => [

            [
                "label" => \Yii::t('skeeks/shop/app', 'Бонусы'),
                "url"   => ["shop/admin-bonus-transaction"],
                "img"   => ['\skeeks\cms\assets\CmsAsset', 'images/icons/admin-menu/coupon.svg'],
            ],

            [
                "label" => \Yii::t('skeeks/shop/app', 'Discounts'),
                "url"   => ["shop/admin-discount"],
                "img"   => ['\skeeks\cms\assets\CmsAsset', 'images/icons/admin-menu/discount.svg'],
            ],

            [
                "label" => \Yii::t('skeeks/shop/app', 'Купоны'),
                "url"   => ["shop/admin-discount-coupon"],
                "img"   => ['\skeeks\cms\assets\CmsAsset', 'images/icons/admin-menu/coupon.svg'],
            ],

            [
                "label" => "Отзывы к товарам",
                "url"   => ["shop/admin-shop-feedback"],
                "img"   => ['\skeeks\cms\assets\CmsAsset', 'images/icons/admin-menu/reviews.svg'],
            ],

            /*[
                "label" => \Yii::t('skeeks/shop/app', 'Cumulative discounts'),
                "url"   => ["shop/admin-discsave"],
            ],*/

        ],
    ],




], []);
