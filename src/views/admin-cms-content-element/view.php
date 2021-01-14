<?php
/* @var $this yii\web\View */
/* @var $model \skeeks\cms\shop\models\ShopCmsContentElement */
/* @var $controller \skeeks\cms\backend\controllers\BackendModelController */
/* @var $action \skeeks\cms\backend\actions\BackendModelCreateAction|\skeeks\cms\backend\actions\IHasActiveForm */
$controller = $this->context;
$action = $controller->action;
$model = $action->model;
\skeeks\cms\themes\unify\assets\components\UnifyThemeStickAsset::register($this);


$jsData = \yii\helpers\Json::encode([
    'backend' => \yii\helpers\Url::to(['update-attribute', 'pk' => $model->id, 'content_id' => $model->content_id]),
]);

$this->registerJs(<<<JS
$('[data-fancybox="images"]').fancybox({
    thumbs: {
    autoStart: true, // Display thumbnails on opening
    hideOnClose: true, // Hide thumbnail grid when closing animation starts
    parentEl: ".fancybox-container", // Container is injected into this element
    axis: "y", // Vertical (y) or horizontal (x) scrolling
    
      clickContent: function(current, event) {
        return current.type === "image" ? "zoom" : false;
      },
  },
});



(function(sx, $, _)
{
    sx.classes.FastEdit = sx.classes.Component.extend({
   
        _onDomReady: function()
        {
            var self = this;
            
            $('body').on('click', function (e) {
                //did not click a popover toggle or popover
                if ($(e.target).data('toggle') !== 'popover'
                    && $(e.target).closest('.popover').length === 0
                    && !$(e.target).hasClass("sx-fast-edit-popover")
                    && !$(e.target).closest(".sx-fast-edit-popover").length
                    ) { 
                    $('.sx-fast-edit-popover').popover('hide');
                }
            });
            
            $("body").on("click", ".sx-fast-edit-popover", function() {
                var jWrapper = $(this);
                $(".sx-fast-edit-popover").popover("hide");
                self._createPopover(jWrapper);
            });
            
            
            /*$("body").on("submit", ".sx-fast-edit-form", function() {
                event.preventDefault()

                var jForm = $(this);
                
                if (jForm.hasClass("sx-save-process")) {
                    return false;
                }
                jForm.addClass("sx-save-process");
                
                var AjaxQuery = sx.ajax.preparePostQuery(self.get('backend'));
                new sx.classes.AjaxHandlerStandartRespose(AjaxQuery);
                
                AjaxQuery.setData($(this).serializeArray());
                
                AjaxQuery.on("success", function() {
                    $.pjax.reload("#" + jWrapper.closest('[data-pjax-container]').attr("id"));
                    
                });
                
                AjaxQuery.on("error", function() {
                    jForm.trigger("complete-edit");
                });
                
                AjaxQuery.execute();
                
                return false;
            });*/
        },
        
        _createPopover(jWrapper) {
            
            if (!jWrapper.hasClass('is-rendered')) {
                jWrapper.popover({
                    "html": true,
                    //'container': "body",
                    'trigger': "click",
                    'boundary': 'window',
                    'title': jWrapper.data('title').length ? jWrapper.data('title') : "",
                    'content': $(jWrapper.data('form'))
                });
    
                jWrapper.on('show.bs.popover', function (e, data) {
                    jWrapper.addClass('is-rendered');
                });
            }
            

            jWrapper.popover('show');
        }
        
        
    });
})(sx, sx.$, sx._);

new sx.classes.FastEdit({$jsData});
JS
);

