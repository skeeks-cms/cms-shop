<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 06.03.2015
 *
 * @var \skeeks\cms\shop\models\ShopCmsContentElement $model
 *
 */
/* @var $this yii\web\View */
//$shopProduct = \skeeks\cms\shop\models\ShopProduct::getInstanceByContentElement($model);
$shopProduct = $model->shopProduct;

//Если этот товар привязан к главному
$infoModel = $model;
/*if ($shopProduct->main_pid) {
    if ($shopProduct->shopMainProduct->isOfferProduct) {
        $element = $shopProduct->shopMainProduct->cmsContentElement;
        $infoModel = $element->parentContentElement;
        $infoModel->name = $element->name;
    } else {
        $infoModel = $shopProduct->shopMainProduct->cmsContentElement;
    }
}*/

$count = $model->relatedPropertiesModel->getSmartAttribute('reviews2Count');
$rating = $model->relatedPropertiesModel->getSmartAttribute('reviews2Rating');
//$v3ProductElement = new \v3toys\parsing\models\V3toysProductContentElement($model->toArray());
$priceHelper = \Yii::$app->shop->shopUser->getProductPriceHelper($model);

?>
<article class="card-prod h-100 to-cart-fly-wrapper">
    <div class="card-prod--labels">


        <!--<div class="card-prod--label red">11</div>
                        <div class="clear"></div>-->
        <? /*
                    if ( $enum->id == 141) : */ ?><!--
                        <div class="card-prod--label red"><? /*=$enum->value;*/ ?></div>
                        <div class="clear"></div>
                    <? /* endif; */ ?>
                    <? /*
                    if ( $enum->id == 143) : */ ?>
                        <div class="card-prod--label blue"><? /*=$enum->value;*/ ?></div>
                        <div class="clear"></div>
                    --><? /* endif; */ ?>
    </div>

    <div class="card-prod--photo">
        <? if ($infoModel->image) : ?>
            <img class="to-cart-fly-img" src="<?= \Yii::$app->imaging->thumbnailUrlOnRequest($infoModel->image ? $infoModel->image->src : null,
                new \skeeks\cms\components\imaging\filters\Thumbnail([
                    'w' => \Yii::$app->unifyShopTheme->catalog_img_preview_width,
                    'h' => \Yii::$app->unifyShopTheme->catalog_img_preview_height,
                    'm' => \Yii::$app->unifyShopTheme->catalog_img_preview_crop,
                ]), $model->code
            ); ?>" title="<?= \yii\helpers\Html::encode($infoModel->name); ?>" alt="<?= \yii\helpers\Html::encode($infoModel->name); ?>"/>
        <? else : ?>
            <img class="img-fluid to-cart-fly-img" src="<?= \skeeks\cms\helpers\Image::getCapSrc(); ?>" alt="<?= $infoModel->name; ?>">
        <? endif; ?>

    </div>
    <div class="card-prod--inner g-px-10">
        <div class="card-prod--reviews">
            <div class="card-prod--category">
                <a href="#" class="btn btn-primary btn-xs sx-btn-dettach"><i class="far fa-trash-alt"></i> Отвязать</a>
            </div>
            <div class="card-prod--category">
                <? if ($model->cmsTree) : ?>
                    <a href="<?= $model->cmsTree->url; ?>" style="color: gray; font-size: 11px;"><?= $model->cmsTree->name; ?></a>
                <? endif; ?>
            </div>
            <div class="card-prod--title">
                <a href="<?= $model->url; ?>" title="<?= $model->name; ?>" data-pjax="0" class="sx-card-prod--title-a sx-main-text-color g-text-underline--none--hover"><?= $infoModel->name; ?></a>
            </div>
        </div>
</article>
