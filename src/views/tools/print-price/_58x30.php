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


<div class="label" style="width: 58mm; height: 30mm; max-width: 58mm; max-height: 30mm;">


    <?php if ($isPrintPrice) : ?>
    <div class="block" style='
				padding-top: 2mm;
				padding-bootom: 1mm;
				border-left-width: 0px;
				border-right-width: 0px;
				border-bottom-width: 0px;
				border-top-width: 0px;
				'>

        <div class="text" style='
							font-size: 14px;
							font-weight: bold;
							font-style: normal;
							text-decoration: ;
							text-align: center'>
            <?php echo $element->shopProduct->baseProductPrice->money; ?>
        </div>


    </div>
    <?php endif; ?>

    <div class="block" style='
				height: 8mm;
				border-left-width: 0px;
				border-right-width: 0px;
				border-bottom-width: 0px;
				border-top-width: 0px;
				padding: 0 5px;
				'>

        <div class="text" style='
							font-size: 13px;
							font-weight: normal;
							font-style: normal;
							text-decoration: ;
							text-align: center'>
            <?php echo $element->productName; ?>
        </div>


    </div>


    <?php if ($isPrintQrcode) : ?>
        <? $qrCodeBase64 = (new \chillerlan\QRCode\QRCode())->render($element->absoluteUrl); ?>
        <div class="text-center" style="height: 16mm; padding: 0 1mm; padding-top:0mm;">
            <div class="block" style='
				height: 16mm;
				border-left-width: 0px;
				border-right-width: 0px;
				border-bottom-width: 0px;
				border-top-width: 0px;
				text-align: center;
				'>
                    <?
                    $generator = new Picqer\Barcode\BarcodeGeneratorPNG();
                    ?>
                    <img style="width: 16mm;" src="<?php echo $qrCodeBase64; ?>"/>
                </div>
        </div>
    <? endif; ?>

    <?php if ($isPrintBarcode && $element->shopProduct->shopProductBarcodes) : ?>
        <div class="text-center" style="height: 11mm; padding: 0 5px; padding-top:1mm;">
            <? foreach ($element->shopProduct->shopProductBarcodes as $data) : ?>
                <div class="block" style='
				height: 30px;
				border-left-width: 0px;
				border-right-width: 0px;
				border-bottom-width: 0px;
				border-top-width: 0px;
				text-align: center;
				'>


                    <?
                    $generator = new Picqer\Barcode\BarcodeGeneratorPNG();
                    ?>
                    <img src="data:image/png;base64,<?php echo base64_encode($generator->getBarcode($data->value, $generator::TYPE_CODE_128, 1, 30)); ?>"/>


                </div>
                <div class="block" style='
				z-index: 1;
				border-left-width: 0px;
				border-right-width: 0px;
				border-bottom-width: 0px;
				border-top-width: 0px;
				font-size: 10px;
				text-align: center;
				margin-top: 3px;
				'>


                    <?php echo $data->value; ?>


                </div>

            <? endforeach; ?>
        </div>
    <?php endif; ?>


</div>
