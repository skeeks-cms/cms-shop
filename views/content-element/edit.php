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

<?= $form->fieldSet(\skeeks\cms\shop\Module::t('app', 'Prices')); ?>

    <?= $form->fieldSelect($model, 'vat_id', \yii\helpers\ArrayHelper::map(
        \skeeks\cms\shop\models\ShopVat::find()->all(), 'id', 'name'
    )); ?>

    <?= $form->fieldRadioListBoolean($model, 'vat_included'); ?>

        <?= \skeeks\cms\modules\admin\widgets\BlockTitleWidget::widget([
            'content' => \skeeks\cms\shop\Module::t('app', 'Main prices')
        ])?>


    <div class="row">
        <div class="col-md-3">
            <?= $form->field($model, 'purchasing_price')->textInput(); ?>
        </div>
        <div class="col-md-2">
            <?= $form->fieldSelect($model, 'purchasing_currency', \yii\helpers\ArrayHelper::map(
                \Yii::$app->money->activeCurrencies, 'code', 'code'
            )); ?>
        </div>
    </div>


    <div class="row">
        <div class="col-md-3">
            <?= $form->field($model, 'baseProductPriceValue')->textInput()->label(\skeeks\cms\shop\Module::t('app', 'Base price')." (".\skeeks\cms\shop\Module::t('app', 'Price type')." \"{$model->baseProductPrice->typePrice->name}\")"); ?>
        </div>
        <div class="col-md-2">
            <?= $form->fieldSelect($model, 'baseProductPriceCurrency', \yii\helpers\ArrayHelper::map(
                \Yii::$app->money->activeCurrencies, 'code', 'code'
            ))->label(\skeeks\cms\shop\Module::t('app', 'Currency base price')); ?>
        </div>

        <div class="col-md-2">
            <label>&nbsp;</label>
            <p>
                <?= \skeeks\cms\shop\widgets\admin\PropductPriceChangeAdminWidget::widget([
                    'productPrice' => $model->baseProductPrice
                ])?>
            </p>
        </div>
    </div>

    <? if ($productPrices) : ?>
        <?= \skeeks\cms\modules\admin\widgets\BlockTitleWidget::widget([
            'content' => \skeeks\cms\shop\Module::t('app', 'Additional costs')
        ])?>

        <? foreach ($productPrices as $productPrice) : ?>

            <div class="row">
                <div class="col-md-3">
                    <label><?= $productPrice->typePrice->name; ?></label>
                    <?= Html::textInput("prices[" . $productPrice->typePrice->id . "][price]", $productPrice->price, [
                        'class' => 'form-control'
                    ]); ?>
                </div>
                <div class="col-md-2">
                    <label>Валюта</label>

                    <?= \skeeks\widget\chosen\Chosen::widget([
                        'name' => "prices[" . $productPrice->typePrice->id . "][currency_code]",
                        'value' => $productPrice->currency_code,
                        'allowDeselect' => false,
                        'items' => \yii\helpers\ArrayHelper::map(
                            \Yii::$app->money->activeCurrencies, 'code', 'code'
                        )
                    ])?>
                </div>

                <div class="col-md-2">
                    <label>&nbsp;</label>
                    <p>
                        <?= \skeeks\cms\shop\widgets\admin\PropductPriceChangeAdminWidget::widget([
                            'productPrice' => $productPrice
                        ])?>
                    </p>
                </div>
            </div>

        <? endforeach; ?>

    <? endif; ?>






<?= $form->fieldSetEnd(); ?>

<?= $form->fieldSet(\skeeks\cms\shop\Module::t('app', 'The number and account')); ?>

    <?= $form->field($model, 'quantity')->textInput(); ?>
    <?= $form->field($model, 'quantity_reserved')->textInput(); ?>

    <?= $form->fieldSelect($model, 'measure_id', \yii\helpers\ArrayHelper::map(
        \skeeks\cms\measure\models\Measure::find()->all(), 'id', 'name'
    )); ?>

    <?= $form->field($model, 'measure_ratio')->textInput(); ?>


<?= $form->fieldSetEnd(); ?>

<?= $form->fieldSet(\skeeks\cms\shop\Module::t('app', 'Weight and size')); ?>

    <?= $form->fieldInputInt($model, 'weight'); ?>
    <?= $form->fieldInputInt($model, 'length'); ?>
    <?= $form->fieldInputInt($model, 'width'); ?>
    <?= $form->fieldInputInt($model, 'height'); ?>

<?= $form->fieldSetEnd(); ?>

<?= $form->fieldSet(\skeeks\cms\shop\Module::t('app', 'Options')); ?>
    <?= $form->fieldRadioListBoolean($model, 'quantity_trace'); ?>
    <?= $form->fieldRadioListBoolean($model, 'can_buy_zero'); ?>
    <?= $form->fieldRadioListBoolean($model, 'negative_amount_trace'); ?>
    <?= $form->fieldRadioListBoolean($model, 'subscribe'); ?>
<?= $form->fieldSetEnd(); ?>

<?= $form->buttonsCreateOrUpdate($model); ?>
<?php ActiveForm::end(); ?>
