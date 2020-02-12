<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */
/* @var $this yii\web\View */
/* @var $data [] */
/* @var $shopProduct \skeeks\cms\shop\models\ShopProduct */
$data = $shopProduct->supplier_external_jsondata;
$shopSupplier = $shopProduct->shopSupplier;
$supplierProperties = $shopSupplier->getShopSupplierProperties()->andWhere(['is_visible' => 1])->andWhere(['in', 'external_code', array_keys($data)])->all();
?>

<? if ($supplierProperties) : ?>
    <div class="sx-supplier-properies-visible">
        <? foreach ($supplierProperties as $supplierProperty) : ?>
            <?
            $row = \yii\helpers\ArrayHelper::getValue($data, $supplierProperty->external_code);
            \yii\helpers\ArrayHelper::remove($data, $supplierProperty->external_code);
            ?>
            <p><span>
            <? if ($supplierProperty->name) : ?>
                <?= $supplierProperty->name; ?>
            <? endif; ?>
                <?= $supplierProperty->external_code; ?>:</span>

            <? if (is_string($row)) : ?>
                <? if (filter_var($row, FILTER_VALIDATE_URL)) : ?>
                    <b><a href="<?= $row; ?>" target="_blank"><?= $row; ?></a></b>
                <? else : ?>
                    <b><?= $row; ?></b>
                <? endif; ?>

            <? else : ?>
                <pre><?= print_r($row, true); ?></pre>
            <? endif; ?>
            </p>
        <? endforeach; ?>
    </div>
<? endif; ?>


<? foreach ($data as $key => $row) : ?>
    <? if ($row) : ?>
        <p><span><?= $key; ?>:</span>
        <? if (is_string($row)) : ?>
            <? if (filter_var($row, FILTER_VALIDATE_URL)) : ?>
                <b><a href="<?= $row; ?>" target="_blank"><?= $row; ?></a></b>
            <? else : ?>
                <b><?= $row; ?></b>
            <? endif; ?>

        <? else : ?>
            <pre><?= print_r($row, true); ?></pre>
        <? endif; ?>
        </p>
    <? endif; ?>

<? endforeach; ?>
