<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 02.06.2015
 */
/* @var $this yii\web\View */
/* @var $controller \skeeks\cms\shop\controllers\AdminBuyerController */
/* @var $searchModel \skeeks\cms\models\Search */
/* @var $dataProvider yii\data\ActiveDataProvider */

$autoColumns = [];
$shopPersonType = $controller->personType;
if ($shopPersonType) {
    $dataProvider->query->andWhere(['shop_person_type_id' => $shopPersonType->id]);
    /**
     * @var $shopPersonType \skeeks\cms\shop\models\ShopPersonType
     */
    $searchModel->shop_person_type_id = $shopPersonType->id;
}

?>

<? $pjax = \skeeks\cms\modules\admin\widgets\Pjax::begin(); ?>

<? if ($shopPersonType) : ?>
    <?
    $shopBuyer = new \skeeks\cms\shop\models\ShopBuyer();
    $shopBuyer->shop_person_type_id = $shopPersonType->id;

    $searchRelatedPropertiesModel = new \skeeks\cms\models\searchs\SearchRelatedPropertiesModel();
    $searchRelatedPropertiesModel->propertyElementClassName = \skeeks\cms\shop\models\ShopBuyerProperty::class;
    $searchRelatedPropertiesModel->initProperties($shopBuyer->relatedProperties);
    $searchRelatedPropertiesModel->load(\Yii::$app->request->get());
    if ($dataProvider) {
        $searchRelatedPropertiesModel->search($dataProvider, $shopBuyer->tableName());
    }

    if ($shopBuyer->relatedPropertiesModel) {
        $autoColumns = \skeeks\cms\modules\admin\widgets\GridViewStandart::getColumnsByRelatedPropertiesModel($shopBuyer->relatedPropertiesModel,
            $searchRelatedPropertiesModel);
    }
    ?>
<? endif; ?>


<?php echo $this->render('_search', [
    'searchModel' => $searchModel,
    'dataProvider' => $dataProvider,
    'shopPersonType' => $shopPersonType
]); ?>

<?= \skeeks\cms\modules\admin\widgets\GridViewStandart::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'pjax' => $pjax,
    'adminController' => \Yii::$app->controller,
    'columns' => \yii\helpers\ArrayHelper::merge(
        [
            /*[
                'class' => \yii\grid\DataColumn::className(),
                'filter' => \yii\helpers\ArrayHelper::map(\Yii::$app->shop->shopPersonTypes, 'id', 'name'),
                'attribute' => 'shop_person_type_id',
                'value' => function(\skeeks\cms\shop\models\ShopBuyer $model)
                {
                    return $model->shopPersonType->name;
                }
            ],*/

            'name',
            [
                'class' => \skeeks\cms\grid\UserColumnData::className(),
                'attribute' => 'cms_user_id'
            ],


        ], $autoColumns)
]); ?>

<? $pjax::end(); ?>
