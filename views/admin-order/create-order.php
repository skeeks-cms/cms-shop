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
#sx-change-user
{
    margin-bottom: 10px;
}
CSS
);
\Yii::$app->shop->setShopFuser($shopFuser);
?>

<?php $form = ActiveForm::begin([
    'id' => 'sx-change-user',
    'method' => 'get',
    'usePjax' => false,
]); ?>
<h1 style="text-align: center;">Новый заказ для покупателя: <a href="#" class="sx-change-user"><?= $shopFuser->user->displayName; ?></a></h1>

<div style="display: none;">
    <?= \skeeks\cms\modules\admin\widgets\formInputs\SelectModelDialogUserInput::widget([
        'id'        => 'cmsUserId',
        'name'      => 'cmsUserId',
    ]); ?>
</div>

<?
$this->registerJs(<<<JS
$('#cmsUserId').on('change', function()
{
    $("#sx-change-user").submit();
});

$('.sx-change-user').on('click', function()
{
    $("#cmsUserId .sx-btn-create").click();
});
JS
)
?>

<?php ActiveForm::end(); ?>

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


            <?/*= \skeeks\widget\chosen\Chosen::widget([
                'name'          => 'select-person-type',
                'id'            => 'select-person-type',
                'items'         => $shopFuser->getBuyersList(),
                'value'         => $shopFuser->buyer_id ? $shopFuser->buyer_id : (
                    $shopFuser->personType->id ? "shopPersonType-" . $shopFuser->personType->id : ""
                ),
                'placeholder'   => 'Выберите профиль покупателя',
                'allowDeselect' => false,
            ]); */?>

            <?=
                $form->field($shopFuser, 'buyer_id')->widget(
                    \skeeks\cms\widgets\formInputs\EditedSelect::className(),
                    [
                        'items' => \yii\helpers\ArrayHelper::map(
                            $shopFuser->shopBuyers, 'id', 'name'
                        ),

                        'controllerRoute'   => '/shop/admin-buyer',
                        'additionalData'    => [
                            'cms_user_id' => $shopFuser->user->id
                        ],
                        'updateAction'      => 'related-properties',
                        'allowDeselect'     => false
                    ]
                );
            ?>


    <? if ($shopFuser->buyer) : ?>
        <?= \skeeks\cms\modules\admin\widgets\BlockTitleWidget::widget([
            'content' => \skeeks\cms\shop\Module::t('app', 'Customer data')
        ])?>

            <?= \yii\widgets\DetailView::widget([
                'model' =>  $shopFuser,
                'template'   => "<tr><th style='width: 50%; text-align: right;'>{label}</th><td>{value}</td></tr>",
                'attributes' =>
                [
                    [                      // the owner name of the model
                        'label' => \skeeks\cms\shop\Module::t('app', 'Type payer'),
                        'format' => 'raw',
                        'value' => $shopFuser->personType->name,
                    ],

                    [                      // the owner name of the model
                        'label' => \skeeks\cms\shop\Module::t('app', 'Profile of buyer'),
                        'format' => 'raw',
                        'value' => Html::a( $shopFuser->buyer->name . " [{$shopFuser->buyer->id}]", \skeeks\cms\helpers\UrlHelper::construct(['/shop/admin-buyer/update', 'pk' =>  $shopFuser->buyer->id ])->enableAdmin(), [
                            'data-pjax' => 0
                        ] ),
                    ],
                ]
            ]); ?>

            <?= \yii\widgets\DetailView::widget([
                'model' => $shopFuser->buyer->relatedPropertiesModel,
                'template'   => "<tr><th style='width: 50%; text-align: right;'>{label}</th><td>{value}</td></tr>",
                'attributes' => $shopFuser->buyer->relatedPropertiesModel->attributes()
            ])?>

    <?/* elseif ($shopFuser->personType) : */?>

        <?/* $buyer = $shopFuser->personType->createModelShopBuyer(); */?><!--

        <?/* if ($properties = $buyer->relatedProperties) : */?>
            <?/* foreach ($properties as $property) : */?>
                <?/*= $property->renderActiveForm($form, $buyer); */?>
            <?/* endforeach; */?>
        --><?/* endif; */?>
    <? else : ?>
        Пользователь еще ничего не покупал на сайте. Для него необходимо завести и выбрать данные для профиля покупателя.
        <hr />
    <? endif; ?>


    <?= \skeeks\cms\modules\admin\widgets\BlockTitleWidget::widget([
        'content' => \skeeks\cms\shop\Module::t('app', 'Payment order')
    ])?>

            <?=
                $form->fieldSelect($shopFuser, 'pay_system_id', \yii\helpers\ArrayHelper::map(
                    $shopFuser->paySystems, 'id', 'name'
                ));
            ?>


    <?= \skeeks\cms\modules\admin\widgets\BlockTitleWidget::widget([
        'content' => \skeeks\cms\shop\Module::t('app', 'Shipping')
    ])?>

            <?=
                $form->fieldSelect($shopFuser, 'delivery_id', \yii\helpers\ArrayHelper::map(
                    \skeeks\cms\shop\models\ShopDelivery::find()->active()->all(), 'id', 'name'
                ));
            ?>




    <?= \skeeks\cms\modules\admin\widgets\BlockTitleWidget::widget([
            'content' => \skeeks\cms\shop\Module::t('app', 'The composition of the order')
        ])?>



