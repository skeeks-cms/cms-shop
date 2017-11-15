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
?>

<? $pjax = \skeeks\cms\modules\admin\widgets\Pjax::begin(); ?>

<?php echo $this->render('_search', [
    'searchModel' => $searchModel,
    'dataProvider' => $dataProvider
]); ?>

<?= \skeeks\cms\modules\admin\widgets\GridViewStandart::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'pjax' => $pjax,
    'adminController' => \Yii::$app->controller,
    'columns' =>
        [
            'value',

            [
                'class' => \yii\grid\DataColumn::className(),
                'value' => function (\skeeks\cms\shop\models\ShopTaxRate $model) {
                    return $model->tax->name . " (" . $model->tax->site->name . ")";
                },
                'attribute' => "tax_id"
            ],
            [
                'class' => \yii\grid\DataColumn::className(),
                'value' => function (\skeeks\cms\shop\models\ShopTaxRate $model) {
                    return $model->personType->name;
                },
                'attribute' => "person_type_id"
            ],

            [
                'class' => \skeeks\cms\grid\BooleanColumn::className(),
                'attribute' => "is_in_price"
            ],

            [
                'class' => \skeeks\cms\grid\BooleanColumn::className(),
                'attribute' => "active"
            ],

            [
                'attribute' => "priority"
            ]
        ]
]); ?>

<? $pjax::end(); ?>
