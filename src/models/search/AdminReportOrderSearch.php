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

class AdminReportOrderSearch extends Model
{
    public $from;
    public $to;

    public $groupType = "d";


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['from', 'to'], 'safe'],
            [['groupType'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'from'  => \Yii::t('skeeks/shop/app', 'From'),
            'to'    => \Yii::t('skeeks/shop/app', 'To'),
            'groupType'    => \Yii::t('skeeks/shop/app', 'Group by')
        ];
    }

    static public function getGroupTypes()
    {
        return [
            'd' => \Yii::t('skeeks/shop/app', 'days'),
            'm' => \Yii::t('skeeks/shop/app', 'months'),
            'Y' => \Yii::t('skeeks/shop/app', 'years'),
        ];
    }

    public function getColumns()
    {
        return
        [
            [
                'attribute' => 'groupType',
                'label' => \Yii::t('skeeks/shop/app', 'Date'),
                'filter' => false,
            ],
            [
                'attribute' => 'total_orders',
                'label' => \Yii::t('skeeks/shop/app', 'Total'),
            ],
            [
                'attribute' => 'total_payed',
                'label' => \Yii::t('skeeks/shop/app', 'Number of paid'),
            ],
            [
                'attribute' => 'total_canceled',
                'label' => \Yii::t('skeeks/shop/app', 'Number of canceled'),
            ],
            [
                'attribute' => 'sum_price',
                'label' => \Yii::t('skeeks/shop/app', 'Cost of'),
            ],
            [
                'attribute' => 'sum_payed',
                'label' => \Yii::t('skeeks/shop/app', 'Cost of paid'),
            ],
            [
                'attribute' => 'sum_canceled',
                'label' => \Yii::t('skeeks/shop/app', 'Cost of canceled'),
            ],
        ];
    }

    /**
     * @param array|null $params
     * @return ActiveDataProvider
     */
    public function search($params = [])
    {
        $query = (new \yii\db\Query())->from('shop_order o')->groupBy('groupType')->select(['o.id']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' =>
            [
                'attributes' => array_keys( \yii\helpers\ArrayHelper::map($this->getColumns(), 'attribute', 'attribute') ),
                'defaultOrder' => [
                    'groupType' => SORT_DESC
                ]
            ]
        ]);

        if ($params && ($this->load($params) && $this->validate()) )
        {
            if ($this->groupType)
            {
                if ($this->groupType == 'd')
                {
                    $format = '%d.%m.%Y';
                } else if ($this->groupType == 'm')
                {
                    $format = '%m.%Y';
                } else if ($this->groupType == 'Y')
                {
                    $format = '%Y';
                }

                $query->addSelect([
                    "count(*) as total_orders",
                    "sum(price) as sum_price",

                    "FROM_UNIXTIME(created_at, '{$format}') as groupType",

                    "(SELECT count(*) FROM shop_order WHERE payed = 'Y' AND FROM_UNIXTIME(created_at, '{$format}') = groupType) as total_payed",
                    "(SELECT sum(price) FROM shop_order WHERE payed = 'Y' AND FROM_UNIXTIME(created_at, '{$format}') = groupType) as sum_payed",


                    "(SELECT count(*) FROM shop_order WHERE canceled = 'Y' AND FROM_UNIXTIME(created_at, '{$format}') = groupType) as total_canceled",
                    "(SELECT sum(price) FROM shop_order WHERE canceled = 'Y' AND FROM_UNIXTIME(created_at, '{$format}') = groupType) as sum_canceled",
                ]);

                if ($this->from)
                {
                    $query->andWhere([
                        '>=', 'o.created_at', $this->from
                    ]);
                }

                if ($this->to)
                {
                    $query->andWhere([
                        '<=', 'o.created_at', $this->to
                    ]);
                }
            }

            return $dataProvider;

        } else
        {
            if ($this->groupType)
            {
                if ($this->groupType == 'd')
                {
                    $format = '%d.%m.%Y';
                } else if ($this->groupType == 'm')
                {
                    $format = '%m.%Y';
                } else if ($this->groupType == 'Y')
                {
                    $format = '%Y';
                }

                $query->addSelect([
                    "count(*) as total_orders",
                    "sum(price) as sum_price",

                    "FROM_UNIXTIME(created_at, '{$format}') as groupType",

                    "(SELECT count(*) FROM shop_order WHERE payed = 'Y' AND FROM_UNIXTIME(created_at, '{$format}') = groupType) as total_payed",
                    "(SELECT sum(price) FROM shop_order WHERE payed = 'Y' AND FROM_UNIXTIME(created_at, '{$format}') = groupType) as sum_payed",


                    "(SELECT count(*) FROM shop_order WHERE canceled = 'Y' AND FROM_UNIXTIME(created_at, '{$format}') = groupType) as total_canceled",
                    "(SELECT sum(price) FROM shop_order WHERE canceled = 'Y' AND FROM_UNIXTIME(created_at, '{$format}') = groupType) as sum_canceled",
                ]);
            }

        }

        return $dataProvider;
    }
}