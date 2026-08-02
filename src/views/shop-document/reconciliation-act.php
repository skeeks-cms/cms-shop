<?php
/**
 * @var $this yii\web\View
 * @var $model \skeeks\cms\shop\models\ShopDocument
 */

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use skeeks\cms\money\Currency;

if (!@$isPdf) {
    $css = file_get_contents(\Yii::getAlias('@skeeks/cms/shop/views/shop-document/document.css'));
    $this->registerCss($css);
}

$noSignature = @$noSignature;
$seller = $model->sellerContractor;
$data = (array)ArrayHelper::getValue((array)$model->document_data, 'reconciliation_act', []);
$operations = (array)ArrayHelper::getValue($data, 'operations', []);
$currencyCode = (string)ArrayHelper::getValue($data, 'currency_code', $model->currency_code ?: 'RUB');
$currencyLabel = $currencyCode === 'RUB'
    ? 'руб.'
    : ((string)Currency::getInstance($currencyCode)->symbol ?: $currencyCode);
$periodStart = (string)ArrayHelper::getValue($data, 'period_start');
$periodEnd = (string)ArrayHelper::getValue($data, 'period_end');
$closingBalance = (float)ArrayHelper::getValue($data, 'closing_balance', 0);
$money = function ($amount) use ($currencyLabel) {
    $amount = (float)$amount;
    return $amount == 0.0 ? '' : number_format($amount, 2, ',', ' ').' '.$currencyLabel;
};
$date = function ($value) {
    $timestamp = strtotime((string)$value);
    return $timestamp ? date('d.m.Y', $timestamp) : '';
};

if ($closingBalance > 0) {
    $conclusion = 'По состоянию на '.$date($periodEnd).' задолженность '
        .$model->buyerName.' в пользу '.$model->sellerName.' составляет '
        .$money(abs($closingBalance)).'.';
} elseif ($closingBalance < 0) {
    $conclusion = 'По состоянию на '.$date($periodEnd).' задолженность '
        .$model->sellerName.' в пользу '.$model->buyerName.' составляет '
        .$money(abs($closingBalance)).'.';
} else {
    $conclusion = 'По состоянию на '.$date($periodEnd).' задолженность между сторонами отсутствует.';
}
?>

<section class="sx-reconciliation-page">
    <h1 class="sx-reconciliation-title">
        Акт сверки взаимных расчетов №<?= Html::encode($model->number ?: $model->id); ?>
    </h1>
    <div class="sx-reconciliation-period">
        за период с <?= Html::encode($date($periodStart)); ?> по <?= Html::encode($date($periodEnd)); ?>
    </div>

    <p class="sx-reconciliation-intro">
        Между <?= Html::encode($model->sellerFullName ?: $model->sellerName); ?>
        и <?= Html::encode($model->buyerFullName ?: $model->buyerName); ?>
        произведена сверка взаимных расчетов по данным учета сторон.
    </p>

    <table class="sx-reconciliation-table">
        <thead>
            <tr>
                <th colspan="4">По данным <?= Html::encode($model->sellerName); ?></th>
                <th colspan="4">По данным <?= Html::encode($model->buyerName); ?></th>
            </tr>
            <tr>
                <th class="sx-reconciliation-date">Дата</th>
                <th>Документ</th>
                <th class="sx-reconciliation-sum">Дебет</th>
                <th class="sx-reconciliation-sum">Кредит</th>
                <th class="sx-reconciliation-date">Дата</th>
                <th>Документ</th>
                <th class="sx-reconciliation-sum">Дебет</th>
                <th class="sx-reconciliation-sum">Кредит</th>
            </tr>
        </thead>
        <tbody>
            <tr class="sx-reconciliation-balance-row">
                <td></td>
                <td>Сальдо на начало периода</td>
                <td><?= Html::encode($money(ArrayHelper::getValue($data, 'opening_debit', 0))); ?></td>
                <td><?= Html::encode($money(ArrayHelper::getValue($data, 'opening_credit', 0))); ?></td>
                <td></td><td></td><td></td><td></td>
            </tr>
            <?php foreach ($operations as $operation) : ?>
                <tr>
                    <td><?= Html::encode($date(ArrayHelper::getValue($operation, 'date'))); ?></td>
                    <td><?= Html::encode(ArrayHelper::getValue($operation, 'description')); ?></td>
                    <td><?= Html::encode($money(ArrayHelper::getValue($operation, 'debit', 0))); ?></td>
                    <td><?= Html::encode($money(ArrayHelper::getValue($operation, 'credit', 0))); ?></td>
                    <td></td><td></td><td></td><td></td>
                </tr>
            <?php endforeach; ?>
            <tr class="sx-reconciliation-total-row">
                <td></td>
                <td>Обороты за период</td>
                <td><?= Html::encode($money(ArrayHelper::getValue($data, 'turnover_debit', 0))); ?></td>
                <td><?= Html::encode($money(ArrayHelper::getValue($data, 'turnover_credit', 0))); ?></td>
                <td></td><td></td><td></td><td></td>
            </tr>
            <tr class="sx-reconciliation-total-row">
                <td></td>
                <td>Сальдо на конец периода</td>
                <td><?= Html::encode($money(ArrayHelper::getValue($data, 'closing_debit', 0))); ?></td>
                <td><?= Html::encode($money(ArrayHelper::getValue($data, 'closing_credit', 0))); ?></td>
                <td></td><td></td><td></td><td></td>
            </tr>
        </tbody>
    </table>

    <p class="sx-reconciliation-conclusion"><strong><?= Html::encode($conclusion); ?></strong></p>

    <table class="sx-reconciliation-signatures">
        <tr>
            <td>
                <div class="sx-reconciliation-party-title"><?= Html::encode($model->sellerName); ?></div>
                <div>ИНН <?= Html::encode($model->sellerInn); ?><?= $model->sellerKpp ? ' / КПП '.Html::encode($model->sellerKpp) : ''; ?></div>
                <div class="sx-reconciliation-sign-line">
                    <?php if (!$noSignature && $seller && $seller->directorSignature) : ?>
                        <img class="sx-reconciliation-signature-image" src="<?= Html::encode($seller->directorSignature->absoluteSrc); ?>" />
                    <?php endif; ?>
                    <span>Подпись</span>
                </div>
                <?php if (!$noSignature && $seller && $seller->stamp) : ?>
                    <img class="sx-reconciliation-stamp" src="<?= Html::encode($seller->stamp->absoluteSrc); ?>" />
                <?php endif; ?>
            </td>
            <td>
                <div class="sx-reconciliation-party-title"><?= Html::encode($model->buyerName); ?></div>
                <div>ИНН <?= Html::encode($model->buyerInn); ?><?= $model->buyerKpp ? ' / КПП '.Html::encode($model->buyerKpp) : ''; ?></div>
                <div class="sx-reconciliation-sign-line"><span>Подпись</span></div>
            </td>
        </tr>
    </table>
</section>

<?php if (!$isPdf) : ?>
    <?= $this->render('_controls', ['model' => $model]); ?>
<?php endif; ?>
