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

<?
\skeeks\cms\widgets\Pjax::begin(['id' => "sx-pjax-order-wrapper"]);
?>

<?
$this->registerCss(<<<CSS

.sx-dashed
{

    border-bottom: dashed 1px;
}

.sx-dashed:hover
{
    text-decoration: none;
}

.datetimepicker
{
    z-index: 100000 !important;
}
CSS
);


$this->registerJs(<<<JS
(function(sx, $, _)
{
    sx.classes.OrderCallback = sx.classes.Component.extend({

        construct: function (jForm, ajaxQuery, opts)
        {
            var self = this;
            opts = opts || {};

            this._jForm     = jForm;
            this._ajaxQuery = ajaxQuery;

            this.applyParentMethod(sx.classes.Component, 'construct', [opts]); // TODO: make a workaround for magic parent calling
        },

        _init: function()
        {
            var jForm   = this._jForm;
            var ajax    = this._ajaxQuery;

            var handler = new sx.classes.AjaxHandlerStandartRespose(ajax, {
                'blockerSelector' : '#' + jForm.attr('id'),
                'enableBlocker' : true,
            });

            handler.bind('success', function(response)
            {
                jForm.closest(".modal").find(".close").click();
                _.delay(function() {
                    $.pjax.reload('#sx-pjax-order-wrapper', {});
                }, 200);
            });
        }
    });

})(sx, sx.$, sx._);
JS
);

$statusDate = \Yii::$app->formatter->asDatetime($model->status_at);
?>


<?= \skeeks\cms\modules\admin\widgets\BlockTitleWidget::widget([
    'content' => \Yii::t('skeeks/shop/app', 'Order'),

]) ?>

<?= \yii\widgets\DetailView::widget([
    'model'      => $model,
    'template'   => "<tr><th style='width: 50%; text-align: right;'>{label}</th><td>{value}</td></tr>",
    'attributes' =>
        [
            /*[                      // the owner name of the model
                'label' => \Yii::t('skeeks/shop/app', 'Number of order'),
                'format' => 'raw',
                'value' => $model->id,
            ],

            [                      // the owner name of the model
                'label' => \Yii::t('skeeks/shop/app', 'Created At'),
                'format' => 'raw',
                'value' => \Yii::$app->formatter->asDatetime($model->created_at),
            ],*/

            [                      // the owner name of the model
                'label'  => \Yii::t('skeeks/shop/app', 'Last modified'),
                'format' => 'raw',
                'value'  => \Yii::$app->formatter->asDatetime($model->updated_at),
            ],

            [                      // the owner name of the model
                'label'  => \Yii::t('skeeks/shop/app', 'Status'),
                'format' => 'raw',
                'value'  => <<<HTML

                    <a href="#" data-toggle="modal" data-target="#sx-status-change" class="sx-dashed" style="color: {$model->status->color}">{$model->status->name}</a>
                    <small>({$statusDate})</small>
HTML
                ,

            ],

            [                      // the owner name of the model
                'label'  => \Yii::t('skeeks/shop/app', 'Canceled'),
                'format' => 'raw',
                'value'  => $this->render('_close-order', [
                    'model' => $model,
                ]),
            ],

            /*[                      // the owner name of the model
                'label' => \Yii::t('skeeks/shop/app', 'Date of status change'),
                'format' => 'raw',
                'value' => \Yii::$app->formatter->asDatetime($model->status_at),
            ],*/

        ],
]) ?>

<?= \skeeks\cms\modules\admin\widgets\BlockTitleWidget::widget([
    'content' => \Yii::t('skeeks/shop/app', 'Buyer'),
]) ?>

<?= \yii\widgets\DetailView::widget([
    'model'      => $model,
    'template'   => "<tr><th style='width: 50%; text-align: right;'>{label}</th><td>{value}</td></tr>",
    'attributes' =>
        [
            [                      // the owner name of the model
                'label'  => \Yii::t('skeeks/shop/app', 'User'),
                'format' => 'raw',
                'value'  => (new \skeeks\cms\shop\widgets\AdminBuyerUserWidget(['user' => $model->cmsUser]))->run(),
            ],

            [                      // the owner name of the model
                'label'  => \Yii::t('skeeks/shop/app', 'Type payer'),
                'format' => 'raw',
                'value'  => $model->shopPersonType->name,
            ],

            [                      // the owner name of the model
                'label'  => \Yii::t('skeeks/shop/app', 'Profile of buyer'),
                'format' => 'raw',
                'value'  => $model->buyer ? Html::a($model->buyer->name." [{$model->buyer->id}]",
                    \skeeks\cms\helpers\UrlHelper::construct([
                        '/shop/admin-buyer/update',
                        'pk' => $model->buyer->id,
                    ])->enableAdmin(), [
                        'data-pjax' => 0,
                    ]) : '-',
            ],


        ],
]) ?>

