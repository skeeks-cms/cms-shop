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

<?= \skeeks\cms\modules\admin\widgets\GridViewStandart::widget([
    'dataProvider'      => $dataProvider,
    'filterModel'       => $searchModel,
    'autoColumns'       => false,
    'adminController'   => $controller,
    'settingsData'  =>
    [
        'namespace' => \Yii::$app->controller->action->getUniqueId() . $content_id
    ],
    'columns' => $columns
]); ?>



<? \yii\bootstrap\Alert::begin([
    'options' => [
        'class' => 'alert-info',
    ],
]); ?>
    <?= \Yii::t('skeeks/shop/app','Change the properties and rights of access to information block you can'); ?> <?= \yii\helpers\Html::a(\Yii::t('skeeks/shop/app','Content Settings'), \skeeks\cms\helpers\UrlHelper::construct([
        '/cms/admin-cms-content/update', 'pk' => $content_id
    ])->enableAdmin()->toString()); ?>.
<? \yii\bootstrap\Alert::end(); ?>
