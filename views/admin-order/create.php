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
//\Yii::$app->shop->setShopFuser($shopFuser);
//$shopFuser->user_id = \Yii::$app->user->id;
//$shopFuser->user_id = \Yii::$app->user->id;
?>


<? if (!\Yii::$app->shop->shopPersonTypes) : ?>
    <div class="panel panel-danger">
        <div class="panel-body">
            <strong>Магазин не настроен.</strong><br />
            В настоящий момент магазин не настроен, не найдены типы плательщиков.
        </div>
    </div>
<? else : ?>

    <?/* if (\Yii::$app->shop->shopFuser->personType || \Yii::$app->shop->shopFuser->buyer) : */?><!--
        <hr />
        <?/*= \skeeks\cms\shop\widgets\ShopPersonTypeFormWidget::widget([]) */?>
    --><?/* endif; */?>


    <?php $form = ActiveForm::begin([
        'id' => 'sx-create-order',
        'pjaxOptions' =>
        [
            'id' => 'sx-pjax-order-wrapper'
        ]
    ]); ?>



    <?= \skeeks\cms\modules\admin\widgets\BlockTitleWidget::widget([
        'content' => \skeeks\cms\shop\Module::t('app', 'Buyer')
    ])?>


        <?= \skeeks\widget\chosen\Chosen::widget([
            'name'          => 'select-user',
            'id'            => 'select-user',
            'items'         => (array) \yii\helpers\ArrayHelper::map(
                \skeeks\cms\models\CmsUser::find()->all(), 'id', 'displayName'
            ),
            'value'         => \Yii::$app->shop->adminUser ? \Yii::$app->shop->adminUser->id : "",
            'placeholder'   => 'Выберите пользователя',
            'allowDeselect' => false,
        ])?>


        <? if (\Yii::$app->shop->adminUser) :?>

            <?= $form->fieldSelect($shopFuser, 'user_id', \yii\helpers\ArrayHelper::map(
                \skeeks\cms\models\CmsUser::find()->all(), 'id', 'displayName'
            )); ?>


            <?/*= \skeeks\widget\chosen\Chosen::widget([
                'name'          => 'select-person-type',
                'id'            => 'select-person-type',
                'items'         => \Yii::$app->shop->shopFuser->getBuyersList(),
                'value'         => \Yii::$app->shop->shopFuser->buyer_id ? \Yii::$app->shop->shopFuser->buyer_id : (
                    \Yii::$app->shop->shopFuser->personType->id ? "shopPersonType-" . \Yii::$app->shop->shopFuser->personType->id : ""
                ),
                'placeholder'   => 'Выберите профиль покупателя',
                'allowDeselect' => false,
            ])*/?>

            <?= $form->fieldSelect($shopFuser, 'person_type_id', \yii\helpers\ArrayHelper::map(
                \Yii::$app->shop->shopPersonTypes, 'id', 'name'
            )); ?>

            <?= $form->fieldSelect($shopFuser, 'buyer_id', \yii\helpers\ArrayHelper::map(
                $shopFuser->getShopBuyers()->andWhere(['shop_person_type_id' => $shopFuser->person_type_id])->all(), 'id', 'name'
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
        <? else : ?>

        <? endif; ?>


    <?= $form->buttonsCreateOrUpdate($model); ?>


    <?

    $clientData = \yii\helpers\Json::encode([
        'backendFuserSave' => \skeeks\cms\helpers\UrlHelper::construct('/shop/admin-order/create-order-fuser-save')->enableAdmin()->toString()
    ]);

$this->registerJs(<<<JS
(function(sx, $, _)
{
    sx.classes.CreateOrder = sx.classes.Component.extend({

        _onDomReady: function()
        {
            var self = this;

            this.jQueryUser         = $("#shopfuser-user_id");
            this.jQueryPersonType   = $("#shoporder-person_type_id");
            this.jQueryForm         = $("#sx-create-order");

            this.jQueryUser.on('change', function()
            {
                var ajax = self.getAjaxQuery();
                ajax.setData(self.jQueryForm.serializeArray());

                var ajaxHandler = new sx.classes.AjaxHandlerStandartRespose(ajax);

                ajaxHandler.bind('success', function()
                {
                    sx.CreateOrder.reload();
                });

                ajax.execute();
            });

            this.jQueryPersonType.on('change', function()
            {
                var ajax = self.getAjaxQuery();
                ajax.setData(self.jQueryForm.serializeArray());

                var ajaxHandler = new sx.classes.AjaxHandlerStandartRespose(ajax);

                ajaxHandler.bind('success', function()
                {
                    sx.CreateOrder.reload();
                });

                ajax.execute();
            });
        },

        /**
        *
        * @returns {sx.classes.shop.App.ajaxQuery|Function|sx.classes.shop._App.ajaxQuery|*}
        */
        getAjaxQuery: function()
        {
            return sx.ajax.preparePostQuery(this.get('backendFuserSave'));
        },

        reload: function()
        {
            $.pjax.reload('#sx-pjax-order-wrapper', {});
        }
    });

    sx.CreateOrder = new sx.classes.CreateOrder({$clientData});

})(sx, sx.$, sx._);
JS
);

    ?>
    <?php ActiveForm::end(); ?>
<? endif; ?>