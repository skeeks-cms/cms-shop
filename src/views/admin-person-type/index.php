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
        'searchModel'   => $searchModel,
        'dataProvider'  => $dataProvider
    ]); ?>

    <?= \skeeks\cms\modules\admin\widgets\GridViewStandart::widget([
        'dataProvider'      => $dataProvider,
        'filterModel'       => $searchModel,
        'adminController'   => $controller,
        'pjax'              => $pjax,
        'settingsData' =>
        [
            'order'     => SORT_ASC,
            'orderBy'   => "priority",
        ],
        'columns' => [
            'name',
            'priority',

            [
                'class'         => \yii\grid\DataColumn::className(),
                'attribute'     => "siteCodes",
                'filter'        => false,
                'value'         => function(\skeeks\cms\shop\models\ShopPersonType $model)
                {
                    $result = [];
                    if ($model->sites)
                    {
                        foreach ($model->sites as $site)
                        {
                            $result[] = "$site->name [$site->code]";
                        }
                    }

                    return implode(", ", $result);
                }
            ],

            [
                'class'         => \skeeks\cms\grid\BooleanColumn::className(),
                'attribute'     => "active"
            ]
        ],
    ]); ?>

<? $pjax::end(); ?>
