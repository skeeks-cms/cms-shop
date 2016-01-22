<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (ÑêèêÑ)
 * @date 28.08.2015
 */
use yii\helpers\Html;
use skeeks\cms\modules\admin\widgets\form\ActiveFormUseTab as ActiveForm;

/* @var $this yii\web\View */
/* @var $model \skeeks\cms\shop\models\ShopOrder */
?>

<?php $form = ActiveForm::begin(); ?>


    <?= \skeeks\cms\modules\admin\widgets\BlockTitleWidget::widget([
        'content' => \skeeks\cms\shop\Module::t('app', 'Buyer')
    ])?>


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
