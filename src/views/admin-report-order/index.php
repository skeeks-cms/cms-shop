<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 01.11.2015
 */
/* @var $this yii\web\View */
/* @var $search \skeeks\cms\shop\models\search\AdminReportOrderSearch */
/* @var $dataProvider \yii\data\ActiveDataProvider */
?>

<? $form = \skeeks\cms\modules\admin\widgets\ActiveForm::begin([
    'method'               => 'get',
    'enableAjaxValidation' => false,
    'usePjax'              => false,
]); ?>
<div class="row">
    <div class="col-md-3">
        <?= $form->field($search,
            'groupType')->listBox(\skeeks\cms\shop\models\search\AdminReportOrderSearch::getGroupTypes(), [
            'size' => 1,
        ]); ?>
    </div>
    <div class="col-md-3">
        <?= $form->field($search, 'from')->widget(\kartik\datecontrol\DateControl::class, [
            //'displayFormat' => 'php:d-M-Y H:i:s',
            'type' => \kartik\datecontrol\DateControl::FORMAT_DATETIME,
        ]); ?>
    </div>
    <div class="col-md-3">
        <?= $form->field($search, 'to')->widget(\kartik\datecontrol\DateControl::class, [
            //'displayFormat' => 'php:d-M-Y H:i:s',
            'type' => \kartik\datecontrol\DateControl::FORMAT_DATETIME,
        ]); ?>
    </div>

    <div class="col-md-3">
        <div class="form-group field-adminreportproductsearch-to">
            <label class="control-label" for="adminreportproductsearch-to" style="width: 100%;">&nbsp;</label>
            <button class="btn btn-default" type="submit">Применить</button>
        </div>
    </div>
</div>


<? \skeeks\cms\modules\admin\widgets\ActiveForm::end(); ?>

<?= \skeeks\cms\modules\admin\widgets\GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel'  => $search,
    'columns'      => $search->getColumns(),
]); ?>

