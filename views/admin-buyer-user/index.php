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

$query->groupBy([\skeeks\cms\models\CmsUser::tableName() . '.id']);
$query->leftJoin(\skeeks\cms\shop\models\ShopOrder::tableName(), '`shop_order`.`user_id` = `cms_user`.`id`');

?>

<?= \skeeks\cms\modules\admin\widgets\GridViewStandart::widget([
    'dataProvider'      => $dataProvider,
    'filterModel'       => $searchModel,
    'adminController'   => \Yii::$app->controller,
    'columns'           =>
    [
        [
            'class'         => \skeeks\cms\grid\UserColumnData::className(),
            'attribute'     => 'id',
            'label'         => \skeeks\cms\shop\Module::t('app', 'Buyer')
        ],

        'email',
        'phone',

        [
            'class'         => \skeeks\cms\grid\DateTimeColumnData::className(),
            'attribute'     => 'created_at',
            'label'         => \skeeks\cms\shop\Module::t('app', 'Date of registration'),
        ],

        [
            'class'         => \yii\grid\DataColumn::className(),
            'label'         => \skeeks\cms\shop\Module::t('app', 'Date of last order'),
            'value'         => function(\skeeks\cms\models\CmsUser $model)
            {
                if ($order = \skeeks\cms\shop\models\ShopOrder::find()->where(['user_id' => $model->id])
                    ->orderBy(['created_at' => SORT_DESC])->one())
                {
                    return \Yii::$app->formatter->asDatetime($order->created_at);
                }

                return null;
            },
        ],

        [
            'class'         => \yii\grid\DataColumn::className(),
            'label'         => \skeeks\cms\shop\Module::t('app', 'The amount paid orders'),
            'value'         => function(\skeeks\cms\models\CmsUser $model)
            {
                return \skeeks\cms\shop\models\ShopOrder::find()->where([
                    'user_id'   => $model->id,
                    'payed'     => \skeeks\cms\components\Cms::BOOL_Y
                ])->count();
            },
        ],

        [
            'class'         => \yii\grid\DataColumn::className(),
            'label'         => \skeeks\cms\shop\Module::t('app', 'The amount paid orders'),
            'value'         => function(\skeeks\cms\models\CmsUser $model)
            {
                return \skeeks\cms\shop\models\ShopOrder::find()->where([
                    'user_id'   => $model->id,
                    'payed'     => \skeeks\cms\components\Cms::BOOL_Y
                ])->count();
            },
        ],
    ]
]); ?>