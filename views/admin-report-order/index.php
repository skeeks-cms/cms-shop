<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 01.11.2015
 */
/* @var $this yii\web\View */

$query = (new \yii\db\Query())->from('shop_order')->select([
    '*',
    "count(*) as total_orders",
    "sum(price) as sum_price",
    "FROM_UNIXTIME(created_at, '%d.%m.%Y') as created_date",

    "(SELECT count(*) FROM shop_order WHERE payed = 'Y' AND FROM_UNIXTIME(created_at, '%d.%m.%Y') = created_date) as total_payed",
    "(SELECT sum(price) FROM shop_order WHERE payed = 'Y' AND FROM_UNIXTIME(created_at, '%d.%m.%Y') = created_date) as sum_payed",


    "(SELECT count(*) FROM shop_order WHERE canceled = 'Y' AND FROM_UNIXTIME(created_at, '%d.%m.%Y') = created_date) as total_canceled",
    "(SELECT sum(price) FROM shop_order WHERE canceled = 'Y' AND FROM_UNIXTIME(created_at, '%d.%m.%Y') = created_date) as sum_canceled",
])
    ->groupBy('created_date')
//echo $query->createCommand()->sql;die;

?>

<?
$columns =
[
    [
        'attribute' => 'created_date',
        'label' => 'Дата',
    ],
    [
        'attribute' => 'total_orders',
        'label' => 'Общее количество',
    ],
    [
        'attribute' => 'total_payed',
        'label' => 'Кол-во оплаченных',
    ],
    [
        'attribute' => 'total_canceled',
        'label' => 'Кол-во отмененных',
    ],
    [
        'attribute' => 'sum_price',
        'label' => 'Стоимость',
    ],
    [
        'attribute' => 'sum_payed',
        'label' => 'Стоимость оплаченных',
    ],
    [
        'attribute' => 'sum_canceled',
        'label' => 'Стоимость отмененных',
    ],
];

?>

<?= \skeeks\cms\modules\admin\widgets\GridView::widget([
    'dataProvider' => new \yii\data\ActiveDataProvider([
        'query' => $query,
        'sort'              => [
            'attributes' => array_keys( \yii\helpers\ArrayHelper::map($columns, 'attribute', 'attribute') ),
            'defaultOrder' =>
            [
                'total_payed' => SORT_DESC
            ]
        ],
    ]),
    'columns' => $columns,
]); ?>

