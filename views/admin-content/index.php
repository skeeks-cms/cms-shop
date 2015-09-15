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

<? \yii\bootstrap\Alert::begin([
    'options' =>
    [
        'class' => 'alert-info'
    ]
]); ?>
Добавте и настройте в эту таблицу типы контентов, которые разрешено продавать на этом сайте.
<? \yii\bootstrap\Alert::end(); ?>

<?= \skeeks\cms\modules\admin\widgets\GridViewStandart::widget([
    'dataProvider'      => $dataProvider,
    'filterModel'       => $searchModel,
    'adminController'   => $controller,
    'columns'           => $columns,
]); ?>
