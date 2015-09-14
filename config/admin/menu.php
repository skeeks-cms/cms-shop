<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 12.03.2015
 */
return [

    'shop' =>
    [
        'label' => 'Магазин',
        "img"       => ['\skeeks\cms\shop\assets\Asset', 'icons/shop.png'],

        'items' =>
        [
            [
                "label"     => "Настройки",
                "img"       => ['\skeeks\cms\modules\admin\assets\AdminAsset', 'images/icons/settings.png'],

                'items' =>
                [
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
        ]
    ]
];