<?= \skeeks\cms\modules\admin\widgets\BlockTitleWidget::widget([
    'content' => \Yii::t('skeeks/shop/app', 'Customer data'),
]) ?>
<? if ($model->buyer) : ?>
    <?= \yii\widgets\DetailView::widget([
        'model'      => $model->buyer->relatedPropertiesModel,
        'template'   => "<tr><th style='width: 50%; text-align: right;'>{label}</th><td>{value}</td></tr>",
        'attributes' => array_keys($model->buyer->relatedPropertiesModel->toArray(
            $model->buyer->relatedPropertiesModel->attributes()
        )),

    ]) ?>
<? endif; ?>


<?= \skeeks\cms\modules\admin\widgets\BlockTitleWidget::widget([
    'content' => \Yii::t('skeeks/shop/app', 'Payment order'),
]) ?>
<?= \yii\widgets\DetailView::widget([
    'model'      => $model,
    'template'   => "<tr><th style='width: 50%; text-align: right;'>{label}</th><td>{value}</td></tr>",
    'attributes' =>
        [
            [                      // the owner name of the model
                'label'  => \Yii::t('skeeks/shop/app', 'Payment method'),
                'format' => 'raw',
                'value'  => $model->paySystem ? $model->paySystem->name : 'Платежная система не выбрана',
            ],

            [                      // the owner name of the model
                'label'  => \Yii::t('skeeks/shop/app', 'Payed'),
                'format' => 'raw',
                'value'  => $this->render("_payed", [
                    'model' => $model,
                ]),
            ],

            [                      // the owner name of the model
                'label'  => \Yii::t('skeeks/shop/app', 'Allow payment'),
                'format' => 'raw',
                'value'  => $this->render('_payment-allow', [
                    'model' => $model,
                ]),
            ],

            [                      // the owner name of the model
                'label'  => "",
                'format' => 'raw',
                'value'  => $this->render('_payment', [
                    'model' => $model,
                ]),

            ],
        ],
]) ?>

<?= \skeeks\cms\modules\admin\widgets\BlockTitleWidget::widget([
    'content' => \Yii::t('skeeks/shop/app', 'Shipping'),
]) ?>

<?= \yii\widgets\DetailView::widget([
    'model'      => $model,
    'template'   => "<tr><th style='width: 50%; text-align: right;'>{label}</th><td>{value}</td></tr>",
    'attributes' =>
        [
            [                      // the owner name of the model
                'label'  => \Yii::t('skeeks/shop/app', 'Delivery service'),
                'format' => 'raw',
                'value'  => Html::a($model->shopDelivery ? $model->shopDelivery->name : "нет", "#", [
                    "data-toggle" => "modal",
                    "data-target" => "#sx-allow-delivery",
                    "class"       => "sx-dashed",
                ]),
            ],


            /*[                      // the owner name of the model
                'label'  => 'Разрешить доставку',
                'format' => 'raw',
                'value'  => $this->render('_delivery-allow', [
                    'model' => $model,
                ]),
            ],*/

            /*[
                'label'  => 'Склад',
                'format' => 'raw',
                'value'  => $model->store ? $model->store->name : "",
            ],*/
        ],
]) ?>

<?= \skeeks\cms\modules\admin\widgets\BlockTitleWidget::widget([
    'content' => 'Комментарий',
]) ?>
<?= \yii\widgets\DetailView::widget([
    'model'      => $model,
    'template'   => "<tr><th style='width: 50%; text-align: right;'>{label}</th><td>{value}</td></tr>",
    'attributes' =>
        [
            [                      // the owner name of the model
                'label'  => \Yii::t('skeeks/shop/app', 'Comment'),
                'format' => 'raw',
                'value'  => $this->render('_comment', [
                    'model' => $model,
                ]),

            ],
        ],
]) ?>


