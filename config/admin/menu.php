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

    try
    {
        $table = \skeeks\cms\models\CmsContent::getTableSchema();
        $table = \skeeks\cms\shop\models\ShopContent::getTableSchema();
    } catch (\Exception $e)
    {
        return $result;
    }

    if ($contents = \skeeks\cms\models\CmsContent::find()->orderBy("priority ASC")->andWhere([
        'id' => \yii\helpers\ArrayHelper::map(\skeeks\cms\shop\models\ShopContent::find()->all(), 'content_id', 'content_id')
    ])->all())
    {
        /**
         * @var $content \skeeks\cms\models\CmsContent
         */
        foreach ($contents as $content)
        {
            $itemData = [
                'label'     => $content->name,
                "img"       => ['\skeeks\cms\modules\admin\assets\AdminAsset', 'images/icons/icon.article.png'],
                'url'   => ["shop/admin-cms-content-element/index", "content_id" => $content->id],
            ];

            $result[] = new \skeeks\cms\modules\admin\helpers\AdminMenuItemCmsConent($itemData);
        }
    }

    return $result;
};

return [

    'shop' =>
    [
        'label' => \skeeks\cms\shop\Module::t('app', 'Shop'),
        "img"       => ['\skeeks\cms\shop\assets\Asset', 'icons/buyers.png'],

        'items' =>
        [

            [
                'priority'  => 0,
                'label'     => \skeeks\cms\shop\Module::t('app', 'Orders'),
                "img"       => ['\skeeks\cms\shop\assets\Asset', 'icons/orders.png'],

                'items' =>
                [
                    [
                        "label"     => \skeeks\cms\shop\Module::t('app', 'Orders'),
                        "url"       => ["shop/admin-order"],
                        "img"       => ['\skeeks\cms\shop\assets\Asset', 'icons/orders.png'],
                    ]
                ],

            ],

            [
                'priority'  => 0,
                'label'     => \skeeks\cms\shop\Module::t('app', 'Goods'),
                "img"       => ['\skeeks\cms\modules\admin\assets\AdminAsset', 'images/icons/icon.article.png'],

                'items' => shopProductsMenu()
            ],

            [
                'priority'  => 0,
                'label'     => \skeeks\cms\shop\Module::t('app', 'Buyers'),
                "img"       => ['\skeeks\cms\shop\assets\Asset', 'icons/buyers.png'],

                'items' =>
                [
                    /*[
                        "label"     => "Покупатели",
                        "url"       => ["shop/admin-buyer"],
                    ],*/

                    [
                        "label"     => \skeeks\cms\shop\Module::t('app', 'Buyers'),
                        "url"       => ["shop/admin-buyer-user"],
                        "img"       => ['\skeeks\cms\shop\assets\Asset', 'icons/buyers.png'],
                    ],

                    [
                        "label"     => \skeeks\cms\shop\Module::t('app', 'Accounts'),
                        "url"       => ["shop/admin-user-account"],
                    ],

                    [
                        "label"     => \skeeks\cms\shop\Module::t('app', 'Baskets'),
                        "url"       => ["shop/admin-fuser"],
                    ],

                    [
                        "label"     => \skeeks\cms\shop\Module::t('app', 'Viewed products'),
                        "url"       => ["shop/admin-viewed-product"],
                    ]
                ],

            ],



            [
                'priority'  => 0,
                'label'     => \skeeks\cms\shop\Module::t('app', 'Marketing management'),
                "img"       => ['\skeeks\cms\shop\assets\Asset', 'icons/marketing.png'],

                'items' => [

                    /*[
                        "label"     => \skeeks\cms\shop\Module::t('app', 'Rules for the basket'),
                        'items' =>
                        [
                            [
                                "label"     => \skeeks\cms\shop\Module::t('app', 'Rules for the basket'),
                                "url"       => ["shop/admin-discount1"],
                            ],

                            [
                                "label"     => \skeeks\cms\shop\Module::t('app', 'Coupons of rules work to basket'),
                                "url"       => ["shop/admin-discsav1e"],
                            ]

                        ]
                    ],*/

                    [
                        "label"     => \skeeks\cms\shop\Module::t('app', 'Discount goods'),
                        'items' =>
                        [
                            [
                                "label"     => \skeeks\cms\shop\Module::t('app', 'Discount goods'),
                                "url"       => ["shop/admin-discount"],
                            ],

                            /*[
                                "label"     => \skeeks\cms\shop\Module::t('app', 'Coupons discount goods'),
                                "url"       => ["shop/admin-cupon"],
                            ]*/

                        ]
                    ],

                    [
                        "label"     => \skeeks\cms\shop\Module::t('app', 'Cumulative discounts'),
                        "url"       => ["shop/admin-discsave"],
                    ],

                ]
            ],

            [
                'priority'  => 0,
                'label'     => \skeeks\cms\shop\Module::t('app', 'Inventory control'),
                "img"       => ['\skeeks\cms\shop\assets\Asset', 'icons/store.png'],

                'items' =>
                [
                    [
                        "label"     => \skeeks\cms\shop\Module::t('app', 'Stocks'),
                        "url"       => ["shop/admin-store"],
                        "img"       => ['\skeeks\cms\shop\assets\Asset', 'icons/store.png'],
                    ],

                ],

            ],


            [
                'priority'  => 0,
                'label'     => \skeeks\cms\shop\Module::t('app', 'Reports'),
                "img"       => ['\skeeks\cms\shop\assets\Asset', 'icons/reports.png'],

                'items' =>
                [
                    [
                        "label"     => \skeeks\cms\shop\Module::t('app', 'Orders'),
                        "url"       => ["shop/admin-store"],
                    ],

                    [
                        "label"     => \skeeks\cms\shop\Module::t('app', 'Goods'),
                        "url"       => ["shop/admin-store"],
                    ],

                ],

            ],


            [
                "label"     => \skeeks\cms\shop\Module::t('app', 'Settings'),
                "img"       => ['\skeeks\cms\modules\admin\assets\AdminAsset', 'images/icons/settings.png'],

                'items' =>
                [
                    [
                        "label" => \skeeks\cms\shop\Module::t('app', 'Main settings'),
                        "url"   => ["cms/admin-settings", "component" => 'skeeks\cms\shop\components\ShopComponent'],
                        "img"       => ['\skeeks\cms\modules\admin\assets\AdminAsset', 'images/icons/settings.png'],
                        "activeCallback"       => function(\skeeks\cms\modules\admin\helpers\AdminMenuItem $adminMenuItem)
                        {
                            return (bool) (\Yii::$app->request->getUrl() == $adminMenuItem->getUrl());
                        },
                    ],

                    [
                        "label"     => \skeeks\cms\shop\Module::t('app', 'Content settings'),
                        "url"       => ["shop/admin-content"],
                    ],

                    [
                        "label"     => \skeeks\cms\shop\Module::t('app', 'Updates'),
                        "url"       => ["shop/admin-order-status"],
                    ],

                    [
                        "label"     => \skeeks\cms\shop\Module::t('app', 'Types of prices'),
                        "url"       => ["shop/admin-type-price"],
                    ],

                    [
                        "label"     => \skeeks\cms\shop\Module::t('app', 'Types of payers'),
                        "url"       => ["shop/admin-person-type"],
                    ],

                    [
                        "label"     => \skeeks\cms\shop\Module::t('app', 'Payment systems'),
                        "url"       => ["shop/admin-pay-system"],
                    ],

                    [
                        "label"     => \skeeks\cms\shop\Module::t('app', 'Delivery services'),
                        "url"       => ["shop/admin-delivery"],
                    ],


                    [
                        "label"     => \skeeks\cms\shop\Module::t('app', 'Taxes'),
                        'items' =>
                        [
                            [
                                "label"     => \skeeks\cms\shop\Module::t('app', 'List of taxes'),
                                "url"       => ["shop/admin-tax"],
                            ],

                            [
                                "label"     => \skeeks\cms\shop\Module::t('app', 'Tax rates'),
                                "url"       => ["shop/admin-tax-rate"],
                            ],

                            [
                                "label"     => \skeeks\cms\shop\Module::t('app', 'VAT rates'),
                                "url"       => ["shop/admin-vat"],
                            ],
                        ],
                    ],

                    [
                        "label"     => \skeeks\cms\shop\Module::t('app', 'Surcharges'),
                        "url"       => ["shop/admin-extra"],
                    ],

                    [
                        "label"     => \skeeks\cms\shop\Module::t('app', 'Currency'),
                        "img"       => ['\skeeks\modules\cms\money\assets\Asset', 'images/money_16_16.png'],

                        'items' =>
                        [
                            [
                                "label"     => \skeeks\cms\shop\Module::t('app', 'Currency'),
                                "url"       => ["money/admin-currency"],
                                "img"       => ['\skeeks\modules\cms\money\assets\Asset', 'images/money_16_16.png']
                            ],

                            [
                                "label" => \skeeks\cms\shop\Module::t('app', 'Settings'),
                                "url"   => ["cms/admin-settings", "component" => 'skeeks\modules\cms\money\components\money\Money'],
                                "img"       => ['\skeeks\cms\modules\admin\assets\AdminAsset', 'images/icons/settings.png'],
                                "activeCallback"       => function(\skeeks\cms\modules\admin\helpers\AdminMenuItem $adminMenuItem)
                                {
                                    return (bool) (\Yii::$app->request->getUrl() == $adminMenuItem->getUrl());
                                },
                            ],
                        ]
                    ],

                    [
                        "label"     => \skeeks\cms\shop\Module::t('app', 'Base of locations'),
                        "img"       => ['\skeeks\cms\kladr\assets\Asset', 'icons/global.png'],

                        'items' =>
                        [
                            [
                                "label"     => \skeeks\cms\shop\Module::t('app', 'Base of locations'),
                                "url"       => ["kladr/admin-kladr-location"],
                                "img"       => ['\skeeks\cms\kladr\assets\Asset', 'icons/global.png'],
                            ],

                            [
                                "label" => \skeeks\cms\shop\Module::t('app', 'Settings'),
                                "url"   => ["cms/admin-settings", "component" => 'skeeks\cms\kladr\components\KladrComponent'],
                                "img"       => ['\skeeks\cms\modules\admin\assets\AdminAsset', 'images/icons/settings.png'],
                                "activeCallback"       => function(\skeeks\cms\modules\admin\helpers\AdminMenuItem $adminMenuItem)
                                {
                                    return (bool) (\Yii::$app->request->getUrl() == $adminMenuItem->getUrl());
                                },
                            ],
                        ]
                    ],

                    [
                        "label"     => \skeeks\cms\shop\Module::t('app', 'Units of measurement'),
                        "img"       => ['\skeeks\cms\measure\assets\Asset', 'icons/misc.png'],

                        'items' =>
                        [
                            [
                                "label"     => \skeeks\cms\shop\Module::t('app', 'Units of measurement'),
                                "url"       => ["measure/admin-measure"],
                                "img"       => ['\skeeks\cms\measure\assets\Asset', 'icons/misc.png'],
                            ],

                            [
                                "label" => \skeeks\cms\shop\Module::t('app', 'Settings'),
                                "url"   => ["cms/admin-settings", "component" => 'skeeks\cms\measure\components\MeasureComponent'],
                                "img"       => ['\skeeks\cms\modules\admin\assets\AdminAsset', 'images/icons/settings.png'],
                                "activeCallback"       => function(\skeeks\cms\modules\admin\helpers\AdminMenuItem $adminMenuItem)
                                {
                                    return (bool) (\Yii::$app->request->getUrl() == $adminMenuItem->getUrl());
                                },
                            ],
                        ]
                    ],
                ]
            ],



            [
                'priority'  => 0,
                'label'     => \skeeks\cms\shop\Module::t('app', 'Affiliates'),
                "img"       => ['\skeeks\cms\shop\assets\Asset', 'icons/affiliate.png'],

                'items' =>
                [
                    [
                        "label"     => \skeeks\cms\shop\Module::t('app', 'Affiliates'),
                        "url"       => ["shop/admin-affiliate"],
                        "img"       => ['\skeeks\cms\shop\assets\Asset', 'icons/affiliate.png'],
                    ],

                    [
                        "label"     => \skeeks\cms\shop\Module::t('app', 'Plans of Commission'),
                        "url"       => ["shop/admin-affiliate-plan"],
                    ],

                    [
                        "label"     => \skeeks\cms\shop\Module::t('app', 'Pyramid'),
                        "url"       => ["shop/admin-affiliate-tier"],
                    ]
                ],

            ],
        ]
    ]
];