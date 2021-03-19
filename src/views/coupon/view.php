<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */
/**
* @var $this yii\web\View
* @var $shopDiscountCoupon \skeeks\cms\shop\models\ShopDiscountCoupon
*/
$this->registerCss(<<<CSS
.sx-discount-coupon-wrapper {
    padding: 150px;
}
.sx-discount-coupon-wrapper {
    background: black;
}

.sx-discount-coupon {
    color:white;
}
.sx-qrcode {
    background: white;
}

CSS
);

?>
<div class="sx-discount-coupon-wrapper">
    <div class="container sx-discount-coupon-container">
        <div class="text-center sx-discount-coupon h1">
            <img src="<?php echo $qrCodeBase64; ?>" class="sx-qrcode" />
        </div>
        <div class="text-center sx-discount-coupon h1">
            <?php echo $shopDiscountCoupon->coupon; ?>
        </div>
    </div>
</div>