<?= \skeeks\cms\modules\admin\widgets\BlockTitleWidget::widget([
    'content' => \Yii::t('skeeks/shop/app', 'The composition of the order'),
]) ?>


<?

$json = \yii\helpers\Json::encode([
    'createUrl' => \skeeks\cms\backend\helpers\BackendUrlHelper::createByParams(['/shop/admin-basket/create'])
        ->merge([
            'shop_order_id' => $model->id,
        ])
        ->enableEmptyLayout()
        ->enableNoActions()
        ->url,
]);

$onclick = new \yii\web\JsExpression(<<<JS
    new sx.classes.AddPosition({$json}).open(); return true;
JS
);
?>



<?


$addItemText = \Yii::t('skeeks/shop/app', 'Add this item');
$addPosition = \Yii::t('skeeks/shop/app', 'Add position');
/*<a class="btn btn-default btn-sm" onclick="new sx.classes.SelectProduct().open(); return true;"><i class="fa fa-plus"></i>
                {$addItemText}
            </a>*/


echo \skeeks\cms\modules\admin\widgets\RelatedModelsGrid::widget([
    'label'       => "",
    'parentModel' => $model,
    'relation'    => [
        'shop_order_id' => 'id',
    ],

    'sort' => [
        'defaultOrder' =>
            [
                'updated_at' => SORT_DESC,
            ],
    ],

    'controllerRoute' => 'shop/admin-basket',
    'gridViewOptions' => [
        'enabledPjax'     => false,
        'beforeTableLeft' => <<<HTML
            <a class="btn btn-default btn-sm sx-btn-create-dialog" href="#"><i class="fa fa-plus"></i>
                {$addItemText}
            </a>
            <a class="btn btn-default btn-sm" onclick='{$onclick}'><i class="fa fa-plus"></i>
                {$addPosition}
            </a>
HTML
        ,

        'columns' => [

            [
                'class' => \skeeks\cms\shop\grid\BasketImageGridColumn::class,
            ],

            [
                'class' => \skeeks\cms\shop\grid\BasketNameGridColumn::class,
            ],

            [
                'class' => \skeeks\cms\shop\grid\BasketQuantityGridColumn::class,
            ],

            [
                'class' => \skeeks\cms\shop\grid\BasketPriceGridColumn::class,
            ],

            [
                'class' => \skeeks\cms\shop\grid\BasketSumGridColumn::class,
            ],
        ],
    ],
]); ?>


<div class="row">
    <div class="col-md-8"></div>
    <div class="col-md-4">
        <div class="sx-result">
            <?
            $this->registerCss(<<<CSS
.sx-result
{
    background-color: #ecf2d3;
    padding: 10px;
}
.sx-result .table tbody tr:last-child
{
    background: #dbe3b9;
    font-weight: bold;
}
.sx-result .table tbody tr:last-child > th,
.sx-result .table tbody tr:last-child > th
{
    font-weight: bold;
}
.sx-result .table tbody > tr > th,
.sx-result .table tbody > tr > td
{
    border-top: none;
    font-weight: normal;
}
CSS
            );
            ?>
            <?=
            \yii\widgets\DetailView::widget([
                'model'      => $model,
                "template"   => "<tr><th>{label}</th><td style='text-align: right;'>{value}</td></tr>",
                "options"    => ['class' => 'sx-result-table table detail-view'],
                'attributes' => [
                    [
                        'label' => \Yii::t('skeeks/shop/app', 'The total value of the goods'),
                        'value' => (string)$model->basketsMoney,
                    ],

                    [
                        'label' => \Yii::t('skeeks/shop/app', 'Discount, margin'),
                        'value' => (string)$model->moneyDiscount,
                    ],

                    [
                        'label' => \Yii::t('skeeks/shop/app', 'Delivery service'),
                        'value' => (string)$model->moneyDelivery,
                    ],

                    [
                        'label' => \Yii::t('skeeks/shop/app', 'Taxe'),
                        'value' => (string)$model->moneyVat,
                    ],

                    [
                        'label' => \Yii::t('skeeks/shop/app', 'Weight (gramm)'),
                        'value' => $model->weight." ".\Yii::t('skeeks/shop/app', 'g.'),
                    ],

                    [
                        'label' => \Yii::t('skeeks/shop/app', 'Already paid'),
                        'value' => (string)$model->moneySummPaid,
                    ],

                    [
                        'label'  => \Yii::t('skeeks/shop/app', 'In total'),
                        'format' => 'raw',
                        'value'  => Html::tag('b', (string)$model->money),
                    ],
                ],
            ])
            ?>
        </div>
    </div>
