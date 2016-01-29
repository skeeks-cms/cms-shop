<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 01.11.2015
 */
/* @var $this yii\web\View */

$query = (new \yii\db\Query())->from('shop_basket b')->select([
    '*',
    "count(*) as total",
    "sum(quantity) as total_quantity",
    "sum(price) as sum_price",

    "(SELECT sum(quantity) FROM shop_basket WHERE product_id = b.product_id AND order_id != '') as total_in_orders",
    "(SELECT sum(quantity) FROM shop_basket as inBasket LEFT JOIN shop_order as o on o.id = inBasket.order_id WHERE inBasket.product_id = b.product_id AND inBasket.order_id != '' AND o.payed = 'Y' ) as total_in_payed_orders",

    "(SELECT count(*) FROM shop_basket WHERE product_id = b.product_id AND order_id != '' ) as total_orders",

    "(SELECT count(*) FROM shop_basket as inBasket LEFT JOIN shop_order as o on o.id = inBasket.order_id WHERE inBasket.product_id = b.product_id AND inBasket.order_id != '' AND o.payed = 'Y' ) as total_payed_orders",

    "(SELECT sum(quantity) FROM shop_basket WHERE product_id = b.product_id AND fuser_id != '') as total_in_carts",
])
    ->where([
        "!=", "product_id", ""
    ])
    //->andHaving([">", "total_payed_orders", "0"])
    ->groupBy('product_id')
//echo $query->createCommand()->sql;die;

?>

<?
$columns =
[
    [
        'attribute' => 'name',
        'label' => 'Название',
    ],

    [
        'attribute' => 'total_quantity',
        'label' => 'Общее количество',
    ],
/*
    [
        'attribute' => 'total',
        'label' => 'Количество корзин',
    ],*/

    [
        'attribute' => 'total_in_orders',
        'label' => 'Общее количество в заказах',
    ],

    [
        'attribute' => 'total_orders',
        'label' => 'Количество заказов',
    ],


    [
        'attribute' => 'total_in_payed_orders',
        'label' => 'Общее количество в оплаченных заказов',
    ],



    [
        'attribute' => 'total_payed_orders',
        'label' => 'Количество оплаченных заказов',
    ],


    [
        'attribute' => 'total_in_carts',
        'label' => 'Общее количество в корзинах',
    ],

    'sum_price',
];

?>

<?= \skeeks\cms\modules\admin\widgets\GridView::widget([
    'dataProvider' => new \yii\data\ActiveDataProvider([
        'query' => $query,
        'sort'              => [
            'attributes' => array_keys( \yii\helpers\ArrayHelper::map($columns, 'attribute', 'attribute') ),
            'defaultOrder' =>
            [
                'total_in_payed_orders' => SORT_DESC
            ]
        ],
    ]),

    'columns' => $columns,
]); ?>

