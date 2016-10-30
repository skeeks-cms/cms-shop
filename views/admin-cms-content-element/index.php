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

$sortAttr = $dataProvider->getSort()->attributes;
$dataProvider->query->joinWith('shopProduct as sp');

$dataProvider->getSort()->attributes = \yii\helpers\ArrayHelper::merge($sortAttr, [
    'quantity' => [
        'asc' => ['sp.quantity' => SORT_ASC],
        'desc' => ['sp.quantity' => SORT_DESC],
        'label' => \Yii::t('skeeks/shop/app', 'Available quantity'),
        'default' => SORT_ASC
    ]
]);

$cmsContent = null;
if ($content_id = \Yii::$app->request->get('content_id'))
{
    $dataProvider->query->andWhere(['content_id' => $content_id]);
    /**
     * @var $cmsContent \skeeks\cms\models\CmsContent
     */
    $cmsContent = \skeeks\cms\models\CmsContent::findOne($content_id);
    $searchModel->content_id = $content_id;
}
$columns = \skeeks\cms\shop\controllers\AdminCmsContentElementController::getColumns($cmsContent, $dataProvider);

$columns = \yii\helpers\ArrayHelper::merge($columns, [
    [
        'label' => \Yii::t('skeeks/shop/app', 'Available quantity'),
        'class' => \yii\grid\DataColumn::class,
        'visible' => false,
        'attribute' => 'quantity',
        'value' => function(\skeeks\cms\shop\models\ShopCmsContentElement $shopCmsContentElement)
        {
            return $shopCmsContentElement->shopProduct ? $shopCmsContentElement->shopProduct->quantity : " - ";
        },
    ]
])
?>

<? $pjax = \yii\widgets\Pjax::begin(); ?>

    <?php echo $this->render('_search', [
        'searchModel' => $searchModel,
        'dataProvider' => $dataProvider,
        'content_id' => $content_id,
        'cmsContent' => $cmsContent,
    ]); ?>

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

<? $pjax::end() ?>

<? \yii\bootstrap\Alert::begin([
    'options' => [
        'class' => 'alert-info',
    ],
]); ?>
    <?= \Yii::t('skeeks/shop/app','Change the properties and rights of access to information block you can'); ?> <?= \yii\helpers\Html::a(\Yii::t('skeeks/shop/app','Content Settings'), \skeeks\cms\helpers\UrlHelper::construct([
        '/cms/admin-cms-content/update', 'pk' => $content_id
    ])->enableAdmin()->toString()); ?>.
<? \yii\bootstrap\Alert::end(); ?>
