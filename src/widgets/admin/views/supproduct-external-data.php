<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */
/* @var $this yii\web\View */
/* @var $widget \skeeks\cms\shop\widgets\admin\SubProductExternalDataWidget */
/* @var $supplierProperties \skeeks\cms\shop\models\ShopSupplierProperty[] */

$data = $widget->shopProduct->supplier_external_jsondata;
$supplierProperties = \Yii::$app->skeeks->site->getShopSupplierProperties()->andWhere(['is_visible' => 1])->andWhere(['in', 'external_code', array_keys($data)])->all();
$this->registerCss(<<<CSS
.sx-supplier-properies-hidden {
display: none;
}

.sx-supproduct-external-widget {
    font-size: 10px;
}

.sx-propery-row:hover {
    background: #e6ecec;
}
.sx-propery-row {
    /*margin: 2px 0px;*/
    min-height: 22px;
    line-height: 21px;
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

$(".sx-copy").on("click", function() {
    var input =  document.getElementById("cont");
    var input =  $(this).find("input");
      // Select the input node's contents
    input.select();
    // Copy it to the clipboard
    _.delay(function() {
        try {
            // Теперь, когда мы выбрали текст ссылки, выполним команду копирования
            var successful = document.execCommand("copy");
            var msg = successful ? 'successful' : 'unsuccessful';  
            sx.notify.success("Скопировано");
        } catch(err) { 
            throw err;
            sx.notify.error('Oops, unable to copy');  
        }  
    }, 300);
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
                    
                    <span style="float: right;" title="Правильное название в нашей системе">
                <? if ($supplierProperty->cmsContentProperty) : ?>
                    
                    <? if (is_string($row)) : ?>
                        <?
                        /**
                         * @var $shopSupplierPropertyOption \skeeks\cms\shop\models\ShopSupplierPropertyOption
                         */
                            $shopSupplierPropertyOption = $supplierProperty->getShopSupplierPropertyOptions()->andWhere(['name' => $row])->one();
                        ?>
                    <? if ($shopSupplierPropertyOption && $shopSupplierPropertyOption->cmsContentElement) : ?>
                        <?= $shopSupplierPropertyOption->cmsContentElement->name; ?>
                        
                        <!--<a href="https://market.yandex.ru/search?cvredirect=2&amp;text=<?/*= $shopSupplierPropertyOption->cmsContentElement->name; */?>" title="Поиск в yandex market" target="_blank" style="color: blue" class="btn btn-xs btn-secondary">
                            <i class="fas fa-shopping-cart"></i>
                        </a>-->
                        
                        <a href="#" class="btn btn-xs sx-copy btn-secondary" data-toggle="tooltip" title="" data-original-title="Скопировать">
                        <i class="fas fa-copy" style="cursor: pointer;"></i>
                        <input id="cont" type="text" value="<?= $shopSupplierPropertyOption->cmsContentElement->name; ?>" style="position: absolute; left: -20000px;">
                        </a>

                        
                            
                    <? endif; ?>
                    <?/*= $supplierProperty->cmsContentProperty->name; */?>
                    <? endif; ?>
                <? endif; ?>
                    </span>
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