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


$dataProvider->setSort(['defaultOrder' => ['published_at' => SORT_DESC]]);

$cmsContent = null;
if ($content_id = \Yii::$app->request->get('content_id'))
{
    $dataProvider->query->andWhere(['content_id' => $content_id]);
    /**
     * @var $cmsContent \skeeks\cms\models\CmsContent
     */
    $cmsContent = \skeeks\cms\models\CmsContent::findOne($content_id);
}
$columns = \skeeks\cms\shop\controllers\AdminCmsContentElementController::getColumns($cmsContent, $dataProvider);
?>

<? $pjax = \yii\widgets\Pjax::begin(); ?>

<?
        $query = $dataProvider->query;
        $filter = new \yii\base\DynamicModel([
            'section',
        ]);
        $filter->addRule('section', 'integer');
        $filter->load(\Yii::$app->request->get());
?>

<? $form = \skeeks\cms\modules\admin\widgets\AdminFiltersForm::begin(); ?>


    <?= $form->field($searchModel, 'name'); ?>
    <?= $form->field($searchModel, 'created_by')->widget(\skeeks\cms\modules\admin\widgets\formInputs\SelectModelDialogUserInput::className()); ?>
    <?= $form->field($searchModel, 'updated_by')->widget(\skeeks\cms\modules\admin\widgets\formInputs\SelectModelDialogUserInput::className()); ?>
    <?= $form->field($searchModel, 'active')->listBox(\Yii::$app->cms->booleanFormat(), [
        'size' => 1
    ]); ?>
    <?/*= $form->field($filter, 'tree')->listBox(\skeeks\cms\helpers\TreeOptions::getAllMultiOptions(), [
        'size' => 1
    ])->label('Раздел'); */?>
    <div class="form-group">
        <div class="col-sm-12">
            <hr style="margin-top: 5px; margin-bottom: 15px;"/>
            <button type="submit" class="btn btn-default pull-left">
                <i class="glyphicon glyphicon-search"></i> Найти
            </button>


            <a class="btn btn-default btn-sm pull-right" href="#" style="margin-left: 10px;">
                <i class="glyphicon glyphicon-plus"></i>
            </a>

            <a class="btn btn-default btn-sm pull-right" href="#">
                <i class="glyphicon glyphicon-cog"></i>
            </a>

        </div>
    </div>
<? $form::end(); ?>

    <?= \skeeks\cms\modules\admin\widgets\GridViewStandart::widget([
        'dataProvider'      => $dataProvider,
        'filterModel'       => $searchModel,
        'autoColumns'       => false,
        'pjax'              => $pjax,
        'adminController'   => $controller,
        'settingsData'  =>
        [
            'namespace' => \Yii::$app->controller->action->getUniqueId() . $content_id
        ],
        'columns' => $columns
    ]); ?>

<? \yii\widgets\Pjax::end() ?>

<? \yii\bootstrap\Alert::begin([
    'options' => [
        'class' => 'alert-info',
    ],
]); ?>
    Изменить свойства и права доступа к информационному блоку вы можете в <?= \yii\helpers\Html::a('Настройках контента', \skeeks\cms\helpers\UrlHelper::construct([
        '/cms/admin-cms-content/update', 'pk' => $content_id
    ])->enableAdmin()->toString()); ?>.
<? \yii\bootstrap\Alert::end(); ?>