</div>


<?

\skeeks\cms\shop\assets\ShopAsset::register($this);

$shopJson = \yii\helpers\Json::encode([

    'backend-add-product' => \skeeks\cms\helpers\UrlHelper::construct([
        '/shop/admin-order/update-order-add-product',
        'pk' => $model->id,
    ])->enableAdmin()->toString(),

]);


$this->registerJs(<<<JS
    sx.classes.SelectProduct = sx.classes.Component.extend({
        _onDomReady: function()
        {
            $(".sx-btn-create-dialog").on("click", function() {
                $('#sx-add-product-wrapper .sx-btn-create').click();
                return false;
            });
            
            $("#sx-add-product").on("change", function() {
                sx.AdminShop.addProduct($(this).val());
                return false;
            });
        },
    });

    new sx.classes.SelectProduct();

    sx.classes.AdminShop = sx.classes.shop.App.extend({});
    sx.AdminShop = new sx.classes.AdminShop({$shopJson});
    sx.AdminShop.bind('addProduct', function()
    {
        $.pjax.reload('#sx-pjax-order-wrapper');
    });


    sx.classes.AddPosition = sx.classes.Component.extend({

        open: function()
        {
            var self = this;
            var window = new sx.classes.Window(this.get('createUrl'));
            window.bind("close", function()
            {
                $.pjax.reload('#sx-pjax-order-wrapper');
            });

            window.open();
        }
    });

JS
);

?>


<? $modal = \yii\bootstrap\Modal::begin([
    'id'     => 'sx-close-order',
    'header' => 'Отмена заказа',
]); ?>

<?php $form = \skeeks\cms\base\widgets\ActiveFormAjaxSubmit::begin([
    'validationUrl' => \skeeks\cms\helpers\UrlHelper::construct([
        'shop/admin-order/validate',
        'pk' => $model->id,
    ])->enableAdmin()->toString(),
    'action'        => \skeeks\cms\helpers\UrlHelper::construct([
        'shop/admin-order/save',
        'pk' => $model->id,
    ])->enableAdmin()->toString(),

    'afterValidateCallback' => new \yii\web\JsExpression(<<<JS
                function(jForm, ajax){
                    new sx.classes.OrderCallback(jForm, ajax);
                };
JS
    ),

]); ?>

<?= $form->fieldRadioListBoolean($model, 'canceled'); ?>
<?= $form->field($model, 'reason_canceled')->textarea(['rows' => 5]) ?>

<button class="btn btn-primary">Сохранить</button>

<?php \skeeks\cms\base\widgets\ActiveFormAjaxSubmit::end(); ?>
<? $modal::end(); ?>



<? $modal = \yii\bootstrap\Modal::begin([
    'id'     => 'sx-status-change',
    'header' => 'Изменение статуса',
]); ?>
<?php $form = \skeeks\cms\base\widgets\ActiveFormAjaxSubmit::begin([
    'validationUrl' => \skeeks\cms\helpers\UrlHelper::construct([
        'shop/admin-order/validate',
        'pk' => $model->id,
    ])->enableAdmin()->toString(),
    'action'        => \skeeks\cms\helpers\UrlHelper::construct([
        'shop/admin-order/save',
        'pk' => $model->id,
    ])->enableAdmin()->toString(),

    'afterValidateCallback' => new \yii\web\JsExpression(<<<JS
            function(jForm, ajax){
                new sx.classes.OrderCallback(jForm, ajax);
            };
JS
    ),

]); ?>

<?= $form->fieldSelect($model, 'shop_order_status_id', \yii\helpers\ArrayHelper::map(
    \skeeks\cms\shop\models\ShopOrderStatus::find()->all(), 'id', 'name'
)); ?>

<button class="btn btn-primary">Сохранить</button>

<?php \skeeks\cms\base\widgets\ActiveFormAjaxSubmit::end(); ?>
<? $modal::end(); ?>




