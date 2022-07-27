<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */
/**
 * @var $this yii\web\View
 * @var $q \skeeks\cms\query\CmsContentElementActiveQuery
 * @var $element \skeeks\cms\shop\models\ShopCmsContentElement
 * @var $isPrintSpec bool
 * @var $isPrintBarcode bool
 */
?>

<div class="label" style="width: 30mm; height: 20mm;">
    <div class="block" style='
				height: 3mm;
				padding-top: 1mm;
				border-left-width: 0px;
				border-right-width: 0px;
				border-bottom-width: 0px;
				border-top-width: 0px;
				'>
        <div class="text" style='
							font-size: 12px;
							font-weight: bold;
							font-style: normal;
							text-decoration: ;
							text-align: center'>
            <?php echo $element->shopProduct->baseProductPrice->money; ?>
        </div>
    </div>
    <div class="block" style='
				height: 6mm;
				border-left-width: 0px;
				border-right-width: 0px;
				border-bottom-width: 0px;
				border-top-width: 0px;
				padding: 0 5px;
				'>
        <div class="text" style='
							font-size: 10px;
							font-weight: normal;
							font-style: normal;
							text-decoration: ;
							text-align: center'>
            <?php echo $element->productName; ?>
        </div>
    </div>
    <?php if ($isPrintBarcode && $element->shopProduct->shopProductBarcodes) : ?>
        <div class="text-center" style="height: 9mm;padding: 0 5px; padding-top: 1mm">
            <? foreach ($element->shopProduct->shopProductBarcodes as $data) : ?>
                <div class="block" style='
				height: 25px;
				z-index: 1;
				border-left-width: 0px;
				border-right-width: 0px;
				border-bottom-width: 0px;
				border-top-width: 0px;
				'>
                    <?
                    $generator = new Picqer\Barcode\BarcodeGeneratorPNG();
                    ?>
                    <img src="data:image/png;base64,<?php echo base64_encode($generator->getBarcode($data->value, $generator::TYPE_CODE_128, 1)); ?>"/>
                </div>
                <div class="block" style='
				z-index: 1;
				border-left-width: 0px;
				border-right-width: 0px;
				border-bottom-width: 0px;
				border-top-width: 0px;
				font-size: 8px;
				text-align: center;
				'>
                    <?php echo $data->value; ?>
                </div>
            <? endforeach; ?>
        </div>
    <?php endif; ?>
</div>
