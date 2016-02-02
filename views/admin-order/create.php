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
/* @var $model \skeeks\cms\shop\models\ShopOrder */
$shopFuser = \Yii::$app->shop->adminShopFuser;
//\Yii::$app->shop->setShopFuser($shopFuser);
//$shopFuser->user_id = \Yii::$app->user->id;



$this->registerJs(<<<JS
(function(sx, $, _)
{
    sx.classes.CreateOrder = sx.classes.Component.extend({

        _init: function()
        {

        },

        _onDomReady: function()
        {
            this.jQueryUser = $("#shopfuser-user_id");
            this.jQueryUser.on('change', function()
            {

            });
        },
    });


})(sx, sx.$, sx._);
JS
);

?>


<? if (!\Yii::$app->shop->shopPersonTypes) : ?>
    <div class="panel panel-danger">
        <div class="panel-body">
            <strong>Магазин не настроен.</strong><br />
            В настоящий момент магазин не настроен, не найдены типы плательщиков.
        </div>
    </div>
<? else : ?>

    <? if (\Yii::$app->shop->shopFuser->personType || \Yii::$app->shop->shopFuser->buyer) : ?>
        <hr />
        <?= \skeeks\cms\shop\widgets\ShopPersonTypeFormWidget::widget([]) ?>
    <? endif; ?>


    <?php $form = ActiveForm::begin([
        'pjaxOptions' =>
        [
            'id' => 'sx-pjax-order-wrapper'
        ]
    ]); ?>


        <?= \skeeks\cms\modules\admin\widgets\BlockTitleWidget::widget([
            'content' => \skeeks\cms\shop\Module::t('app', 'Buyer')
        ])?>


        <?= $form->fieldSelect($shopFuser, 'user_id', \yii\helpers\ArrayHelper::map(
            \skeeks\cms\models\CmsUser::find()->all(), 'id', 'displayName'
        )); ?>


        <?= $form->fieldSelect($model, 'person_type_id', \yii\helpers\ArrayHelper::map(
            \Yii::$app->shop->shopPersonTypes, 'id', 'name'
        )); ?>



        <?= \skeeks\cms\modules\admin\widgets\BlockTitleWidget::widget([
            'content' => \skeeks\cms\shop\Module::t('app', 'Payment order')
        ])?>

        <?=
            $form->fieldSelect($model, 'pay_system_id', \yii\helpers\ArrayHelper::map(
                \Yii::$app->shop->shopFuser->paySystems, 'id', 'name'
            ));
        ?>

        <?= $form->field($model, 'comments')->textarea([
            'rows' => 5
        ])->hint(\skeeks\cms\shop\Module::t('app', 'Internal comment, the customer (buyer) does not see'))?>

    <?= $form->buttonsCreateOrUpdate($model); ?>
    <?php ActiveForm::end(); ?>
<? endif; ?>