<? $modal = \yii\bootstrap\Modal::begin([
    'id'     => 'sx-allow-payment',
    'header' => 'Разрешение оплаты',
]); ?>

<?php $form = \skeeks\cms\base\widgets\ActiveFormAjaxSubmit::begin([
    'validationUrl' => \skeeks\cms\helpers\UrlHelper::construct([
        'shop/admin-order/validate',
        'pk' => $model->id,
    ])->enableAdmin()->toString(),
    'action'        => \skeeks\cms\helpers\UrlHelper::construct([
        'shop/admin-order/save',
        'pk' => $model->id,
    ])->enableAdmin()->toString(),

    'afterValidateCallback' => new \yii\web\JsExpression(<<<JS
                function(jForm, ajax){
                    new sx.classes.OrderCallback(jForm, ajax);
                };
JS
    ),

]); ?>

<?=
$form->fieldSelect($model, 'shop_pay_system_id', \yii\helpers\ArrayHelper::map(
    $model->paySystems, 'id', 'name'
));
?>

<?= $form->field($model, 'is_allowed_payment')->checkbox(); ?>

<button class="btn btn-primary">Сохранить</button>

<?php \skeeks\cms\base\widgets\ActiveFormAjaxSubmit::end(); ?>
<? $modal::end(); ?>

<? $modal = \yii\bootstrap\Modal::begin([
    'id'     => 'sx-payment-container',
    'header' => 'Оплата заказа',
]); ?>
<?php $form = \skeeks\cms\base\widgets\ActiveFormAjaxSubmit::begin([
    'validationUrl' => \skeeks\cms\helpers\UrlHelper::construct([
        'shop/admin-order/pay-validate',
        'pk' => $model->id,
    ])->enableAdmin()->toString(),
    'action'        => \skeeks\cms\helpers\UrlHelper::construct([
        'shop/admin-order/pay',
        'pk' => $model->id,
    ])->enableAdmin()->toString(),

    'afterValidateCallback' => new \yii\web\JsExpression(<<<JS
            function(jForm, ajax){
                new sx.classes.OrderCallback(jForm, ajax);
            };
JS
    ),

]); ?>

<?= $form->fieldSelect($model, 'shop_order_status_id', \yii\helpers\ArrayHelper::map(
    \skeeks\cms\shop\models\ShopOrderStatus::find()->all(), 'id', 'name'
)); ?>

<? /*= $form->field($model, 'pay_voucher_num'); */ ?><!--
        --><? /*= $form->field($model, 'pay_voucher_at')->widget(
            \kartik\datecontrol\DateControl::class, [
            'type' => \kartik\datecontrol\DateControl::FORMAT_DATETIME,
        ]); */ ?>

<button class="btn btn-primary">Сохранить</button>

<?php \skeeks\cms\base\widgets\ActiveFormAjaxSubmit::end(); ?>
<? $modal::end(); ?>





<? $modal = \yii\bootstrap\Modal::begin([
    'id'     => 'sx-payment-container-close',
    'header' => 'Изменение данных по оплате',
]); ?>

<?php $form = \skeeks\cms\base\widgets\ActiveFormAjaxSubmit::begin([
    'validationUrl' => \skeeks\cms\helpers\UrlHelper::construct([
        'shop/admin-order/pay-validate',
        'pk' => $model->id,
    ])->enableAdmin()->toString(),
    'action'        => \skeeks\cms\helpers\UrlHelper::construct([
        'shop/admin-order/pay',
        'pk' => $model->id,
    ])->enableAdmin()->toString(),

    'afterValidateCallback' => new \yii\web\JsExpression(<<<JS
                function(jForm, ajax){
                    new sx.classes.OrderCallback(jForm, ajax);
                };
JS
    ),

]); ?>

<?= $form->fieldSelect($model, 'shop_order_status_id', \yii\helpers\ArrayHelper::map(
    \skeeks\cms\shop\models\ShopOrderStatus::find()->all(), 'id', 'name'
)); ?>

<? /*= $form->field($model, 'pay_voucher_num'); */ ?><!--
            --><? /*= $form->field($model, 'pay_voucher_at')->widget(
                \kartik\datecontrol\DateControl::class, [
                'type' => \kartik\datecontrol\DateControl::FORMAT_DATETIME,
            ]); */ ?>

