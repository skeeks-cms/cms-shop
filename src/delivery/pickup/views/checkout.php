<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */
/**
 * @var $this yii\web\View
 * @var $widget \skeeks\cms\shop\delivery\pickup\ickupCheckoutWidget
 * @var $checkoutModel \skeeks\cms\shop\delivery\pickup\PickupCheckoutModel
 */
$widget = $this->context;
$checkoutModelCurrent = $widget->deliveryHandler->checkoutModel;
$checkoutModel = $widget->shopOrder->deliveryHandlerCheckoutModel;
if (!$checkoutModel instanceof $checkoutModelCurrent) {
    $checkoutModel = $checkoutModelCurrent;
}


/*if (!$checkoutModel->shop_store_id) {

}*/

$q = \skeeks\cms\shop\models\ShopStore::find()->sort()->active()->isSupplier(false);
$this->registerJs(<<<JS

$(".sx-pickup-widget").on("click", ".btn-check", function() {
    $(".btn-check", $(".sx-pickup-widget")).removeClass("sx-checked");
    $(".sx-checked-icon", $(".sx-pickup-widget")).empty();
    $(this).addClass("sx-checked");
    $(".sx-checked-icon", $(this)).append($(".sx-checked-icon", $(this)).data("icon")).append(" ");
    
    $("#pickupcheckoutmodel-shop_store_id").val($(this).data("id")).trigger("change");
});

//Происходит когда пользователь меняет способ доставки в заказе
//Тут можно дополнительно сделать расчет цены и отправить данные


sx.classes.PickupWidget = sx.classes.Component.extend({

    _init: function()
    {},
    
    _onDomReady: function()
    {
        var self = this;
        
        //Когда доставка изменилась
        this.getJForm().on("change-delivery", function() {
            $(this).submit();
        });
        
        $("select, input, textarea", this.getJForm()).on("change", function () {
            self.getJForm().submit();
        });
    },
    
    getJForm: function()
    {
        return $("form", ".sx-pickup-widget");
    }
});
 
new sx.classes.PickupWidget();

JS
);
$this->registerCss(<<<CSS

.sx-pickup-widget .sx-checked-icon {
    margin-right: 5px;
}

CSS
);
?>
<?php if ($shopStores = $q->all()) : ?>

    <?php
    /**
     * @var $shopStore \skeeks\cms\shop\models\ShopStore
     */
    ?>
    <div class="sx-pickup-widget">

        <div style="display: none;">
            <?php $form = \yii\widgets\ActiveForm::begin([
                'enableClientValidation' => false,
            ]); ?>
            <?php echo $form->field($checkoutModel, 'shop_store_id') ?>
            <?php echo $form->field($checkoutModel, 'price') ?>
            <? $form::end(); ?>
        </div>

        <? foreach ($shopStores as $shopStore) : ?>
            <div class="sx-store btn btn-block btn-check <?php echo $checkoutModel->shop_store_id == $shopStore->id ? "sx-checked" : ""; ?>" data-id="<?php echo $shopStore->id; ?>">
                <div class="d-flex">

                    <span class="sx-checked-icon" data-icon="✓">
                        <?php echo $checkoutModel->shop_store_id == $shopStore->id ? "✓" : ""; ?>
                    </span>

                    <div class="sx-address-info">
                        <?php /*if($shopStore->name) : */ ?><!--
                            <div class="sx-address-name"><?php /*echo $shopStore->name; */ ?></div>
                        --><?php /*endif; */ ?>
                        <div class="sx-address"><?php echo $shopStore->address; ?></div>
                    </div>


                </div>
            </div>
        <? endforeach; ?>
    </div>
<?php endif; ?>