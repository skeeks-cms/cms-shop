<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model \skeeks\cms\shop\models\ShopOrder */
?>

<? if ($model->payed == 'Y') : ?>
    <a href="#sx-payment-container" class="btn btn-primary sx-fancybox">Изменить<a>

    <div style="display: none;">
        <div id="sx-payment-container" style="min-width: 500px;">


            <?php $form = \skeeks\cms\base\widgets\ActiveFormAjaxSubmit::begin(); ?>

                <?= $form->fieldSelect($model, 'status_code', \yii\helpers\ArrayHelper::map(
                    \skeeks\cms\shop\models\ShopOrderStatus::find()->all(), 'code', 'name'
                )); ?>



            <?php \skeeks\cms\base\widgets\ActiveFormAjaxSubmit::end(); ?>

        </div>
    </div>
<? else : ?>
    <a href="#sx-payment-container" class="btn btn-primary sx-fancybox">Оплатить<a>

    <div style="display: none;">
        <div id="sx-payment-container" style="min-width: 500px;">
            <h2>Оплата заказа:</h2><hr />
            <?php $form = \skeeks\cms\base\widgets\ActiveFormAjaxSubmit::begin(); ?>

                <?= $form->fieldSelect($model, 'status_code', \yii\helpers\ArrayHelper::map(
                    \skeeks\cms\shop\models\ShopOrderStatus::find()->all(), 'code', 'name'
                )); ?>

                <?= $form->field($model, 'pay_voucher_num'); ?>
                <?= $form->field($model, 'pay_voucher_at'); ?>

                <button class="btn btn-primary">Сохранить</button>

            <?php \skeeks\cms\base\widgets\ActiveFormAjaxSubmit::end(); ?>

        </div>
    </div>
<? endif; ?>

