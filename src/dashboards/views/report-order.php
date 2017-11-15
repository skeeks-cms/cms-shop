<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 01.11.2015
 */
/* @var $this yii\web\View */
/* @var $widget \skeeks\cms\shop\dashboards\ReportOrderDashboard */

$search = new \skeeks\cms\shop\models\search\AdminReportOrderSearch();
$dataProvider = $search->search(\Yii::$app->request->get());

$this->registerCss(<<<CSS
.sx-report-order .sx-table-additional
{
    padding: 0 15px;
}

.sx-report-order .sx-form-filters
{
    padding: 10px 15px;
}
CSS
)

?>
<? \skeeks\cms\modules\admin\widgets\Pjax::begin(); ?>


    <div class="sx-report-order">

        <? $form = \yii\bootstrap\ActiveForm::begin([
            'method' => 'get',
            'options' =>
                [
                    'data-pjax' => '1'
                ],
            'enableAjaxValidation' => false,
        ]); ?>
        <div class="row sx-form-filters">
            <div class="col-md-3">
                <?= $form->field($search,
                    'groupType')->listBox(\skeeks\cms\shop\models\search\AdminReportOrderSearch::getGroupTypes(), [
                    'size' => 1
                ]); ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($search, 'from')->widget(\kartik\datecontrol\DateControl::classname(), [
                    //'displayFormat' => 'php:d-M-Y H:i:s',
                    'type' => \kartik\datecontrol\DateControl::FORMAT_DATETIME,
                ]); ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($search, 'to')->widget(\kartik\datecontrol\DateControl::classname(), [
                    //'displayFormat' => 'php:d-M-Y H:i:s',
                    'type' => \kartik\datecontrol\DateControl::FORMAT_DATETIME,
                ]); ?>
            </div>

            <div class="col-md-3">
                <div class="form-group field-adminreportproductsearch-to">
                    <label class="control-label" for="adminreportproductsearch-to" style="width: 100%;">&nbsp;</label>
                    <button class="btn btn-default" type="submit"><?= \Yii::t('skeeks/shop/app', 'Apply'); ?></button>
                </div>
            </div>
        </div>


        <? \yii\bootstrap\ActiveForm::end(); ?>

        <?= \skeeks\cms\modules\admin\widgets\GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $search,
            'columns' => $search->getColumns(),
        ]); ?>

    </div>
<? \skeeks\cms\modules\admin\widgets\Pjax::end(); ?>