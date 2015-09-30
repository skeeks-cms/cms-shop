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

    if ($contents = \skeeks\cms\models\CmsContent::find()->orderBy("priority DESC")->andWhere([
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
        'label' => 'Магазин',
        "img"       => ['\skeeks\cms\shop\assets\Asset', 'icons/shop.png'],

        'items' =>
        [

            [
                'priority'  => 0,
                'label'     => 'Заказы',

                'items' =>
                [
                    [
                        "label"     => "Заказы",
                        "url"       => ["shop/admin-order"],
                    ]
                ],

            ],

            [
                'priority'  => 0,
                'label'     => 'Покупатели',
                "img"       => ['\skeeks\cms\modules\admin\assets\AdminAsset', 'images/icons/user.png'],

                'items' =>
                [
                    [
                        "label"     => "Покупатели",
                        "url"       => ["shop/admin-buyer"],
                    ],

                    [
                        "label"     => "Счета",
                        "url"       => ["shop/admin-user-account"],
                    ],

                    [
                        "label"     => "Корзины",
                        "url"       => ["shop/admin-fuser"],
                    ]
                ],

            ],



            [
                'priority'  => 0,
                'label'     => 'Товары',
                "img"       => ['\skeeks\cms\modules\admin\assets\AdminAsset', 'images/icons/icon.article.png'],

                'items' => shopProductsMenu()
            ],

            [
                "label"     => "Настройки",
                "img"       => ['\skeeks\cms\modules\admin\assets\AdminAsset', 'images/icons/settings.png'],

                'items' =>
                [
                    [
                        "label"     => "Настройка контента",
                        "url"       => ["shop/admin-content"],
                    ],

                    [
                        "label"     => "Статусы",
                        "url"       => ["shop/admin-order-status"],
                    ],

                    [
                        "label"     => "Типы цен",
                        "url"       => ["shop/admin-type-price"],
                    ],

                    [
                        "label"     => "Типы плательщиков",
                        "url"       => ["shop/admin-person-type"],
                    ],

                    [
                        "label"     => "Платежные системы",
                        "url"       => ["shop/admin-pay-system"],
                    ],

                    [
                        "label"     => "Службы доставки",
                        "url"       => ["shop/admin-delivery"],
                    ],


                    [
                        "label"     => "Налоги",
                        'items' =>
                        [
                            [
                                "label"     => "Список налогов",
                                "url"       => ["shop/admin-tax"],
                            ],

                            [
                                "label"     => "Ставки налогов",
                                "url"       => ["shop/admin-tax-rate"],
                            ],

                            [
                                "label"     => "Ставки НДС",
                                "url"       => ["shop/admin-vat"],
                            ],
                        ],
                    ],

                    [
                        "label"     => "Наценки",
                        "url"       => ["shop/admin-extra"],
                    ],

                    [
                        "label"     => "Валюты",
                        "img"       => ['\skeeks\modules\cms\money\assets\Asset', 'images/money_16_16.png'],

                        'items' =>
                        [
                            [
                                "label"     => "Валюты",
                                "url"       => ["money/admin-currency"],
                                "img"       => ['\skeeks\modules\cms\money\assets\Asset', 'images/money_16_16.png']
                            ],

                            [
                                "label" => "Настройки",
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
                        "label"     => "База местопложений",
                        "img"       => ['\skeeks\cms\kladr\assets\Asset', 'icons/global.png'],

                        'items' =>
                        [
                            [
                                "label"     => "База местопложений",
                                "url"       => ["kladr/admin-kladr-location"],
                                "img"       => ['\skeeks\cms\kladr\assets\Asset', 'icons/global.png'],
                            ],

                            [
                                "label" => "Настройки",
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
                        "label"     => "Единицы измерений",
                        "img"       => ['\skeeks\cms\measure\assets\Asset', 'icons/misc.png'],

                        'items' =>
                        [
                            [
                                "label"     => "Единицы измерений",
                                "url"       => ["measure/admin-measure"],
                                "img"       => ['\skeeks\cms\measure\assets\Asset', 'icons/misc.png'],
                            ],

                            [
                                "label" => "Настройки",
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
                'label'     => 'Складской учет',

                'items' =>
                [
                    [
                        "label"     => "Склады",
                        "url"       => ["shop/admin-store"],
                    ],

                ],

            ],

            [
                'priority'  => 0,
                'label'     => 'Аффилиаты',
                "img"       => ['\skeeks\cms\shop\assets\Asset', 'icons/affiliate-icon.png'],

                'items' =>
                [
                    [
                        "label"     => "Аффилиаты",
                        "url"       => ["shop/admin-affiliate"],
                    ],

                    [
                        "label"     => "Планы коммисий",
                        "url"       => ["shop/admin-affiliate-plan"],
                    ],

                    [
                        "label"     => "Пирамида",
                        "url"       => ["shop/admin-affiliate-tier"],
                    ]
                ],

            ],
        ]
    ]
];