<?php
/**
 * index
 *
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010-2014 SkeekS (Sx)
 * @date 30.10.2014
 * @since 1.0.0
 */

/* @var $this yii\web\View */
/* @var $searchModel common\models\searchs\Game */
/* @var $dataProvider yii\data\ActiveDataProvider */

?>
<? $pjax = \skeeks\cms\modules\admin\widgets\Pjax::begin(); ?>

<?php echo $this->render('_search', [
    'searchModel'  => $searchModel,
    'dataProvider' => $dataProvider,
]); ?>

<?= \skeeks\cms\modules\admin\widgets\GridViewStandart::widget([
    'dataProvider'    => $dataProvider,
    'filterModel'     => $searchModel,
    'adminController' => $controller,
    'pjax'            => $pjax,
    'columns'         => [

        [
            'class'     => \yii\grid\DataColumn::class,
            'attribute' => 'id',
            'format'    => 'raw',
            'value'     => function (\skeeks\cms\shop\models\ShopOrder $order) {
                return \yii\helpers\Html::a($order->id, $order->publicUrl, [
                    'data-pjax' => 0,
                    'target'    => '_blank',
                ]);
            },
        ],


        [
            'class' => \skeeks\cms\grid\CreatedAtColumn::class,
        ],

        [
            'class'     => \yii\grid\DataColumn::class,
            'attribute' => 'status_code',
            'format'    => 'raw',
            'filter'    => \yii\helpers\ArrayHelper::map(\skeeks\cms\shop\models\ShopOrderStatus::find()->all(), 'code',
                'name'),
            'value'     => function (\skeeks\cms\shop\models\ShopOrder $order) {
                return \yii\helpers\Html::label($order->status->name, null, [
                        'style' => "background: {$order->status->color}",
                        'class' => "label",
                    ])."<br />".
                    \yii\helpers\Html::tag("small",
                        \Yii::$app->formatter->asDatetime($order->status_at)." (".\Yii::$app->formatter->asRelativeTime($order->status_at).")");
            },
        ],

        /*[
            'class'     => DataColumn::class,
            'attribute' => 'buyer_id',
            'format'    => 'raw',
            'value'     => function(ShopOrder $model)
            {
                if (!$model->buyer)
                {
                    return null;
                }

                return Html::a($model->buyer->name . " [{$model->buyer->id}]", UrlHelper::construct('shop/admin-buyer/related-properties', ['pk' => $model->buyer->id])->enableAdmin()->toString());
            }
        ],*/

        [
            'class'     => \skeeks\cms\grid\BooleanColumn::class,
            'attribute' => 'payed',
            'format'    => 'raw',
        ],

        [
            'class'     => \yii\grid\DataColumn::class,
            'attribute' => "canceled",
            'format'    => "raw",
            'filter'    => [
                'Y' => \Yii::t('skeeks/shop/app', 'Yes'),
                'N' => \Yii::t('skeeks/shop/app', 'No'),
            ],

            'value' => function (\skeeks\cms\shop\models\ShopOrder $shopOrder, $key, $index) {
                $reuslt = "<div>";
                if ($shopOrder->canceled == "Y") {
                    $this->registerJs(<<<JS
$('tr[data-key={$key}]').addClass('sx-tr-red');
JS
                    );

                    $this->registerCss(<<<CSS
tr.sx-tr-red, tr.sx-tr-red:nth-of-type(odd), tr.sx-tr-red td
{
background: #FFECEC !important;
}
CSS
                    );
                    $reuslt = "<div style='color: red;'>";
                }

                $reuslt .= $shopOrder->canceled == "Y" ? \Yii::t('skeeks/shop/app', 'Yes') : \Yii::t('skeeks/shop/app',
                    'No');
                $reuslt .= "</div>";
                return $reuslt;
            },
        ],


        [
            'class'     => \yii\grid\DataColumn::class,
            'attribute' => "user_id",
            'label'     => \Yii::t('skeeks/shop/app', 'Buyer'),
            'format'    => "raw",
            'filter'    => false,
            'value'     => function (\skeeks\cms\shop\models\ShopOrder $shopOrder) {
                return (new \skeeks\cms\shop\widgets\AdminBuyerUserWidget(['user' => $shopOrder->user]))->run();
            },
        ],

        [
            'class'  => \yii\grid\DataColumn::class,
            'filter' => false,
            'format' => 'raw',
            'label'  => \Yii::t('skeeks/shop/app', 'Good'),
            'value'  => function (\skeeks\cms\shop\models\ShopOrder $model) {
                if ($model->shopBaskets) {
                    $result = [];
                    foreach ($model->shopBaskets as $shopBasket) {
                        $result[] = \yii\helpers\Html::a($shopBasket->name, $shopBasket->url, [
                                'target'    => '_blank',
                                'data-pjax' => '0',
                            ]).<<<HTML
 — $shopBasket->quantity $shopBasket->measure_name
HTML;

                    }
                    return implode('<hr style="margin: 0px;"/>', $result);
                }
            },
        ],

        [
            'class'     => \yii\grid\DataColumn::class,
            'format'    => 'raw',
            'filter'    => false,
            'visible'   => true,
            'attribute' => '',
            'label'     => 'Смотреть',
            'value'     => function (\skeeks\cms\shop\models\ShopOrder $model) {
                return \yii\helpers\Html::a('<i class="glyphicon glyphicon-arrow-right"></i>', $model->publicUrl, [
                    'target'    => '_blank',
                    'title'     => \Yii::t('skeeks/cms', 'Watch to site (opens new window)'),
                    'data-pjax' => '0',
                    'class'     => 'btn btn-default btn-sm',
                ]);
            },
        ],


        [
            'class'     => \yii\grid\DataColumn::class,
            'format'    => 'raw',
            'filter'    => false,
            'visible'   => false,
            'attribute' => 'price',
            'label'     => \Yii::t('skeeks/shop/app', 'Sum'),
            'value'     => function (\skeeks\cms\shop\models\ShopOrder $model) {
                $result = (string)$model->money;


                if ($model->moneyDiscount->getValue()) {
                    $result .= "<br /><small>".\Yii::t('skeeks/shop/app',
                            'Discount').":".(string)$model->moneyDiscount."</small>";
                }

                if ($model->moneyDelivery->getValue()) {
                    $result .= "<br /><small>".\Yii::t('skeeks/shop/app',
                            'Delivery').":".(string)$model->moneyDelivery."</small>";
                }

                return $result;
            },
        ],


        [
            'class'     => \yii\grid\DataColumn::class,
            'format'    => 'raw',
            'filter'    => false,
            'visible'   => false,
            'attribute' => 'discount_value',
            'label'     => \Yii::t('skeeks/shop/app', 'Discount'),
            'value'     => function (\skeeks\cms\shop\models\ShopOrder $model) {
                return (string)$model->moneyDiscount;
            },
        ],

        [
            'class'  => \yii\grid\DataColumn::class,
            'format' => 'raw',
            'filter' => false,
            //'visible'        => false,
            'label'  => \Yii::t('skeeks/shop/app', 'Discount coupons'),
            'value'  => function (\skeeks\cms\shop\models\ShopOrder $model) {
                $result = null;

                if ($model->discountCoupons) {
                    foreach ($model->discountCoupons as $discountCoupon) {
                        $result .= \yii\helpers\Html::a($discountCoupon->coupon, '#', [
                            'title' => $discountCoupon->description." ".$discountCoupon->shopDiscount->name,
                        ]);
                    }
                }

                return $result;
            },
        ],

        [
            'class'     => \yii\grid\DataColumn::class,
            'filter'    => \yii\helpers\ArrayHelper::map(\skeeks\cms\models\CmsSite::find()->active()->all(), 'id',
                'name'),
            'attribute' => 'site_id',
            'format'    => 'raw',
            'visible'   => false,
            'label'     => \Yii::t('skeeks/shop/app', 'Site'),
            'value'     => function (\skeeks\cms\shop\models\ShopOrder $model) {
                return $model->site->name." [{$model->site->code}]";
            },
        ],

        [
            'filter'    => \yii\helpers\ArrayHelper::map(\Yii::$app->shop->stores, 'id', 'name'),
            'attribute' => 'store_id',
            'format'    => 'raw',
            'visible'   => false,
            'label'     => \Yii::t('skeeks/shop/app', 'Store'),
            'value'     => function (\skeeks\cms\shop\models\ShopOrder $model) {
                return $model->store->name;
            },
        ],
    ],
]); ?>

<? $pjax::end(); ?>
