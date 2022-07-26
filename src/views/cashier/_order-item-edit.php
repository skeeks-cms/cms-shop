<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */
/**
 * @var $this yii\web\View
 * @var $model \skeeks\cms\shop\models\ShopOrderItem
 */
?>
<div class="sx-order-item-edit" data-id="<?php echo $model->id; ?>">
    <div class="edit-order-item-head">

        <div class="image-wrapper" style="height: 50px; width: 60px; border-radius: 4px;">
            <?php if ($model->image) : ?>
                <img class="image" src="data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw=="
                     style="background: url('<?= \Yii::$app->imaging->thumbnailUrlOnRequest($model->image->src,
                         new \skeeks\cms\components\imaging\filters\Thumbnail([
                             'w' => 230,
                             'h' => 150,
                             'm' => \Imagine\Image\ManipulatorInterface::THUMBNAIL_INSET,
                         ])
                     ); ?>'); background-repeat: no-repeat; background-position: center; background-size: cover;"
                >
            <?php else : ?>
                <img class="image" src="data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==">
            <?php endif; ?>
            <i class="animate fa icon fa-image fa-fw" style="font-size: 30px;"></i>
        </div>

        <div class="sx-title">
            <h4><?php echo $model->name; ?></h4>
            <?php if ($model->shopProduct) : ?>
                <span>
                <i class="fa icon fa-asterisk fa-fw"></i> <?php echo $model->shopProduct->id; ?>
            </span>
                <?php if ($barcodes = $model->shopProduct->shopProductBarcodes) : ?>
                    <span class="barcode"><i class="fa icon fa-barcode fa-fw"></i> <?php echo implode(",", \yii\helpers\ArrayHelper::map($barcodes, 'id', 'value')); ?></span>
                <?php endif; ?>
            <?php endif; ?>

        </div>
        <div>
            <?php if($model->shopProduct) : ?>
                <a class="btn btn-secondary" title="Открыть карточку на сайте" target="_blank" href="<?php echo $model->shopProduct->cmsContentElement->url; ?>"><i class="fas fa-external-link-alt"></i></a>
            <?php endif; ?>
        </div>
    </div>
    <div class="edit-order-item-content">
        <form class="ui big form">
            <div class="form-group">
                <label>Цена</label>
                <div class="input-group mb-3">
                    <input class="form-control" name="amount" type="text" value="<?php echo (float)$model->amount; ?>">
                    <div class="input-group-append">
                        <span class="input-group-text" id="basic-addon2"><?php echo $model->money->currency->symbol; ?></span>
                    </div>
                </div>
            </div>


            <div class="form-group sx-quantity-wrapper">
                <label>
                    Количество, <?= $model->measure_name; ?>
                </label>
                <div class="d-flex sx-quantity-input sx-quantity-group">
                    <button class="ui red basic icon button sx-minus"><i class="fa icon fa-minus fa-fw"></i></button>
                    <div class="" style="width: 100%; margin: 0 10px;">
                        <input
                                name="quantity"
                                value="<?= (float)$model->quantity; ?>"
                                class="sx-quantity-input form-control sx-quantity-input sx-basket-quantity"
                                data-measure_ratio="<?= $model->shopProduct ? $model->shopProduct->measure_ratio : ""; ?>"
                                data-measure_ratio_min="<?= $model->shopProduct ? $model->shopProduct->measure_ratio_min : ""; ?>"
                                data-basket_id="<?= $model->id; ?>"
                        />
                    </div>
                    <button class="ui green basic icon button sx-plus"><i class="fa icon fa-plus fa-fw"></i></button>
                </div>
            </div>
            <div class="row">
                <div class="col-6">
                    <div class="eight wide field"><label>Скидка</label>
                        <div class="ui right labeled input">
                            <div class="input-group mb-3">
                                <input type="text" name="discount_percent" class="form-control" value="<?php echo $model->discount_percent_round; ?>">
                                <div class="input-group-append">
                                    <span class="input-group-text" id="basic-addon2">%</span>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="eight wide field"><label>&nbsp;</label>
                        <div class="ui right labeled input">
                            <div class="input-group mb-3">
                                <input type="text" name="discount_amount" class="form-control form-control-lg" value="<?php echo $model->discount_amount; ?>">
                                <div class="input-group-append">
                                    <span class="input-group-text" id="basic-addon2"><?php echo $model->money->currency->symbol; ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <div class="edit-order-item-footer">
        <div class="sx-order-item-total">
            Итог: <span class="sx-order-item-total-money-amount-with-discount"><strong class="sx-amount">
                        <?php echo \Yii::$app->formatter->asDecimal($model->totalMoneyWithDiscount->amount); ?>
                    </strong><?php echo $model->money->currency->symbol; ?></span>
                    <span class="sx-order-item-total-money-amount <?php echo $model->discount_percent ? "": "sx-hidden"; ?>"><strong class="sx-amount">
                        <?php echo \Yii::$app->formatter->asDecimal($model->totalMoney->amount); ?>
                    </strong><?php echo $model->money->currency->symbol; ?></span>


        </div>
        <button class="ui huge basic button sx-close-modal">Закрыть</button>
    </div>
</div>
