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
/* @var $model \skeeks\cms\shop\models\ShopProduct */
/* @var $productPrices \skeeks\cms\shop\models\ShopProductPrice[] */

?>

<?php $form = ActiveForm::begin(); ?>

<?= $form->fieldSet('Цены'); ?>

    <?= $form->fieldSelect($model, 'vat_id', \yii\helpers\ArrayHelper::map(
        \skeeks\cms\shop\models\ShopVat::find()->all(), 'id', 'name'
    )); ?>

    <?= $form->fieldRadioListBoolean($model, 'vat_included'); ?>

        <?= \skeeks\cms\modules\admin\widgets\BlockTitleWidget::widget([
            'content' => 'Основные цены'
        ])?>


    <div class="row">
        <div class="col-md-3">
            <?= $form->field($model, 'purchasing_price')->textInput(); ?>
        </div>
        <div class="col-md-2">
            <?= $form->fieldSelect($model, 'purchasing_currency', \yii\helpers\ArrayHelper::map(
                \Yii::$app->money->activeCurrencies, 'code', 'name'
            )); ?>
        </div>
    </div>


    <div class="row">
        <div class="col-md-3">
            <?= $form->field($model, 'baseProductPriceValue')->textInput()->label('Базовая цена'); ?>
        </div>
        <div class="col-md-2">
            <?= $form->fieldSelect($model, 'baseProductPriceCurrency', \yii\helpers\ArrayHelper::map(
                \Yii::$app->money->activeCurrencies, 'code', 'name'
            ))->label('Валюта базовой цены'); ?>
        </div>
    </div>

    <? if ($productPrices) : ?>
        <?= \skeeks\cms\modules\admin\widgets\BlockTitleWidget::widget([
            'content' => 'Дополнительные цены'
        ])?>

        <? foreach ($productPrices as $productPrice) : ?>

            <div class="row">
                <div class="col-md-3">
                    <?= Html::textInput($productPrice->typePrice->code . "[price]", $productPrice->price); ?>
                </div>
                <div class="col-md-2">
                    <?= Html::listBox($productPrice->typePrice->code . "[currency_code]", $productPrice->price, \yii\helpers\ArrayHelper::map(
                        \Yii::$app->money->activeCurrencies, 'code', 'name'
                    )); ?>
                </div>
            </div>

        <? endforeach; ?>

    <? endif; ?>






<?= $form->fieldSetEnd(); ?>

<?= $form->fieldSet('Количество и учет'); ?>

    <?= $form->field($model, 'quantity')->textInput(); ?>
    <?= $form->field($model, 'quantity_reserved')->textInput(); ?>

    <?= $form->fieldSelect($model, 'measure_id', \yii\helpers\ArrayHelper::map(
        \skeeks\cms\measure\models\Measure::find()->all(), 'id', 'name'
    )); ?>

    <?= $form->field($model, 'measure_ratio')->textInput(); ?>


<?= $form->fieldSetEnd(); ?>

<?= $form->fieldSet('Вес и размеры'); ?>

    <?= $form->fieldInputInt($model, 'weight'); ?>
    <?= $form->fieldInputInt($model, 'length'); ?>
    <?= $form->fieldInputInt($model, 'width'); ?>
    <?= $form->fieldInputInt($model, 'height'); ?>

<?= $form->fieldSetEnd(); ?>

<?= $form->fieldSet('Параметры'); ?>
    <?= $form->fieldRadioListBoolean($model, 'quantity_trace'); ?>
    <?= $form->fieldRadioListBoolean($model, 'can_buy_zero'); ?>
    <?= $form->fieldRadioListBoolean($model, 'negative_amount_trace'); ?>
    <?= $form->fieldRadioListBoolean($model, 'subscribe'); ?>
<?= $form->fieldSetEnd(); ?>

<?= $form->buttonsCreateOrUpdate($model); ?>
<?php ActiveForm::end(); ?>
