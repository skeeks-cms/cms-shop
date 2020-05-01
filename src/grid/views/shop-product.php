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
    <!--Товар привязан к главному-->
<? if ($model->shopProduct->isSubProduct) : ?>
    <div class="d-flex flex-row">

        <? if ($model->shopProduct->main_pid) : ?>
            <div class="my-auto text-center" style="margin-right: 5px;">
                <?
                \skeeks\cms\backend\widgets\AjaxControllerActionsWidget::begin([
                    'controllerId' => "/shop/admin-cms-content-element",
                    'modelId'      => $model->shopProduct->shopMainProduct->id,
                    'options'      => [
                        'style' => 'color: gray; text-align: left;',
                        'class' => '',
                    ],
                ]);
                ?>
                <span style="color: green; font-size: 17px;">
                        <i class="fas fa-link" style="width: 20px;" title="Привязан к главному товару! <?= $model->shopProduct->shopMainProduct->asText; ?>"></i>
                    </span>
                <? \skeeks\cms\backend\widgets\AjaxControllerActionsWidget::end(); ?>
            </div>
        <? elseif ($model->cmsSite->shopSite->is_supplier) : ?>
            <div class="my-auto text-center" style="margin-right: 5px;">
                <span style="color: red; font-size: 17px;">
                    <i class="fas fa-link" style="width: 20px;" title="Привязан к главному товару"></i>
                </span>
            </div>
        <? endif; ?>


        <div class="my-auto text-center d-flex flex-row" style="margin-right: 5px; width: 50px; height: 50px; min-width: 50px; min-height: 50px;">
            <?
            $image = null;
            if ($model->image) {
                $image = $model->image;
            } elseif ($model->shopProduct->main_pid) {
                $image = $model->shopProduct->shopMainProduct->cmsContentElement->image;
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

    <? if ($model->shopProduct->shopSupplierProducts) : ?>

        <? foreach ($model->shopProduct->shopSupplierProducts as $shopSupplierProduct) : ?>

            <div style="margin-top: 5px; margin-left: 20px; color: gray;">
                <?
                \skeeks\cms\backend\widgets\AjaxControllerActionsWidget::begin([
                    'controllerId' => "/shop/admin-cms-content-element-sub",
                    'modelId'      => $shopSupplierProduct->id,
                    'options'      => [
                        'style' => 'color: gray; text-align: left;',
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


<div class="sx-product-controls">
    <? if ($model->shopProduct->tradeOffers) : ?>
        <a href="#" class="sx-offers-trigger" style="border-bottom: 1px dashed;"><i class="fab fa-product-hunt"></i> Предложения (<?= count($model->shopProduct->tradeOffers); ?>)</a>
    <? endif; ?>
    
    <? if ($model->shopProduct->shopSupplierProducts) : ?>
        <a href="#" class="sx-supplier-trigger" style="border-bottom: 1px dashed;"><i class="fab fa-product-hunt"></i> Кто поставляет (<?= count($model->shopProduct->shopSupplierProducts); ?>)</a>
    <? endif; ?>
    
    <? if ($model->shopProduct->shopSellerProducts) : ?>
        <a href="#" class="sx-seller-trigger" style="border-bottom: 1px dashed;"><i class="fab fa-product-hunt"></i> Где продается (<?= count($model->shopProduct->shopSellerProducts); ?>)</a>
    <? endif; ?>
    
</div>


<? if ($model->shopProduct->tradeOffers) : ?>
    <div class="sx-hidden-wrapper sx-offers-wrapper">
        <? foreach ($model->shopProduct->tradeOffers as $tradeOffer) : ?>
            <div>

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
<? endif; ?>