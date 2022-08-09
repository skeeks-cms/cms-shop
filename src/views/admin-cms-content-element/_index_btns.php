<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */
/**
* @var $this yii\web\View
*/


$this->registerJs(<<<JS

$("#sx-is_group").on("change", function() {
    $(this).closest("form").submit();
    return false;
})



JS
);
?>

<?php \yii\bootstrap\Alert::begin([
    'closeButton' => false,
    'options'     => [
        'class' => 'alert-default',
    ],
]); ?>
<div>
    <?php $form = \yii\bootstrap\ActiveForm::begin(); ?>
        <input type="checkbox" id="sx-is_group" name="is_group" value="1" <?php echo $isGroup ? "checked": ""; ?>/>
        <label style="margin-bottom: 0px;" for="sx-is_group">Группировать товары с модификациями </label>
    <?php $form::end(); ?>
</div>
<?php \yii\bootstrap\Alert::end(); ?>