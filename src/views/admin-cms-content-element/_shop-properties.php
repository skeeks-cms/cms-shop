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
    'id' => $action->id
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
                ]
            ]); ?>
            <?= \skeeks\widget\chosen\Chosen::widget([
                'multiple' => true,
                'name' => 'fields',
                'options' => [
                    'class' => 'sx-select'
                ],
                'items' => [
                    'quantity' => \yii\helpers\ArrayHelper::getValue($element->attributeLabels(), 'quantity')
                ]
            ]); ?>

            <?= \yii\helpers\Html::hiddenInput('content_id', $content->id); ?>

            <?php /*foreach ($element->toArray() as $key => $value) : */?>
                <div class="sx-multi-shop sx-multi-shop-quantity" style="display: none;">
                    <?
                    echo $form->field($element, "quantity");
                    ?>
                </div>
            <?php /*endforeach; */?>

            
            <?= $form->buttonsStandart($model, ['apply']); ?>
            <?php $form::end(); ?>
        <?php else
            : ?>
            Not found properties
        <?php endif;
        ?>
    <?php endif; ?>
</div>



