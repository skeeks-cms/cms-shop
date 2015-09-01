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
                                "label"     => "Ставки НДС",
                                "url"       => ["shop/admin-vat"],
                            ],
                        ],
                    ],
                ]
            ],
        ]
    ]
];