<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 30.01.2016
 */

namespace skeeks\cms\shop\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;

class AdminReportProductSearch extends Model
{
    public $from;
    public $to;

    public $onlyPayed;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['from', 'to'], 'safe'],
            [['onlyPayed'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'from' => \Yii::t('skeeks/shop/app', 'From'),
            'to' => \Yii::t('skeeks/shop/app', 'To'),
            'onlyPayed' => \Yii::t('skeeks/shop/app', 'Only prepaid orders')
        ];
    }

    public function getColumns()
    {
        return [
            [
                'attribute' => 'name',
                'label' => \Yii::t('skeeks/shop/app', 'Name'),
            ],

            [
                'attribute' => 'total_quantity',
                'label' => \Yii::t('skeeks/shop/app', 'Total'),
            ],
            /*
                [
                    'attribute' => 'total',
                    'label' => \Yii::t('skeeks/shop/app', 'Number of baskets'),
                ],*/

            [
                'attribute' => 'total_in_orders',
                'label' => \Yii::t('skeeks/shop/app', 'The total number of orders'),
            ],

            [
                'attribute' => 'total_orders',
                'label' => \Yii::t('skeeks/shop/app', 'Number of orders'),
            ],


            [
                'attribute' => 'total_in_payed_orders',
                'label' => \Yii::t('skeeks/shop/app', 'Total summ of prepaid orders'),
            ],


            [
                'attribute' => 'total_payed_orders',
                'label' => \Yii::t('skeeks/shop/app', 'The amount paid orders'),
            ],


            [
                'attribute' => 'total_in_carts',
                'label' => \Yii::t('skeeks/shop/app', 'Total in baskets'),
            ],


            [
                'attribute' => 'sum_in_payed_orders',
                'label' => \Yii::t('skeeks/shop/app', 'Price of the prepaid orders'),
            ],
        ];
    }

    /**
     * @param array|null $params
     * @return ActiveDataProvider
     */
    public function search($params = [])
    {
        $query = (new \yii\db\Query())->from('shop_basket b')->groupBy('b.product_id')->select(['b.name']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' =>
                [
                    'attributes' => array_keys(\yii\helpers\ArrayHelper::map($this->getColumns(), 'attribute',
                        'attribute')),
                    'defaultOrder' => [
                        'total_in_payed_orders' => SORT_DESC
                    ]
                ]
        ]);

        if ($params && ($this->load($params) && $this->validate())) {

            $query->addSelect([
                "count(*) as total",
                "sum(quantity) as total_quantity",
            ]);

            $query->where([
                ">",
                "b.product_id",
                0
            ]);

            //->andHaving([">", "total_payed_orders", "0"])

            $total_in_orders = "SELECT sum(quantity) FROM shop_basket WHERE product_id = b.product_id AND order_id != ''";
            $total_in_payed_orders = "SELECT sum(quantity) FROM shop_basket as inBasket LEFT JOIN shop_order as o on o.id = inBasket.order_id WHERE inBasket.product_id = b.product_id AND inBasket.order_id != '' AND o.payed = 'Y'";
            $sum_in_payed_orders = "SELECT sum(inBasket.price) FROM shop_basket as inBasket LEFT JOIN shop_order as o on o.id = inBasket.order_id WHERE inBasket.product_id = b.product_id AND inBasket.order_id != '' AND o.payed = 'Y'";
            $total_orders = "SELECT count(*) FROM shop_basket WHERE product_id = b.product_id AND order_id != ''";
            $total_payed_orders = "SELECT count(*) FROM shop_basket as inBasket LEFT JOIN shop_order as o on o.id = inBasket.order_id WHERE inBasket.product_id = b.product_id AND inBasket.order_id != '' AND o.payed = 'Y'";
            $total_in_carts = "SELECT sum(quantity) FROM shop_basket WHERE product_id = b.product_id AND fuser_id != ''";

            if ($this->from) {
                $query->andWhere(['>=', 'b.updated_at', (int)$this->from]);
                $total_in_orders = $total_in_orders . " AND updated_at >= " . (int)$this->from;
                $total_in_payed_orders = $total_in_payed_orders . " AND inBasket.updated_at >= " . (int)$this->from;
                $sum_in_payed_orders = $sum_in_payed_orders . " AND inBasket.updated_at >= " . (int)$this->from;
                $total_orders = $total_orders . " AND shop_basket.updated_at >= " . (int)$this->from;
                $total_payed_orders = $total_payed_orders . " AND inBasket.updated_at >= " . (int)$this->from;
                $total_in_carts = $total_in_carts . " AND shop_basket.updated_at >= " . (int)$this->from;
            }
            if ($this->to) {
                $query->andWhere(['<=', 'b.updated_at', (int)$this->to]);
                $total_in_orders = $total_in_orders . " AND updated_at <= " . (int)$this->to;
                $total_in_payed_orders = $total_in_payed_orders . " AND inBasket.updated_at <= " . (int)$this->to;
                $sum_in_payed_orders = $sum_in_payed_orders . " AND inBasket.updated_at <= " . (int)$this->to;
                $total_orders = $total_orders . " AND shop_basket.updated_at <= " . (int)$this->to;
                $total_payed_orders = $total_payed_orders . " AND inBasket.updated_at <= " . (int)$this->to;
                $total_in_carts = $total_in_carts . " AND shop_basket.updated_at <= " . (int)$this->to;
            }

            $query->addSelect([
                "({$total_in_orders}) as total_in_orders",
                "({$total_in_payed_orders}) as total_in_payed_orders",
                "({$total_orders}) as total_orders",
                "({$total_payed_orders}) as total_payed_orders",
                "({$total_in_carts}) as total_in_carts",
                "({$sum_in_payed_orders}) as sum_in_payed_orders",
            ]);

            if ($this->onlyPayed) {
                $query->leftJoin('shop_order as ord', 'ord.id = b.order_id');
                $query->andWhere(['ord.payed' => 'Y']);
            }
            return $dataProvider;

        } else {
            $query->addSelect([
                "count(*) as total",
                "sum(quantity) as total_quantity",

                "(SELECT sum(quantity) FROM shop_basket WHERE product_id = b.product_id AND order_id != '') as total_in_orders",
                "(SELECT sum(quantity) FROM shop_basket as inBasket LEFT JOIN shop_order as o on o.id = inBasket.order_id WHERE inBasket.product_id = b.product_id AND inBasket.order_id != '' AND o.payed = 'Y' ) as total_in_payed_orders",
                "(SELECT sum(inBasket.price) FROM shop_basket as inBasket LEFT JOIN shop_order as o on o.id = inBasket.order_id WHERE inBasket.product_id = b.product_id AND inBasket.order_id != '' AND o.payed = 'Y' ) as sum_in_payed_orders",

                "(SELECT count(*) FROM shop_basket WHERE product_id = b.product_id AND order_id != '' ) as total_orders",

                "(SELECT count(*) FROM shop_basket as inBasket LEFT JOIN shop_order as o on o.id = inBasket.order_id WHERE inBasket.product_id = b.product_id AND inBasket.order_id != '' AND o.payed = 'Y' ) as total_payed_orders",

                "(SELECT sum(quantity) FROM shop_basket WHERE product_id = b.product_id AND fuser_id != '') as total_in_carts",
            ]);

            $query->where([
                ">",
                "b.product_id",
                0
            ]);
        }


        return $dataProvider;
    }
}