$this->registerCSS(<<<CSS
.sx-fast-edit-value {
    padding: 5px;
}

.sx-fast-edit-form-wrapper {
    display: none;
}

.sx-fast-edit {
    cursor: pointer;
    min-width: 40px;
    border-bottom: 1px dotted;
}
.js-slide img {
     max-height: 300px;
     margin: auto;
}
.sx-stick-navigation .js-slide {
    padding: 5px;
}
.sx-stick-navigation .slick-slide {
    opacity: .6;
}
.sx-stick-navigation .slick-slide:hover {
    opacity: 1;
}
.sx-stick-navigation .js-slide {
    cursor: pointer;
    border: none;
    margin: 0 0px;
    position: relative;
}

.sx-stick-navigation {
    margin-top: 10px;
    margin-bottom: 10px;
}

.sx-stick-navigation .slick-current:before {
    border: 1px solid #d2d2d2;
    content: '';
    position: absolute;
    z-index: 2;
    top: 0;
    right: 0;
    bottom: 0;
    left: 0;
    /* border: 1px solid rgba(21,146,165,0); */
    -moz-transition: all .3s ease;
    -o-transition: all .3s ease;
    -webkit-transition: all .3s ease;
    transition: all .3s ease;
}




/**
 * Современное оформление свойств
 */
.sx-properties-wrapper.sx-columns-1 ul.sx-properties {
    -moz-column-count: 1;
    column-count: 1;
}

.sx-properties-wrapper.sx-columns-2 ul.sx-properties {
    -moz-column-count: 2;
    column-count: 2;
}

.sx-properties-wrapper.sx-columns-3 ul.sx-properties {
    -moz-column-count: 3;
    column-count: 3;
}

ul.sx-properties {
    -moz-column-count: 2;
    column-count: 2;
    grid-column-gap: 40px;
    -moz-column-gap: 40px;
    column-gap: 40px;
    margin: 0px;
    padding: 0px;
}

ul.sx-properties li {
    display: flex;
    align-items: baseline;
    justify-content: space-between;
    margin-bottom: 8px;
    page-break-inside: avoid;
    -moz-column-break-inside: avoid;
    break-inside: avoid;
}

ul.sx-properties .sx-properties--value {
    text-align: right;
    max-width: 200px;
    line-height: 1.4;
}

ul.sx-properties .sx-properties--name {
    color: gray;
    flex: 1;
    display: flex;
    align-items: baseline;
    white-space: nowrap;
}

ul.sx-properties .sx-properties--name:after {
    content: "";
    flex-grow: 1;
    opacity: .25;
    margin: 0 6px 0 2px;
    border-bottom: 1px dotted gray;
}



.sx-table td, .sx-table th {
    border: 0;
    text-align: center;
    padding: 7px 10px;
    font-size: 13px;
    border-bottom: 1px solid #dee2e68f;
    background: white;
}


.sx-table th {
    background: #f9f9f9;
}

.sx-table-wrapper {
    border-radius: 5px;
    border-left: 1px solid #dee2e68f;
    border-right: 1px solid #dee2e68f;
    border-top: 1px solid #dee2e68f;
}
.sx-table-wrapper table {
    margin-bottom: 0;
}


.sx-info-block {
    background: #f9f9f9;
    margin-top: 10px;
    padding: 10px;
}
.sx-title {
    font-weight: bold;
    text-transform: uppercase;
    margin-bottom: 5px;
}

CSS
);
$noValue = "<span style='color: silver;'>—</span>";
?>

