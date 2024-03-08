<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 02.03.2016
 */
/* @var $this yii\web\View */
/* @var $widget \skeeks\cms\shop\widgets\admin\ProductbarcodeMatchesInputWidget */
/* @var $model \skeeks\cms\shop\models\ShopProduct */

$widget = $this->context;
$model = $widget->model;

$this->registerCss(<<<CSS

.sx-product-barcodes-wrapper .sx-new-barcode {
    width: 40px;
    text-align: center;
}
.sx-barcode-row {
    padding-top: 3px;
    padding-bottom: 3px;
}
.sx-barcode-row .sx-remove-row-btn {
    cursor: pointer;
    font-size: 16px;
    color: black;
    opacity: 0.5;
}
.sx-barcode-row .sx-remove-row-btn:hover {
    opacity: 0.8;
}

.popover {
    max-width: none;
}
CSS
);
?>

<?= \yii\helpers\Html::beginTag('div', $widget->wrapperOptions); ?>
    <div style="display: none;">
        <?php echo $element; ?>
    </div>

    <div class="sx-stars">
        <div class="rating-area">
            <input type="radio" id="star-5-<?php echo $widget->id; ?>" name="rating-<?php echo $widget->id; ?>" value="5">
            <label for="star-5-<?php echo $widget->id; ?>" title="Оценка «5»"></label>
            <input type="radio" id="star-4-<?php echo $widget->id; ?>" name="rating-<?php echo $widget->id; ?>" value="4">
            <label for="star-4-<?php echo $widget->id; ?>" title="Оценка «4»"></label>
            <input type="radio" id="star-3-<?php echo $widget->id; ?>" name="rating-<?php echo $widget->id; ?>" value="3">
            <label for="star-3-<?php echo $widget->id; ?>" title="Оценка «3»"></label>
            <input type="radio" id="star-2-<?php echo $widget->id; ?>" name="rating-<?php echo $widget->id; ?>" value="2">
            <label for="star-2-<?php echo $widget->id; ?>" title="Оценка «2»"></label>
            <input type="radio" id="star-1-<?php echo $widget->id; ?>" name="rating-<?php echo $widget->id; ?>" value="1">
            <label for="star-1-<?php echo $widget->id; ?>" title="Оценка «1»"></label>
        </div>
    </div>
<?= \yii\helpers\Html::endTag('div'); ?>