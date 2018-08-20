<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use skeeks\cms\modules\admin\widgets\form\ActiveFormUseTab as ActiveForm;

/* @var $this yii\web\View */
/* @var $model \skeeks\cms\shop\models\ShopBasket */

if (\Yii::$app->request->get('shop_order_id') && $model->isNewRecord) {
    $model->shop_order_id = \Yii::$app->request->get('shop_order_id');
}

?>

<?php $form = ActiveForm::begin(); ?>

<?= $form->fieldSet(\Yii::t('skeeks/shop/app', 'Main')); ?>

<? if ($model->isNewRecord) : ?>
    <?= $form->field($model, 'shop_order_id')->hiddenInput()->label(false); ?>
<? endif; ?>
<!--
<div style="display: none;">
    <?/*= $form->field($model, 'shop_product_id')->widget(
        \skeeks\cms\backend\widgets\SelectModelDialogContentElementWidget::class,
        [
            'dialogRoute' => ['/shop/admin-cms-content-element'],
        ]
    ); */?>
</div>-->

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
            <?= $form->field($model, 'amount')->textInput(); ?>

        </div>

        <div class="col-md-2">
            <?= $form->field($model, 'currency_code')->listBox(
                \yii\helpers\ArrayHelper::map(\skeeks\cms\money\models\MoneyCurrency::find()->andWhere(['is_active' => true])->all(),
                    'code', 'code')
                , ['size' => 1]
            ); ?>
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
    /*if (Component instanceof sx.classes.SelectModelDialog)
    {
        Component.bind('change', function(e, data)
        {
            $('#shopbasket-name').val(data.name);
            $('#shopbasket-quantity').val(1);
            $('#shopbasket-amount').val(data.basePrice.amount);
            $('#shopbasket-currency_code').val(data.basePrice.currency_code);
            $('#shopbasket-notes').val(data.basePriceType.name);
            $('#shopbasket-measure_name').val(data.measure.symbol_rus);
        });
    }*/
});
JS
);
?>