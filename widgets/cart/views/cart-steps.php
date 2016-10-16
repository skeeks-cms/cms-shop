<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 16.10.2016
 */
/* @var $this yii\web\View */
/* @var $widget \skeeks\cms\shop\widgets\cart\ShopCartStepsWidget */
$widget     = $this->context;
?>
<?= \yii\helpers\Html::beginTag('div', $widget->options); ?>
    <ul class="process-steps nav nav-justified">
        <li class="active">
            <a href="<?= \yii\helpers\Url::to(['/shop/cart']); ?>" data-pjax="0">1</a>
            <h5>Корзина</h5>
        </li>
        <li class="<?= in_array(\Yii::$app->controller->action->getUniqueId(), ['shop/cart/checkout', 'shop/order/finish']) ? "active" : ""; ?>">
            <a href="<?= \yii\helpers\Url::to(['/shop/cart/checkout']); ?>" data-pjax="0">2</a>
            <h5>Оформление</h5>
        </li>
        <li class="<?= \Yii::$app->controller->action->getUniqueId() == 'shop/order/finish' ? "active" : ""; ?>">
            <a href="#">3</a>
            <h5>Готовый заказ</h5>
        </li>
    </ul>
<?= \yii\helpers\Html::endTag('div'); ?>

<?
$this->registerCss(<<<CSS

/* Tab Process Steps */
ul.process-steps,
ul.process-steps li {
	border:0 !important;
	text-align: center;
}
ul.process-steps li a {
	width:50px;
	height:50px;
	font-size:30px;
	line-height:30px;
	text-align: center;
	display:inline-block;
	color:#111;
	border:#666 1px solid !important;
	background-color:#fff;

	-webkit-border-radius: 50% !important;
	   -moz-border-radius: 50% !important;
			border-radius: 50% !important;
}

ul.process-steps li.active a,
ul.process-steps li.active:hover>a {
	color:#fff !important;
	background-color:#333;
}

ul.process-steps li:after,
ul.process-steps li:before {
	content: '';
	position: absolute;
	top: 26px;
	left: 0;
	width: 50%;
	border-top: 1px dashed #DDD;
}
ul.process-steps li:first-child:before {
	display:none;
}
ul.process-steps li:last-child:after {
	display:none;
}
ul.process-steps li:after {
	left: auto;
	right: 0;
	margin: 0 -26px 0 0;
}
ul.process-steps li h1,
ul.process-steps li h2,
ul.process-steps li h3,
ul.process-steps li h4,
ul.process-steps li h5,
ul.process-steps li h6 {
	margin:20px 0 0 0;
}


ul.process-steps li>a>i {
	margin:0;
	padding:0;
	margin-left:-4px;
	margin-top:-1px;
	font-size:28px;
	line-height:28px;
}
ul.process-steps li>a>i.fa {
	font-size:30px;
	line-height:30px;
}

ul.process-steps.process-steps-square li a {
	-webkit-border-radius: 3px !important;
	   -moz-border-radius: 3px !important;
			border-radius: 3px !important;
}

@media only screen and (max-width: 768px) {
	ul.process-steps li:after,
	ul.process-steps li:before  {
		display:none;
	}

	ul.process-steps li h1,
	ul.process-steps li h2,
	ul.process-steps li h3,
	ul.process-steps li h4,
	ul.process-steps li h5,
	ul.process-steps li h6 {
		margin:10px 0 30px 0;
	}

}

@media only screen and (max-width: 482px) {
	ul.process-steps li>a {
		display:inline-block !important;
	}
	ul.process-steps li h1,
	ul.process-steps li h2,
	ul.process-steps li h3,
	ul.process-steps li h4,
	ul.process-steps li h5,
	ul.process-steps li h6 {
		margin:3px 0;
		display:block;
	}
	ul.process-steps li {
		padding:10px 0;
	}
}
CSS
);
?>