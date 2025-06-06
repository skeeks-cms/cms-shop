<?php
/* @var $this yii\web\View */
/* @var $model \skeeks\cms\shop\models\ShopCmsContentElement */
/* @var $joinModel \skeeks\cms\shop\models\ShopCmsContentElement */
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
                /*console.log($(e.target));
                                    return false;

                if ($(e.target).closest(".select2-dropdown").length) {
                }*/
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

<?php echo \skeeks\cms\widgets\Alert::widget(); ?>

<!--Если это сайт поставщика или получателя товаров-->
<?php if ($model->cmsSite->shopSite->is_receiver) : ?>
    <!-- Если товар не привязан к инфокрточке -->
    <?php if (!$model->main_cce_id) : ?>
        <?php
        $joinModels = [];
        /*Поиск по штрихкоду*/
        $barcodes = $model->shopProduct->getBarcodes();
        if ($barcodes) {
            $barcodeValues = array_keys($barcodes);

            $q = \skeeks\cms\shop\models\ShopCmsContentElement::find()
                ->joinWith("shopProduct as shopProduct", true, "INNER JOIN")
                ->joinWith("shopProduct.shopProductBarcodes as shopProductBarcodes", true, "INNER JOIN")
                ->joinWith("cmsSite as cmsSite")
                ->andWhere(['cmsSite.is_default' => 1])
                ->andWhere(['shopProductBarcodes.value' => $barcodeValues]);

            $joinModels = $q->all();
        }
        ?>

        <?php if ($joinModels) : ?>

            <section class="sx-info-block" style="background: #ceffd0;">
                <div class="row no-gutters">
                    <div class="col-12">
                        <div class="sx-title">Возможно этот товар уже оформлен</div>
                    </div>
                    <div class="col-12">
                        <?php foreach ($joinModels as $joinModel) : ?>

                        <?php endforeach; ?>
                    </div>
                </div>
            </section>


        <?php endif; ?>

    <?php endif; ?>
<?php endif; ?>


