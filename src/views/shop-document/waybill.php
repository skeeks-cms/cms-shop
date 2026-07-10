<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 */

/* @var $this yii\web\View */
/* @var $model \skeeks\cms\shop\models\ShopDocument */

use skeeks\cms\models\CmsContractor;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

if (!@$isPdf) {
    $css = file_get_contents(\Yii::getAlias('@skeeks/cms/shop/views/shop-document/document.css'));
    $this->registerCss($css);
}

$noSignature = @$noSignature;
$documentData = (array)$model->document_data;
$specificData = (array)ArrayHelper::getValue($documentData, 'waybill', []);
$items = $model->documentItems;
$bills = $model->bills;
$sourceBill = $bills ? reset($bills) : null;
$number = $model->number ?: $model->id;
$issuedAt = $model->issued_at ?: $model->created_at ?: time();
$date = \Yii::$app->formatter->asDate($issuedAt, 'php:d.m.Y');

$pick = function ($values, $fallback = '-') {
    foreach ((array)$values as $value) {
        $value = trim((string)$value);
        if ($value !== '' && $value !== '-') {
            return $value;
        }
    }

    return $fallback;
};
$e = function ($value, $fallback = '-') use ($pick) {
    return Html::encode($pick($value, $fallback));
};
$money = function ($value) {
    return number_format((float)$value, 2, ',', ' ');
};
$quantity = function ($value) {
    return rtrim(rtrim(number_format((float)$value, 3, ',', ' '), '0'), ',');
};

$sellerContractor = $model->sellerContractor;
if (!$sellerContractor && $sourceBill) {
    $sellerContractor = $sourceBill->receiverContractor;
}
if (!$sellerContractor) {
    $sellerContractor = CmsContractor::find()->our()->one();
}

$buyerContractor = $model->buyerContractor;
if (!$buyerContractor && $sourceBill) {
    $buyerContractor = $sourceBill->senderContractor;
}
if (!$buyerContractor && $sourceBill && $sourceBill->company && $sourceBill->company->contractors) {
    $companyContractors = $sourceBill->company->contractors;
    $buyerContractor = reset($companyContractors);
}
if (!$buyerContractor && $sourceBill && $sourceBill->cmsUser && $sourceBill->cmsUser->cmsContractors) {
    $userContractors = $sourceBill->cmsUser->cmsContractors;
    $buyerContractor = reset($userContractors);
}

$sellerName = $pick([
    $model->sellerFullName,
    $model->sellerName,
    $sellerContractor ? ($sellerContractor->full_name ?: $sellerContractor->asShortText) : null,
]);
$sellerAddress = $pick([$model->sellerAddress, $sellerContractor ? $sellerContractor->address : null]);
$sellerInn = $pick([$model->sellerInn, $sellerContractor ? $sellerContractor->inn : null], '');
$sellerKpp = $pick([$model->sellerKpp, $sellerContractor ? $sellerContractor->kpp : null], '');

$buyerName = $pick([
    $model->buyerFullName,
    $model->buyerName,
    $buyerContractor ? ($buyerContractor->full_name ?: $buyerContractor->asShortText) : null,
    $sourceBill && $sourceBill->company ? $sourceBill->company->name : null,
]);
$buyerAddress = $pick([$model->buyerAddress, $buyerContractor ? $buyerContractor->address : null]);
$buyerInn = $pick([$model->buyerInn, $buyerContractor ? $buyerContractor->inn : null], '');
$buyerKpp = $pick([$model->buyerKpp, $buyerContractor ? $buyerContractor->kpp : null], '');

$shipper = $pick(ArrayHelper::getValue($specificData, 'shipper'), $sellerName);
$shipperAddress = $pick(ArrayHelper::getValue($specificData, 'shipper_address'), $sellerAddress);
$consignee = $pick(ArrayHelper::getValue($specificData, 'consignee'), $buyerName);
$consigneeAddress = $pick(ArrayHelper::getValue($specificData, 'consignee_address'), $buyerAddress);
$transportDocument = $pick(ArrayHelper::getValue($specificData, 'transport_document'), '-');
$baseDocument = $pick([ArrayHelper::getValue($specificData, 'base_document'), $model->description], '-');
$operationType = $pick(ArrayHelper::getValue($specificData, 'operation_type'), '-');
$logoSrc = $pick(ArrayHelper::getValue($documentData, 'logo_src'), '');
if (!$logoSrc && $sellerContractor && $sellerContractor->cmsImage) {
    $logoSrc = $sellerContractor->cmsImage->absoluteSrc;
}
if (!$logoSrc && \Yii::$app->has('skeeks') && \Yii::$app->skeeks->site && \Yii::$app->skeeks->site->image) {
    $logoSrc = \Yii::$app->skeeks->site->image->absoluteSrc;
}
?>

