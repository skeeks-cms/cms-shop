<?php
/* @var $this yii\web\View */
/* @var $joinModel \skeeks\cms\shop\models\ShopCmsContentElement */
/* @var $controller \skeeks\cms\backend\controllers\BackendModelController */
/* @var $action \skeeks\cms\backend\actions\BackendModelCreateAction|\skeeks\cms\backend\actions\IHasActiveForm */
$controller = $this->context;
$action = $controller->action;

$url = \yii\helpers\Url::to(['join-by-vendor']);
$urlBarcode = \yii\helpers\Url::to(['join-by-barcode']);
$urlModelBarcode = \yii\helpers\Url::to(['join-by-model-barcode']);

$this->registerJs(<<<JS

$(".sx-join-by-brand-trigger").on("click", function() {
    var ajaxQuery = sx.ajax.preparePostQuery("{$url}");
    
    new sx.classes.AjaxHandlerStandartRespose(ajaxQuery, {
        'blockerSelector' : 'body',
        'enableBlocker' : true,
    }).on("success", function(e, response) {
        if (response.data.added) {
            $(".sx-vendor-result").empty().append("Связано товаров: " + response.data.added);
        }
    });
    
    ajaxQuery.execute();
});

$(".sx-join-by-barcode-trigger").on("click", function() {
    var ajaxQuery = sx.ajax.preparePostQuery("{$urlBarcode}");
    
    new sx.classes.AjaxHandlerStandartRespose(ajaxQuery, {
        'blockerSelector' : 'body',
        'enableBlocker' : true,
    }).on("success", function(e, response) {
        if (response.data.added) {
            $(".sx-vendor-result").empty().append("Связано товаров: " + response.data.added);
        }
    });
    
    ajaxQuery.execute();
});

$(".sx-join-by-model-barcode-trigger").on("click", function() {
    var ajaxQuery = sx.ajax.preparePostQuery("{$urlModelBarcode}");
    
    new sx.classes.AjaxHandlerStandartRespose(ajaxQuery, {
        'blockerSelector' : 'body',
        'enableBlocker' : true,
    }).on("success", function(e, response) {
        if (response.data.added) {
            $(".sx-vendor-result").empty().append("Связано товаров: " + response.data.added);
        }
    });
    
    ajaxQuery.execute();
});

JS
);


$cmsContentPropertyVendor = \skeeks\cms\models\CmsContentProperty::find()->cmsSite()
    ->andWhere(['is_vendor' => 1])
    ->one();

$cmsContentPropertyVendorCode = \skeeks\cms\models\CmsContentProperty::find()->cmsSite()
    ->andWhere(['is_vendor_code' => 1])
    ->one();

$isBrand = false;
$isBarcode = false;
if ($cmsContentPropertyVendor && $cmsContentPropertyVendorCode) {
    $qShopStoreProperties = \Yii::$app->shop->backendShopStore->getShopStoreProperties();
    $shopStorePropertyVendor = $qShopStoreProperties->andWhere(['cms_content_property_id' => $cmsContentPropertyVendor->id])->one();

    $qShopStoreProperties = \Yii::$app->shop->backendShopStore->getShopStoreProperties();
    $shopStorePropertyVendorCode = $qShopStoreProperties->andWhere(['cms_content_property_id' => $cmsContentPropertyVendorCode->id])->one();

    if ($shopStorePropertyVendor && $shopStorePropertyVendorCode) {
        $isBrand = true;
    }
}

/*\skeeks\cms\shop\models\ShopCmsContentElement::find()
    ->cmsSite()
    ->innerJoinWith("shopProduct as sp")
    ->andWhere(['is not', \skeeks\cms\shop\models\ShopCmsContentElement::tableName() . ".main_cce_id", null])
;*/



$qShopStoreProperties = \Yii::$app->shop->backendShopStore->getShopStoreProperties();
$shopStorePropertyBarcode = $qShopStoreProperties->andWhere(['property_nature' => \skeeks\cms\shop\models\ShopStoreProperty::PROPERTY_NATURE_BARCODE])->one();
if ($shopStorePropertyBarcode) {
    $isBarcode = true;
}


?>
<?php if ($isBrand || $isBarcode) : ?>
    <div style="margin-bottom: 20px;">

        <div class="col-12">
            <div style="margin-bottom: 10px;">
                <b style="text-transform: uppercase;">Связка по бренду</b></div>
        </div>

        <div class="col-12">
            <?php if ($isBrand) : ?>
                <button type="submit" class="btn btn-primary sx-join-by-brand-trigger">Запустить связку товаров по бренду + артикул</button>
            <?php endif; ?>

            <?php if ($isBarcode) : ?>
                <button type="submit" class="btn btn-primary sx-join-by-barcode-trigger">Запустить по штрихкоду</button>

                <?php if(\Yii::$app->skeeks->site->shopSite->is_receiver) : ?>
                    <button type="submit" class="btn btn-primary sx-join-by-model-barcode-trigger">Запустить по штрихкоду через модели</button>
                <?php endif; ?>

            <?php endif; ?>


        </div>
        <div class="col-12">
            <div class="sx-vendor-result"></div>
        </div>

    </div>
<?php else : ?>
    <p>На вашем сайте не настроены свойства бренд и артикул бренда</p>
<?php endif; ?>



