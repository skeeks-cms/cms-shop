<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */
/* @var $this yii\web\View */
?>

<? foreach (\Yii::$app->shop->shopUser->shopOrder->shopOrderItems as $orderItem) : ?>
    <div class="row no-gutters sx-order-item" data-id="<?php echo $orderItem->id; ?>">
        <div class="col" style="max-width: 160px;">
            <a href="<?= $orderItem->url; ?>" data-pjax="0">
                <img src="<?= \skeeks\cms\helpers\Image::getSrc(
                    \Yii::$app->imaging->getImagingUrl($orderItem->image ? $orderItem->image->src : null, new \skeeks\cms\components\imaging\filters\Thumbnail([
                        'h' => 150,
                        'w' => 150,
                        'm' => \Imagine\Image\ManipulatorInterface::THUMBNAIL_INSET,
                    ]))
                ) ?>" class="sx-lazy" alt="<?= $orderItem->name; ?> title="<?= $orderItem->name; ?> width="150"/>
            </a>
        </div>
        <div class="col" style="padding: 0 10px;">
            <?
                \skeeks\cms\themes\unifyshop\assets\components\ShopUnifyProductCardAsset::register($this);
            ?>
            <div class="sx-product-card--title text-left" style="min-height: auto;">
                <a href="<?= $orderItem->url; ?>" class="product_name sx-product-card--title-a g-px-0 sx-main-text-color g-color-primary--hover g-text-underline--none--hover" data-pjax="0">
                    <?= $orderItem->name; ?>
                </a>
            </div>
            <? if ($orderItem->shopBasketProps) : ?>
                <div class="sx-order-item-properties">
                    <? foreach ($orderItem->shopBasketProps as $prop) : ?>
                        <p><?= $prop->name; ?>: <?= $prop->value; ?></p>
                    <? endforeach; ?>
                </div>
            <? endif; ?>
            <div class="d-flex flex-row sx-quantity-wrapper">
                <span class="d-flex flex-row sx-quantity-group">
                    <div class="my-auto sx-minus">-</div>
                    <div class="my-auto">
                        <input
                            value="<?= (float)$orderItem->quantity; ?>"
                            class="form-control sx-quantity-input sx-basket-quantity"
                            data-measure_ratio="<?= $orderItem->shopProduct ? $orderItem->shopProduct->measure_ratio : ""; ?>"
                            data-measure_ratio_min="<?= $orderItem->shopProduct ? $orderItem->shopProduct->measure_ratio_min : ""; ?>"
                            data-basket_id="<?= $orderItem->id; ?>"
                        />
                    </div>
                    <div class="my-auto sx-plus">+</div>
                </span>
                <div class="my-auto sx-measure-symbol">
                    <?= $orderItem->measure_name; ?>
                </div>

            </div>

            <? if ((float)$orderItem->moneyOriginal->amount > 0) : ?>
                <div class="sx-order-item-price">
                    <? if ($orderItem->moneyOriginal->getAmount() == $orderItem->money->getAmount()) : ?>
                        <?= $orderItem->moneyOriginal; ?>
                    <? else : ?>
                        <?= $orderItem->money; ?>
                        <span class="line-through nopadding-left sx-old-price"><?= $orderItem->moneyOriginal; ?></span>
                    <? endif; ?>

                    <? /*= $orderItem->moneyOriginal; */ ?> / <?= $orderItem->measure_name; ?>
                </div>
            <? endif; ?>

        </div>
        <div class="col my-auto" style="max-width: 19px;">
            <span class="sx-remove-order-item" title="Удалить позицию">
                <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" preserveAspectRatio="xMidYMid meet" viewBox="0 0 640 640" width="20" height="20"><defs><path d="M0 320C0 496.84 143.16 640 320 640C496.84 640 640 496.84 640 320C640 143.16 496.84 0 320 0C143.16 0 0 143.16 0 320ZM33.68 320C33.68 161.68 161.68 33.68 320 33.68C478.32 33.68 606.32 161.68 606.32 320C606.32 478.32 478.32 606.32 320 606.32C161.68 606.32 33.68 478.32 33.68 320ZM449.68 213.89C456.42 207.16 456.42 197.05 449.68 190.32C442.95 183.58 432.84 183.58 426.11 190.32C419.03 197.39 383.66 232.76 320 296.42C256.34 232.76 220.97 197.39 213.89 190.32C207.16 183.58 197.05 183.58 190.32 190.32C183.58 197.05 183.58 207.16 190.32 213.89C197.39 220.97 232.76 256.34 296.42 320C232.76 383.66 197.39 419.03 190.32 426.11C183.58 432.84 183.58 442.95 190.32 449.68C197.05 456.42 207.16 456.42 213.89 449.68C220.97 442.61 256.34 407.24 320 343.58C383.66 407.24 419.03 442.61 426.11 449.68C432.84 456.42 442.95 456.42 449.68 449.68C456.42 442.95 456.42 432.84 449.68 426.11C442.61 419.03 407.24 383.66 343.58 320C407.24 256.34 442.61 220.97 449.68 213.89Z" id="a1pP7byjY6"></path><path d="M0 320C0 496.84 143.16 640 320 640C496.84 640 640 496.84 640 320C640 143.16 496.84 0 320 0C143.16 0 0 143.16 0 320ZM33.68 320C33.68 161.68 161.68 33.68 320 33.68C478.32 33.68 606.32 161.68 606.32 320C606.32 478.32 478.32 606.32 320 606.32C161.68 606.32 33.68 478.32 33.68 320ZM449.68 213.89C456.42 207.16 456.42 197.05 449.68 190.32C442.95 183.58 432.84 183.58 426.11 190.32C419.03 197.39 383.66 232.76 320 296.42C256.34 232.76 220.97 197.39 213.89 190.32C207.16 183.58 197.05 183.58 190.32 190.32C183.58 197.05 183.58 207.16 190.32 213.89C197.39 220.97 232.76 256.34 296.42 320C232.76 383.66 197.39 419.03 190.32 426.11C183.58 432.84 183.58 442.95 190.32 449.68C197.05 456.42 207.16 456.42 213.89 449.68C220.97 442.61 256.34 407.24 320 343.58C383.66 407.24 419.03 442.61 426.11 449.68C432.84 456.42 442.95 456.42 449.68 449.68C456.42 442.95 456.42 432.84 449.68 426.11C442.61 419.03 407.24 383.66 343.58 320C407.24 256.34 442.61 220.97 449.68 213.89Z" id="adGPsZa8H"></path></defs><g><g><g><use xlink:href="#a1pP7byjY6" opacity="1" fill="#ffffff" fill-opacity="1"></use><g><use xlink:href="#a1pP7byjY6" opacity="1" fill-opacity="0" stroke="#000000" stroke-width="1" stroke-opacity="0"></use></g></g><g><use xlink:href="#adGPsZa8H" opacity="1" fill="#808080" fill-opacity="1"></use><g><use xlink:href="#adGPsZa8H" opacity="1" fill-opacity="0" stroke="#000000" stroke-width="1" stroke-opacity="0"></use></g></g></g></g></svg>
            </span>
        </div>
    </div>
<? endforeach; ?>
