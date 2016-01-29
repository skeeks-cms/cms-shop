<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 01.11.2015
 */
/* @var $this yii\web\View */
/* @var $search \skeeks\cms\shop\models\search\AdminReportProductSearch */
/* @var $dataProvider \yii\data\ActiveDataProvider */
?>

<? $form = \skeeks\cms\modules\admin\widgets\ActiveForm::begin([
    'method' => 'get',
    'enableAjaxValidation' => false,
    'usePjax' => false
]); ?>
    <?= $form->field($search, 'from')->widget(\kartik\datecontrol\DateControl::classname(), [
        //'displayFormat' => 'php:d-M-Y H:i:s',
        'type' => \kartik\datecontrol\DateControl::FORMAT_DATETIME,
    ]); ?>


    <?= $form->field($search, 'to')->widget(\kartik\datecontrol\DateControl::classname(), [
        //'displayFormat' => 'php:d-M-Y H:i:s',
        'type' => \kartik\datecontrol\DateControl::FORMAT_DATETIME,
    ]); ?>
    <button class="btn btn-default" type="submit">Применить</button>
<? \skeeks\cms\modules\admin\widgets\ActiveForm::end(); ?>

<?= \skeeks\cms\modules\admin\widgets\GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $search,
    'columns' => $search->getColumns(),
]); ?>

