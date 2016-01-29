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

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['from', 'to'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'from' => 'От',
            'to'    => 'До'
        ];
    }

    public function getColumns()
    {
        return [
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
    }

    /**
     * @param array|null $params
     * @return ActiveDataProvider
     */
    public function search($params = [])
    {
        $query = (new \yii\db\Query())->from('shop_basket b')->groupBy('b.product_id')->select(['*']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' =>
            [
                'attributes' => array_keys( \yii\helpers\ArrayHelper::map($this->getColumns(), 'attribute', 'attribute') ),
                'defaultOrder' => [
                    'total_in_payed_orders' => SORT_DESC
                ]
            ]
        ]);

        if ($params && ($this->load($params) && $this->validate()) )
        {

            $query->addSelect([
                "count(*) as total",
                "sum(quantity) as total_quantity",
                "sum(price) as sum_price",
            ]);

            $query->where([
                ">", "b.product_id", 0
            ]);

            //->andHaving([">", "total_payed_orders", "0"])

            $total_in_orders        = "SELECT sum(quantity) FROM shop_basket WHERE product_id = b.product_id AND order_id != ''";
            $total_in_payed_orders  = "SELECT sum(quantity) FROM shop_basket as inBasket LEFT JOIN shop_order as o on o.id = inBasket.order_id WHERE inBasket.product_id = b.product_id AND inBasket.order_id != '' AND o.payed = 'Y'";
            $total_orders           = "SELECT count(*) FROM shop_basket WHERE product_id = b.product_id AND order_id != ''";
            $total_payed_orders     = "SELECT count(*) FROM shop_basket as inBasket LEFT JOIN shop_order as o on o.id = inBasket.order_id WHERE inBasket.product_id = b.product_id AND inBasket.order_id != '' AND o.payed = 'Y'";
            $total_in_carts         = "SELECT sum(quantity) FROM shop_basket WHERE product_id = b.product_id AND fuser_id != ''";

            if ($this->from)
            {
                $query->andWhere(['>=', 'b.updated_at', (int) $this->from]);
                $total_in_orders = $total_in_orders . " AND updated_at >= " . (int) $this->from;
                $total_in_payed_orders = $total_in_payed_orders . " AND inBasket.updated_at >= " . (int) $this->from;
                $total_orders = $total_orders . " AND shop_basket.updated_at >= " . (int) $this->from;
                $total_payed_orders = $total_payed_orders . " AND inBasket.updated_at >= " . (int) $this->from;
                $total_in_carts = $total_in_carts . " AND shop_basket.updated_at >= " . (int) $this->from;
            }
            if ($this->to)
            {
                $query->andWhere(['<=', 'b.updated_at', (int) $this->to]);
                $total_in_orders = $total_in_orders . " AND updated_at <= " . (int) $this->to;
                $total_in_payed_orders = $total_in_payed_orders . " AND inBasket.updated_at <= " . (int) $this->to;
                $total_orders = $total_orders . " AND shop_basket.updated_at <= " . (int) $this->to;
                $total_payed_orders = $total_payed_orders . " AND inBasket.updated_at <= " . (int) $this->to;
                $total_in_carts = $total_in_carts . " AND shop_basket.updated_at <= " . (int) $this->to;
            }

            $query->addSelect([
                "({$total_in_orders}) as total_in_orders",
                "({$total_in_payed_orders}) as total_in_payed_orders",
                "({$total_orders}) as total_orders",
                "({$total_payed_orders}) as total_payed_orders",
                "({$total_in_carts}) as total_in_carts",
            ]);

            return $dataProvider;

        } else
        {
            $query->addSelect([
                "count(*) as total",
                "sum(quantity) as total_quantity",
                "sum(price) as sum_price",

                "(SELECT sum(quantity) FROM shop_basket WHERE product_id = b.product_id AND order_id != '') as total_in_orders",
                "(SELECT sum(quantity) FROM shop_basket as inBasket LEFT JOIN shop_order as o on o.id = inBasket.order_id WHERE inBasket.product_id = b.product_id AND inBasket.order_id != '' AND o.payed = 'Y' ) as total_in_payed_orders",

                "(SELECT count(*) FROM shop_basket WHERE product_id = b.product_id AND order_id != '' ) as total_orders",

                "(SELECT count(*) FROM shop_basket as inBasket LEFT JOIN shop_order as o on o.id = inBasket.order_id WHERE inBasket.product_id = b.product_id AND inBasket.order_id != '' AND o.payed = 'Y' ) as total_payed_orders",

                "(SELECT sum(quantity) FROM shop_basket WHERE product_id = b.product_id AND fuser_id != '') as total_in_carts",
            ]);

            $query->where([
                ">", "b.product_id", 0
            ]);
        }



        return $dataProvider;
    }
}