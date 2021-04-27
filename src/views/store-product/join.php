<?php
/* @var $this yii\web\View */
/* @var $joinModel \skeeks\cms\shop\models\ShopCmsContentElement */
/* @var $controller \skeeks\cms\backend\controllers\BackendModelController */
/* @var $action \skeeks\cms\backend\actions\BackendModelCreateAction|\skeeks\cms\backend\actions\IHasActiveForm */
$controller = $this->context;
$action = $controller->action;

$url = \yii\helpers\Url::to(['join-by-vendor']);

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

JS
);

$shopCmsContentPropertyVendor = \skeeks\cms\shop\models\ShopCmsContentProperty::find()
    ->innerJoinWith('cmsContentProperty as cmsContentProperty')
    ->andWhere(['cmsContentProperty.cms_site_id' => \Yii::$app->skeeks->site->id])
    ->andWhere(['is_vendor' => 1])
    ->one();

$shopCmsContentPropertyVendorCode = \skeeks\cms\shop\models\ShopCmsContentProperty::find()
    ->innerJoinWith('cmsContentProperty as cmsContentProperty')
    ->andWhere(['cmsContentProperty.cms_site_id' => \Yii::$app->skeeks->site->id])
    ->andWhere(['is_vendor_code' => 1])
    ->one();

$isBrand = false;
if ($shopCmsContentPropertyVendor && $shopCmsContentPropertyVendorCode) {
    $qShopStoreProperties = \Yii::$app->shop->backendShopStore->getShopStoreProperties();
    $shopStorePropertyVendor = $qShopStoreProperties->andWhere(['cms_content_property_id' => $shopCmsContentPropertyVendor->cms_content_property_id])->one();

    $qShopStoreProperties = \Yii::$app->shop->backendShopStore->getShopStoreProperties();
    $shopStorePropertyVendorCode = $qShopStoreProperties->andWhere(['cms_content_property_id' => $shopCmsContentPropertyVendorCode->cms_content_property_id])->one();

    if ($shopStorePropertyVendor && $shopStorePropertyVendorCode) {
        $isBrand = true;
    }

}

?>
<?php if($isBrand) : ?>
    <div style="margin-bottom: 20px;">

        <div class="col-12">
            <div style="margin-bottom: 10px;">
                <b style="text-transform: uppercase;">Связка по бренду</b></div>
        </div>

        <div class="col-12">
            <button type="submit" class="btn btn-primary sx-join-by-brand-trigger">Запустить связку товаров по бренду</button>
        </div>
        <div class="col-12">
            <div class="sx-vendor-result"></div>
        </div>

    </div>
<?php else : ?>
    <p>На вашем сайте не настроены свойства бренд и артикул бренда</p>
<?php endif; ?>



