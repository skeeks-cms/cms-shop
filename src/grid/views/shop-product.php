<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */
/* @var $this yii\web\View */
/* @var $model \skeeks\cms\shop\models\ShopCmsContentElement */
$shopSellerProducts = [];
$apiIconClass = $model->is_sx_info_update ? "sx-text--success" : "sx-text--danger";
$apiIconTitle = $model->is_sx_info_update
    ? "SkeekS ID: {$model->sx_id}. Информация обновляется из сервиса SkeekS Товары"
    : "SkeekS ID: {$model->sx_id}. Обновление информации из сервиса SkeekS Товары запрещено";
$apiMarketUrl = $model->sx_id && isset(\Yii::$app->skeeksSuppliersApi) ? \Yii::$app->skeeksSuppliersApi->getProductUrl($model->sx_id) : "#";
$apiIcon = "<i class='fas fa-link {$apiIconClass}'></i>";
$isSubProduct = $model->shopProduct->isSubProduct;
$image = $model->image;
if (!$image && $isSubProduct && $model->main_cce_id) {
    $image = $model->mainCmsContentElement->image;
}
$imageSrc = $image
    ? ($isSubProduct
        ? $image->src
        : \Yii::$app->imaging->thumbnailUrlOnRequest(
            $image->src,
            new \skeeks\cms\components\imaging\filters\Thumbnail(),
            $model->code
        ))
    : \skeeks\cms\helpers\Image::getCapSrc();
$title = $isSubProduct ? $model->name : $model->productName;
$statusItems = [];

if ($isSubProduct && $model->main_cce_id) {
    $statusItems[] = \skeeks\cms\backend\widgets\AjaxControllerActionsWidget::widget([
        'controllerId' => "/shop/admin-cms-content-element",
        'modelId'      => $model->main_cce_id,
        'content'      => '<i class="fas fa-link"></i>',
        'options'      => [
            'class' => 'sx-status sx-status--success',
            'title' => "Привязан к информационной карточке! {$model->mainCmsContentElement->asText}",
        ],
    ]);
} elseif ($isSubProduct && $model->cmsSite->shopSite->is_receiver) {
    $statusItems[] = \yii\helpers\Html::tag(
        'span',
        '<i class="fas fa-link"></i>',
        ['class' => 'sx-status sx-status--danger', 'title' => 'Не привязан к информационной карточке']
    );
}

if ($model->is_adult) {
    $statusItems[] = \yii\helpers\Html::tag(
        'span',
        '18+',
        [
            'class' => 'sx-status sx-status--danger',
            'title' => 'Этот раздел содержит информацию для взрослых',
        ]
    );
}
if (!$model->isAllowIndex) {
    $statusItems[] = \yii\helpers\Html::tag(
        'span',
        'noindex',
        [
            'class' => 'sx-status sx-status--warning',
            'title' => 'Этот товар не индексируется поисковыми системами',
        ]
    );
}
if ($model->sx_id) {
    $statusItems[] = \yii\helpers\Html::a($apiIcon, $apiMarketUrl, [
        'class' => 'sx-status',
        'target' => '_blank',
        'data-pjax' => '0',
        'data-toggle' => 'tooltip',
        'title' => $apiIconTitle,
        'onclick' => 'event.stopPropagation();',
    ]);
}

$related = '';
if ($model->tree_id) {
    $related = \skeeks\cms\backend\widgets\AjaxControllerActionsWidget::widget([
        'controllerId' => "/cms/admin-tree",
        'modelId'      => $model->cmsTree->id,
        'content'      => '<i class="far fa-folder"></i> '.\yii\helpers\Html::encode($model->cmsTree->name),
        'options'      => [
            'title' => $model->cmsTree->fullName,
            'class' => 'sx-preview-card__related',
        ],
    ]);
}

$tradeOffers = $model->shopProduct->getTradeOffers()->count();
?>
<div class="sx-preview-card sx-preview-card--file">
    <div class="sx-preview-card__media">
        <a href="#" class="sx-preview-card__media-link sx-trigger-action">
            <img
                src="<?= \yii\helpers\Html::encode($imageSrc); ?>"
                class="sx-photo sx-img-size-50"
                alt=""
            />
        </a>
    </div>
    <div class="sx-preview-card__content sx-collection-cell sx-collection-cell--stack">
        <a
            class="sx-preview-card__title sx-collection-cell__primary sx-trigger-action"
            href="#"
            title="id: <?= (int)$model->id; ?>"
            data-toggle="tooltip"
        ><?= \yii\helpers\Html::encode($title); ?></a>

        <? if ($statusItems) : ?>
            <div class="sx-preview-card__statuses"><?= implode('', $statusItems); ?></div>
        <? endif; ?>

        <?= $related; ?>

        <? if ($tradeOffers) : ?>
            <a href="#" class="sx-offers-trigger sx-preview-card__related">
                <i class="fab fa-product-hunt"></i> Модификации (<?= (int)$tradeOffers; ?>)
            </a>
        <? endif; ?>
    </div>
</div>
