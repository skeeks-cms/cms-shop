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
$dataProvider->query->andWhere(['type' => \skeeks\cms\shop\models\ShopDiscount::TYPE_DISCOUNT_SAVE]);
?>

<? $pjax = \skeeks\cms\modules\admin\widgets\Pjax::begin(); ?>

    <?php echo $this->render('_search', [
        'searchModel'   => $searchModel,
        'dataProvider'  => $dataProvider
    ]); ?>

    <?= \skeeks\cms\modules\admin\widgets\GridViewStandart::widget([
        'dataProvider'      => $dataProvider,
        'filterModel'       => $searchModel,
        'pjax'              => $pjax,
        'adminController'   => \Yii::$app->controller,
        'settingsData' =>
        [
            'order' => SORT_ASC,
            'orderBy' => "priority",
        ],

        'columns'           =>
        [
            'id',

            [
                'attribute'     => 'name',
                'label'         => \Yii::t('skeeks/shop/app', 'Name of the program'),
            ],

            [
                'attribute'     => 'active',
                'class'         => \skeeks\cms\grid\BooleanColumn::className(),
            ],

            [
                'class' => \skeeks\cms\grid\UpdatedByColumn::className()
            ],

            [
                'class' => \skeeks\cms\grid\UpdatedAtColumn::className()
            ],

            'priority',
        ]
    ]); ?>

<? $pjax::end(); ?>
