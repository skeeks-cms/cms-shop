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
function shopProductsMenu()
{
    $result = [];

    try {
        $table = \skeeks\cms\models\CmsContent::getTableSchema();
        $table = \skeeks\cms\shop\models\ShopContent::getTableSchema();
    } catch (\Exception $e) {
        return $result;
    }

    if ($contents = \skeeks\cms\models\CmsContent::find()->orderBy("priority ASC")->andWhere([
        'id' => \yii\helpers\ArrayHelper::map(\skeeks\cms\shop\models\ShopContent::find()->all(), 'content_id',
            'content_id'),
    ])->all()
    ) {
        /**
         * @var $content \skeeks\cms\models\CmsContent
         */
        foreach ($contents as $content) {
            $itemData = [
                'label'          => $content->name,
                "img"            => ['skeeks\cms\assets\CmsAsset', 'images/icons/icon.article.png'],
                'url'            => ["shop/admin-cms-content-element", "content_id" => $content->id],
                "activeCallback" => function ($adminMenuItem) use ($content) {
                    return (bool)($content->id == \Yii::$app->request->get("content_id") && \Yii::$app->controller->uniqueId == 'shop/admin-cms-content-element');
                },

                "accessCallback" => function ($adminMenuItem) use ($content) {
                    $controller = \Yii::$app->createController('shop/admin-cms-content-element')[0];
                    $controller->setContent($content);
                    foreach ([$controller->permissionName] as $permissionName) {
                        if ($permission = \Yii::$app->authManager->getPermission($permissionName)) {
                            if (!\Yii::$app->user->can($permission->name)) {
                                return false;
                            }
                        }
                    }

                    return true;
                },

            ];

            $result[] = $itemData;
        }
    }

    return $result;
}

;

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

