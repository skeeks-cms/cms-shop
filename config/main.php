<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 27.08.2015
 */
return [

    'components' =>
    [
        'shop' => [
            'class'         => 'skeeks\cms\shop\components\ShopComponent',
        ]
    ],

    'modules' =>
    [
        'shop' => [
            'class'         => 'skeeks\cms\shop\Module',
        ]
    ]
];