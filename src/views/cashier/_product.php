<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */
/**
 * @var $this yii\web\View
 * @var $model \skeeks\cms\shop\models\ShopCmsContentElement
 * @var $storeProduct \skeeks\cms\shop\models\ShopStoreProduct
 */
$storeProduct = $model->shopProduct->getShopStoreProducts([\Yii::$app->shop->backendShopStore])->one();
$quantity = 0;
if ($storeProduct) {
    $quantity = $storeProduct->quantity;
}
?>
<div class="catalog-card catalog-card-grid item service"
     draggable="false"
     data-id="<?php echo $model->id; ?>"
>
    <div>
        <div class="stock"><span><?php echo $quantity; ?> <?php echo $model->shopProduct->measure->asShortText; ?></span></div>
        <div class="img">
            <div class="image-wrapper" style="height: 130px; width: 100%; border-radius: 4px;">
                <?php if ($model->mainProductImage) : ?>
                    <img class="image" src="data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw=="
                         style="background: url('<?= \Yii::$app->imaging->thumbnailUrlOnRequest($model->mainProductImage->src,
                             new \skeeks\cms\components\imaging\filters\Thumbnail([
                                 'w' => 230,
                                 'h' => 150,
                                 'm' => \Imagine\Image\ManipulatorInterface::THUMBNAIL_INSET,
                             ]), $model->code
                         ); ?>'); background-repeat: no-repeat; background-position: center; background-size: cover;"
                    >
                    <i class="animate fa icon fa-image fa-fw" style="font-size: 78px;"></i>
                <?php else : ?>
                    <img class="image" src="data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==">
                    <i class="animate fa icon fa-image fa-fw" style="font-size: 78px;"></i>
                <?php endif; ?>


            </div>
        </div>
        <div class="text">
            <div class="title"><span><?php echo $model->productName; ?></span></div>
            <div class="sku"><i class="fa icon fa-asterisk fa-fw"></i><span><?php echo $model->id; ?></span></div>
            <?php if ($barcodes = $model->shopProduct->shopProductBarcodes) : ?>
                <div class="barcode"><i class="fa icon fa-barcode fa-fw"></i><span><?php echo implode(",", \yii\helpers\ArrayHelper::map($barcodes, 'id', 'value')); ?></span></div>
            <?php endif; ?>

        </div>
        <div class="price"><?php echo $model->shopProduct->baseProductPrice->money; ?></div>
        <div class="label">0</div>
    </div>
</div>
