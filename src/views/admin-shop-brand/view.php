<?php
/* @var $this yii\web\View */
/* @var $model \skeeks\cms\shop\models\ShopBrand */

use skeeks\cms\base\widgets\ActiveFormAjaxSubmit;
use skeeks\cms\helpers\Image;
use skeeks\cms\shop\models\ShopCollection;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;

$controller = $this->context;
$action = $controller->action;
$model = $action->model;
$this->render("@skeeks/cms/shop/views/admin-shop-store-doc-move/view-css");

$apiColor = $model->is_sx_info_update ? "green" : "red";
$apiText = $model->is_sx_info_update
    ? "Синхронизация включена"
    : "Синхронизация отключена";
$apiMarketUrl = $model->sx_id && isset(\Yii::$app->skeeksSuppliersApi) ? \Yii::$app->skeeksSuppliersApi->getBrandUrl($model->sx_id) : "#";
$mainImage = $model->logo ? $model->logo->src : Image::getCapSrc();
$noValue = "<span style='color: silver;'>&mdash;</span>";
$productsCount = $model->getProducts()->count();
$collectionsCount = ShopCollection::find()->where(['shop_brand_id' => $model->id])->count();

$this->registerJs(<<<JS
(function($) {
    $("body").off("click.sx-info-fast-edit-hide").on("click.sx-info-fast-edit-hide", function(e) {
        if ($(e.target).data("toggle") !== "popover"
            && $(e.target).closest(".popover").length === 0
            && !$(e.target).hasClass("sx-fast-edit-popover")
            && !$(e.target).closest(".sx-fast-edit-popover").length
        ) {
            $(".sx-fast-edit-popover").popover("hide");
        }
    });

    $("body").off("click.sx-info-fast-edit", ".sx-fast-edit-popover").on("click.sx-info-fast-edit", ".sx-fast-edit-popover", function() {
        var jWrapper = $(this);
        $(".sx-fast-edit-popover").popover("hide");

        if (!jWrapper.hasClass("is-rendered")) {
            jWrapper.popover({
                html: true,
                trigger: "click",
                boundary: "window",
                title: jWrapper.data("title") || "",
                content: $(jWrapper.data("form"))
            });

            jWrapper.on("show.bs.popover", function() {
                jWrapper.addClass("is-rendered");
            });
        }

        jWrapper.popover("show");
    });
})(sx.$ || jQuery);
JS
);

$this->registerCss(<<<CSS
.sx-fast-edit-form-wrapper {
    display: none;
}
.sx-fast-edit {
    cursor: pointer;
    min-width: 40px;
    border-bottom: 1px dotted;
}
.sx-shop-card-image {
    max-height: 300px;
    max-width: 100%;
    object-fit: contain;
}
.sx-shop-gallery {
    display: flex;
    gap: 12px;
    margin-top: 14px;
    flex-wrap: wrap;
}
.sx-shop-gallery img {
    width: 110px;
    height: 76px;
    object-fit: contain;
    border-radius: 5px;
    background: #fff;
    border: 1px solid #ededed;
    padding: 4px;
}
.sx-shop-description-block {
    background: #fff;
    border-radius: 5px;
    margin-top: 18px;
    padding: 18px;
}
.sx-shop-description-title {
    font-weight: 600;
    margin-bottom: 12px;
}
.sx-shop-main-card {
    background: #fff;
    border-radius: 5px;
    padding: 18px;
}
CSS
);
?>

