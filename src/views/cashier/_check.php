<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */
/**
 * @var $this yii\web\View
 * @var $model \skeeks\cms\shop\models\ShopCheck
 * @var $storeProduct \skeeks\cms\shop\models\ShopStoreProduct
 */
?>
<div class="sx-check-block">
    <div>
       ЗН ККТ: <?php echo $model->fiscal_kkt_number; ?>
    </div>
    <div>
       <?php $qrCodeBase64 = (new \chillerlan\QRCode\QRCode())->render($model->qr); ?>
        <div class="text-center">
            <img src="<?php echo $qrCodeBase64; ?>" class="sx-qrcode">
        </div>
    </div>
</div>
