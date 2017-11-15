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
<? if (!\Yii::$app->shop->storeContent) : ?>
    <? \yii\bootstrap\Alert::begin([
        'options' => [
            'class' => 'alert-danger',
        ],
    ]); ?>
    <?= \Yii::t('skeeks/shop/app',
        'Functional "warehouses" is not set in your store. Create new content, and specify it in the general settings of your store as a storage content.'); ?>
    <? \yii\bootstrap\Alert::end(); ?>
<? else : ?>

    <?
    $dataProvider->setSort(['defaultOrder' => ['published_at' => SORT_DESC]]);

    $cmsContent = \Yii::$app->shop->storeContent;
    $content_id = $cmsContent->id;
    if ($content_id) {
        $dataProvider->query->andWhere(['content_id' => $content_id]);
        /**
         * @var $cmsContent \skeeks\cms\models\CmsContent
         */
        $cmsContent = \skeeks\cms\models\CmsContent::findOne($content_id);
        $searchModel->content_id = $content_id;
    }
    $columns = \skeeks\cms\controllers\AdminCmsContentElementController::getColumns($cmsContent, $dataProvider);
    ?>

    <? $pjax = \yii\widgets\Pjax::begin(); ?>

    <?php echo $this->render('@skeeks/cms/views/admin-cms-content-element/_search', [
        'searchModel' => $searchModel,
        'dataProvider' => $dataProvider,
        'content_id' => $content_id,
        'cmsContent' => $cmsContent,
    ]); ?>

    <?= \skeeks\cms\modules\admin\widgets\GridViewStandart::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'autoColumns' => false,
        'pjax' => $pjax,
        'adminController' => $controller,
        'settingsData' =>
            [
                'namespace' => \Yii::$app->controller->action->getUniqueId() . $content_id
            ],
        'columns' => $columns
    ]); ?>

    <? $pjax::end() ?>

    <? \yii\bootstrap\Alert::begin([
        'options' => [
            'class' => 'alert-info',
        ],
    ]); ?>
    <?= \Yii::t('skeeks/shop/app',
        'Change the properties and rights of access to information block you can'); ?> <?= \yii\helpers\Html::a(\Yii::t('skeeks/shop/app',
        'Content Settings'), \skeeks\cms\helpers\UrlHelper::construct([
        '/cms/admin-cms-content/update',
        'pk' => $content_id
    ])->enableAdmin()->toString()); ?>.
    <? \yii\bootstrap\Alert::end(); ?>

<? endif; ?>

