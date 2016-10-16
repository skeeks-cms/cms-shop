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
        'searchModel'   => $searchModel,
        'dataProvider'  => $dataProvider
    ]); ?>

    <?= \skeeks\cms\modules\admin\widgets\GridViewStandart::widget([
        'dataProvider'      => $dataProvider,
        'filterModel'       => $searchModel,
        'pjax'              => $pjax,
        'adminController'   => \Yii::$app->controller,
        'columns'           =>
        [
            [
                'class' => \yii\grid\DataColumn::className(),
                'filter' => \yii\helpers\ArrayHelper::map(\Yii::$app->shop->shopPersonTypes, 'id', 'name'),
                'attribute' => 'shop_person_type_id',
                'value' => function(\skeeks\cms\shop\models\ShopBuyer $model)
                {
                    return $model->shopPersonType->name;
                }
            ],

            'name',
            [
                'class' => \skeeks\cms\grid\UserColumnData::className(),
                'attribute' => 'cms_user_id'
            ],


        ]
    ]); ?>

<? $pjax::end(); ?>
