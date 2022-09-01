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
 * @var $shopStoreDocMove \skeeks\cms\shop\models\ShopStoreDocMove
 */
$storeProduct = $model->shopProduct->getShopStoreProducts([$shopStoreDocMove->shopStore])->one();
$quantity = 0;
if ($storeProduct) {
    $quantity = $storeProduct->quantity;
}
?>
<div class="catalog-card catalog-card-grid item service"
     draggable="false"
     data-id="<?php echo $model->id; ?>"
>
    <div class="d-flex">
    <div>
        <div class="text">
            <div class="title d-flex">
                <?php if($model->mainProductImage) : ?>
                    <img class="my-auto" src="<?php echo \Yii::$app->imaging->thumbnailUrlOnRequest($model->mainProductImage->src, new \skeeks\cms\components\imaging\filters\Thumbnail(), $model->code); ?>" style="max-width: 30px;     height: 100%;
    width: 100%; margin-right: 5px;"/>
                <?php endif; ?>
                
                <span><?php echo $model->productName; ?></span>
            </div>
            <div class="sku"><i class="fa icon fa-asterisk fa-fw"></i><span><?php echo $model->id; ?></span></div>
            <?php if ($barcodes = $model->shopProduct->shopProductBarcodes) : ?>
                <div class="barcode"><i class="fa icon fa-barcode fa-fw"></i><span><?php echo implode(",", \yii\helpers\ArrayHelper::map($barcodes, 'id', 'value')); ?></span></div>
            <?php endif; ?>

        </div>
        <div class="price"><?php echo $model->shopProduct->baseProductPrice->money; ?></div>
    </div>
    <div class="stock"><span><?php echo $quantity; ?>&nbsp;<?php echo $model->shopProduct->measure->asShortText; ?></span></div>
    </div>
</div>
