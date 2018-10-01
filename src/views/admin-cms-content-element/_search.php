<? /**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 26.05.2016
 */


$filter = new \yii\base\DynamicModel([
    'product_type',
]);
$filter->addRule('product_type', 'safe');

$filter->load(\Yii::$app->request->get());

if ($filter->product_type) {
    $dataProvider->query->andWhere(['sp.product_type' => $filter->product_type]);
}

?>

<? $form = \skeeks\cms\modules\admin\widgets\filters\AdminFiltersForm::begin([
    //'action' => '/' . \Yii::$app->request->pathInfo,
    'namespace' => \Yii::$app->controller->uniqueId."_".$content_id,
]); ?>

<?= \yii\helpers\Html::hiddenInput('content_id', $content_id) ?>

<?= $form->field($searchModel, 'id'); ?>


<?= $form->field($searchModel, 'q')->textInput([
    'placeholder' => \Yii::t('skeeks/cms', 'Search name and description'),
])->setVisible(); ?>

<?= $form->fieldSelectMulti($filter, 'product_type', \skeeks\cms\shop\models\ShopProduct::possibleProductTypes())->label('Тип товара')->setVisible(); ?>


<?= $form->field($searchModel, 'name')->textInput([
    'placeholder' => \Yii::t('skeeks/cms', 'Search by name'),
]) ?>

<?= $form->field($searchModel, 'active')->listBox(\yii\helpers\ArrayHelper::merge([
    '' => ' - ',
], \Yii::$app->cms->booleanFormat()), [
    'size' => 1,
]); ?>

<?= $form->field($searchModel, 'section')->widget(
    \skeeks\cms\backend\widgets\SelectModelDialogTreeWidget::class
); ?>
<? /*= $form->field($searchModel, 'section')->widget(
        \skeeks\cms\widgets\formInputs\selectTree\SelectTreeInputWidget::class,
        [
            'multiple' => false,
        ]
    ); */ ?>

<? /*= $form->field($searchModel, 'section')->widget(
        \skeeks\widget\chosen\Chosen::class,
        [
            'items' => \skeeks\cms\helpers\TreeOptions::getAllMultiOptions()
        ]
    ); */ ?>


<?= $form->field($searchModel, 'has_image')->checkbox(\Yii::$app->formatter->booleanFormat, false); ?>
<?= $form->field($searchModel, 'has_full_image')->checkbox(\Yii::$app->formatter->booleanFormat, false); ?>


<?= $form->field($searchModel, 'created_by')->widget(
    \skeeks\cms\backend\widgets\SelectModelDialogUserWidget::class
); ?>
<?= $form->field($searchModel, 'updated_by')->widget(
    \skeeks\cms\backend\widgets\SelectModelDialogUserWidget::class
); ?>


<?= $form->field($searchModel, 'created_at_from')->widget(
    \kartik\datetime\DateTimePicker::class
); ?>
<?= $form->field($searchModel, 'created_at_to')->widget(
    \kartik\datetime\DateTimePicker::class
); ?>

<?= $form->field($searchModel, 'updated_at_from')->widget(
    \kartik\datetime\DateTimePicker::class
); ?>
<?= $form->field($searchModel, 'updated_at_to')->widget(
    \kartik\datetime\DateTimePicker::class
); ?>

<?= $form->field($searchModel, 'published_at_from')->widget(
    \kartik\datetime\DateTimePicker::class
); ?>
<?= $form->field($searchModel, 'published_at_to')->widget(
    \kartik\datetime\DateTimePicker::class
); ?>

<?= $form->field($searchModel, 'code'); ?>

<?
$searchRelatedPropertiesModel = new \skeeks\cms\models\searchs\SearchRelatedPropertiesModel();
if ($cmsContent) {
    $searchRelatedPropertiesModel->initProperties($cmsContent->cmsContentProperties);
    $searchRelatedPropertiesModel->load(\Yii::$app->request->get());
}

?>
<?= $form->relatedFields($searchRelatedPropertiesModel); ?>


<?
if ($shopContent = \skeeks\cms\shop\models\ShopContent::findOne(['content_id' => $content_id])) {
    if ($offerContent = $shopContent->offerContent) {
        $searchOfferRelatedPropertiesModel = new \skeeks\cms\models\searchs\SearchChildrenRelatedPropertiesModel();
        $searchOfferRelatedPropertiesModel->initCmsContent($offerContent);
        $searchOfferRelatedPropertiesModel->load(\Yii::$app->request->get());
        $searchOfferRelatedPropertiesModel->search($dataProvider);

        echo $form->relatedFields($searchOfferRelatedPropertiesModel);
    }
};
?>

<? $form::end(); ?>