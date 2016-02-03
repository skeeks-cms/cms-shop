<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */
use yii\helpers\Html;
use skeeks\cms\modules\admin\widgets\form\ActiveFormUseTab as ActiveForm;

/* @var $this yii\web\View */
/* @var $model \skeeks\cms\shop\models\ShopBasket */

if (\Yii::$app->request->get('order_id') && $model->isNewRecord)
{
    $model->order_id = \Yii::$app->request->get('order_id');
}

if (\Yii::$app->request->get('fuser_id') && $model->isNewRecord)
{
    $model->fuser_id = \Yii::$app->request->get('fuser_id');
}

?>

<?php $form = ActiveForm::begin(); ?>

    <?= $form->fieldSet(\skeeks\cms\shop\Module::t('app', 'Main')); ?>

<? if ($model->isNewRecord) : ?>
    <?= $form->field($model, 'order_id')->hiddenInput()->label(false); ?>
    <?= $form->field($model, 'fuser_id')->hiddenInput()->label(false); ?>
<? endif; ?>

        <?= $form->field($model, 'product_id')->widget(
            \skeeks\cms\modules\admin\widgets\formInputs\CmsContentElementInput::className(),
            [
                'baseRoute' => '/shop/tools/select-cms-element'
            ]
        ); ?>

        <?= $form->field($model, 'name'); ?>



        <div class="row">
            <div class="col-md-3">
                <?= $form->field($model, 'quantity'); ?>

            </div>

            <div class="col-md-2">
                <?= $form->field($model, 'measure_name'); ?>
            </div>
        </div>


        <div class="row">
            <div class="col-md-3">
                <?= $form->field($model, 'price')->textInput(); ?>

            </div>

            <div class="col-md-2">
                <?= $form->field($model, 'currency_code')->listBox(
                    \yii\helpers\ArrayHelper::map(\skeeks\modules\cms\money\models\Currency::find()->active()->all(), 'code', 'code')
                    ,['size' => 1]
                );?>
            </div>

            <div class="col-md-3">
                <?= $form->field($model, 'notes')->textInput()->label('Примечание к цене'); ?>
            </div>
        </div>

    <?= $form->fieldSetEnd(); ?>

    <?= $form->buttonsCreateOrUpdate($model); ?>
<?php ActiveForm::end(); ?>

<?


$this->registerJs(<<<JS
_.each(sx.components, function(Component, key)
{
    if (Component instanceof sx.classes.SelectCmsElement)
    {
        Component.bind('change', function(e, data)
        {
            console.log(data);
            $('#shopbasket-name').val(data.name);
            $('#shopbasket-quantity').val(1);
            $('#shopbasket-price').val(data.basePrice.price);
            $('#shopbasket-currency_code').val(data.basePrice.currency_code);
            $('#shopbasket-notes').val(data.basePriceType.name);
            $('#shopbasket-measure_name').val(data.measure.symbol_rus);
        });
    }
});
JS
);
?>