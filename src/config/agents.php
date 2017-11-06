<?php
return [
    'shop/agents/delete-empty-carts' =>
    [
        'description'       => \Yii::t('skeeks/shop/app','Remove empty baskets'),
        'agent_interval'    => 3600*6, //раз в 6
    ],

    'shop/flush/price-changes' =>
    [
        'description'       => \Yii::t('skeeks/shop/app', 'Removing the old price changes'),
        'agent_interval'    => 3600*24, //раз в 6
    ],

    'shop/notify/quantity-emails' =>
    [
        'description'       => \Yii::t('skeeks/shop/app', 'Notify admission'),
        'agent_interval'    => 60*10, //раз в 10 минут
    ]
];