<div class="row no-gutters sx-block">
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
                            <!--w-100-->
                            <a class="sx-fancybox-gallary" data-fancybox="images" href="<?= $image->src; ?>">
                                <img class="img-fluid" src="<?= $preview->src; ?>" alt="<?= $model->name; ?>">
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
                                <img class="img-fluid" src="<?= $preview->src; ?>" alt="<?= $model->name; ?>">
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

    <div class="col-lg-4 col-sm-6 col-12">
        <div style="padding: 10px;">
            <h4 style="line-height: 1.1;">
                <?php echo $model->productName; ?>
            </h4>

            <div class="sx-properties-wrapper sx-columns-1" style="max-width: 350px; margin-top: 15px;">
                <ul class="sx-properties">


                    <li>
                        <span class="sx-properties--name">
                            Показ на сайте <i class="far fa-question-circle" style="margin-left: 5px;" data-toggle="tooltip" title="Если товар активен, значит он показывается на сайте и доступен для всех!"></i>
                        </span>
                        <span class="sx-properties--value">
                            <span class="sx-fast-edit sx-fast-edit-popover"
                                  data-form="#is_active-form"
                                  data-title="Активность"
                            >
                                <?php echo $model->is_active ? '<span data-toggle="tooltip" title="Товар показывается на сайте"  style="color: green;">✓</span>' : '<span data-toggle="tooltip" title="Товар не активен" style="color: red;">x</span>' ?>
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
                            ID товара <i class="far fa-question-circle" style="margin-left: 5px;" data-toggle="tooltip" title="Уникальный, неизменный идентификатор товара, присваивается при создании в системе"></i>
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
                                <?php if ($model->shopProduct->shopProductBarcodes) : ?>
                                    <? foreach ($model->shopProduct->shopProductBarcodes as $data) : ?>
                                        <?php echo $data->value; ?>
                                    <? endforeach; ?>
                                <?php else : ?>
                                    &nbsp;&nbsp;&nbsp;
                                <?php endif; ?>

                                <?php /*echo $model->shopProduct->shopProductBarcodes ? implode("<br>", \yii\helpers\ArrayHelper::map($model->shopProduct->shopProductBarcodes, 'value', 'value')) : "&nbsp;&nbsp;&nbsp;" */ ?>
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
                            
                        
                            <span class=""
                                  data-form="#tree_id-form"
                                  data-title="Категория"
                            >
                                <?php echo $model->cmsTree ? $model->cmsTree->name : "&nbsp;&nbsp;&nbsp;"; ?>
                            </span>

                            <!--<div class="sx-fast-edit-form-wrapper">
                                <?php /*$form = \skeeks\cms\base\widgets\ActiveFormAjaxSubmit::begin([
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
                                ]); */ ?>
                                <?php /*echo $form->field($model, 'tree_id')
                                    ->widget(
                                        \skeeks\cms\backend\widgets\SelectModelDialogTreeWidget::class, [
                                            'visibleInput' => false,
                                        ]
                                    )
                                    ->label(false); */ ?>
                                    <div class="input-group-append">
                                        <button class="btn btn-primary" type="submit"><i class="fas fa-check"></i> Сохранить</button>
                                    </div>
                                <?php /*$form::end(); */ ?>
                            </div>-->
                        </span>

                    </li>

                    <?
                    $this->registerCss(<<<CSS
.select2-container--krajee-bs3 .select2-dropdown {
    z-index: 9999;
}
CSS
                    );
                    ?>

                    <?php /*if (YII_ENV_DEV) : */?>



                        <li>
                        <span class="sx-properties--name">
                            Бренд
                        </span>
                            <span class="sx-properties--value">
                            <span class="sx-fast-edit sx-fast-edit-popover"
                                  data-form="#brand-form"
                                  data-title="Бренд"
                            >
                                <?php echo $model->shopProduct->brand_id ? $model->shopProduct->brand->name : "&nbsp;&nbsp;&nbsp;" ?>
                            </span>

                            <div class="sx-fast-edit-form-wrapper">
                                <?php $form = \skeeks\cms\base\widgets\ActiveFormAjaxSubmit::begin([
                                    'id'             => "brand-form",
                                    'action'         => \yii\helpers\Url::to(['update-attribute', 'pk' => $model->id, 'content' => $model->content_id]),
                                    'options'        => [
                                        'class' => 'sx-fast-edit-form',
                                        'style' => 'min-width: 200px;',
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
                                ]);

                                $this->registerJs(<<<JS
$("#shopproduct-brand_id").on("change", function() {
     $("#brand-form").submit();
});
JS
                                );
                                ?>
                                <?php echo $form->field($model->shopProduct, 'brand_id')->widget(
                                    \skeeks\cms\widgets\AjaxSelectModel::class,
                                    [
                                        'modelClass' => \skeeks\cms\shop\models\ShopBrand::class,
                                        "ajaxUrl"    => \yii\helpers\Url::to([
                                            '/cms/ajax/autocomplete-brands',
                                        ]),
                                    ]
                                )->label(false); ?>
                                    <div class="input-group-append" style="display: none;">
                                        <button class="btn btn-primary" type="submit"><i class="fas fa-check"></i> Сохранить</button>
                                    </div>
                                <?php $form::end(); ?>
                            </div>

                        </span>
                        </li>


                        <li>
                        <span class="sx-properties--name">
                            Артикул бренда
                        </span>
                            <span class="sx-properties--value">
                            <span class="sx-fast-edit sx-fast-edit-popover"
                                  data-form="#brandsku-form"
                                  data-title="Артикул бренда"
                            >
                                <?php echo $model->shopProduct->brand_sku ? $model->shopProduct->brand_sku : "&nbsp;&nbsp;&nbsp;" ?>
                            </span>

                            <div class="sx-fast-edit-form-wrapper">
                                <?php $form = \skeeks\cms\base\widgets\ActiveFormAjaxSubmit::begin([
                                    'id'             => "brandsku-form",
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
                                <?php echo $form->field($model->shopProduct, 'brand_sku')->label(false); ?>
                                    <div class="input-group-append">
                                        <button class="btn btn-primary" type="submit"><i class="fas fa-check"></i> Сохранить</button>
                                    </div>
                                <?php $form::end(); ?>
                            </div>

                        </span>
                        </li>


                    <li>
                        <span class="sx-properties--name">
                            Страна
                        </span>
                        <span class="sx-properties--value">
                            <span class="sx-fast-edit sx-fast-edit-popover"
                                  data-form="#country-form"
                                  data-title="Страна"
                            >
                                <?php echo $model->shopProduct->country ? $model->shopProduct->country->name : "&nbsp;&nbsp;&nbsp;" ?>
                            </span>

                            <div class="sx-fast-edit-form-wrapper">
                                <?php $form = \skeeks\cms\base\widgets\ActiveFormAjaxSubmit::begin([
                                    'id'             => "country-form",
                                    'action'         => \yii\helpers\Url::to(['update-attribute', 'pk' => $model->id, 'content' => $model->content_id]),
                                    'options'        => [
                                        'class' => 'sx-fast-edit-form',
                                        'style' => 'min-width: 200px;',
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
                                ]);

                                $this->registerJs(<<<JS
$("#shopproduct-country_alpha2").on("change", function() {
     $("#country-form").submit();
});
JS
                                );
                                ?>
                                <?php echo $form->field($model->shopProduct, 'country_alpha2')->widget(
                                    \skeeks\cms\widgets\AjaxSelectModel::class,
                                    [
                                        'modelClass'       => \skeeks\cms\models\CmsCountry::class,
                                        'modelPkAttribute' => "alpha2",
                                        "ajaxUrl"          => \yii\helpers\Url::to([
                                            '/cms/ajax/autocomplete-countries',
                                        ]),
                                    ]
                                )->label(false); ?>
                                    <div class="input-group-append" style="display: none;">
                                        <button class="btn btn-primary" type="submit"><i class="fas fa-check"></i> Сохранить</button>
                                    </div>
                                <?php $form::end(); ?>
                            </div>

                        </span>
                    </li>


                    <?php if ($model->cmsTree && $model->cmsTree->shop_has_collections) : ?>

                        <li>
                        <span class="sx-properties--name">
                            Коллекции
                        </span>
                            <span class="sx-properties--value">
                            <span class="sx-fast-edit sx-fast-edit-popover"
                                  data-form="#collections-form"
                                  data-title="Коллекции"
                            >
                                <?php echo $model->shopProduct->collections ? implode(", ", \yii\helpers\ArrayHelper::map(
                                    $model->shopProduct->collections,
                                    'id',
                                    'name'
                                )) : "&nbsp;&nbsp;&nbsp;" ?>
                            </span>

                            <div class="sx-fast-edit-form-wrapper">
                                <?php $form = \skeeks\cms\base\widgets\ActiveFormAjaxSubmit::begin([
                                    'id'             => "collections-form",
                                    'action'         => \yii\helpers\Url::to(['update-attribute', 'pk' => $model->id, 'content' => $model->content_id]),
                                    'options'        => [
                                        'class' => 'sx-fast-edit-form',
                                        'style' => 'min-width: 200px;',
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
                                ]);

                                $this->registerJs(<<<JS
$("#shopproduct-collections").on("change", function() {
     $("#collections-form").submit();
});
JS
                                );
                                ?>
                                <?php echo $form->field($model->shopProduct, 'collections')->widget(
                                    \skeeks\cms\widgets\AjaxSelectModel::class,
                                    [
                                        'multiple'    => true,
                                        'modelClass'  => \skeeks\cms\shop\models\ShopCollection::class,
                                        'searchQuery' => function ($word = '') {
                                            $query = \skeeks\cms\shop\models\ShopCollection::find();
                                            if ($word) {
                                                $query->search($word);
                                            }
                                            return $query;
                                        },
                                    ]
                                )->label(false); ?>
                                    <div class="input-group-append" style="display: none;">
                                        <button class="btn btn-primary" type="submit"><i class="fas fa-check"></i> Сохранить</button>
                                    </div>
                                <?php $form::end(); ?>
                            </div>

                        </span>
                        </li>

                    <?php /*endif; */?>

                    <?php endif; ?>

                    <li>
                        <span class="sx-properties--name">
                            Внешний код <i class="far fa-question-circle" style="margin-left: 5px;" data-toggle="tooltip"
                                           title="Чаще всего внешний код заполняется автоматически и используется для итеграции с внешними системами"></i>
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
                            Количество просмотров <i class="far fa-question-circle" style="margin-left: 5px;" data-toggle="tooltip"
                                           title="Количество просмотров, от этого зависит популярность товара. По умолчанию чем популярнее товар, тем он выше в списке, в разделе."></i>
                        </span>
                        <span class="sx-properties--value">
                            <span class="sx-fast-edit sx-fast-edit-popover"
                                  data-form="#show_counter-form"
                                  data-title="Количество просмотров"
                            >
                                <?php echo $model->show_counter ? $model->show_counter : "&nbsp;&nbsp;&nbsp;" ?>
                            </span>

                            <div class="sx-fast-edit-form-wrapper">
                                <?php $form = \skeeks\cms\base\widgets\ActiveFormAjaxSubmit::begin([
                                    'id'             => "show_counter-form",
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
                                <?php echo $form->field($model, 'show_counter')->label(false)->hint(false); ?>
                                    <div class="input-group-append">
                                        <button class="btn btn-primary" type="submit"><i class="fas fa-check"></i> Сохранить</button>
                                    </div>
                                <?php $form::end(); ?>
                            </div>

                        </span>
                    </li>


                    <!--<li>
                        <span class="sx-properties--name">
                            Создан
                        </span>
                                <span class="sx-properties--value" title="<?php /*echo $model->created_at ? \Yii::$app->formatter->asRelativeTime($model->created_at) : ""; */ ?>" data-toggle="tooltip">
                                <?php /*echo $model->created_at ? \Yii::$app->formatter->asDate($model->created_at) : ""; */ ?>
                        </span>
                    </li>-->


                </ul>


                <?php if ($model->shopProduct->shopProductBarcodes) : ?>
                    <div class="text-center" style="margin-top: 10px; height: 50px;">
                        <? foreach ($model->shopProduct->shopProductBarcodes as $data) : ?>

                            <?
                            $generator = new Picqer\Barcode\BarcodeGeneratorPNG();
                            ?>
                            <img src="data:image/png;base64,<?php echo base64_encode($generator->getBarcode($data->value, $generator::TYPE_CODE_128, 1, 40)); ?>"/>

                            <div class="block" style='
                        z-index: 1;
                        border-left-width: 0px;
                        border-right-width: 0px;
                        border-bottom-width: 0px;
                        border-top-width: 0px;
                        font-size: 10px;
                        text-align: center;
                        margin-top: 3px;
                        '>


                                <?php echo $data->value; ?>


                            </div>
                            <?
                            break;
                            ?>
                        <? endforeach; ?>
                    </div>
                <?php else : ?>

                    <?php
                    $backendUrl = \yii\helpers\Url::to(['generate-barcode', 'pk' => $model->id]);

                    $this->registerJs(<<<JS
$(".sx-generate-barcode").on("click", function() {
    var ajaxQuery = sx.ajax.preparePostQuery("{$backendUrl}");
    
    new sx.classes.AjaxHandlerStandartRespose(ajaxQuery, {
        'blockerSelector' : 'body',
        'enableBlocker' : true,
    }).on("success", function(e, response) {
        window.location.reload();
    });
    
    ajaxQuery.execute();
});
JS
                    );
                    ?>

                    <div class="text-center">
                        <button class="btn btn-default sx-generate-barcode">Сгенерировать штрихкод</button>
                    </div>

                <?php endif; ?>

            </div>
        </div>
    </div>


    <div class="col-lg-4 col-sm-6 col-12">
        <div style="padding: 10px;">
            <div class="sx-properties-wrapper sx-columns-1" style="max-width: 350px; margin-top: 15px;">
                <div style="color: gray; margin-bottom: 8px;">Габариты товара с упаковкой</div>
                <ul class="sx-properties">
                    <li>
                        <span class="sx-properties--name">
                            Вес
                        </span>
                        <span class="sx-properties--value">
                            <span class="sx-fast-edit sx-fast-edit-popover"
                                  data-form="#weight-form"
                                  data-title="Вес"
                            >
                                <?php echo $model->shopProduct->weightFormatted; ?>
                            </span>

                            <div class="sx-fast-edit-form-wrapper">
                                <?php $form = \skeeks\cms\base\widgets\ActiveFormAjaxSubmit::begin([
                                    'id'             => "weight-form",
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
                                <?php echo $form->field($model->shopProduct, 'weight')->widget(
                                    \skeeks\cms\shop\widgets\admin\SmartWeightShortInputWidget::class
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
                            Длина
                        </span>
                        <span class="sx-properties--value">
                            <span class="sx-fast-edit sx-fast-edit-popover"
                                  data-form="#length-form"
                                  data-title="Длина"
                            >
                                <?php echo $model->shopProduct->lengthFormatted; ?>
                            </span>

                            <div class="sx-fast-edit-form-wrapper">
                                <?php $form = \skeeks\cms\base\widgets\ActiveFormAjaxSubmit::begin([
                                    'id'             => "length-form",
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
                                <?php echo $form->field($model->shopProduct, 'length')->widget(
                                    \skeeks\cms\shop\widgets\admin\SmartDimensionsShortInputWidget::class
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
                            Ширина
                        </span>
                        <span class="sx-properties--value">
                            <span class="sx-fast-edit sx-fast-edit-popover"
                                  data-form="#width-form"
                                  data-title="Ширина"
                            >
                                <?php echo $model->shopProduct->widthFormatted; ?>
                            </span>

                            <div class="sx-fast-edit-form-wrapper">
                                <?php $form = \skeeks\cms\base\widgets\ActiveFormAjaxSubmit::begin([
                                    'id'             => "width-form",
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
                                <?php echo $form->field($model->shopProduct, 'width')->widget(
                                    \skeeks\cms\shop\widgets\admin\SmartDimensionsShortInputWidget::class
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
                            Высота
                        </span>
                        <span class="sx-properties--value">
                            <span class="sx-fast-edit sx-fast-edit-popover"
                                  data-form="#height-form"
                                  data-title="Высота"
                            >
                                <?php echo $model->shopProduct->heightFormatted; ?>
                            </span>

                            <div class="sx-fast-edit-form-wrapper">
                                <?php $form = \skeeks\cms\base\widgets\ActiveFormAjaxSubmit::begin([
                                    'id'             => "height-form",
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
                                <?php echo $form->field($model->shopProduct, 'height')->widget(
                                    \skeeks\cms\shop\widgets\admin\SmartDimensionsShortInputWidget::class
                                )->label(false); ?>
                                    <div class="input-group-append">
                                        <button class="btn btn-primary" type="submit"><i class="fas fa-check"></i> Сохранить</button>
                                    </div>
                                <?php $form::end(); ?>
                            </div>

                        </span>
                    </li>

                </ul>
            </div>


            <div class="sx-properties-wrapper sx-columns-1" style="max-width: 350px; margin-top: 15px;">

                <div style="color: gray; margin-bottom: 8px;">Гарантия и срок службы</div>
                <ul class="sx-properties">
                    <li>
                        <span class="sx-properties--name">
                            <?php echo $model->shopProduct->getAttributeLabel("expiration_time"); ?>
                            <i class="far fa-question-circle" style="margin-left: 5px;" data-toggle="tooltip"
                               title="<?php echo $model->shopProduct->getAttributeHint("expiration_time"); ?>"></i>
                        </span>
                        <span class="sx-properties--value">
                            <span class="sx-fast-edit sx-fast-edit-popover"
                                  data-form="#expiration_time-form"
                                  data-title="<?php echo $model->shopProduct->getAttributeLabel("expiration_time"); ?>"
                            >
                                <?php echo $model->shopProduct->expiration_time ? \skeeks\cms\shop\models\ShopProduct::formatExperationTime($model->shopProduct->expiration_time) : "&nbsp;&nbsp;&nbsp;"; ?>
                                <?php if ($model->shopProduct->expiration_time_comment) : ?>
                                    <i class="far fa-question-circle" style="margin-left: 5px;" data-toggle="tooltip" data-html="true"
                                       title="<?php echo $model->shopProduct->expiration_time_comment; ?>"></i>
                                <?php endif; ?>

                            </span>

                            <div class="sx-fast-edit-form-wrapper">
                                <?php $form = \skeeks\cms\base\widgets\ActiveFormAjaxSubmit::begin([
                                    'id'             => "expiration_time-form",
                                    'action'         => \yii\helpers\Url::to(['update-attribute', 'pk' => $model->id, 'content' => $model->content_id]),
                                    'options'        => [
                                        'class' => 'sx-fast-edit-form',
                                        'style' => 'max-width: 400px;',
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

                                <?php echo $form->field($model->shopProduct, 'expiration_time')->widget(
                                    \skeeks\cms\shop\widgets\admin\SmartExpirationTimeInputWidget::class
                                )->label(false); ?>

                                <?= $form->field($model->shopProduct, 'expiration_time_comment')->textarea([
                                    'rows'        => 5,
                                    'placeholder' => $model->shopProduct->getAttributeLabel("expiration_time_comment"),
                                ])->label(false); ?>

                                    <div class="input-group-append">
                                        <button class="btn btn-primary" type="submit"><i class="fas fa-check"></i> Сохранить</button>
                                    </div>
                                <?php $form::end(); ?>
                            </div>

                        </span>
                    </li>


                    <li>
                        <span class="sx-properties--name">
                            <?php echo $model->shopProduct->getAttributeLabel("service_life_time"); ?>
                            <i class="far fa-question-circle" style="margin-left: 5px;" data-toggle="tooltip"
                               title="<?php echo $model->shopProduct->getAttributeHint("service_life_time"); ?>"></i>
                        </span>
                        <span class="sx-properties--value">
                            <span class="sx-fast-edit sx-fast-edit-popover"
                                  data-form="#service_life_time-form"
                                  data-title="<?php echo $model->shopProduct->getAttributeLabel("service_life_time"); ?>"
                            >
                                <?php echo $model->shopProduct->service_life_time ? \skeeks\cms\shop\models\ShopProduct::formatExperationTime($model->shopProduct->service_life_time) : "&nbsp;&nbsp;&nbsp;"; ?>
                                <?php if ($model->shopProduct->service_life_time_comment) : ?>
                                    <i class="far fa-question-circle" style="margin-left: 5px;" data-toggle="tooltip" data-html="true"
                                       title="<?php echo $model->shopProduct->service_life_time_comment; ?>"></i>
                                <?php endif; ?>

                            </span>

                            <div class="sx-fast-edit-form-wrapper">
                                <?php $form = \skeeks\cms\base\widgets\ActiveFormAjaxSubmit::begin([
                                    'id'             => "service_life_time-form",
                                    'action'         => \yii\helpers\Url::to(['update-attribute', 'pk' => $model->id, 'content' => $model->content_id]),
                                    'options'        => [
                                        'class' => 'sx-fast-edit-form',
                                        'style' => 'max-width: 400px;',
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

                                <?php echo $form->field($model->shopProduct, 'service_life_time')->widget(
                                    \skeeks\cms\shop\widgets\admin\SmartExpirationTimeInputWidget::class
                                )->label(false); ?>

                                <?= $form->field($model->shopProduct, 'service_life_time_comment')->textarea([
                                    'rows'        => 5,
                                    'placeholder' => $model->shopProduct->getAttributeLabel("service_life_time_comment"),
                                ])->label(false); ?>

                                    <div class="input-group-append">
                                        <button class="btn btn-primary" type="submit"><i class="fas fa-check"></i> Сохранить</button>
                                    </div>
                                <?php $form::end(); ?>
                            </div>

                        </span>
                    </li>

                    <li>
                        <span class="sx-properties--name">
                            <?php echo $model->shopProduct->getAttributeLabel("warranty_time"); ?>
                            <i class="far fa-question-circle" style="margin-left: 5px;" data-toggle="tooltip"
                               title="<?php echo $model->shopProduct->getAttributeHint("warranty_time"); ?>"></i>
                        </span>
                        <span class="sx-properties--value">
                            <span class="sx-fast-edit sx-fast-edit-popover"
                                  data-form="#warranty_time-form"
                                  data-title="<?php echo $model->shopProduct->getAttributeLabel("warranty_time"); ?>"
                            >
                                <?php echo $model->shopProduct->warranty_time ? \skeeks\cms\shop\models\ShopProduct::formatExperationTime($model->shopProduct->warranty_time) : "&nbsp;&nbsp;&nbsp;"; ?>
                                <?php if ($model->shopProduct->warranty_time_comment) : ?>
                                    <i class="far fa-question-circle" style="margin-left: 5px;" data-toggle="tooltip" data-html="true"
                                       title="<?php echo $model->shopProduct->warranty_time_comment; ?>"></i>
                                <?php endif; ?>

                            </span>

                            <div class="sx-fast-edit-form-wrapper">
                                <?php $form = \skeeks\cms\base\widgets\ActiveFormAjaxSubmit::begin([
                                    'id'             => "warranty_time-form",
                                    'action'         => \yii\helpers\Url::to(['update-attribute', 'pk' => $model->id, 'content' => $model->content_id]),
                                    'options'        => [
                                        'class' => 'sx-fast-edit-form',
                                        'style' => 'max-width: 400px;',
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

                                <?php echo $form->field($model->shopProduct, 'warranty_time')->widget(
                                    \skeeks\cms\shop\widgets\admin\SmartExpirationTimeInputWidget::class
                                )->label(false); ?>

                                <?= $form->field($model->shopProduct, 'warranty_time_comment')->textarea([
                                    'rows'        => 5,
                                    'placeholder' => $model->shopProduct->getAttributeLabel("warranty_time_comment"),
                                ])->label(false); ?>

                                    <div class="input-group-append">
                                        <button class="btn btn-primary" type="submit"><i class="fas fa-check"></i> Сохранить</button>
                                    </div>
                                <?php $form::end(); ?>
                            </div>

                        </span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
</div>

<?php if (
    !$model->shopProduct->isOffersProduct //И если это товар без предложений
) : ?>
    <div class="row no-gutters" style="margin-top: 10px;">
        <div class="col-12">
            <div style="margin-bottom: 5px;"><b style="text-transform: uppercase;">Цена на сайте</b> <i class="far fa-question-circle" style="margin-left: 5px; color: silver;" data-toggle="tooltip"
                                                                                                        title="Эта цена используется на сайте, именно ее видит клиент на сайте."></i></div>

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
                                    <div class="input-group" style="margin-top: 10px;">
                                        <? echo \yii\helpers\Html::checkbox("is_fixed", ($price && $price->is_fixed ? true : false), [
                                            'label' => 'Зафиксирована?',
                                        ]); ?>
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

    <?php
    $marketplaces = \skeeks\cms\shop\models\ShopMarketplace::find()->active()->all();
    ?>
    <?php if ($marketplaces) : ?>
        <div class="row no-gutters" style="margin-top: 10px;">
            <div class="col-12">
                <div style="margin-bottom: 5px;"><b style="text-transform: uppercase;">Маркетплейсы</b> <i class="far fa-question-circle" style="margin-left: 5px; color: silver;" data-toggle="tooltip"
                                                                                                           title="Этот товар на маркетплейсах"></i></div>
                <div class="sx-table-wrapper table-responsive">

                </div>
            </div>
        </div>
    <?php endif; ?>
    <div class="row no-gutters" style="margin-top: 10px;">
        <div class="col-12">
            <div style="margin-bottom: 5px;"><b style="text-transform: uppercase;">Склады и магазины</b> <i class="far fa-question-circle" style="margin-left: 5px; color: silver;" data-toggle="tooltip"
                                                                                                            title="В каждом магазине может быть своя цена, она может отличатся от цены сайта, это зависит от настроек системы."></i>
            </div>
            <div class="sx-table-wrapper table-responsive">
                <table class="table sx-table">
                    <tr>
                        <th style="text-align: left;">Магазин/склад</th>

                        <th>Закупочная цена</th>
                        <th>Розничная цена</th>
                        <th>Остаток, <?php echo $model->shopProduct->measure->symbol; ?></th>
                        <!-- <th><?php /*echo \Yii::$app->shop->baseTypePrice->name; */ ?> (сумма)</th>-->
                    </tr>

                    <?php
                    $totalSummPrice = 0;
                    $totalSummQuantity = 0;
                    ?>

                    <?php if ($shopStores = \Yii::$app->skeeks->site->getShopStores()->andWhere(['is_supplier' => 0])->all()) : ?>
                        <?php
                        /**
                         * @var $shopStore \skeeks\cms\shop\models\ShopStore
                         */
                        foreach ($shopStores as $shopStore) : ?>
                            <?php
                            $storeProduct = $model->shopProduct->getStoreProduct($shopStore);
                            $totalPrice = $noValue;
                            $money = $model->shopProduct->baseProductPrice ? $model->shopProduct->baseProductPrice->money : null;
                            if ($storeProduct) {
                                if ($money) {
                                    $totalPrice = $money->mul((float)$storeProduct->quantity);
                                }

                                $totalSummQuantity = $totalSummQuantity + $storeProduct->quantity;
                            }

                            ?>
                            <tr>
                                <td style="text-align: left;">
                                    <?php if ($storeProduct) : ?>
                                        <?
                                        \skeeks\cms\backend\widgets\AjaxControllerActionsWidget::begin([
                                            'controllerId'            => "/shop/store-product",
                                            'modelId'                 => $storeProduct->id,
                                            'tag'                     => 'span',
                                            'isRunFirstActionOnClick' => true,
                                            'options'                 => [
                                                'style' => 'text-align: left;',
                                                'class' => 'sx-fast-edit',
                                            ],
                                        ]);
                                        ?>
                                        <?php echo $shopStore->name; ?>
                                        <?php \skeeks\cms\backend\widgets\AjaxControllerActionsWidget::end(); ?>
                                    <?php else : ?>
                                        <?php echo $shopStore->name; ?>
                                    <?php endif; ?>
                                </td>

                                <td>


                                    <span class="sx-fast-edit sx-fast-edit-popover"
                                          data-form="#price-purchase-store-form"
                                          data-title="Закупочная цена"
                                    >
                                        <?php
                                        $purchasePrice = null;
                                        $shopStoreProduct = $model->shopProduct->getStoreProduct($shopStore);
                                        if ($shopStore->is_personal_price && $shopStoreProduct && $shopStoreProduct->purchase_price): ?>
                                            <?php echo new \skeeks\cms\money\Money((string)$shopStoreProduct->purchase_price, \Yii::$app->money->currency_code); ?>
                                        <?php else : ?>
                                            <?php if (\Yii::$app->shop->purchaseTypePrice) : ?>
                                                <?php
                                                $purchasePrice = $model->shopProduct->getPrice(\Yii::$app->shop->purchaseTypePrice);
                                                echo $purchasePrice ? $purchasePrice->money : "&nbsp;&nbsp;&nbsp;"; ?>
                                            <?php endif; ?>
                                        <?php endif; ?>

                                    </span>

                                    <div class="sx-fast-edit-form-wrapper">
                                        <?php $form = \skeeks\cms\base\widgets\ActiveFormAjaxSubmit::begin([
                                            'id'             => "price-purchase-store-form",
                                            'action'         => \yii\helpers\Url::to(['update-price-purchase', 'pk' => $model->id]),
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
                                        <input type="hidden" value="<?php echo $shopStore->id; ?>" name="shop_store_id" class="form-control"/>

                                        <?
                                        $this->registerJs(<<<JS
if ($(".sx-check-purchase-price").is(":checked")) {
    $(".sx-input-purchase-price").show();
} else {
    $(".sx-input-purchase-price").hide();
}

$(".sx-check-purchase-price").on("change", function() {
    if ($(this).is(":checked")) {
        $(".sx-input-purchase-price").show();
    } else {
        $(".sx-input-purchase-price").hide();
    }
});

JS
                                        );
                                        ?>
                                        <div class="input-group" style="margin-top: 10px;">
                                            <? echo \yii\helpers\Html::checkbox("is_personal_price", ($shopStoreProduct && $shopStoreProduct->purchase_price ? true : false), [
                                                'label' => 'В магазине своя цена',
                                                'class' => 'sx-check-purchase-price',
                                            ]); ?>
                                        </div>

                                        <div class="input-group sx-input-purchase-price">
                                            <?php if ($shopStoreProduct && $shopStoreProduct->purchase_price) : ?>
                                                <input type="text" value="<?php echo $shopStoreProduct->purchase_price; ?>" name="price_value" class="form-control"/>
                                            <?php else : ?>
                                                <input type="text" value="<?php echo $purchasePrice ? $purchasePrice->price : ""; ?>" name="price_value" class="form-control"/>
                                            <?php endif; ?>
                                        </div>

                                        <div class="input-group" style="margin-top: 10px;">
                                            <button class="btn btn-primary" type="submit"><i class="fas fa-check"></i> Сохранить</button>
                                        </div>

                                        <?php $form::end(); ?>
                                    </div>


                                </td>

                                <td>
                                    <?php
                                    $shopStoreProduct = $model->shopProduct->getStoreProduct($shopStore);
                                    if ($shopStore->is_personal_price && $shopStoreProduct && $shopStoreProduct->selling_price): ?>
                                        <?php echo new \skeeks\cms\money\Money((string)$shopStoreProduct->selling_price, \Yii::$app->money->currency_code); ?>
                                    <?php else : ?>
                                        <?php echo $model->shopProduct->baseProductPrice ? $model->shopProduct->baseProductPrice->money : ""; ?>
                                    <?php endif; ?>

                                </td>
                                <td>
                                    <a href="<?php echo \yii\helpers\Url::to(['store-moves', 'pk' => $model->id]); ?>" class="sx-fast-edit" style="color: black;">
                                        <?php echo $storeProduct ? (float)$storeProduct->quantity : "&nbsp;&nbsp;&nbsp;"; ?>
                                        <!--<span class="sx-fast-edit sx-fast-edit-popover"
                                          data-form="#store-<?php /*echo $shopStore->id; */ ?>-form"
                                          data-title="<?php /*echo \yii\helpers\Html::encode($shopStore->name); */ ?>"
                                    >
                                        <?php /*echo $storeProduct ? (float)$storeProduct->quantity : "&nbsp;&nbsp;&nbsp;"; */ ?>
                                    </span>-->
                                    </a>

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
                                <!--<td><?php /*echo $totalPrice; */ ?></td>-->
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td><?php echo $noValue; ?></td>
                            <td><?php echo $noValue; ?></td>
                            <td><?php echo $noValue; ?></td>
                            <td><?php echo $noValue; ?></td>
                            <!--<td><?php /*echo $noValue; */ ?></td>-->
                        </tr>
                    <?php endif; ?>


                    <tr>
                        <td colspan="3" style="text-align: right;"><b>Итого</b></td>
                        <td><?php echo $totalSummQuantity ? $totalSummQuantity : $noValue; ?></td>
                        <!--<td><?php /*echo $noValue; */ ?></td>-->
                    </tr>
                </table>
            </div>
        </div>
    </div>


    <div class="row no-gutters" style="margin-top: 10px;">
        <div class="col-12">
            <div style="margin-bottom: 5px;"><b style="text-transform: uppercase;">Поставщики</b> <i class="far fa-question-circle" style="margin-left: 5px; color: silver;" data-toggle="tooltip"
                                                                                                     title="Если ваш проект интегрирован с поставщиками, то в этом разделе можно смотреть количество оставшегося товара у поставщика + цены."></i>
            </div>
            <div class="sx-table-wrapper table-responsive">
                <table class="table sx-table">
                    <tr>
                        <th style="text-align: left;">Поставщик</th>
                        <th>Код</th>
                        <th>Закупочная цена</th>
                        <th>Розничная цена</th>
                        <th>Остаток, <?php echo $model->shopProduct->measure->symbol; ?></th>
                    </tr>

                    <?php
                    $totalSummPrice = 0;
                    $totalSummQuantity = 0;
                    ?>

                    <?php if ($shopStores = \Yii::$app->skeeks->site->getShopStores()->andWhere(['is_supplier' => 1])->all()) : ?>
                        <?php foreach ($shopStores as $shopStore) : ?>
                            <?php
                            $storeProduct = $model->shopProduct->getStoreProduct($shopStore);
                            $totalPrice = $noValue;
                            $money = $model->shopProduct->baseProductPrice ? $model->shopProduct->baseProductPrice->money : "";
                            if ($storeProduct) {
                                if ($money) {
                                    $totalPrice = $money->mul((float)$storeProduct->quantity);
                                }
                                $totalSummQuantity = $totalSummQuantity + $storeProduct->quantity;

                            }
                            ?>
                            <? if ($storeProduct) : ?>
                                <tr>
                                    <td style="text-align: left;">
                                        <?php if ($storeProduct) : ?>
                                            <?
                                            \skeeks\cms\backend\widgets\AjaxControllerActionsWidget::begin([
                                                'controllerId' => "/shop/store-product",
                                                'modelId'      => $storeProduct->id,
                                                'tag'          => 'span',
                                                'options'      => [
                                                    'style' => 'text-align: left;',
                                                    'class' => 'sx-fast-edit',
                                                ],
                                            ]);
                                            ?>
                                            <?php echo $shopStore->name; ?>
                                            <?php \skeeks\cms\backend\widgets\AjaxControllerActionsWidget::end(); ?>
                                        <?php else : ?>
                                            <?php echo $shopStore->name; ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($storeProduct) : ?>
                                            <?
                                            \skeeks\cms\backend\widgets\AjaxControllerActionsWidget::begin([
                                                'controllerId' => "/shop/store-product",
                                                'modelId'      => $storeProduct->id,
                                                'tag'          => 'span',
                                                'options'      => [
                                                    'style' => 'text-align: left;',
                                                    'class' => 'sx-fast-edit',
                                                ],
                                            ]);
                                            ?>
                                            <?php echo $storeProduct ? $storeProduct->external_id : "&nbsp;&nbsp;&nbsp;"; ?>
                                            <?php \skeeks\cms\backend\widgets\AjaxControllerActionsWidget::end(); ?>
                                        <?php else : ?>
                                            &nbsp;&nbsp;&nbsp;
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $storeProduct ? $storeProduct->purchase_price : "&nbsp;&nbsp;&nbsp;"; ?></td>
                                    <td><?php echo $storeProduct ? $storeProduct->selling_price : "&nbsp;&nbsp;&nbsp;"; ?></td>
                                    <td>
                                    <span class=""
                                    >
                                        <?php echo $storeProduct ? (float)$storeProduct->quantity : "&nbsp;&nbsp;&nbsp;"; ?>
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
                                </tr>
                            <? endif; ?>

                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td><?php echo $noValue; ?></td>
                            <td><?php echo $noValue; ?></td>
                            <td><?php echo $noValue; ?></td>
                            <td><?php echo $noValue; ?></td>
                            <td><?php echo $noValue; ?></td>
                        </tr>
                    <?php endif; ?>


                    <tr>
                        <td colspan="4" style="text-align: right;"><b>Итого</b></td>
                        <td><?php echo $totalSummQuantity ? $totalSummQuantity : $noValue; ?></td>
                        <!-- <td><?php /*echo $noValue; */ ?></td>-->
                    </tr>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>



<?php
$infoModel = $model;
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
                                    <?php /*print_r($rp->getAttribute($code)); */ ?>
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

<?php /*if ($model->shopProduct->supplier_external_jsondata) : */ ?><!--
    <section class="sx-info-block">
        <div class="row no-gutters">
            <div class="col-12">
                <div class="sx-title">Прочие данные <i class="far fa-question-circle" title="Неразобранные данные, которые сохранились по товару в момент импорта на сайт" data-toggle="tooltip"
                                                       style="margin-left: 5px;"></i></div>
                <? /*= \skeeks\cms\shop\widgets\admin\SubProductExternalDataWidget::widget(['shopProduct' => $model->shopProduct]); */ ?>
            </div>
        </div>
    </section>
--><?php /*endif; */ ?>

<?php $pjax::end(); ?>