<?

$json = \yii\helpers\Json::encode([
    'createUrl' => \skeeks\cms\helpers\UrlHelper::construct('/shop/admin-basket/create', [
                    'fuser_id'      => $shopFuser->id,
                ])
                ->setSystemParam(\skeeks\cms\modules\admin\Module::SYSTEM_QUERY_EMPTY_LAYOUT, 'true')
                ->setSystemParam(\skeeks\cms\modules\admin\Module::SYSTEM_QUERY_NO_ACTIONS_MODEL, 'true')
                ->enableAdmin()->toString()
]);

$onclick = new \yii\web\JsExpression(<<<JS
    new sx.classes.AddPosition({$json}).open(); return true;
JS
);
?>
    <?= \skeeks\cms\modules\admin\widgets\RelatedModelsGrid::widget([
        'label'             => "",
        'parentModel'       => $shopFuser,
        'relation'          => [
            'fuser_id'      => 'id',
        ],

        'sort'              => [
            'defaultOrder' =>
            [
                'updated_at' => SORT_DESC
            ]
        ],

        'controllerRoute'   => 'shop/admin-basket',
        'gridViewOptions'   => [
            'enabledPjax' => false,
            'beforeTableLeft' => <<<HTML
    <a class="btn btn-default btn-sm" onclick="new sx.classes.SelectProduct().open(); return true;"><i class="glyphicon glyphicon-plus"></i>Добавить товар</a>
    <a class="btn btn-default btn-sm" onclick='{$onclick}'><i class="glyphicon glyphicon-plus"></i>Добавить позицию</a>
HTML
,

            'columns' => [

                [
                    'class' => \skeeks\cms\shop\grid\BasketImageGridColumn::className(),
                ],

                [
                    'class' => \skeeks\cms\shop\grid\BasketNameGridColumn::className(),
                ],


                [
                    'class' => \skeeks\cms\shop\grid\BasketQuantityGridColumn::className(),
                ],

                [
                    'class' => \skeeks\cms\shop\grid\BasketPriceGridColumn::className(),
                ],

                [
                    'class' => \skeeks\cms\shop\grid\BasketSumGridColumn::className()
                ],
            ]
        ],
    ]); ?>



        <div class="row">
            <div class="col-md-8"></div>
            <div class="col-md-4">
                    <div class="sx-result">
                <?

                $model = $shopFuser;

                $this->registerCss(<<<CSS
.sx-result
{
    background-color: #ecf2d3;
}
CSS
);
                ?>
                <?=
                \yii\widgets\DetailView::widget([
                    'model' => $model,
                    "template" => "<tr><th>{label}</th><td style='text-align: right;'>{value}</td></tr>",
                    "options" => ['class' => 'sx-result-table table detail-view'],
                    'attributes' => [
                        [
                            'label' => \skeeks\cms\shop\Module::t('app', 'The total value of the goods'),
                            'value' => \Yii::$app->money->intlFormatter()->format($model->money),
                        ],

                        [
                            'label' => \skeeks\cms\shop\Module::t('app', 'Discount, margin'),
                            'value' => \Yii::$app->money->intlFormatter()->format($model->moneyDiscount),
                        ],

                        [
                            'label' => \skeeks\cms\shop\Module::t('app', 'Delivery service'),
                            'value' => \Yii::$app->money->intlFormatter()->format($model->moneyDelivery),
                        ],

                        [
                            'label' => \skeeks\cms\shop\Module::t('app', 'Taxe'),
                            'value' => \Yii::$app->money->intlFormatter()->format($model->moneyVat),
                        ],

                        [
                            'label' => \skeeks\cms\shop\Module::t('app', 'Weight (gramm)'),
                            'value' => $model->weight . " ".\skeeks\cms\shop\Module::t('app', 'g.'),
                        ],

                        [
                            'label' => \skeeks\cms\shop\Module::t('app', 'In total'),
                            'format' => 'raw',
                            'value' => Html::tag('b', \Yii::$app->money->intlFormatter()->format($model->money)),
                        ]
                    ]
                ])
                ?>
                    </div>
            </div>
        </div>



    <?= $form->buttonsCreateOrUpdate($shopFuser); ?>



    <?
