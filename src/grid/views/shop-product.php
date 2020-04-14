<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */
/* @var $this yii\web\View */
/* @var $model \skeeks\cms\shop\models\ShopCmsContentElement */
?>

<? if ($model->shopProduct->isSubProduct) : ?>
    <!-- Товар поставщика -->
    <div class="d-flex flex-row">
        <div class="my-auto" style="margin-right: 5px;">
            <img src='<?= $model->image ? $model->image->src : \skeeks\cms\helpers\Image::getCapSrc(); ?>' style='max-width: 50px; max-height: 50px; border-radius: 5px;'/>
        </div>
        <div class="my-auto">
            <div class="my-auto" style="margin-bottom: 5px; overflow: hidden; max-height: 40px;">
                <a class="sx-trigger-action" href="#" title="<?= $model->asText; ?>"><?= $model->asText; ?></a>
            </div>
        </div>
    </div>

    <!--<div>

        <?/*
        \skeeks\cms\backend\widgets\AjaxControllerActionsWidget::begin([
            'controllerId' => "/shop/admin-shop-supplier",
            'modelId'      => $model->shopProduct->shopSupplier->id,
        ]);
        */?>
        <i class="fas fa-truck" style="width: 20px;" title="Поставщик"></i>
        <?/*= $model->shopProduct->shopSupplier->asText; */?>
        <?/* \skeeks\cms\backend\widgets\AjaxControllerActionsWidget::end(); */?>
    </div>-->
    <? if ($model->tree_id) : ?>
        <div>
            <?
            \skeeks\cms\backend\widgets\AjaxControllerActionsWidget::begin([
                'controllerId' => "/cms/admin-tree",
                'modelId'      => $model->cmsTree->id,
                'options'      => [
                    'title' => $model->cmsTree->fullName,
                ],
            ]);
            ?>
            <i class="far fa-folder" style="width: 20px;"></i>
            <?= $model->cmsTree->name; ?>
            <? \skeeks\cms\backend\widgets\AjaxControllerActionsWidget::end(); ?>
        </div>
    <? endif; ?>

    <? if ($model->shopProduct->main_pid) : ?>
        <div>


            <?
            \skeeks\cms\backend\widgets\AjaxControllerActionsWidget::begin([
                'controllerId' => "/shop/admin-cms-content-element",
                'modelId'      => $model->shopProduct->shopMainProduct->id,
            ]);
            ?>
            <span style="color: green;">
                <i class="fas fa-link" style="width: 20px;" title="Привязан к главному товару"></i>
                <img src='<?= $model->shopProduct->shopMainProduct->cmsContentElement->image ? $model->shopProduct->shopMainProduct->cmsContentElement->image->src : \skeeks\cms\helpers\Image::getCapSrc(); ?>'
                     style='max-width: 20px; max-height: 20px; border-radius: 5px;'
                />
                <span><?= $model->shopProduct->shopMainProduct->cmsContentElement->asText; ?></span>
            </span>
            <? \skeeks\cms\backend\widgets\AjaxControllerActionsWidget::end(); ?>


        </div>
    <? else : ?>
        <div>
            <span style="color: red;"><i class="fas fa-link" style="width: 20px;" title="Привязан к главному товару"></i><span>Не привязан к главному товару!</span></span>
        </div>
    <? endif; ?>