<?php $pjax = \skeeks\cms\widgets\Pjax::begin(); ?>
<div class="row no-gutters sx-bg-secondary">
    <div class="col-lg-4 col-sm-6 col-12">

        <div style="padding: 10px;">
            <?

            $images = [];

            if ($model->mainProductImage) {
                $images[] = $model->mainProductImage;
            }

            if ($productImages = $model->productImages) {
                $images = \yii\helpers\ArrayHelper::merge($images, $productImages);
            }


            ?>
            <? if ($images) : ?>
                <div id="carouselCus1" class="js-carousel sx-stick sx-stick-slider"
                     data-infinite="true"
                     data-fade="true"
                     data-arrows-classes="g-color-primary--hover sx-arrows sx-images-carousel-arrows sx-color-silver"
                     data-arrow-left-classes="hs-icon hs-icon-arrow-left sx-left"
                     data-arrow-right-classes="hs-icon hs-icon-arrow-right sx-right"
                     data-nav-for="#carouselCus2">

                    <? foreach ($images as $image) : ?>
                        <div class="js-slide">
                            <!--w-100-->
                            <a class="sx-fancybox-gallary" data-fancybox="images" href="<?= $image->src; ?>">
                                <img class="img-fluid" src="<?= \Yii::$app->imaging->thumbnailUrlOnRequest($image->src,
                                    new \skeeks\cms\components\imaging\filters\Thumbnail([
                                        'w' => 700,
                                        'h' => 500,
                                        'm' => \Imagine\Image\ImageInterface::THUMBNAIL_INSET,
                                    ]), $model->code
                                ); ?>" alt="<?= $model->name; ?>">
                            </a>
                        </div>
                    <? endforeach; ?>
                </div>

                <? if (count($images) > 1) : ?>
                    <div id="carouselCus2" class="js-carousel text-center g-mx-minus-5 sx-stick sx-stick-navigation"
                         data-infinite="true"
                         data-center-mode="true"
                         data-slides-show="8"
                         data-is-thumbs="true"
                         data-vertical="false"
                         data-focus-on-select="false"
                         data-nav-for="#carouselCus1"
                         data-arrows-classes="sx-arrows g-color-primary--hover sx-color-silver"
                         data-arrow-left-classes="hs-icon hs-icon-arrow-left sx-left"
                         data-arrow-right-classes="hs-icon hs-icon-arrow-right sx-right"
                    >
                        <? foreach ($images as $image) : ?>
                            <div class="js-slide">
                                <img class="img-fluid" src="<?= \Yii::$app->imaging->thumbnailUrlOnRequest($image->src,
                                    new \skeeks\cms\components\imaging\filters\Thumbnail([
                                        'w' => 75,
                                        'h' => 75,
                                        'm' => \Imagine\Image\ImageInterface::THUMBNAIL_OUTBOUND,
                                    ]), $model->code
                                ); ?>" alt="<?= $model->name; ?>">
                            </div>
                        <? endforeach; ?>
                    </div>
                <? endif; ?>
            <? else: ?>
                <div id="carouselCus1" class="js-carousel sx-stick sx-stick-slider"
                     data-infinite="true"
                     data-fade="true"
                     data-arrows-classes="u-arrow-v1 g-brd-around g-brd-gray-dark-v5 g-absolute-centered--y g-width-45 g-height-45 g-font-size-25 g-color-gray-dark-v5 g-color-primary--hover rounded-circle"
                     data-arrow-left-classes="hs-icon hs-icon-arrow-left sx-left"
                     data-arrow-right-classes="hs-icon hs-icon-arrow-right sx-right"
                     data-nav-for="#carouselCus2">
                    <div class="js-slide g-bg-cover">
                        <!--w-100-->
                        <img class="img-fluid" src="<?= \skeeks\cms\helpers\Image::getCapSrc(); ?>" alt="<?= $model->name; ?>">
                    </div>
                </div>
            <? endif; ?>

        </div>
    </div>

    <div class="col-lg-8 col-sm-6 col-12">
        <div style="padding: 10px;">
            <h4 style="line-height: 1.1;">
                <?php if (
                $model->cmsSite->shopSite->is_receiver //Если это товар собирает данные от поставщиков
                ) : ?>
                    <?php if ($model->main_cce_id) : ?>
                        <?
                        \skeeks\cms\backend\widgets\AjaxControllerActionsWidget::begin([
                            'controllerId' => "/shop/admin-cms-content-element",
                            'modelId'      => $model->main_cce_id,
                            'tag'          => 'span',
                            'options'      => [
                                'style' => 'color: green; text-align: left;',
                                'class' => '',
                            ],
                        ]);
                        ?>
                        <i class="fas fa-link" title="Товар привязан к инфокарточке. Берет описание, фото и характеристики из нее." data-toggle="tooltip" style="margin-left: 5px;"></i>
                        <?php \skeeks\cms\backend\widgets\AjaxControllerActionsWidget::end(); ?>
                    <?php else : ?>
                        <span style="color: red;">
                            <i class="fas fa-link" title="Товар не привязан к инфокарточке" data-toggle="tooltip" style="margin-left: 5px;"></i>
                        </span>
                    <?php endif; ?>


                    <?php echo $model->productName; ?>
                <? endif; ?>
            </h4>

            <div class="sx-properties-wrapper sx-columns-1" style="max-width: 350px; margin-top: 15px;">
                <ul class="sx-properties">


                    <li>
                        <span class="sx-properties--name">
                            Активность
                        </span>
                        <span class="sx-properties--value">
                            <span class="sx-fast-edit sx-fast-edit-popover"
                                  data-form="#is_active-form"
                                  data-title="Активность"
                            >
                                <?php echo $model->is_active ? '<span class="fa fa-check" data-toggle="tooltip" title="Товар показывается на сайте"  style="color: green;"></span>' : '<span data-toggle="tooltip" title="Товар не активен" class="fa fa-times" style="color: red;"></span>' ?>
                            </span>

                            <div class="sx-fast-edit-form-wrapper">
                                <?php $form = \skeeks\cms\base\widgets\ActiveFormAjaxSubmit::begin([
                                    'id'             => "is_active-form",
                                    'action'         => \yii\helpers\Url::to(['update-attribute', 'pk' => $model->id, 'content' => $model->content_id]),
                                    'options'        => [
                                        'class' => 'sx-fast-edit-form',
                                    ],
                                    'clientCallback' => new \yii\web\JsExpression(<<<JS
                                        function (ActiveFormAjaxSubmit) {
                                            ActiveFormAjaxSubmit.on('success', function(e, response) {
                                                $.pjax.reload("#{$pjax->id}");
                                                $(".sx-fast-edit").popover("hide");
                                            });
                                        }
JS
                                    ),
                                ]); ?>
                                <?php echo $form->field($model, 'active')->radioList(\Yii::$app->cms->booleanFormat())->label(false); ?>
                                    <div class="input-group-append">
                                        <button class="btn btn-primary" type="submit"><i class="fas fa-check"></i> Сохранить</button>
                                    </div>
                                <?php $form::end(); ?>
                            </div>

                        </span>
                    </li>


                    <li>
                <span class="sx-properties--name">
                    Тип товара <i class="far fa-question-circle" style="margin-left: 5px;" data-toggle="tooltip" title="От типа товара зависит то как он отображается на сайте"></i>
                </span>
                        <span class="sx-properties--value">
                    <?php echo \skeeks\cms\helpers\StringHelper::strtolower($model->shopProduct->productTypeAsText); ?>
                </span>
                    </li>
                    <li>
                <span class="sx-properties--name">
                    Код товара
                </span>
                        <span class="sx-properties--value">
                    <?php echo $model->id; ?>
                </span>
                    </li>

                    <li>
                        <span class="sx-properties--name">
                            Штрих-код
                        </span>
                        <span class="sx-properties--value">

                            <span class="sx-fast-edit sx-fast-edit-popover"
                                  data-form="#barcodes-form"
                                  data-title="Штрихкод"
                            >
                                <?php echo $model->shopProduct->shopProductBarcodes ? implode("<br>", \yii\helpers\ArrayHelper::map($model->shopProduct->shopProductBarcodes, 'value', 'value')) : "&nbsp;&nbsp;&nbsp;" ?>
                            </span>

                            <div class="sx-fast-edit-form-wrapper">
                                <?php $form = \skeeks\cms\base\widgets\ActiveFormAjaxSubmit::begin([
                                    'id'             => "barcodes-form",
                                    'action'         => \yii\helpers\Url::to(['update-attribute', 'pk' => $model->id, 'content' => $model->content_id]),
                                    'options'        => [
                                        'class' => 'sx-fast-edit-form',
                                    ],
                                    'clientCallback' => new \yii\web\JsExpression(<<<JS
                                        function (ActiveFormAjaxSubmit) {
                                            ActiveFormAjaxSubmit.on('success', function(e, response) {
                                                $.pjax.reload("#{$pjax->id}");
                                                $(".sx-fast-edit").popover("hide");
                                            });
                                        }
JS
                                    ),
                                ]); ?>
                                <?php echo $form->field($model->shopProduct, 'barcodes')->widget(
                                    \skeeks\cms\shop\widgets\admin\ProductBarcodesInputWidget::class
                                )->label(false); ?>
                                    <div class="input-group-append">
                                        <button class="btn btn-primary" type="submit"><i class="fas fa-check"></i> Сохранить</button>
                                    </div>
                                <?php $form::end(); ?>
                            </div>

                        </span>
                    </li>
                    <li>
                        <span class="sx-properties--name">
                            Категория
                        </span>
                        <span class="sx-properties--value" title="<?php echo $model->cmsTree ? $model->cmsTree->fullName : ""; ?>" data-toggle="tooltip">
                            
                        
                            <span class="sx-fast-edit sx-fast-edit-popover"
                                  data-form="#tree_id-form"
                                  data-title="Категория"
                            >
                                <?php echo $model->cmsTree ? $model->cmsTree->name : "&nbsp;&nbsp;&nbsp;"; ?>
                            </span>
                            
                            <div class="sx-fast-edit-form-wrapper">
                                <?php $form = \skeeks\cms\base\widgets\ActiveFormAjaxSubmit::begin([
                                    'id'             => "tree_id-form",
                                    'action'         => \yii\helpers\Url::to(['update-attribute', 'pk' => $model->id, 'content' => $model->content_id]),
                                    'options'        => [
                                        'class' => 'sx-fast-edit-form',
                                    ],
                                    'clientCallback' => new \yii\web\JsExpression(<<<JS
                                        function (ActiveFormAjaxSubmit) {
                                            ActiveFormAjaxSubmit.on('success', function(e, response) {
                                                $.pjax.reload("#{$pjax->id}");
                                                $(".sx-fast-edit").popover("hide");
                                            });
                                        }
JS
                                    ),
                                ]); ?>
                                <?php echo $form->field($model, 'tree_id')
                                    ->widget(
                                        \skeeks\cms\backend\widgets\SelectModelDialogTreeWidget::class, [
                                            'visibleInput' => false
                                        ]
                                    )
                                    ->label(false); ?>
                                    <div class="input-group-append">
                                        <button class="btn btn-primary" type="submit"><i class="fas fa-check"></i> Сохранить</button>
                                    </div>
                                <?php $form::end(); ?>
                            </div>
                        </span>

                    </li>

                    <li>
                        <span class="sx-properties--name">
                            Внешний код <i class="far fa-question-circle" style="margin-left: 5px;" data-toggle="tooltip" title="Чаще всего внешний код заполняется автоматически и используется для итеграции с внешними системами"></i>
                        </span>
                        <span class="sx-properties--value">
                            <span class="sx-fast-edit sx-fast-edit-popover"
                                  data-form="#external_id-form"
                                  data-title="Артикул"
                            >
                                <?php echo $model->external_id ? $model->external_id : "&nbsp;&nbsp;&nbsp;" ?>
                            </span>

                            <div class="sx-fast-edit-form-wrapper">
                                <?php $form = \skeeks\cms\base\widgets\ActiveFormAjaxSubmit::begin([
                                    'id'             => "external_id-form",
                                    'action'         => \yii\helpers\Url::to(['update-attribute', 'pk' => $model->id, 'content' => $model->content_id]),
                                    'options'        => [
                                        'class' => 'sx-fast-edit-form',
                                    ],
                                    'clientCallback' => new \yii\web\JsExpression(<<<JS
                                        function (ActiveFormAjaxSubmit) {
                                            ActiveFormAjaxSubmit.on('success', function(e, response) {
                                                $.pjax.reload("#{$pjax->id}");
                                                $(".sx-fast-edit").popover("hide");
                                            });
                                        }
JS
                                    ),
                                ]); ?>
                                <?php echo $form->field($model, 'external_id')->label(false); ?>
                                    <div class="input-group-append">
                                        <button class="btn btn-primary" type="submit"><i class="fas fa-check"></i> Сохранить</button>
                                    </div>
                                <?php $form::end(); ?>
                            </div>

                        </span>
                    </li>

                    <li>
                <span class="sx-properties--name">
                    Создан
                </span>
                        <span class="sx-properties--value" title="<?php echo $model->created_at ? \Yii::$app->formatter->asRelativeTime($model->created_at) : ""; ?>" data-toggle="tooltip">
                        <?php echo $model->created_at ? \Yii::$app->formatter->asDate($model->created_at) : ""; ?>
                </span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php if (
    (!$model->cmsSite->is_default && \skeeks\cms\models\CmsSite::find()->count() > 0) //Если у нас многосайтовость и этот товар - инфокарточка
    && !$model->shopProduct->isOffersProduct //И если это товар без предложений
) : ?>
    <div class="row no-gutters" style="margin-top: 10px;">
        <div class="col-12">
            <div style="margin-bottom: 5px;"><b style="text-transform: uppercase;">Цены</b></div>

            <div class="sx-table-wrapper table-responsive">
                <table class="table sx-table">
                    <tr>
                        <? foreach ($model->cmsSite->shopTypePrices as $shopTypePrice) : ?>
                            <th><?php echo $shopTypePrice->name; ?></th>
                        <? endforeach; ?>
                        <th>Себестоимость</th>
                        <th>Наценка</th>
                        <th>Маржинальность</th>
                    </tr>
                    <tr>
                        <? foreach ($model->cmsSite->shopTypePrices as $shopTypePrice) : ?>
                            <?php $price = $model->shopProduct->getPrice($shopTypePrice); ?>
                            <td>
                                <span class="sx-fast-edit sx-fast-edit-popover"
                                      data-form="#price-<?php echo $shopTypePrice->id; ?>-form"
                                      data-title="<?php echo \yii\helpers\Html::encode($shopTypePrice->name); ?>"
                                >
                                    <?php echo $price && (float)$price->money->amount > 0 ? $price->money : "&nbsp;&nbsp;&nbsp;" ?>
                                </span>

                                <div class="sx-fast-edit-form-wrapper">
                                    <?php $form = \skeeks\cms\base\widgets\ActiveFormAjaxSubmit::begin([
                                        'id'             => "price-{$shopTypePrice->id}-form",
                                        'action'         => \yii\helpers\Url::to(['update-attribute', 'pk' => $model->id, 'content' => $model->content_id]),
                                        'options'        => [
                                            'class' => 'sx-fast-edit-form',
                                        ],
                                        'clientCallback' => new \yii\web\JsExpression(<<<JS
                                            function (ActiveFormAjaxSubmit) {
                                                ActiveFormAjaxSubmit.on('success', function(e, response) {
                                                    $.pjax.reload("#{$pjax->id}");
                                                    $(".sx-fast-edit").popover("hide");
                                                });
                                            }
JS
                                        ),
                                    ]); ?>
                                    <input type="hidden" value="update-price" name="act" class="form-control"/>
                                    <input type="hidden" value="<?php echo $shopTypePrice->id; ?>" name="shop_type_price_id" class="form-control"/>

                                    <div class="input-group">
                                        <input type="text" value="<?php echo(($price && (float)$price->money->amount > 0) ? (float)$price->money->amount : ""); ?>" name="price_value" class="form-control"/>
                                        <?php if (count(\Yii::$app->money->activeCurrencies) > 1) : ?>
                                            <? echo \yii\helpers\Html::listBox("price_currency_code", $price ? $price->money->currency->code : "", \yii\helpers\ArrayHelper::map(
                                                \Yii::$app->money->activeCurrencies, 'code', 'code'
                                            ), ['size' => 1, 'class' => 'form-control']); ?>
                                        <?php endif; ?>
                                        <? /* echo \skeeks\cms\widgets\Select::widget([
                                            'name'          => "price_currency_code",
                                            'value'         => $price ? $price->money->currency->code : "",
                                            'allowDeselect' => false,
                                            'items'         => \yii\helpers\ArrayHelper::map(
                                                \Yii::$app->money->activeCurrencies, 'code', 'code'
                                            ),
                                        ]) */ ?>
                                        <div class="input-group-append">
                                            <button class="btn btn-primary" type="submit"><i class="fas fa-check"></i></button>
                                        </div>
                                    </div>

                                    <?php $form::end(); ?>
                                </div>

                            </td>
                        <? endforeach; ?>
                        <td><?php echo $noValue; ?></td>
                        <td><?php echo $noValue; ?></td>
                        <td><?php echo $noValue; ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="row no-gutters" style="margin-top: 10px;">
        <div class="col-12">
            <div style="margin-bottom: 5px;"><b style="text-transform: uppercase;">Склады</b></div>
            <div class="sx-table-wrapper table-responsive">
                <table class="table sx-table">
                    <tr>
                        <th style="text-align: left;">Магазин - склад</th>
                        <th><?php echo \Yii::$app->shop->baseTypePrice->name; ?></th>
                        <th>Остаток, <?php echo $model->shopProduct->measure->symbol; ?></th>
                        <th><?php echo \Yii::$app->shop->baseTypePrice->name; ?> (сумма)</th>
                    </tr>

                    <?php
                        $totalSummPrice = 0;
                        $totalSummQuantity = 0;
                    ?>
                
                    <?php if (\Yii::$app->skeeks->site->shopStores) : ?>
                    
                        
                    
                        <?php foreach (\Yii::$app->skeeks->site->shopStores as $shopStore) : ?>
                            <?php
                            $storeProduct = $model->shopProduct->getStoreProduct($shopStore);
                            $totalPrice = $noValue;
                            $money = $model->shopProduct->baseProductPrice->money;
                            if ($storeProduct) {
                                $totalPrice = $money->mul((float)$storeProduct->quantity);
                                $totalSummQuantity = $totalSummQuantity + $storeProduct->quantity;

                            }

                            ?>
                            <tr>
                                <td style="text-align: left;"><?php echo $shopStore->name; ?></td>
                                <td><?php echo $model->shopProduct->baseProductPrice->money; ?></td>
                                <td>
                                    <span class="sx-fast-edit sx-fast-edit-popover"
                                          data-form="#store-<?php echo $shopStore->id; ?>-form"
                                          data-title="<?php echo \yii\helpers\Html::encode($shopStore->name); ?>"
                                    >
                                        <?php echo $storeProduct ? (float) $storeProduct->quantity : "&nbsp;&nbsp;&nbsp;"; ?>
                                    </span>
    
                                    <div class="sx-fast-edit-form-wrapper">
                                        <?php $form = \skeeks\cms\base\widgets\ActiveFormAjaxSubmit::begin([
                                            'id'             => "store-{$shopStore->id}-form",
                                            'action'         => \yii\helpers\Url::to(['update-attribute', 'pk' => $model->id, 'content' => $model->content_id]),
                                            'options'        => [
                                                'class' => 'sx-fast-edit-form',
                                            ],
                                            'clientCallback' => new \yii\web\JsExpression(<<<JS
                                                function (ActiveFormAjaxSubmit) {
                                                    ActiveFormAjaxSubmit.on('success', function(e, response) {
                                                        $.pjax.reload("#{$pjax->id}");
                                                        $(".sx-fast-edit").popover("hide");
                                                    });
                                                }
JS
                                            ),
                                        ]); ?>
                                        <input type="hidden" value="update-store" name="act" class="form-control"/>
                                        <input type="hidden" value="<?php echo $shopStore->id; ?>" name="shop_store_id" class="form-control"/>
                                        <div class="input-group">
                                            <input type="text" value="<?php echo($storeProduct ? (float)$storeProduct->quantity : ""); ?>" name="store_quantity" class="form-control"/>
                                            <div class="input-group-append">
                                                <button class="btn btn-primary" type="submit"><i class="fas fa-check"></i></button>
                                            </div>
                                        </div>
    
                                        <?php $form::end(); ?>
                                    </div>
                                        
                                </td>
                                <td><?php echo $totalPrice; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td><?php echo $noValue; ?></td>
                            <td><?php echo $noValue; ?></td>
                            <td><?php echo $noValue; ?></td>
                            <td><?php echo $noValue; ?></td>
                        </tr>
                    <?php endif; ?>


                    <tr>
                        <td colspan="2" style="text-align: right;"><b>Итого</b></td>
                        <td><?php echo $totalSummQuantity ? $totalSummQuantity : $noValue; ?></td>
                        <td><?php echo $noValue; ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php if (
    ($model->cmsSite->shopSite->is_receiver) //Если это товар собирает данные от поставщиков
    && !$model->shopProduct->isOffersProduct //И если это товар без предложений
) : ?>

    <?php

    $q = \skeeks\cms\shop\models\ShopImportCmsSite::find()->select([
        'sender_cms_site_id',
    ])->andWhere(['cms_site_id' => $model->cmsSite->id]);

    $shopSupplierProducts = [];
    if ($model->mainCmsContentElement) {
        $shopSupplierProducts = [];

        $shopSupplierProducts = $model->mainCmsContentElement->getShopSupplierElements()
            ->andWhere(['cmsSite.id' => $q])
            ->all();
    };
    ?>
    <?php if ($shopSupplierProducts) : ?>


        <div class="row no-gutters" style="margin-top: 10px;">
            <div class="col-12">
                <div style="margin-bottom: 5px;"><b style="text-transform: uppercase;">Поставщики</b></div>

                <?
                /**
                 * @var $shopSupplierProduct \skeeks\cms\shop\models\ShopCmsContentElement
                 */
                foreach ($shopSupplierProducts as $shopSupplierProduct) : ?>

                    <?php
                    /**
                     * @var $shopImportCmsSite \skeeks\cms\shop\models\ShopImportCmsSite
                     */
                        $shopImportCmsSite = \skeeks\cms\shop\models\ShopImportCmsSite::find()
                            ->andWhere([
                                'sender_cms_site_id' => $shopSupplierProduct->cms_site_id
                            ])
                            ->andWhere(['cms_site_id' => $model->cmsSite->id])
                            ->one()
                    ?>
                    <?php /*if ($shopSupplierProduct->quantity > 0) : */ ?>

                    <div class="sx-table-wrapper table-responsive" style="margin-bottom: 10px;">
                        <table class="table sx-table">
                            <tr>
                                <th style="text-align: left; width: 300px;">Поставщик</th>
                                <th>Код поставщика</th>
                                <th>Закупочная цена</th>
                                <th>Количество, <?php echo $model->shopProduct->measure->symbol; ?></th>
                            </tr>
                            <tr>
                                <td style="text-align: left;">
                                    <?
                                    \skeeks\cms\backend\widgets\AjaxControllerActionsWidget::begin([
                                        'controllerId' => "/shop/admin-cms-content-element",
                                        'modelId'      => $shopSupplierProduct->id,
                                        'tag'          => 'span',
                                        'options'      => [
                                            'style' => 'color: gray; text-align: left;',
                                            'class' => '',
                                        ],
                                    ]);
                                    ?>
                                    <i class="fas fa-link" title="<?php echo \yii\helpers\Html::encode($shopSupplierProduct->asText); ?>" data-toggle="tooltip" style="margin-left: 5px;"></i>
                                    <?php \skeeks\cms\backend\widgets\AjaxControllerActionsWidget::end(); ?>
                                    <?php echo $shopSupplierProduct->cmsSite->name; ?>

                                </td>
                                <td><?php echo $shopSupplierProduct->external_id ? $shopSupplierProduct->external_id : $noValue; ?></td>
                                <td><?php
                                    if ($shopImportCmsSite->sender_purchasing_shop_type_price_id) {
                                        if ($price = $shopSupplierProduct->shopProduct->getPrice($shopImportCmsSite->sender_purchasing_shop_type_price_id)) {
                                            echo $price->money->mul($shopImportCmsSite->purchasing_extra_charge / 100);
                                        } else {
                                            echo $noValue;
                                        }
                                    } else {
                                        echo $noValue;
                                    }

                                    ?></td>
                                <td><?php echo $shopSupplierProduct->shopProduct->quantity; ?></td>
                            </tr>
                        </table>
                    </div>
                    <?php /*endif; */ ?>
                <? endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

