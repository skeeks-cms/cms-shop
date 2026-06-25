<?php
/* @var $this yii\web\View */
/* @var $model \skeeks\cms\shop\models\ShopCollection */

use skeeks\cms\base\widgets\ActiveFormAjaxSubmit;
use skeeks\cms\helpers\Image;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;

$controller = $this->context;
$action = $controller->action;
$model = $action->model;
\skeeks\cms\themes\unify\assets\components\UnifyThemeStickAsset::register($this);
$this->render("@skeeks/cms/shop/views/admin-shop-store-doc-move/view-css");

$apiColor = $model->is_sx_info_update ? "green" : "red";
$apiText = $model->is_sx_info_update
    ? "Синхронизация включена"
    : "Синхронизация отключена";
$apiMarketUrl = $model->sx_id && isset(\Yii::$app->skeeksSuppliersApi) ? \Yii::$app->skeeksSuppliersApi->getCollectionUrl($model->sx_id) : "#";
$mainImage = $model->image ? $model->image->src : Image::getCapSrc();
$productsCount = $model->getShopProducts()->count();
$galleryImages = [];
$galleryImageIds = [];
if ($model->image) {
    $galleryImages[] = $model->image;
    $galleryImageIds[$model->image->id] = true;
}
foreach ((array)$model->images as $image) {
    if ($image && !isset($galleryImageIds[$image->id])) {
        $galleryImages[] = $image;
        $galleryImageIds[$image->id] = true;
    }
}
$noValue = "<span style='color: silver;'>&mdash;</span>";

$this->registerJs(<<<JS
$('[data-fancybox="collection-images"]').fancybox({
    thumbs: {
        autoStart: true,
        hideOnClose: true,
        parentEl: ".fancybox-container",
        axis: "y",
        clickContent: function(current, event) {
            return current.type === "image" ? "zoom" : false;
        }
    }
});

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
    max-height: 360px;
    max-width: 100%;
    object-fit: contain;
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
                <?php if ($galleryImages) : ?>
                    <div id="collectionCarouselMain" class="js-carousel sx-stick sx-stick-slider"
                         data-infinite="true"
                         data-fade="true"
                         data-arrows-classes="g-color-primary--hover sx-arrows sx-images-carousel-arrows sx-color-silver"
                         data-arrow-left-classes="hs-icon hs-icon-arrow-left sx-left"
                         data-arrow-right-classes="hs-icon hs-icon-arrow-right sx-right"
                         data-nav-for="#collectionCarouselNav">
                        <?php foreach ($galleryImages as $image) : ?>
                            <?
                            $preview = \Yii::$app->imaging->getPreview($image,
                                new \skeeks\cms\components\imaging\filters\Thumbnail([
                                    'w' => 700,
                                    'h' => 700,
                                    'm' => \Imagine\Image\ManipulatorInterface::THUMBNAIL_INSET,
                                    'sx_preview' => \skeeks\cms\components\storage\SkeeksSuppliersCluster::IMAGE_PREVIEW_BIG,
                                ]), $model->code
                            );
                            ?>
                            <div class="js-slide">
                                <a class="sx-fancybox-gallary" data-fancybox="collection-images" href="<?= $image->src; ?>">
                                    <img class="img-fluid sx-shop-card-image" src="<?= $preview->src; ?>" alt="<?= Html::encode($model->name); ?>">
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <?php if (count($galleryImages) > 1) : ?>
                        <div id="collectionCarouselNav" class="js-carousel text-center g-mx-minus-5 sx-stick sx-stick-navigation"
                             data-infinite="true"
                             data-center-mode="true"
                             data-slides-show="8"
                             data-is-thumbs="true"
                             data-vertical="false"
                             data-focus-on-select="false"
                             data-nav-for="#collectionCarouselMain"
                             data-arrows-classes="sx-arrows g-color-primary--hover sx-color-silver"
                             data-arrow-left-classes="hs-icon hs-icon-arrow-left sx-left"
                             data-arrow-right-classes="hs-icon hs-icon-arrow-right sx-right"
                        >
                            <?php foreach ($galleryImages as $image) : ?>
                                <?
                                $preview = \Yii::$app->imaging->getPreview($image,
                                    new \skeeks\cms\components\imaging\filters\Thumbnail([
                                        'w' => 100,
                                        'h' => 100,
                                        'm' => \Imagine\Image\ManipulatorInterface::THUMBNAIL_OUTBOUND,
                                        'sx_preview' => \skeeks\cms\components\storage\SkeeksSuppliersCluster::IMAGE_PREVIEW_MICRO,
                                    ]), $model->code
                                );
                                ?>
                                <div class="js-slide">
                                    <img class="img-fluid" src="<?= $preview->src; ?>" alt="<?= Html::encode($model->name); ?>">
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                <?php else : ?>
                    <img class="sx-shop-card-image" src="<?= Html::encode(Image::getCapSrc()); ?>" alt="<?= Html::encode($model->name); ?>">
                <?php endif; ?>
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
                                  data-form="#collection-is_sx_info_update-form"
                                  data-title="Получать информацию из сервиса SkeekS Товары?"
                            >
                                <span style="color: <?= $apiColor; ?>;"><?= $apiText; ?></span>
                            </span>
                            <div class="sx-fast-edit-form-wrapper">
                                <?php $form = ActiveFormAjaxSubmit::begin([
                                    'id'             => "collection-is_sx_info_update-form",
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
                    <span class="sx-properties--name">&#1053;&#1072;&#1079;&#1074;&#1072;&#1085;&#1080;&#1077; &#1082;&#1086;&#1083;&#1083;&#1077;&#1082;&#1094;&#1080;&#1080;</span>
                    <span class="sx-properties--value"><?= Html::encode($model->name); ?></span>
                </li>
                <li>
                    <span class="sx-properties--name">&#1041;&#1088;&#1077;&#1085;&#1076;</span>
                    <span class="sx-properties--value"><?= $model->brand ? Html::encode($model->brand->name) : $noValue; ?></span>
                </li>
                <li>
                    <span class="sx-properties--name">Страна</span>
                    <span class="sx-properties--value"><?= $model->brand && $model->brand->country ? Html::encode($model->brand->country->name) : $noValue; ?></span>
                </li>
                <li>
                    <span class="sx-properties--name">Количество товаров</span>
                    <span class="sx-properties--value"><?= (int)$productsCount; ?></span>
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
