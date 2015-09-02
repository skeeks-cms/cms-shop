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
                ]
            ],
        ]
    ]
];