<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */
/* @var $this yii\web\View */
/* @var $widget \skeeks\cms\shop\widgets\admin\SubProductExternalDataWidget */

$data = $widget->shopProduct->supplier_external_jsondata;
$shopSupplier = $widget->shopProduct->shopSupplier;
$supplierProperties = $shopSupplier->getShopSupplierProperties()->andWhere(['is_visible' => 1])->andWhere(['in', 'external_code', array_keys($data)])->all();
$this->registerCss(<<<CSS
.sx-supplier-properies-hidden {
display: none;
}

.sx-supproduct-external-widget {
    font-size: 10px;
}
CSS
);

$this->registerJs(<<<JS
$("body").on("click", ".sx-more", function() {
    if ($(".sx-supplier-properies-hidden").is(":visible")) {
        $(this).empty().append("Показать еще");
        $(".sx-supplier-properies-hidden").slideUp();
    } else {
        $(".sx-supplier-properies-hidden").slideDown();
        $(this).empty().append("Скрыть");
    }
    
    return false;
});
JS
);
?>
<?= \yii\helpers\Html::beginTag('div', $widget->options); ?>

<div itemscope="" itemtype="http://schema.org/Product">
    <meta itemprop="name" content="<?= $widget->shopProduct->cmsContentElement->name; ?>">

<? if ($supplierProperties) : ?>
    <div class="sx-supplier-properies-visible">
        <? foreach ($supplierProperties as $supplierProperty) : ?>
            <?
            $row = \yii\helpers\ArrayHelper::getValue($data, $supplierProperty->external_code);
            \yii\helpers\ArrayHelper::remove($data, $supplierProperty->external_code);
            ?>


            <? if ($row) : ?>
                <div class="sx-propery-row">
            <span>
            <? if ($supplierProperty->name) : ?>
                <?= $supplierProperty->name; ?>
            <? else : ?>
                <?= $supplierProperty->external_code; ?>
            <? endif; ?>
                :
            </span>


                <? if (is_string($row)) : ?>
                    <? if (filter_var($row, FILTER_VALIDATE_URL)) : ?>
                        <b><a href="<?= $row; ?>" target="_blank"><?= $row; ?></a></b>
                    <? else : ?>
                        <b><?= $row; ?></b>
                    <? endif; ?>

                <? else : ?>
                    <pre><?= print_r($row, true); ?></pre>
                <? endif; ?>
            </div>

            <? endif; ?>
        <? endforeach; ?>
    </div>
<? endif; ?>

<? if ($data) : ?>
    <div class="">
        <button class="btn btn-secondary btn-sm sx-more">Показать еще</button>
    </div>
    <div class="sx-supplier-properies-hidden">
        <? foreach ($data as $key => $row) : ?>
            <? if ($row) : ?>
                <div class="sx-propery-row"><span><?= $key; ?>:</span>
                    <? if (is_string($row)) : ?>
                        <? if (filter_var($row, FILTER_VALIDATE_URL)) : ?>
                            <b><a href="<?= $row; ?>" target="_blank"><?= $row; ?></a></b>
                        <? else : ?>
                            <b><?= $row; ?></b>
                        <? endif; ?>

                    <? else : ?>
                        <pre><?= print_r($row, true); ?></pre>
                    <? endif; ?>
                </div>
            <? endif; ?>
        <? endforeach; ?>
    </div>
<? endif; ?>

</div>
<?= \yii\helpers\Html::endTag('div'); ?>