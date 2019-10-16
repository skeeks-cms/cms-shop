<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 16.07.2015
 */
/* @var $this           yii\web\View */
/* @var $searchModel    common\models\searchs\Game */
/* @var $dataProvider   yii\data\ActiveDataProvider */
/* @var $controller     \skeeks\cms\modules\admin\controllers\AdminController */
/* @var $columns        array */

?>

<? $alert = \yii\bootstrap\Alert::begin([
    'options' =>
        [
            'class' => 'alert-info',
        ],
]); ?>
<?= \Yii::t('skeeks/shop/app', 'In this section, you can customize what content you can sell on your site.'); ?>
<? $alert::end(); ?>

<?= \skeeks\cms\modules\admin\widgets\GridViewStandart::widget([
    'dataProvider'    => $dataProvider,
    'filterModel'     => $searchModel,
    'adminController' => $controller,
    'columns'         => [
        [
            'filter'    => false,
            'attribute' => 'content_id',
            'class'     => \yii\grid\DataColumn::class,
            'value'     => function (\skeeks\cms\shop\models\ShopContent $model) {
                return $model->content->name." ({$model->content->contentType->name})";
            },
        ],

        /*[
            'attribute' => 'yandex_export',
            'class' => BooleanColumn::class,
        ]*/
    ],
]); ?>
