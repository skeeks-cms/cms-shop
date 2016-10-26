Shop on SkeekS CMS
===================================

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist skeeks/cms-shop "*"
```

or add

```
"skeeks/cms-shop": "*"
```

Configuration app
----------

```php

'components' =>
[
    'admin' => [
        'dashboards'         => [
            'Shop' =>
            [
                'skeeks\cms\shop\dashboards\ReportOrderDashboard'
            ]
        ],
    ],

    'shop' => [
        'class'         => 'skeeks\cms\shop\components\ShopComponent',
    ],

    'i18n' => [
        'translations'  =>
        [
            'skeeks/shop/app' =>
            [
                'class'             => 'yii\i18n\PhpMessageSource',
                'basePath'          => '@skeeks/cms/shop/messages',
                'fileMap' => [
                    'skeeks/shop/app' => 'app.php',
                ],
            ]
        ],
    ],

    'urlManager' => [
        'rules' => [
            '~shop-cart'                             => 'shop/cart',
            '~shop-<_a:(checkout|payment)>'          => 'shop/cart/<_a>',
            '~shop-<_a:(finish)>'                    => 'shop/order/<_a>',
            '~shop-order/<_a>'                       => 'shop/order/<_a>',
        ]
    ],
],

'modules' =>
[
    'shop' => [
        'class'         => 'skeeks\cms\shop\Module',
    ]
]

```



Pay systems
----------

 * paypal
 * robokassa
 * Yandex kassa
 * Tinkoff



##Links
* [Web site](http://en.cms.skeeks.com)
* [Web site (rus)](http://cms.skeeks.com)
* [Author](http://skeeks.com)
* [ChangeLog](https://github.com/skeeks-cms/cms-shop/blob/master/CHANGELOG.md)


___

> [![skeeks!](https://gravatar.com/userimage/74431132/13d04d83218593564422770b616e5622.jpg)](http://skeeks.com)  
<i>SkeekS CMS (Yii2) — quickly, easily and effectively!</i>  
[skeeks.com](http://skeeks.com) | [en.cms.skeeks.com](http://en.cms.skeeks.com) | [cms.skeeks.com](http://cms.skeeks.com) | [marketplace.cms.skeeks.com](http://marketplace.cms.skeeks.com)


