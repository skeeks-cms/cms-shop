<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */
/**
 * @var $this yii\web\View
 * @var $widget \skeeks\cms\shop\delivery\simple\SimpleCheckoutWidget
 * @var $checkoutModel \skeeks\cms\shop\delivery\pickup\SimpleCheckoutModel
 */
$widget = $this->context;
$checkoutModelCurrent = $widget->deliveryHandler->checkoutModel;
$checkoutModel = $widget->shopOrder->deliveryHandlerCheckoutModel;

if (!$checkoutModel instanceof $checkoutModelCurrent) {
    $checkoutModel = $checkoutModelCurrent;
}

$q = \skeeks\cms\shop\models\ShopStore::find()->sort()->active()->isSupplier(false);
$this->registerJs(<<<JS

$(".sx-simple-widget").on("click", ".btn-check", function() {
    $(".btn-check", $(".sx-simple-widget")).removeClass("sx-checked");
    $(".sx-checked-icon", $(".sx-simple-widget")).empty();
    $(this).addClass("sx-checked");
    $(".sx-checked-icon", $(this)).append($(".sx-checked-icon", $(this)).data("icon")).append(" ");
    
    $("#simplecheckoutmodel-cms_user_address_id").val($(this).data("id")).change();
});

//Происходит когда пользователь меняет способ доставки в заказе
//Тут можно дополнительно сделать расчет цены и отправить данные

sx.classes.SimpleWidget = sx.classes.Component.extend({

    _init: function()
    {},
    
    _onDomReady: function()
    {
        var self = this;
        
        this.getJForm().on("change-delivery", function() {
            $(this).submit();
        });
        
        $("#simplecheckoutmodel-cms_user_address_id").on("change", function () {
            var jAddressData = $(".sx-address[data-id='" + $(this).val() + "']");
            
            $("#simplecheckoutmodel-address").val(jAddressData.data("address"));
            $("#simplecheckoutmodel-latitude").val(jAddressData.data("latitude"));
            $("#simplecheckoutmodel-longitude").val(jAddressData.data("longitude"));
            $("#simplecheckoutmodel-entrance").val(jAddressData.data("entrance"));
            $("#simplecheckoutmodel-floor").val(jAddressData.data("floor"));
            $("#simplecheckoutmodel-apartment_number").val(jAddressData.data("apartment_number"));
            $("#simplecheckoutmodel-comment").val(jAddressData.data("comment"));
            
            $("#simplecheckoutmodel-address").trigger("change");
            
            $("input, textarea", $(".sx-address-fields")).each(function() {
                
                var jElement = $(this);
                if (jElement.closest(".js-float-label-wrapper").length) {
                    if (jElement.val()) {
                        setTimeout(function() {
                            jElement.closest(".js-float-label-wrapper").addClass("populated");
                        }, 200);
                                
                    } else {
                        jElement.closest(".js-float-label-wrapper").removeClass("populated");
                    }
                }
            });
            
            setTimeout(function() {
                self.getJForm().submit();
            }, 300);
        });
        
        $("select, input, textarea", ".sx-address-fields").on("change", function () {
            
            //Если выбран адрес пользователя
            if ($("#simplecheckoutmodel-cms_user_address_id").val()) {
                 var jAddressData = $(".sx-address[data-id='" + $("#simplecheckoutmodel-cms_user_address_id").val() + "']");
                 if (
                     $("#simplecheckoutmodel-address").val() != jAddressData.data("address") 
                    || $("#simplecheckoutmodel-entrance").val() != jAddressData.data("entrance") 
                    || $("#simplecheckoutmodel-floor").val() != jAddressData.data("floor") 
                    || $("#simplecheckoutmodel-apartment_number").val() != jAddressData.data("apartment_number") 
                 ) {
                     $("#simplecheckoutmodel-cms_user_address_id").val("");
                    $(".btn-check", $(".sx-simple-widget")).removeClass("sx-checked");
                    $(".sx-checked-icon", $(".sx-simple-widget")).empty();
                 }
            }
            
                
            
            
            setTimeout(function() {
                self.getJForm().submit();
            }, 300);
        });
    },
    
    getJForm: function()
    {
        return $("form", ".sx-simple-widget");
    }
});
 
new sx.classes.SimpleWidget();


JS
);
$this->registerCss(<<<CSS

.sx-simple-widget .sx-checked-icon {
    margin-right: 5px;
}

.populated .sx-trigger-show-map {
    font-size: 10px;
    top: 4px;
}

.sx-address {
    text-align: left;
}

CSS
);
?>

<div class="sx-simple-widget">
    <?php $form = \yii\bootstrap\ActiveForm::begin([
        'enableClientValidation' => false,
    ]); ?>
    
    <div class="sx-hidden cms-user-field">
        <?php echo $form->field($checkoutModel, 'cms_user_address_id'); ?>
    </div>
    <?php if ($cmsUser = $widget->shopOrder->cmsUser) : ?>
        <?php if ($cmsUser->cmsUserAddresses) : ?>
            <div class="row">
                <div class="col-12">
                    <? foreach ($cmsUser->cmsUserAddresses as $cmsUserAddress) : ?>
                        <div class="sx-address btn btn-block btn-check <?php echo $checkoutModel->cms_user_address_id == $cmsUserAddress->id ? "sx-checked" : ""; ?>" 
                             data-id="<?php echo $cmsUserAddress->id; ?>"
                             data-address="<?php echo $cmsUserAddress->value; ?>"
                             data-latitude="<?php echo $cmsUserAddress->latitude; ?>"
                             data-longitude="<?php echo $cmsUserAddress->longitude; ?>"
                             data-entrance="<?php echo $cmsUserAddress->entrance; ?>"
                             data-floor="<?php echo $cmsUserAddress->floor; ?>"
                             data-apartment_number="<?php echo $cmsUserAddress->apartment_number; ?>"
                             data-comment="<?php echo $cmsUserAddress->comment; ?>"
                        >
                            <div class="d-flex">
        
                            <span class="sx-checked-icon my-auto" data-icon="✓">
                                <?php echo $checkoutModel->cms_user_address_id == $cmsUserAddress->id ? "✓" : ""; ?>
                            </span>

                                <div class="sx-address-info">
                                    <div class="sx-address"><?php echo $cmsUserAddress->value; ?></div>
                                </div>


                            </div>
                        </div>
                    <? endforeach; ?>
                </div>
            </div>

        <?php endif; ?>
    <?php endif; ?>
    
    <div class="sx-address-fields">
        <?php echo $form->field($checkoutModel, 'address')->widget(
            \skeeks\cms\ya\map\widgets\YaMapDecodeInput::class,
            [
                'modelLatitudeAttr'  => 'latitude',
                'modelLongitudeAttr' => 'longitude',
            ]
        ); ?>
    
        
    
        <div class="row">
            <div class="col-md-4 col-12">
                <?php echo $form->field($checkoutModel, 'entrance'); ?>
            </div>
            <div class="col-md-4 col-12"><?php echo $form->field($checkoutModel, 'floor'); ?></div>
            <div class="col-md-4 col-12"><?php echo $form->field($checkoutModel, 'apartment_number'); ?></div>
        </div>
        <?php echo $form->field($checkoutModel, 'comment')->textarea(); ?>
        <div class="sx-hidden">
            <?php echo $form->field($checkoutModel, 'longitude'); ?>
            <?php echo $form->field($checkoutModel, 'latitude'); ?>
            <?php echo $form->field($checkoutModel, 'price'); ?>
        </div>
    </div>
    <? $form::end(); ?>
</div>
