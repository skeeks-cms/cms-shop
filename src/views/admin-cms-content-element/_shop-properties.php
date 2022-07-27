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
    'id' => $action->id,
]);

$this->registerJs(<<<JS
(function(sx, $, _)
{
    sx.classes.MultiRP = sx.classes.Component.extend({

        _onDomReady: function()
        {
            var self = this;
            this.jWrapper = $("#" + this.get('id'));
            this.jForm = $('form', this.jWrapper);
            this.jSelect = $('.sx-select', this.jWrapper);

            this.jSelect.on('change', function()
            {
                $(".sx-multi-shop", self.jForm).slideUp();

                if (self.jSelect.val())
                {
                    self.jForm.show();
                } else
                {
                    self.jForm.hide();
                }

                _.each(self.jSelect.val(), function(element)
                {
                    $(".sx-multi-shop-" + element, self.jForm).slideDown();

                });
            });
        }
    });

    new sx.classes.MultiRP({$jsData});
})(sx, sx.$, sx._);
JS
);
?>
<div id="<?= $action->id; ?>">
    <?php if ($action->controller && $action->controller->content) : ?>

        <?php $content = $action->controller->content; ?>
        <?php $element = new \skeeks\cms\shop\models\ShopProduct(); ?>
        <?php $element->loadDefaultValues(); ?>


        <?php if ($element) : ?>

            <?php $form = \skeeks\cms\modules\admin\widgets\ActiveForm::begin([
                'options' => [
                    'class' => 'sx-form',
                ],
            ]); ?>

            <?

            $items = [
                //'quantity' => \yii\helpers\ArrayHelper::getValue($element->attributeLabels(), 'quantity'),
            ];

            if (\Yii::$app->skeeks->site->shopTypePrices) {
                foreach (\Yii::$app->skeeks->site->shopTypePrices as $typePrice) {
                    $items["price-".$typePrice->id] = $typePrice->name;
                }
            }

            echo \skeeks\cms\widgets\Select::widget([
                'multiple' => true,
                'name'     => 'fields',
                'options'  => [
                    'class' => 'sx-select',
                ],
                'items'    => $items,
            ]); ?>

            <?= \yii\helpers\Html::hiddenInput('content_id', $content->id); ?>

            <?php /*foreach ($element->toArray() as $key => $value) : */ ?>
            <div class="sx-multi-shop sx-multi-shop-quantity" style="display: none;">
                <?
                echo $form->field($element, "quantity");
                ?>
            </div>
            <?php /*endforeach; */ ?>

            <? if (\Yii::$app->skeeks->site->shopTypePrices) : ?>
                <? foreach (\Yii::$app->skeeks->site->shopTypePrices as $shopTypePrice) : ?>

                    <div class="sx-multi-shop sx-multi-shop-price-<?= $shopTypePrice->id; ?>" style="display: none;">
                        <div class="form-group">
                            <div class="row">
                                <div class="col-9">
                                    <input name="price[<?= $shopTypePrice->id; ?>][value]" class="form-control" placeholder="<?= $shopTypePrice->name; ?> — значение" value=""/>
                                </div>
                                <div class="col-3">
                                    <?= \yii\helpers\Html::listBox("price[{$shopTypePrice->id}][currency]", \Yii::$app->money->currencyCode,
                                        \yii\helpers\ArrayHelper::map(
                                            \Yii::$app->money->activeCurrencies, 'code', 'code'
                                        ), [
                                            'class' => 'form-control',
                                            'size'  => 1,
                                        ]
                                    ); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <? endforeach; ?>

            <? endif; ?>

            <?= $form->buttonsStandart($model, ['apply']); ?>
            <?php $form::end(); ?>
        <?php else
            : ?>
            Not found properties
        <?php endif;
        ?>
    <?php endif; ?>
</div>



