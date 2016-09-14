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
                'url'   => ["shop/admin-cms-content-element", "content_id" => $content->id],
            ];

            $result[] = new \skeeks\cms\modules\admin\helpers\AdminMenuItemCmsConent($itemData);
        }
    }

    return $result;
};

return [

    'shop' =>
    [
        'label' => \Yii::t('skeeks/shop/app', 'Shop'),
        "img"       => ['\skeeks\cms\shop\assets\Asset', 'icons/e-commerce.png'],
        'priority'  => 250,

        'items' =>
        [

            [
                'priority'  => 0,
                'label'     => \Yii::t('skeeks/shop/app', 'Orders'),
                "img"       => ['\skeeks\cms\shop\assets\Asset', 'icons/orders.png'],

                'items' =>
                [
                    [
                        "label"     => \Yii::t('skeeks/shop/app', 'Orders'),
                        "url"       => ["shop/admin-order"],
                        "img"       => ['\skeeks\cms\shop\assets\Asset', 'icons/orders.png'],
                    ]
                ],

            ],

            [
                'priority'  => 0,
                'label'     => \Yii::t('skeeks/shop/app', 'Goods'),
                "img"       => ['\skeeks\cms\modules\admin\assets\AdminAsset', 'images/icons/icon.article.png'],

                'items' => shopProductsMenu()
            ],

            [
                'priority'  => 0,
                'label'     => \Yii::t('skeeks/shop/app', 'Buyers'),
                "img"       => ['\skeeks\cms\shop\assets\Asset', 'icons/buyers.png'],

                'items' =>
                [
                    /*[
                        "label"     => "Покупатели",
                        "url"       => ["shop/admin-buyer"],
                    ],*/

                    [
                        "label"     => \Yii::t('skeeks/shop/app', 'Buyers'),
                        "url"       => ["shop/admin-buyer-user"],
                        "img"       => ['\skeeks\cms\shop\assets\Asset', 'icons/buyers.png'],
                    ],

                    [
                        "label"     => \Yii::t('skeeks/shop/app', 'Accounts'),
                        "url"       => ["shop/admin-user-account"],
                    ],

                    [
                        "label"     => \Yii::t('skeeks/shop/app', 'Baskets'),
                        "url"       => ["shop/admin-fuser"],
                    ],

                    [
                        "label"     => \Yii::t('skeeks/shop/app', 'Viewed products'),
                        "url"       => ["shop/admin-viewed-product"],
                    ]
                ],

            ],



            [
                'priority'  => 0,
                'label'     => \Yii::t('skeeks/shop/app', 'Marketing management'),
                "img"       => ['\skeeks\cms\shop\assets\Asset', 'icons/marketing.png'],

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
                        "label"     => \Yii::t('skeeks/shop/app', 'Discount goods'),
                        'items' =>
                        [
                            [
                                "label"     => \Yii::t('skeeks/shop/app', 'Discount goods'),
                                "url"       => ["shop/admin-discount"],
                            ],

                            /*[
                                "label"     => \Yii::t('skeeks/shop/app', 'Coupons discount goods'),
                                "url"       => ["shop/admin-cupon"],
                            ]*/

                        ]
                    ],

                    [
                        "label"     => \Yii::t('skeeks/shop/app', 'Cumulative discounts'),
                        "url"       => ["shop/admin-discsave"],
                    ],

                ]
            ],

            [
                'priority'  => 0,
                'label'     => \Yii::t('skeeks/shop/app', 'Inventory control'),
                "img"       => ['\skeeks\cms\shop\assets\Asset', 'icons/store.png'],

                'items' =>
                [
                    [
                        "label"     => \Yii::t('skeeks/shop/app', 'Stocks'),
                        "url"       => ["shop/admin-store"],
                        "img"       => ['\skeeks\cms\shop\assets\Asset', 'icons/store.png'],
                    ],

                ],

            ],


            [
                'priority'  => 0,
                'label'     => \Yii::t('skeeks/shop/app', 'Reports'),
                "img"       => ['\skeeks\cms\shop\assets\Asset', 'icons/reports.png'],

                'items' =>
                [
                    [
                        "label"     => \Yii::t('skeeks/shop/app', 'Reports on orders'),
                        "url"       => ["shop/admin-report-order"],
                    ],

                    [
                        "label"     => \Yii::t('skeeks/shop/app', 'Reports on products'),
                        "url"       => ["shop/admin-report-product"],
                    ],

                ],

            ],


            [
                "label"     => \Yii::t('skeeks/shop/app', 'Settings'),
                "img"       => ['\skeeks\cms\modules\admin\assets\AdminAsset', 'images/icons/settings.png'],

                'items' =>
                [
                    [
                        "label" => \Yii::t('skeeks/shop/app', 'Main settings'),
                        "url"   => ["cms/admin-settings", "component" => 'skeeks\cms\shop\components\ShopComponent'],
                        "img"       => ['\skeeks\cms\modules\admin\assets\AdminAsset', 'images/icons/settings.png'],
                        "activeCallback"       => function(\skeeks\cms\modules\admin\helpers\AdminMenuItem $adminMenuItem)
                        {
                            return (bool) (\Yii::$app->request->getUrl() == $adminMenuItem->getUrl());
                        },
                    ],

                    [
                        "label"     => \Yii::t('skeeks/shop/app', 'Content settings'),
                        "url"       => ["shop/admin-content"],
                    ],

                    [
                        "label"     => \Yii::t('skeeks/shop/app', 'Order statuses'),
                        "url"       => ["shop/admin-order-status"],
                    ],

                    [
                        "label"     => \Yii::t('skeeks/shop/app', 'Types of prices'),
                        "url"       => ["shop/admin-type-price"],
                    ],

                    [
                        "label"     => \Yii::t('skeeks/shop/app', 'Types of profiles'),
                        "url"       => ["shop/admin-person-type"],
                    ],

                    [
                        "label"     => \Yii::t('skeeks/shop/app', 'Payment systems'),
                        "url"       => ["shop/admin-pay-system"],
                    ],

                    [
                        "label"     => \Yii::t('skeeks/shop/app', 'Delivery services'),
                        "url"       => ["shop/admin-delivery"],
                    ],


                    [
                        "label"     => \Yii::t('skeeks/shop/app', 'Taxes'),
                        'items' =>
                        [
                            [
                                "label"     => \Yii::t('skeeks/shop/app', 'List of taxes'),
                                "url"       => ["shop/admin-tax"],
                            ],

                            [
                                "label"     => \Yii::t('skeeks/shop/app', 'Tax rates'),
                                "url"       => ["shop/admin-tax-rate"],
                            ],

                            [
                                "label"     => \Yii::t('skeeks/shop/app', 'VAT rates'),
                                "url"       => ["shop/admin-vat"],
                            ],
                        ],
                    ],

                    [
                        "label"     => \Yii::t('skeeks/shop/app', 'Surcharges'),
                        "url"       => ["shop/admin-extra"],
                    ],
                ]
            ],



            [
                'priority'  => 0,
                'label'     => \Yii::t('skeeks/shop/app', 'Affiliates'),
                "img"       => ['\skeeks\cms\shop\assets\Asset', 'icons/affiliate.png'],

                'items' =>
                [
                    [
                        "label"     => \Yii::t('skeeks/shop/app', 'Affiliates'),
                        "url"       => ["shop/admin-affiliate"],
                        "img"       => ['\skeeks\cms\shop\assets\Asset', 'icons/affiliate.png'],
                    ],

                    [
                        "label"     => \Yii::t('skeeks/shop/app', 'Plans of Commission'),
                        "url"       => ["shop/admin-affiliate-plan"],
                    ],

                    [
                        "label"     => \Yii::t('skeeks/shop/app', 'Pyramid'),
                        "url"       => ["shop/admin-affiliate-tier"],
                    ]
                ],

            ],
        ]
    ]
];