<?php endif; ?>


<!--Если у нас многосайтовость и это портал-->
<?php if (
($model->cmsSite->is_default && \skeeks\cms\models\CmsSite::find()->count() > 0) //Если у нас многосайтовость и этот товар - инфокарточка
) : ?>

    <?php if (
        ($model->shopSupplierElements) //Если это товар собирает данные от поставщиков
        && !$model->shopProduct->isOffersProduct //И если это товар без предложений
    ) : ?>


        <div class="row no-gutters" style="margin-top: 10px;">
            <div class="col-12">
                <div style="margin-bottom: 5px;"><b style="text-transform: uppercase;">Поставщики</b></div>

                <?
                /**
                 * @var $shopSupplierProduct \skeeks\cms\shop\models\ShopCmsContentElement
                 */
                foreach ($model->shopSupplierElements as $shopSupplierProduct) : ?>
                    <?php /*if ($shopSupplierProduct->quantity > 0) : */ ?>
                    <div class="sx-table-wrapper table-responsive" style="margin-bottom: 10px;">
                        <table class="table sx-table">
                            <tr>
                                <th style="text-align: left; width: 300px;">Поставщик</th>
                                <th>Код поставщика</th>
                                <th>Количество, <?php echo $model->shopProduct->measure->symbol; ?></th>
                            </tr>
                            <tr>
                                <td style="text-align: left;">
                                    <?php echo $shopSupplierProduct->cmsSite->name; ?>
                                    <?
                                    \skeeks\cms\backend\widgets\AjaxControllerActionsWidget::begin([
                                        'controllerId' => "/shop/admin-cms-content-element",
                                        'modelId'      => $shopSupplierProduct->id,
                                        'tag'          => 'span',
                                        'options'      => [
                                            'style' => 'color: gray; text-align: left;',
                                            'class' => '',
                                        ],
                                    ]);
                                    ?>
                                    <i class="fas fa-link" title="<?php echo \yii\helpers\Html::encode($shopSupplierProduct->asText); ?>" data-toggle="tooltip" style="margin-left: 5px;"></i>
                                    <?php \skeeks\cms\backend\widgets\AjaxControllerActionsWidget::end(); ?>
                                </td>
                                <td><?php echo $shopSupplierProduct->external_id ? $shopSupplierProduct->external_id : $noValue; ?></td>
                                <td><?php echo $shopSupplierProduct->shopProduct->quantity; ?></td>
                            </tr>
                        </table>
                    </div>
                    <?php /*endif; */ ?>
                <? endforeach; ?>
            </div>
        </div>


    <?php endif; ?>


    <?php if (
    ($model->shopSellerElements) //Если это товар собирает данные от поставщиков
    ) : ?>


        <div class="row no-gutters" style="margin-top: 10px;">
            <div class="col-12">
                <div style="margin-bottom: 5px;"><b style="text-transform: uppercase;">Где продается</b></div>

                <?
                /**
                 * @var $shopSupplierProduct \skeeks\cms\shop\models\ShopCmsContentElement
                 */
                foreach ($model->shopSellerElements as $shopSupplierProduct) : ?>
                    <?php /*if ($shopSupplierProduct->quantity > 0) : */ ?>
                    <div class="sx-table-wrapper table-responsive" style="margin-bottom: 10px;">
                        <table class="table sx-table">
                            <tr>
                                <th style="text-align: left; width: 300px;">Сайт</th>
                                <th>Id на сайте</th>
                                <th>Количество, <?php echo $model->shopProduct->measure->symbol; ?></th>
                            </tr>
                            <tr>
                                <td style="text-align: left;">
                                    <?php echo $shopSupplierProduct->cmsSite->internalName; ?>
                                    <?
                                    \skeeks\cms\backend\widgets\AjaxControllerActionsWidget::begin([
                                        'controllerId' => "/shop/admin-cms-content-element",
                                        'modelId'      => $shopSupplierProduct->id,
                                        'tag'          => 'span',
                                        'options'      => [
                                            'style' => 'color: gray; text-align: left;',
                                            'class' => '',
                                        ],
                                    ]);
                                    ?>
                                    <i class="fas fa-link" title="<?php echo $shopSupplierProduct->asText; ?>" data-toggle="tooltip" style="margin-left: 5px;"></i>
                                    <?php \skeeks\cms\backend\widgets\AjaxControllerActionsWidget::end(); ?>
                                    <a class="" style="margin-left: 5px; border-bottom: 0px; color: gray;" href="<?php echo $shopSupplierProduct->url; ?>" title="Посмотреть на сайте (Откроется в новом окне)"
                                       target="_blank"><i class="fas fa-external-link-alt"></i></a>
                                </td>
                                <td><?php echo $shopSupplierProduct->id; ?></td>
                                <td><?php echo $shopSupplierProduct->shopProduct->quantity; ?></td>
                            </tr>
                        </table>
                    </div>
                    <?php /*endif; */ ?>
                <? endforeach; ?>
            </div>
        </div>


    <?php endif; ?>
