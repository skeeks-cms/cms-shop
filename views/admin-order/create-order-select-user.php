<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */
use yii\helpers\Html;
use skeeks\cms\modules\admin\widgets\form\ActiveFormUseTab as ActiveForm;

/* @var $this yii\web\View */
/* @var $cmsUser \skeeks\cms\models\CmsUser */
/* @var $shopFuser \skeeks\cms\shop\models\ShopFuser */
$this->registerCss(<<<CSS
h1 a
{
    border-bottom: 1px dashed;
    text-decoration: none;
}
h1 a:hover
{
    border-bottom: 1px dashed;
    text-decoration: none;
}
CSS
);
?>
<?php $form = ActiveForm::begin([
    'id' => 'sx-change-user',
    'method' => 'get',
    'usePjax' => false,
]); ?>
<h1 style="text-align: center;">Новый заказ для покупателя: <a href="#" class="sx-change-user">выбрать</a> или <a href="#">создать</a></h1>
<hr />

<div style="text-align: center">
    <a href="#" class="btn btn-lg btn-primary sx-btn-for-me" data-me="<?= \Yii::$app->user->identity->id; ?>">Создать для меня</a>
</div>
<div style="display: none;">
    <?= \skeeks\cms\modules\admin\widgets\formInputs\SelectModelDialogUserInput::widget([
        'id'        => 'cmsUserId',
        'name'      => 'cmsUserId',
    ]); ?>
</div>

<?
$this->registerJs(<<<JS
$("#cmsUserId [name=cmsUserId]").on('change', function()
{
    $("#sx-change-user").submit();
});

$('.sx-change-user').on('click', function()
{
    $(".sx-btn-create").click();
});

$('.sx-btn-for-me').on('click', function()
{
    $("#cmsUserId [name=cmsUserId]").val($(this).data('me'));
    $("#sx-change-user").submit();
});
JS
)
?>

<?php ActiveForm::end(); ?>