return [

    'shop' =>
        [
            'label'    => \Yii::t('skeeks/shop/app', 'Shop'),
            "img"      => ['\skeeks\cms\shop\assets\Asset', 'icons/e-commerce.png'],
            'priority' => 250,

            'items' =>
                [

                    [
                        'priority' => 0,
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
                            ],

                    ],

                    [
                        'priority' => 0,
                        'label'    => \Yii::t('skeeks/shop/app', 'Goods'),
                        "img"      => ['skeeks\cms\assets\CmsAsset', 'images/icons/icon.article.png'],

                        'items' => shopProductsMenu(),
                    ],

                    [
                        'priority' => 0,
                        'label'    => \Yii::t('skeeks/shop/app', 'Buyers'),
                        "img"      => ['\skeeks\cms\shop\assets\Asset', 'icons/buyers.png'],

                        'items' =>
                            [
                                /*[
                                    "label"     => "Покупатели",
                                    "url"       => ["shop/admin-buyer"],
                                ],*/

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

                                /*[
                                    "label" => \Yii::t('skeeks/shop/app', 'Accounts'),
                                    "url"   => ["shop/admin-user-account"],
                                ],*/

                                [
                                    "label" => \Yii::t('skeeks/shop/app', 'Baskets'),
                                    "url"   => ["shop/admin-fuser"],
                                ],

                                [
                                    "label" => \Yii::t('skeeks/shop/app', 'Notify on receipt (Email)'),
                                    "url"   => ["shop/admin-quantity-notice-email"],
                                ],

                                [
                                    "label" => \Yii::t('skeeks/shop/app', 'Viewed products'),
                                    "url"   => ["shop/admin-viewed-product"],
                                ],
                            ],

                    ],


                    [
                        'priority' => 0,
                        'label'    => \Yii::t('skeeks/shop/app', 'Marketing management'),
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
                                'items' =>
                                    [
                                        [
                                            "label" => \Yii::t('skeeks/shop/app', 'Discounts'),
                                            "url"   => ["shop/admin-discount"],
                                        ],

                                        [
                                            "label" => \Yii::t('skeeks/shop/app', 'Discount coupons'),
                                            "url"   => ["shop/admin-discount-coupon"],
                                        ],

                                    ],
                            ],

                            [
                                "label" => \Yii::t('skeeks/shop/app', 'Cumulative discounts'),
                                "url"   => ["shop/admin-discsave"],
                            ],

                        ],
                    ],

                    [
                        'priority' => 0,
                        'label'    => \Yii::t('skeeks/shop/app', 'Inventory control'),
                        "img"      => ['\skeeks\cms\shop\assets\Asset', 'icons/store.png'],

                        'items' =>
                            [
                                [
                                    "label" => \Yii::t('skeeks/shop/app', 'Stocks'),
                                    "url"   => ["shop/admin-store"],
                                    "img"   => ['\skeeks\cms\shop\assets\Asset', 'icons/store.png'],
                                ],

                            ],

                    ],


                    [
                        'priority' => 0,
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

                    ],


                    [
                        "label" => \Yii::t('skeeks/shop/app', 'Settings'),
                        "img"   => ['skeeks\cms\assets\CmsAsset', 'images/icons/settings.png'],

                        'items' =>
                            [
                                [
                                    "label"          => \Yii::t('skeeks/shop/app', 'Main settings'),
                                    "url"            => [
                                        "cms/admin-settings",
                                        "component" => 'skeeks\cms\shop\components\ShopComponent',
                                    ],
                                    "img"            => ['skeeks\cms\assets\CmsAsset', 'images/icons/settings.png'],
                                    "activeCallback" => function ($adminMenuItem) {
                                        return (bool)(\Yii::$app->request->getUrl() == $adminMenuItem->getUrl());
                                    },
                                ],

                                [
                                    "label" => \Yii::t('skeeks/shop/app', 'Content settings'),
                                    "url"   => ["shop/admin-content"],
                                ],

                                [
                                    "label" => \Yii::t('skeeks/shop/app', 'Order statuses'),
                                    "url"   => ["shop/admin-order-status"],
                                ],

                                [
                                    "label" => \Yii::t('skeeks/shop/app', 'Types of prices'),
                                    "url"   => ["shop/admin-type-price"],
                                ],

                                [
                                    "label" => \Yii::t('skeeks/shop/app', 'Types of profiles'),
                                    "url"   => ["shop/admin-person-type"],
                                ],

                                [
                                    "label" => \Yii::t('skeeks/shop/app', 'Payment systems'),
                                    "url"   => ["shop/admin-pay-system"],
                                ],

                                [
                                    "label" => \Yii::t('skeeks/shop/app', 'Delivery services'),
                                    "url"   => ["shop/admin-delivery"],
                                ],


                                [
                                    "label" => \Yii::t('skeeks/shop/app', 'Taxes'),
                                    'items' =>
                                        [
                                            [
                                                "label" => \Yii::t('skeeks/shop/app', 'List of taxes'),
                                                "url"   => ["shop/admin-tax"],
                                            ],

                                            [
                                                "label" => \Yii::t('skeeks/shop/app', 'Tax rates'),
                                                "url"   => ["shop/admin-tax-rate"],
                                            ],

                                            [
                                                "label" => \Yii::t('skeeks/shop/app', 'VAT rates'),
                                                "url"   => ["shop/admin-vat"],
                                            ],
                                        ],
                                ],

                            ],
                    ],


                    [
                        'priority' => 0,
                        'label'    => \Yii::t('skeeks/shop/app', 'Affiliates'),
                        "img"      => ['\skeeks\cms\shop\assets\Asset', 'icons/affiliate.png'],

                        'items' =>
                            [
                                [
                                    "label" => \Yii::t('skeeks/shop/app', 'Affiliates'),
                                    "url"   => ["shop/admin-affiliate"],
                                    "img"   => ['\skeeks\cms\shop\assets\Asset', 'icons/affiliate.png'],
                                ],

                                [
                                    "label" => \Yii::t('skeeks/shop/app', 'Plans of Commission'),
                                    "url"   => ["shop/admin-affiliate-plan"],
                                ],

                                [
                                    "label" => \Yii::t('skeeks/shop/app', 'Pyramid'),
                                    "url"   => ["shop/admin-affiliate-tier"],
                                ],
                            ],

                    ],
                ],
        ],
];