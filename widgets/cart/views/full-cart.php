<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 16.10.2016
 */
/* @var $this yii\web\View */
/* @var $widget \skeeks\cms\shop\widgets\cart\ShopCartWidget */
$widget     = $this->context;
?>
<?= \yii\helpers\Html::beginTag('div', $widget->options); ?>
    <!-- CART -->
    <form class="cartContent clearfix" method="post" action="#">
        <!-- cart content -->
        <div id="cartContent">
            <!-- cart header -->
            <div class="item head clearfix">
                <span class="cart_img"></span>
                <span class="product_name size-13 bold">Товар</span>
                <span class="remove_item size-13 bold"></span>
                <span class="total_price size-13 bold">Всего</span>
                <span class="qty size-13 bold">Количество</span>
            </div>
            <!-- /cart header -->
            <? foreach ($widget->shopFuser->shopBaskets as $shopBasket) : ?>
                <!-- cart item -->
                <div class="item">
                    <div class="cart_img pull-left width-100 padding-10 text-left">
                        <img src="<?= \skeeks\cms\helpers\Image::getSrc(
                                 \Yii::$app->imaging->getImagingUrl($shopBasket->image ? $shopBasket->image->src : null, new \skeeks\cms\components\imaging\filters\Thumbnail([
                                     'h' => 100,
                                     'w' => 100,
                                 ]))
                             ) ?>" class="sx-lazy"
                             alt="<?= $shopBasket->name; ?> title="<?= $shopBasket->name; ?> width="80"/>
                    </div>
                    <a href="<?= $shopBasket->url; ?>" class="product_name" data-pjax="0">
                        <span><?= $shopBasket->name; ?></span>
                        <? if ($shopBasket->shopBasketProps) : ?>
                            <? foreach ($shopBasket->shopBasketProps as $prop) : ?>
                                <small><?= $prop->name; ?>: <?= $prop->value; ?></small>
                            <? endforeach; ?>
                        <? endif; ?>
                        <!--<small>Color: Brown, Size: XL</small>-->
                    </a>
                    <a href="#" class="remove_item" data-toggle="tooltip" title=""
                       onclick="sx.Shop.removeBasket('<?= $shopBasket->id; ?>'); return false;"
                       data-original-title="Удалить позицию"><i class="fa fa-times"></i></a>

                    <div class="total_price">
                        <span><?= \Yii::$app->money->convertAndFormat($shopBasket->money->multiply($shopBasket->quantity)); ?></span>
                    </div>
                    <div class="qty">
                        <input type="number" value="<?= round($shopBasket->quantity); ?>" name="qty"
                               class="sx-basket-quantity" maxlength="3" max="999" min="1"
                               data-basket_id="<?= $shopBasket->id; ?>"/>
                        &times;
                        <? if ($shopBasket->moneyOriginal->getAmount() == $shopBasket->money->getAmount()) : ?>

                            <?= \Yii::$app->money->convertAndFormat($shopBasket->moneyOriginal); ?>
                        <? else : ?>
                            <span
                                class="line-through nopadding-left"><?= \Yii::$app->money->convertAndFormat($shopBasket->moneyOriginal); ?></span>
                            <?= \Yii::$app->money->convertAndFormat($shopBasket->money); ?>
                        <? endif; ?>
                    </div>
                    <div class="clearfix"></div>
                </div>
                <!-- /cart item -->
            <? endforeach; ?>
            <!-- update cart -->
            <button onclick="sx.Shop.clearCart(); return false;"
                    class="btn btn-default btn-sm margin-top-20 margin-right-10 pull-left"><i
                    class="glyphicon glyphicon-remove"></i> Очистить корзину
            </button>
            <!-- /update cart -->
            <div class="clearfix"></div>
        </div>
        <!-- /cart content -->
    </form>
    <!-- /CART -->
<?= \yii\helpers\Html::endTag('div'); ?>

<?
$this->registerCss(<<<CSS
/* SHOP CART */
.cartContent {
	padding:0;
}
.cartContent .item {
	position:relative;
	background: rgba(0,0,0,0.01);
}

.cartContent .item {
	margin-top:-1px;
	border:rgba(0,0,0,0.05) 1px solid;
}
.cartContent .sky-form.boxed {
	border: rgba(0,0,0,0.1) 1px solid;
}

.cartContent .item.head {
	border-bottom:0;
}
.cartContent .product_name {
	float:left;
	width:35%;
	padding:10px;
	text-decoration:none;
	min-height:60px;
}
	.cartContent .product_name:hover>span {
		text-decoration:underline;
	}
	.cartContent .product_name >small {
		display:block;
		font-size:12px;
		line-height:12px;
		color:rgba(0,0,0,0.5);
		font-family:'Open Sans',Arial,Helvetica,sans-serif;
	}
.cartContent .qty {
	float:right;
	width:160px;
	font-size:15px;
	padding:10px;
	text-align:center;
}
.cartContent .qty input {
	padding:3px; margin:0;
	border:#ccc 1px solid;
	width:50px; margin-right:3px;
	text-align:center;
}
.cartContent .total_price {
	float:right;
	width:150px;
	font-size:15px;
	padding:10px;
	line-height:30px;
	text-align:center;
	font-weight:bold;
}
.cartContent .remove_item {
	float:right;
	padding:5px 5px 5px 7px;
	width:30px; margin-right:8px;
}
.cartContent a.remove_item {
	background:rgba(0,0,0,0.1);
	border:rgba(0,0,0,0.1) 1px solid;
	padding-top:0;
	margin-top:10px;
	height:30px;
	line-height:26px;
	font-size:18px;
	text-decoration:none;
	color:rgba(0,0,0,0.5);
	border-radius:3px;

}
.cartContent .btn_update {
	margin-top:20px;
}

@media only screen and (max-width: 992px) {
	.cartContent .item.head {
		display:none;
	}
	.cartContent .product_name {
		font-size:11px;
		line-height:15px;
	}
	.cartContent .item .qty {
		float:left;
		text-align:left;
	}
	.cartContent .product_name {
		width:50%;
	}
}

CSS
);
?>