<?php endif; ?>

<?php
$infoModel = $model;
if ($model->main_cce_id) {
    $infoModel = $model->mainCmsContentElement;
}
?>

<section class="sx-info-block">
    <div class="row no-gutters">
        <div class="col-12">
            <div class="sx-title">Характеристики</div>

            <?php
            $rp = $infoModel->relatedPropertiesModel;
            $rp->initAllProperties();
            $attributes = $rp->toArray();
            ?>

            <div class="sx-properties-wrapper sx-columns-3" style="margin-top: 5px;">
                <ul class="sx-properties">

                    <?php foreach ($attributes as $code => $value) : ?>
                        <? $property = $infoModel->relatedPropertiesModel->getRelatedProperty($code); ?>
                        <li>
                            <span class="sx-properties--name">
                                <?php echo $rp->getAttributeLabel($code); ?>
                                <? if ($property->hint) : ?>
                                    <i class="far fa-question-circle" title="<?= $property->hint; ?>" data-toggle="tooltip" style="margin-left: 5px;"></i>
                                <? endif; ?>
                            </span>
                            <span class="sx-properties--value">
                                <? if ($value) : ?>
                                    <?php echo $rp->getAttributeAsHtml($code); ?>
                                    <? if ($property->cms_measure_code) : ?>
                                        <?= $property->cmsMeasure->asShortText; ?>
                                    <? endif; ?>
                                <? endif; ?>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

        </div>
    </div>
</section>

<section class="sx-info-block">
    <div class="row no-gutters">
        <div class="col-12">
            <div class="sx-title">Описание</div>
            <?php echo $model->productDescriptionFull; ?>
        </div>
    </div>
</section>

<?php if ($model->shopProduct->supplier_external_jsondata) : ?>
    <section class="sx-info-block">
        <div class="row no-gutters">
            <div class="col-12">
                <div class="sx-title">Прочие данные <i class="far fa-question-circle" title="Неразобранные данные, которые сохранились по товару в момент импорта на сайт" data-toggle="tooltip"
                                                       style="margin-left: 5px;"></i></div>
                <?= \skeeks\cms\shop\widgets\admin\SubProductExternalDataWidget::widget(['shopProduct' => $model->shopProduct]); ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<?php $pjax::end(); ?>