<p>
    <?= Html::checkbox('payment-close', false, ['label' => 'Отменить оплату']); ?>
</p>
<p>
    <?= Html::checkbox('payment-close-on-client', false,
        ['label' => 'Вернуть средства на внутренний счет']); ?>
</p>
<button class="btn btn-primary">Сохранить</button>

<?php \skeeks\cms\base\widgets\ActiveFormAjaxSubmit::end(); ?>

<? $modal::end(); ?>

<? $modal = \yii\bootstrap\Modal::begin([
    'id'     => 'sx-allow-delivery',
    'header' => 'Доставка',
]); ?>

<?php $form = \skeeks\cms\base\widgets\ActiveFormAjaxSubmit::begin([
    'validationUrl' => \skeeks\cms\helpers\UrlHelper::construct([
        'shop/admin-order/validate',
        'pk' => $model->id,
    ])->enableAdmin()->toString(),
    'action'        => \skeeks\cms\helpers\UrlHelper::construct([
        'shop/admin-order/save',
        'pk' => $model->id,
    ])->enableAdmin()->toString(),

    'afterValidateCallback' => new \yii\web\JsExpression(<<<JS
                function(jForm, ajax){
                    new sx.classes.OrderCallback(jForm, ajax);
                };
JS
    ),

]); ?>

<?=
$form->fieldSelect($model, 'shop_delivery_id', \yii\helpers\ArrayHelper::map(
    \skeeks\cms\shop\models\ShopDelivery::find()->active()->all(), 'id', 'name'
));
?>

<? /*= $form->fieldRadioListBoolean($model, 'allow_delivery'); */ ?>

<button class="btn btn-primary">Сохранить</button>

<?php \skeeks\cms\base\widgets\ActiveFormAjaxSubmit::end(); ?>

<? $modal::end(); ?>




<? $modal = \yii\bootstrap\Modal::begin([
    'id'     => 'sx-comment',
    'header' => 'Комментарий',
]); ?>
<?php $form = \skeeks\cms\base\widgets\ActiveFormAjaxSubmit::begin([
    'validationUrl' => \skeeks\cms\helpers\UrlHelper::construct([
        'shop/admin-order/validate',
        'pk' => $model->id,
    ])->enableAdmin()->toString(),
    'action'        => \skeeks\cms\helpers\UrlHelper::construct([
        'shop/admin-order/save',
        'pk' => $model->id,
    ])->enableAdmin()->toString(),

    'afterValidateCallback' => new \yii\web\JsExpression(<<<JS
            function(jForm, ajax){
                new sx.classes.OrderCallback(jForm, ajax);
            };
JS
    ),

]); ?>

<?= $form->field($model, 'comments')->textarea([
    'rows' => 5,
])->hint(\Yii::t('skeeks/shop/app', 'Internal comment, the customer (buyer) does not see'));
?>

<button class="btn btn-primary">Сохранить</button>

<?php \skeeks\cms\base\widgets\ActiveFormAjaxSubmit::end(); ?>
<? $modal::end(); ?>


<div style="display: none;">
    <?=
    \skeeks\cms\backend\widgets\SelectModelDialogContentElementWidget::widget([
        'dialogRoute'            => ['/shop/admin-cms-content-element'],
        'name'                   => 'sx-add-product',
        'id'                     => 'sx-add-product',
        'closeDialogAfterSelect' => false,
    ]);
    ?>


    <? /*=
\skeeks\cms\backend\widgets\SelectModelDialogContentElementWidget::widget([
    'id' => 'sx-add-product',
    'name' => 'test',
    'multiple' => true,
    'dialogRoute' => ['/shop/admin-cms-content-element']
]);
*/ ?>
</div>


<? /*= $form->buttonsCreateOrUpdate($model); */ ?>




<?

$this->registerJs(<<<JS
(function(sx, $, _)
{
    _.each(sx.components, function(Component, key)
    {
        /*if (Component instanceof sx.classes.SelectModelDialog)
        {
            Component.bind('change', function(e, data)
            {
                sx.AdminShop.addProduct(data.id);
            });

            Component.unbind('change', function(e, data)
            {
                sx.AdminShop.addProduct(data.id);
            });
        }*/
    });
})(sx, sx.$, sx._);
JS
);
?>

<?
\skeeks\cms\widgets\Pjax::end();
?>