<div class="sx-shop-main-card">
    <div class="row">
        <div class="col-lg-5 col-md-6 col-12">
            <div style="padding: 10px; text-align: center;">
                <img class="sx-shop-card-image" src="<?= Html::encode($mainImage); ?>" alt="<?= Html::encode($model->name); ?>">
            </div>
        </div>

        <div class="col-lg-5 col-md-6 col-12">
            <div class="sx-properties-wrapper sx-columns-1" style="padding: 10px;">
                <ul class="sx-properties">
                <?php if ($model->sx_id) : ?>
                    <li>
                        <span class="sx-properties--name">SkeekS Товары <i class="far fa-question-circle" style="margin-left: 5px;" data-toggle="tooltip" title="Если выключить, скрипт не будут брать информацию о товаре из сервиса SkeekS Товары и ваши изменения по товару не будут перезатираться. Цены и остатки обновляются всегда."></i></span>
                        <span class="sx-properties--value">
                            <?= Html::a("<i class='fas fa-link' style='color: {$apiColor};'></i>", $apiMarketUrl, [
                                'target' => '_blank',
                                'data-pjax' => '0',
                                'data-toggle' => 'tooltip',
                                'title' => "SkeekS ID: ".(int)$model->sx_id,
                            ]); ?>
                            <span class="sx-fast-edit sx-fast-edit-popover"
                                  data-form="#brand-is_sx_info_update-form"
                                  data-title="Получать информацию из сервиса SkeekS Товары?"
                            >
                                <span style="color: <?= $apiColor; ?>;"><?= $apiText; ?></span>
                            </span>
                            <div class="sx-fast-edit-form-wrapper">
                                <?php $form = ActiveFormAjaxSubmit::begin([
                                    'id'             => "brand-is_sx_info_update-form",
                                    'action'         => Url::to(['update-attribute', 'pk' => $model->id]),
                                    'options'        => [
                                        'class' => 'sx-fast-edit-form',
                                    ],
                                    'clientCallback' => new JsExpression(<<<JS
                                        function (ActiveFormAjaxSubmit) {
                                            ActiveFormAjaxSubmit.on('success', function(e, response) {
                                                window.location.reload();
                                            });
                                        }
JS
                                    ),
                                ]); ?>
                                    <?= Html::radioList(
                                        Html::getInputName($model, 'is_sx_info_update'),
                                        (int)$model->is_sx_info_update,
                                        [
                                            1 => 'Да',
                                            0 => 'Нет',
                                        ]
                                    ); ?>
                                    <div class="input-group-append">
                                        <button class="btn btn-primary" type="submit"><i class="fas fa-check"></i> &#1057;&#1086;&#1093;&#1088;&#1072;&#1085;&#1080;&#1090;&#1100;</button>
                                    </div>
                                <?php $form::end(); ?>
                            </div>
                        </span>
                    </li>
                <?php endif; ?>
                <li>
                    <span class="sx-properties--name">&#1054;&#1090;&#1086;&#1073;&#1088;&#1072;&#1078;&#1072;&#1077;&#1090;&#1089;&#1103; &#1085;&#1072; &#1089;&#1072;&#1081;&#1090;&#1077;?</span>
                    <span class="sx-properties--value"><?= $model->is_active ? "<span style='color: green;'>&#10003;</span>" : "<span style='color: red;'>&times;</span>"; ?></span>
                </li>
                <li>
                    <span class="sx-properties--name">&#1053;&#1072;&#1079;&#1074;&#1072;&#1085;&#1080;&#1077; &#1073;&#1088;&#1077;&#1085;&#1076;&#1072;</span>
                    <span class="sx-properties--value"><?= Html::encode($model->name); ?></span>
                </li>
                <li>
                    <span class="sx-properties--name">&#1057;&#1090;&#1088;&#1072;&#1085;&#1072; &#1073;&#1088;&#1077;&#1085;&#1076;&#1072;</span>
                    <span class="sx-properties--value"><?= $model->country ? Html::encode($model->country->name) : $noValue; ?></span>
                </li>
                <li>
                    <span class="sx-properties--name">&#1057;&#1072;&#1081;&#1090;</span>
                    <span class="sx-properties--value"><?= $model->website_url ? Html::a(Html::encode($model->website_url), $model->website_url, ['target' => '_blank', 'data-pjax' => '0']) : $noValue; ?></span>
                </li>
                <li>
                    <span class="sx-properties--name">Количество товаров</span>
                    <span class="sx-properties--value"><?= (int)$productsCount; ?></span>
                </li>
                <li>
                    <span class="sx-properties--name">Количество коллекций</span>
                    <span class="sx-properties--value"><?= (int)$collectionsCount; ?></span>
                </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="sx-shop-description-block">
    <div class="sx-shop-description-title">&#1050;&#1088;&#1072;&#1090;&#1082;&#1086;&#1077; &#1086;&#1087;&#1080;&#1089;&#1072;&#1085;&#1080;&#1077;</div>
    <?= $model->description_short ? $model->description_short : $noValue; ?>
</div>

<div class="sx-shop-description-block">
    <div class="sx-shop-description-title">&#1055;&#1086;&#1083;&#1085;&#1086;&#1077; &#1086;&#1087;&#1080;&#1089;&#1072;&#1085;&#1080;&#1077;</div>
    <?= $model->description_full ? $model->description_full : $noValue; ?>
</div>
