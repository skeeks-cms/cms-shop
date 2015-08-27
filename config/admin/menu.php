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

        'items' =>
        [
            [
                "label"     => "Настройки",
                "img"       => ['\skeeks\cms\modules\admin\assets\AdminAsset', 'images/icons/settings.png'],

                'items' =>
                [
                    [
                        "label"     => "Типы цен",
                        "url"       => ["shop/admin-price"],
                    ],
                ]
            ],
        ]
    ]
];