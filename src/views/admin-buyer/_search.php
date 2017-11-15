<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 21.09.2016
 */

/* @var $this yii\web\View */
/* @var $searchModel common\models\searchs\Game */
/* @var $dataProvider yii\data\ActiveDataProvider */

$filter = new \yii\base\DynamicModel([
    'id',
]);
$filter->addRule('id', 'integer');

$filter->load(\Yii::$app->request->get());

if ($filter->id) {
    $dataProvider->query->andWhere(['id' => $filter->id]);
}
?>
<? $form = \skeeks\cms\modules\admin\widgets\filters\AdminFiltersForm::begin([
    'action' => '/' . \Yii::$app->request->pathInfo,
    'namespace' => \Yii::$app->controller->uniqueId . ($shopPersonType ? "-{$shopPersonType->id}" : "")
]); ?>

<?= $form->field($filter, 'id')->setVisible(); ?>

<? if ($shopPersonType) : ?>
    <?= \yii\helpers\Html::hiddenInput('person_type_id', $shopPersonType->id) ?>

    <?
    $shopBuyer = new \skeeks\cms\shop\models\ShopBuyer();
    $shopBuyer->shop_person_type_id = $shopPersonType->id;

    $searchRelatedPropertiesModel = new \skeeks\cms\models\searchs\SearchRelatedPropertiesModel();
    $searchRelatedPropertiesModel->propertyElementClassName = \skeeks\cms\shop\models\ShopBuyerProperty::className();
    $searchRelatedPropertiesModel->initProperties($shopBuyer->relatedProperties);
    $searchRelatedPropertiesModel->load(\Yii::$app->request->get());
    $searchRelatedPropertiesModel->search($dataProvider, $shopBuyer::tableName());
    ?>
    <?= $form->relatedFields($searchRelatedPropertiesModel); ?>
<? endif; ?>


<? $form::end(); ?>
