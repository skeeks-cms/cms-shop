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
?>
<!--Товар привязан к главному-->
<? if ($model->shopProduct->isSubProduct) : ?>
    <div class="d-flex flex-row">

        <? if ($model->main_cce_id) : ?>
            <div class="my-auto text-center" style="margin-right: 5px;">
                <?
                \skeeks\cms\backend\widgets\AjaxControllerActionsWidget::begin([
                    'controllerId' => "/shop/admin-cms-content-element",
                    'modelId'      => $model->main_cce_id,
                    'options'      => [
                        'style' => 'color: gray; text-align: left;',
                        'class' => '',
                    ],
                ]);
                ?>
                <span style="color: green; font-size: 17px;">
                        <i class="fas fa-link" style="width: 20px;" data-toggle="tooltip" title="Привязан к информационной карточке! <?= $model->mainCmsContentElement->asText; ?>"></i>
                    </span>
                <? \skeeks\cms\backend\widgets\AjaxControllerActionsWidget::end(); ?>
            </div>
        <? elseif ($model->cmsSite->shopSite->is_supplier || $model->cmsSite->shopSite->is_receiver) : ?>
            <div class="my-auto text-center" style="margin-right: 5px;">
                <span style="color: red; font-size: 17px;">
                    <i class="fas fa-link" style="width: 20px;" data-toggle="tooltip" title="Не привязан к информационной карточке"></i>
                </span>
            </div>
        <? endif; ?>


        <div class="my-auto text-center d-flex flex-row" style="margin-right: 5px; width: 50px; height: 50px; min-width: 50px; min-height: 50px;">
            <?
            $image = null;
            if ($model->image) {
                $image = $model->image;
            } elseif ($model->main_cce_id) {
                $image = $model->mainCmsContentElement->image;
            }
            ?>
            <div class="my-auto mx-auto text-center">
                <img src='<?= $image ? $image->src : \skeeks\cms\helpers\Image::getCapSrc(); ?>' style='max-width: 50px; max-height: 50px; border-radius: 5px;'/>
            </div>
        </div>
        <div class="my-auto d-flex flex-row" style="height: 50px;">
            <div class="my-auto">
                <div style="max-height: 40px; overflow: hidden;">
                    <a class="sx-trigger-action" href="#" title="<?= $model->asText; ?>"><?= $model->asText; ?></a>
                </div>
                <? if ($model->tree_id) : ?>
                    <div style="">
                        <?
                        \skeeks\cms\backend\widgets\AjaxControllerActionsWidget::begin([
                            'controllerId' => "/cms/admin-tree",
                            'modelId'      => $model->cmsTree->id,
                            'options'      => [
                                'title' => $model->cmsTree->fullName,
                                'class' => "",
                                'style' => "display: inline-block; color: gray; cursor: pointer; white-space: nowrap;",
                            ],
                        ]);
                        ?>
                        <i class="far fa-folder" style=""></i>
                        <?= $model->cmsTree->name; ?>
                        <? \skeeks\cms\backend\widgets\AjaxControllerActionsWidget::end(); ?>
                    </div>
                <? endif; ?>
            </div>
        </div>
    </div>

<? else : ?>
    <!-- Простой товар -->

    <div class="d-flex flex-row">
        <div class="my-auto text-center d-flex flex-row" style="margin-right: 5px; width: 50px; height: 50px; min-width: 50px; min-height: 50px;">
            <?
            $image = null;
            if ($model->image) {
                $image = $model->image;
            }
            ?>
            <div class="my-auto mx-auto text-center">
                <img src='<?= $image ? $image->src : \skeeks\cms\helpers\Image::getCapSrc(); ?>' style='max-width: 50px; max-height: 50px; border-radius: 5px;'/>
            </div>
        </div>
        <div class="my-auto d-flex flex-row" style="height: 50px;">
            <div class="my-auto">
                <div style="max-height: 40px; overflow: hidden;">
                    <a class="sx-trigger-action" href="#" title="<?= $model->asText; ?>"><?= $model->asText; ?></a>
                </div>
                <? if ($model->tree_id) : ?>
                    <div style="">
                        <?
                        \skeeks\cms\backend\widgets\AjaxControllerActionsWidget::begin([
                            'controllerId' => "/cms/admin-tree",
                            'modelId'      => $model->cmsTree->id,
                            'options'      => [
                                'title' => $model->cmsTree->fullName,
                                'class' => "",
                                'style' => "display: inline-block; color: gray; cursor: pointer; white-space: nowrap;",
                            ],
                        ]);
                        ?>
                        <i class="far fa-folder" style=""></i>
                        <?= $model->cmsTree->name; ?>
                        <? \skeeks\cms\backend\widgets\AjaxControllerActionsWidget::end(); ?>
                    </div>
                <? endif; ?>
            </div>
        </div>
    </div>

<? endif; ?>


<div class="sx-product-controls">
    <? if ($tradeOffers = $model->shopProduct->getTradeOffers()->count()) : ?>
        <a href="#" class="sx-offers-trigger" style="border-bottom: 1px dashed;"><i class="fab fa-product-hunt"></i> Модификации (<?= $tradeOffers; ?>)</a>
    <? endif; ?>
</div>