<? elseif ($model->shopProduct->tradeOffers) : ?>
    <!-- Общий товар -->

    <div class="d-flex flex-row">
        <div class="my-auto" style="margin-right: 5px;">
            <img src='<?= $model->image ? $model->image->src : \skeeks\cms\helpers\Image::getCapSrc(); ?>' style='max-width: 50px; max-height: 50px; border-radius: 5px;'/>
        </div>
        <div class="my-auto">
            <div class="my-auto" style="margin-bottom: 5px; overflow: hidden; max-height: 40px;">
                <a class="sx-trigger-action" href="#" title="<?= $model->asText; ?>"><?= $model->asText; ?></a>
            </div>
        </div>
    </div>

    <? if ($model->tree_id) : ?>
        <?
            \skeeks\cms\backend\widgets\AjaxControllerActionsWidget::begin([
                'controllerId' => "/cms/admin-tree",
                'modelId'      => $model->cmsTree->id,
                'options'      => [
                    'title' => $model->cmsTree->fullName,
                ],
            ]);
            ?>
            <i class="far fa-folder" style="width: 20px;"></i>
            <?= $model->cmsTree->name; ?>
            <? \skeeks\cms\backend\widgets\AjaxControllerActionsWidget::end(); ?>
    <? endif; ?>

    <div style="margin-top: 5px;">
        <? foreach ($model->shopProduct->tradeOffers as $tradeOffer) : ?>
            <div style="margin-left: 20px;">

                <?
                \skeeks\cms\backend\widgets\AjaxControllerActionsWidget::begin([
                    'controllerId' => "/shop/admin-cms-content-element",
                    'modelId'      => $tradeOffer->id,
                    'options'      => [
                        'style' => 'color: #333;',
                    ],
                ]);
                ?>
                <i class="fas fa-link" title="Привязан к главному товару"></i> <?= $tradeOffer->asText; ?>
                <? \skeeks\cms\backend\widgets\AjaxControllerActionsWidget::end(); ?>
                
                <? if ($tradeOffer->shopProduct->shopSupplierProducts) : ?>


                    <div style="margin-top: 5px; margin-bottom: 5px;">
                        <? foreach ($tradeOffer->shopProduct->shopSupplierProducts as $shopSupplierProduct) : ?>
                            <div style="margin-left: 20px;">
                                <?
                                \skeeks\cms\backend\widgets\AjaxControllerActionsWidget::begin([
                                    'controllerId' => "/shop/admin-cms-content-element-sub",
                                    'modelId'      => $shopSupplierProduct->id,
                                    'options'      => [
                                        'style' => 'color: gray;',
                                    ],
                                ]);
                                ?>
                                <i class="fas fa-link" title="Привязан к главному товару"></i>
                                <i class="fas fa-truck" style="" title="Поставщик"></i> <?= $shopSupplierProduct->cmsContentElement->cmsSite->name; ?> -
                                <?= $shopSupplierProduct->asText; ?>
                                <? \skeeks\cms\backend\widgets\AjaxControllerActionsWidget::end(); ?>
                            </div>
                        <? endforeach; ?>
                    </div>
                <? endif; ?>

            </div>
        <? endforeach; ?>
    </div>
<? else : ?>
    <!-- Простой товар -->

    <div class="d-flex flex-row">
        <div class="my-auto" style="margin-right: 5px;">
            <img src='<?= $model->image ? $model->image->src : \skeeks\cms\helpers\Image::getCapSrc(); ?>' style='max-width: 50px; max-height: 50px; border-radius: 5px;'/>
        </div>
        <div class="my-auto">
            <div class="my-auto" style="margin-bottom: 5px; overflow: hidden; max-height: 40px;">
                <a class="sx-trigger-action" href="#" title="<?= $model->asText; ?>"><?= $model->asText; ?></a>
            </div>
        </div>
    </div>

    <? if ($model->tree_id) : ?>

        <div>
            <?
            \skeeks\cms\backend\widgets\AjaxControllerActionsWidget::begin([
                'controllerId' => "/cms/admin-tree",
                'modelId'      => $model->cmsTree->id,
                'options'      => [
                    'title' => $model->cmsTree->fullName,
                ],
            ]);
            ?>
            <i class="far fa-folder" style="width: 20px;"></i>
            <?= $model->cmsTree->name; ?>
            <? \skeeks\cms\backend\widgets\AjaxControllerActionsWidget::end(); ?>
        </div>
    <? endif; ?>

    <? if ($model->shopProduct->shopSupplierProducts) : ?>

        <? foreach ($model->shopProduct->shopSupplierProducts as $shopSupplierProduct) : ?>

            <div style="margin-top: 5px; margin-left: 20px; color: gray;">
                <?
                \skeeks\cms\backend\widgets\AjaxControllerActionsWidget::begin([
                    'controllerId' => "/shop/admin-cms-content-element-sub",
                    'modelId'      => $shopSupplierProduct->id,
                    'options'      => [
                        'style' => 'color: gray;',
                    ],
                ]);
                ?>
                <i class="fas fa-link" title="Привязан к главному товару"></i>
                <i class="fas fa-truck" style="" title="Поставщик"></i> <?= $shopSupplierProduct->cmsContentElement->cmsSite->name; ?> -
                <?= $shopSupplierProduct->asText; ?>
                <? \skeeks\cms\backend\widgets\AjaxControllerActionsWidget::end(); ?>
            </div>
        <? endforeach; ?>

    <? endif; ?>


<? endif; ?>