<section class="sx-waybill-page">
    <table class="sx-waybill-top">
        <tr>
            <td class="sx-waybill-logo-cell">
                <?php if ($logoSrc) : ?>
                    <img class="sx-waybill-logo" src="<?= Html::encode($logoSrc); ?>" />
                <?php endif; ?>
            </td>
            <td class="sx-waybill-form-code">
                Унифицированная форма ТОРГ-12<br>
                Товарная накладная
            </td>
        </tr>
    </table>

    <h1 class="sx-waybill-title">Товарная накладная № <?= Html::encode($number); ?> от <?= Html::encode($date); ?></h1>

    <table class="sx-waybill-lines">
        <tr>
            <td class="sx-waybill-label">Грузоотправитель</td>
            <td><?= $e($shipper); ?>, адрес: <?= $e($shipperAddress); ?></td>
        </tr>
        <tr>
            <td class="sx-waybill-label">Грузополучатель</td>
            <td><?= $e($consignee); ?>, адрес: <?= $e($consigneeAddress); ?></td>
        </tr>
        <tr>
            <td class="sx-waybill-label">Поставщик</td>
            <td><?= $e($sellerName); ?>, ИНН <?= Html::encode($sellerInn); ?><?= $sellerKpp ? ', КПП '.Html::encode($sellerKpp) : ''; ?>, адрес: <?= $e($sellerAddress); ?></td>
        </tr>
        <tr>
            <td class="sx-waybill-label">Плательщик</td>
            <td><?= $e($buyerName); ?>, ИНН <?= Html::encode($buyerInn); ?><?= $buyerKpp ? ', КПП '.Html::encode($buyerKpp) : ''; ?>, адрес: <?= $e($buyerAddress); ?></td>
        </tr>
        <tr>
            <td class="sx-waybill-label">Основание</td>
            <td><?= $e($baseDocument); ?></td>
        </tr>
        <tr>
            <td class="sx-waybill-label">Транспортная накладная</td>
            <td><?= $e($transportDocument); ?></td>
        </tr>
        <tr>
            <td class="sx-waybill-label">Вид операции</td>
            <td><?= $e($operationType); ?></td>
        </tr>
    </table>

    <table class="sx-waybill-products">
        <thead>
            <tr>
                <th rowspan="2">№</th>
                <th rowspan="2">Наименование товара</th>
                <th rowspan="2">Ед. изм.</th>
                <th rowspan="2">Упаковка</th>
                <th rowspan="2">В месте</th>
                <th rowspan="2">Мест/штук</th>
                <th rowspan="2">Кол-во</th>
                <th rowspan="2">Цена</th>
                <th colspan="2">Масса</th>
                <th rowspan="2">НДС</th>
                <th rowspan="2">Сумма</th>
            </tr>
            <tr>
                <th>Брутто</th>
                <th>Нетто</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $index => $item) : ?>
                <?php $extra = (array)$item->extra_data; ?>
                <tr>
                    <td><?= $index + 1; ?></td>
                    <td class="sx-waybill-product-name"><?= Html::encode($item->name); ?></td>
                    <td><?= $e($item->measure_name); ?></td>
                    <td><?= Html::encode(ArrayHelper::getValue($extra, 'package', '')); ?></td>
                    <td><?= Html::encode(ArrayHelper::getValue($extra, 'items_per_package', '')); ?></td>
                    <td><?= Html::encode(ArrayHelper::getValue($extra, 'places', '')); ?></td>
                    <td class="sx-waybill-num"><?= Html::encode($quantity($item->quantity)); ?></td>
                    <td class="sx-waybill-num"><?= Html::encode($money($item->price)); ?></td>
                    <td class="sx-waybill-num"><?= Html::encode(ArrayHelper::getValue($extra, 'gross_weight', '')); ?></td>
                    <td class="sx-waybill-num"><?= Html::encode(ArrayHelper::getValue($extra, 'net_weight', '')); ?></td>
                    <td><?= Html::encode($item->vat_name ?: 'Без НДС'); ?></td>
                    <td class="sx-waybill-num"><?= Html::encode($money($item->amount)); ?></td>
                </tr>
            <?php endforeach; ?>
            <tr class="sx-waybill-total">
                <td colspan="11">Итого</td>
                <td class="sx-waybill-num"><?= Html::encode($money($model->amount)); ?></td>
            </tr>
        </tbody>
    </table>

    <?php if ($model->comment_after) : ?>
        <p class="sx-waybill-comment"><?= nl2br(Html::encode($model->comment_after)); ?></p>
    <?php endif; ?>

    <table class="sx-waybill-signatures">
        <tr>
            <td>
                Отпуск груза разрешил
                <span></span>
                <small>должность, подпись, расшифровка подписи</small>
            </td>
            <td>
                Главный бухгалтер
                <span></span>
                <small>подпись, расшифровка подписи</small>
            </td>
            <td>
                Груз принял
                <span></span>
                <small>должность, подпись, расшифровка подписи</small>
            </td>
        </tr>
        <tr>
            <td>
                Отпуск груза произвел
                <?php if (!$noSignature && $sellerContractor && $sellerContractor->directorSignature) : ?>
                    <img src="<?= Html::encode($sellerContractor->directorSignature->absoluteSrc); ?>" class="sx-waybill-signature-image" />
                <?php endif; ?>
                <span></span>
                <small>должность, подпись, расшифровка подписи</small>
            </td>
            <td>
                М.П.
                <?php if (!$noSignature && $sellerContractor && $sellerContractor->stamp) : ?>
                    <img src="<?= Html::encode($sellerContractor->stamp->absoluteSrc); ?>" class="sx-waybill-stamp" />
                <?php endif; ?>
            </td>
            <td>
                Груз получил грузополучатель
                <span></span>
                <small>должность, подпись, расшифровка подписи</small>
            </td>
        </tr>
    </table>

</section>

<?php if (!@$isPdf) : ?>
    <?= $this->render('_controls', ['model' => $model]); ?>
<?php endif; ?>
