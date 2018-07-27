<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 02.06.2015
 */
/* @var $this yii\web\View */
/* @var $searchModel \skeeks\cms\models\Search */
/* @var $dataProvider yii\data\ActiveDataProvider */

$query = $dataProvider->query;
/**
 * @var \yii\db\ActiveQuery $query
 */
$query->groupBy(['shop_fuser.id']);

$query->with('user');
$query->with('personType');
$query->with('buyer');
$query->with('shopBaskets');
$query->with('shopBaskets.product');

$query->joinWith('shopBaskets as sb');
$query->andWhere(
    [
        'or',
        ['>=', 'sb.id', 0],
        ['>=', 'shop_fuser.user_id', 0],
        ['>=', 'shop_fuser.person_type_id', 0],
        ['>=', 'shop_fuser.buyer_id', 0],
    ]
);

$query->orderBy(['shop_fuser.updated_at' => SORT_DESC]);

?>

<? $pjax = \skeeks\cms\modules\admin\widgets\Pjax::begin(); ?>

<?php echo $this->render('_search', [
    'searchModel'  => $searchModel,
    'dataProvider' => $dataProvider,
]); ?>

<?= \skeeks\cms\modules\admin\widgets\GridViewStandart::widget([
    'dataProvider'    => $dataProvider,
    'filterModel'     => $searchModel,
    'pjax'            => $pjax,
    'adminController' => \Yii::$app->controller,
    'columns'         =>
        [
            [
                'class' => \skeeks\cms\grid\UpdatedAtColumn::className(),
            ],

            [
                'class'  => \yii\grid\DataColumn::className(),
                'filter' => false,
                'format' => 'raw',
                'label'  => \Yii::t('skeeks/shop/app', 'User'),
                'value'  => function (\skeeks\cms\shop\models\ShopFuser $model) {
                    return $model->user ? (new \skeeks\cms\shop\widgets\AdminBuyerUserWidget(['user' => $model->user]))->run() : \Yii::t('skeeks/shop/app',
                        'Not authorized');
                },
            ],

            [
                'class'  => \yii\grid\DataColumn::className(),
                'filter' => false,
                'format' => 'raw',
                'label'  => \Yii::t('skeeks/shop/app', 'Profile of buyer'),
                'value'  => function (\skeeks\cms\shop\models\ShopFuser $model) {
                    if (!$model->buyer) {
                        return null;
                    }

                    return \yii\helpers\Html::a($model->buyer->name." [{$model->buyer->id}]",
                        \skeeks\cms\helpers\UrlHelper::construct('shop/admin-buyer/related-properties',
                            ['pk' => $model->buyer->id])->enableAdmin()->toString());
                },
            ],

            [
                'class'     => \yii\grid\DataColumn::className(),
                'filter'    => \yii\helpers\ArrayHelper::map(\skeeks\cms\shop\models\ShopPersonType::find()->active()->all(),
                    'id', 'name'),
                'attribute' => 'person_type_id',
                'label'     => \Yii::t('skeeks/shop/app', 'Profile type'),
                'value'     => function (\skeeks\cms\shop\models\ShopFuser $model) {
                    return $model->personType ? $model->personType->name : "";
                },
            ],

            [
                'class'  => \yii\grid\DataColumn::className(),
                'filter' => false,
                'label'  => \Yii::t('skeeks/shop/app', 'Price of basket'),
                'value'  => function (\skeeks\cms\shop\models\ShopFuser $model) {
                    return (string)$model->money;
                },
            ],

            [
                'class'  => \yii\grid\DataColumn::className(),
                'filter' => false,
                'label'  => \Yii::t('skeeks/shop/app', 'Number of items'),
                'value'  => function (\skeeks\cms\shop\models\ShopFuser $model) {
                    return $model->countShopBaskets;
                },
            ],

            [
                'class'  => \yii\grid\DataColumn::className(),
                'filter' => false,
                'format' => 'raw',
                'label'  => \Yii::t('skeeks/shop/app', 'Good'),
                'value'  => function (\skeeks\cms\shop\models\ShopFuser $model) {
                    if ($model->shopBaskets) {
                        $result = [];
                        foreach ($model->shopBaskets as $shopBasket) {
                            $money = (string)$shopBasket->money;
                            $result[] = \yii\helpers\Html::a($shopBasket->name,
                                    $shopBasket->product ? $shopBasket->product->cmsContentElement->url : '#',
                                    ['target' => '_blank']).<<<HTML
($shopBasket->quantity $shopBasket->measure_name) — {$money}
HTML;

                        }
                        return implode('<hr style="margin: 0px;"/>', $result);
                    }
                },
            ],

            [
                'class'     => \yii\grid\DataColumn::className(),
                'filter'    => \yii\helpers\ArrayHelper::map(\skeeks\cms\models\CmsSite::find()->active()->all(), 'id',
                    'name'),
                'attribute' => 'site_id',
                'format'    => 'raw',
                'visible'   => false,
                'label'     => \Yii::t('skeeks/shop/app', 'Site'),
                'value'     => function (\skeeks\cms\shop\models\ShopFuser $model) {
                    return $model->site->name." [{$model->site->code}]";
                },
            ],

            [
                'class' => \skeeks\cms\grid\CreatedAtColumn::className(),
            ],
        ],
]); ?>

<? $pjax::end(); ?>
