<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 14.10.2015
 */
/* @var $this yii\web\View */
/* @var $action \skeeks\cms\modules\admin\actions\modelEditor\AdminMultiDialogModelEditAction */
/* @var $content \skeeks\cms\models\CmsContent */
/* @var $element \skeeks\cms\shop\models\ShopCmsContentElement */

$model = new \skeeks\cms\models\CmsContentElement();


$jsData = \yii\helpers\Json::encode([
    'id'       => $action->id,
    'post-url' => \yii\helpers\Url::to(['/shop/tools/print-price']),
]);

$this->registerJs(<<<JS
(function(sx, $, _)
{
    sx.classes.PriceTags = sx.classes.Component.extend({

        _onDomReady: function()
        {
            var self = this;
            
            this.actionComponent = null;
            
            sx.components.forEach(function(obj) {
                if (obj.id == "price-tags") {
                    self.actionComponent = obj;
                } 
            });
            
            
            $(".sx-print-prices").on("click", function() {
                var selected = self.actionComponent.Grid.getDataForRequest();
                
                var oldJForm = $("#sx-print-price-form");
                var newJForm = $("#sx-print-price-form").clone();
                $("body").append(newJForm);
                
                $("#sx-print-template", newJForm).val($("#sx-print-template", oldJForm).val());
                $('textarea', newJForm).empty().append(selected.pk.join(","))
                
                newJForm.submit();
                newJForm.remove();
                
                return false;
            });
        },
        
    });

    new sx.classes.PriceTags({$jsData});
})(sx, sx.$, sx._);
JS
);
?>
<div id="<?= $action->id; ?>">
    <?php if ($action->controller && $action->controller->content) : ?>
        <div class="row">
            <div class="col-12">
                <div style="text-align: center; margin-bottom: 20px; color: #ED1B2F;">Для корректной печати ценников отключите блокирующие плагины</div>
                <form method="post" id="sx-print-price-form" action="<?php echo \yii\helpers\Url::to(['/shop/tools/print-price']); ?>" target="_blank" name="post">
                    <!--<div class="form-group">
                        <div class="btn-group sx-printer-type" role="group">
                            <button type="button" class="btn btn-default btn-lg">Принтер формата А4</button>
                            <button type="button" class="btn btn-default btn-lg">Принтер этикеток</button>
                        </div>
                    </div>-->

                    <div class="form-group">


                        <select id="sx-print-template" name='template' class="form-control">
                            <option value="30x20" selected>30x20 мм (маленький стандартный)</option>
                            <option value="50x40">50x40 см (28 на странице А4, вертикального расположения)</option>
                            <option value="58x40">58x40 см (24 на странице А4, альбомного расположения)</option>
                            <option value="70x50">70x50 см (16 на странице А4, альбомного расположения)</option>
                            <option value="58x30">58x30 см</option>
                            <option value="50x30">50x30 см</option>
                            <option value="40x30">40x30 см</option>
                        </select>

                    </div>
                    <div style="display: none;">
                        <textarea name="ids"></textarea>
                    </div>

                    <div class="form-group">
                        <input type="checkbox" name="is-print-price" id="is-print-price" checked/>
                        <label for="is-print-price">Печатать цену на ценнике</label>
                    </div>
                    <div class="form-group">
                        <input type="checkbox" name="is-print-barcode" id="is-print-barcode" checked/>
                        <label for="is-print-barcode">Печатать штрихкод на ценнике</label>
                    </div>

                    <div class="form-group">
                        <input type="checkbox" name="is-print-qrcode" id="is-print-qrcode"/>
                        <label for="is-print-qrcode">Печатать qrcode со ссылкой на товар</label>
                    </div>
                    <div class="form-group">
                        <input type="checkbox" name="is-print-spec" id="is-print-spec"/>
                        <label for="is-print-spec">Печать на принтере этикеток</label>
                    </div>
                    <a href="#" class="btn btn-primary sx-print-prices btn-lg">Сформировать ценники</a>
                </form>
            </div>
        </div>

    <?php endif; ?>
</div>