\skeeks\cms\shop\assets\ShopAsset::register($this);

    $clientData = \yii\helpers\Json::encode([

        'backendFuserSave' => \skeeks\cms\helpers\UrlHelper::construct([
            '/shop/admin-order/create-order-fuser-save', 'shopFuserId' => $shopFuser->id
        ])->enableAdmin()->toString(),

    ]);

    $shopJson = \yii\helpers\Json::encode([

        'backend-add-product' => \skeeks\cms\helpers\UrlHelper::construct([
            '/shop/admin-order/create-order-add-product', 'shopFuserId' => $shopFuser->id
        ])->enableAdmin()->toString(),

    ]);

$this->registerJs(<<<JS
(function(sx, $, _)
{
    sx.classes.CreateOrder = sx.classes.Component.extend({

        _onDomReady: function()
        {
            var self = this;

            this.jQueryUser         = $("#shopfuser-user_id");
            this.jQueryBuyer         = $("#shopfuser-buyer_id");
            this.jQueryPaySystem   = $("#shopfuser-pay_system_id");
            this.jQueryPersonType   = $("#shoporder-person_type_id");
            this.jQueryDelivery   = $("#shopfuser-delivery_id");
            this.jQueryForm         = $("#sx-create-order");

            this.jQueryPaySystem.on('change', function()
            {
                var ajax = self.getAjaxQuery();
                ajax.setData(self.jQueryForm.serializeArray());

                var ajaxHandler = new sx.classes.AjaxHandlerStandartRespose(ajax);
                new sx.classes.AjaxHandlerNoLoader(ajax);

                ajaxHandler.bind('success', function()
                {
                    sx.CreateOrder.reload();
                });

                ajax.execute();
            });

            this.jQueryDelivery.on('change', function()
            {
                var ajax = self.getAjaxQuery();
                ajax.setData(self.jQueryForm.serializeArray());

                var ajaxHandler = new sx.classes.AjaxHandlerStandartRespose(ajax);
                new sx.classes.AjaxHandlerNoLoader(ajax);

                ajaxHandler.bind('success', function()
                {
                    sx.CreateOrder.reload();
                });

                ajax.execute();
            });

            this.jQueryBuyer.on('change', function()
            {
                var ajax = self.getAjaxQuery();
                ajax.setData(self.jQueryForm.serializeArray());

                var ajaxHandler = new sx.classes.AjaxHandlerStandartRespose(ajax);
                new sx.classes.AjaxHandlerNoLoader(ajax);

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

    sx.classes.SelectProduct = sx.classes.Component.extend({

        open: function()
        {
            $('#sx-add-product .sx-btn-create').click()
            return this;
        }
    });

    sx.classes.AdminShop = sx.classes.shop.App.extend({});
    sx.AdminShop = new sx.classes.AdminShop({$shopJson});
    sx.AdminShop.bind('addProduct', function()
    {
        sx.CreateOrder.reload();
    });


    sx.classes.AddPosition = sx.classes.Component.extend({

        open: function()
        {
            var self = this;
            var window = new sx.classes.Window(this.get('createUrl'));
            window.bind("close", function()
            {
                sx.CreateOrder.reload();
            });

            window.open();
        }
    });


})(sx, sx.$, sx._);
JS
);

    ?>



<?php ActiveForm::end(); ?>

<div style="display: none;">
    <?=
        \skeeks\cms\modules\admin\widgets\formInputs\CmsContentElementInput::widget([
            'baseRoute'     => '/shop/tools/select-cms-element',
            'name'          => 'sx-add-product',
            'id'            => 'sx-add-product',
            'closeWindow'   => false,
        ]);
    ?>
</div>

<?

$this->registerJs(<<<JS
(function(sx, $, _)
{
    _.each(sx.components, function(Component, key)
    {
        if (Component instanceof sx.classes.SelectCmsElement)
        {
            Component.bind('change', function(e, data)
            {
                sx.AdminShop.addProduct(data.id);
            });

            Component.unbind('change', function(e, data)
            {
                sx.AdminShop.addProduct(data.id);
            });
        }
    });
})(sx, sx.$, sx._);
JS
);
?>
