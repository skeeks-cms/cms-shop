<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 */

/* @var $this yii\web\View */
/* @var $model \skeeks\cms\shop\models\ShopDocument */

use skeeks\cms\helpers\StringHelper;
use skeeks\cms\shop\models\ShopDocument;
use yii\helpers\Html;

if (!@$isPdf) {
    $css = file_get_contents(\Yii::getAlias('@skeeks/cms/shop/views/shop-document/document.css'));
    $this->registerCss($css);
}

$noSignature = @$noSignature;
$documentData = (array)$model->document_data;
$items = $model->documentItems;
$hasDiscounts = false;
$itemsSubtotal = 0;
foreach ($items as $item) {
    $itemsSubtotal += (float)$item->price * (float)$item->quantity;
    if ((float)$item->discount_amount > 0) {
        $hasDiscounts = true;
    }
}

$title = $model->type == ShopDocument::TYPE_UPD
    ? 'Универсальный передаточный документ'
    : $model->typeAsText;
$number = $model->number ?: $model->id;
$seller = $model->sellerContractor;
$logoSrc = (string)\yii\helpers\ArrayHelper::getValue($documentData, 'logo_src');
if (!$logoSrc && $seller && $seller->cmsImage) {
    $logoSrc = $seller->cmsImage->absoluteSrc;
}
if (!$logoSrc && \Yii::$app->has('skeeks') && \Yii::$app->skeeks->site && \Yii::$app->skeeks->site->image) {
    $logoSrc = \Yii::$app->skeeks->site->image->absoluteSrc;
}
?>

<section class="sx-document-page">
    <?php if ($logoSrc) : ?>
        <div class="sx-document-logo-wrap">
            <img class="sx-document-logo" src="<?= Html::encode($logoSrc); ?>" />
        </div>
    <?php endif; ?>

    <h1 class="sx-document-title">
        <?= Html::encode($title); ?> №<?= Html::encode($number); ?> от <?= \Yii::$app->formatter->asDate($model->issued_at ?: $model->created_at, 'long'); ?>
    </h1>

    <table class="sx-document-requisites">
        <tr>
            <td>
                <div class="sx-document-requisites-title">Продавец / исполнитель</div>
                <div><?= Html::encode($model->sellerFullName ?: $model->sellerName); ?></div>
                <?php if ($model->sellerInn || $model->sellerKpp) : ?>
                    <div>ИНН <?= Html::encode($model->sellerInn); ?><?= $model->sellerKpp ? ' / КПП '.Html::encode($model->sellerKpp) : ''; ?></div>
                <?php endif; ?>
                <?php if ($model->sellerOgrn) : ?>
                    <div>ОГРН <?= Html::encode($model->sellerOgrn); ?></div>
                <?php endif; ?>
                <?php if ($model->sellerAddress) : ?>
                    <div>Адрес: <?= Html::encode($model->sellerAddress); ?></div>
                <?php endif; ?>
            </td>
            <td>
                <div class="sx-document-requisites-title">Покупатель / заказчик</div>
                <div><?= Html::encode($model->buyerFullName ?: $model->buyerName); ?></div>
                <?php if ($model->buyerInn || $model->buyerKpp) : ?>
                    <div>ИНН <?= Html::encode($model->buyerInn); ?><?= $model->buyerKpp ? ' / КПП '.Html::encode($model->buyerKpp) : ''; ?></div>
                <?php endif; ?>
                <?php if ($model->buyerOgrn) : ?>
                    <div>ОГРН <?= Html::encode($model->buyerOgrn); ?></div>
                <?php endif; ?>
                <?php if ($model->buyerAddress) : ?>
                    <div>Адрес: <?= Html::encode($model->buyerAddress); ?></div>
                <?php endif; ?>
            </td>
        </tr>
    </table>

    <?php if ($model->description) : ?>
        <p class="sx-document-comment"><?= nl2br(Html::encode($model->description)); ?></p>
    <?php endif; ?>
    <?php if ($model->comment_before) : ?>
        <p class="sx-document-comment"><?= nl2br(Html::encode($model->comment_before)); ?></p>
    <?php endif; ?>

    <table class="sx-document-items">
        <thead>
            <tr>
                <th class="sx-document-number">№</th>
                <th>Наименование</th>
                <th>Кол-во</th>
                <th>Ед.</th>
                <th>Цена</th>
                <?php if ($hasDiscounts) : ?>
                    <th>Скидка</th>
                <?php endif; ?>
                <th>НДС</th>
                <th>Сумма</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $index => $item) : ?>
                <tr>
                    <td class="sx-document-number"><?= $index + 1; ?></td>
                    <td><?= Html::encode($item->name); ?></td>
                    <td class="sx-document-quantity"><?= (float)$item->quantity; ?></td>
                    <td><?= Html::encode($item->measure_name); ?></td>
                    <td class="sx-document-money"><?= Html::encode((string)$item->priceMoney); ?></td>
                    <?php if ($hasDiscounts) : ?>
                        <td class="sx-document-money"><?= (float)$item->discount_amount > 0 ? Html::encode((string)$item->discountMoney) : ''; ?></td>
                    <?php endif; ?>
                    <td><?= Html::encode($item->vat_name ?: 'Без НДС'); ?></td>
                    <td class="sx-document-money"><?= Html::encode((string)$item->money); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="sx-document-summary">
        Итого: <?= Html::encode((string)$model->money); ?><br>
        Без НДС: 0
    </div>

    <p>Всего наименований: <?= count($items); ?>, на сумму <?= Html::encode((string)$model->money); ?></p>
    <p class="sx-document-total-text">
        <?php
        $formatter = new NumberFormatter('ru', NumberFormatter::SPELLOUT);
        echo Html::encode(StringHelper::ucfirst($formatter->format($model->money->amount)));
        ?> руб.
    </p>

    <?php if ($model->comment_after) : ?>
        <p class="sx-document-comment"><?= nl2br(Html::encode($model->comment_after)); ?></p>
    <?php endif; ?>

    <table class="sx-document-signatures">
        <tr>
            <td>
                <table class="sx-document-signature-row">
                    <tr>
                        <td class="sx-document-signature-label">Исполнитель:</td>
                        <td class="sx-document-signature-line-cell">
                            <?php if (!$noSignature && $seller && $seller->directorSignature) : ?>
                                <img class="sx-document-signature-image" src="<?= Html::encode($seller->directorSignature->absoluteSrc); ?>" />
                            <?php endif; ?>
                        </td>
                        <td class="sx-document-signature-name"><?= Html::encode($model->sellerName); ?></td>
                    </tr>
                </table>
                <?php if (!$noSignature && $seller && $seller->stamp) : ?>
                    <div class="sx-document-stamp-wrap">
                        <img class="sx-document-stamp" src="<?= Html::encode($seller->stamp->absoluteSrc); ?>" />
                    </div>
                <?php endif; ?>
            </td>
            <td>
                <table class="sx-document-signature-row">
                    <tr>
                        <td class="sx-document-signature-label">Заказчик:</td>
                        <td class="sx-document-signature-line-cell"></td>
                        <td class="sx-document-signature-name"><?= Html::encode($model->buyerName); ?></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

</section>

<?php if (!$isPdf) : ?>
    <?= $this->render('_controls', ['model' => $model]); ?>
<?php endif; ?>
