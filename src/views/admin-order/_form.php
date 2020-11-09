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


.sx-detail-order, .sx-buyer-info {
    font-size: 16px;
}
.sx-data .sx-data-row {
    padding: 5px 0;
}
.sx-data .sx-data-row:nth-of-type(2n+1) {
    background-color: #f7f7f7;
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


<div class="sx-detail-order sx-data">
    <div class="col-12">

        <div class="row sx-data-row">
            <div class="col-3">Статус</div>
            <div class="col-9">
                <?php echo Html::tag('a', $model->shopOrderStatus->name, [
                    'style' => "padding: 2px 5px; color: {$model->shopOrderStatus->color}; background: {$model->shopOrderStatus->bg_color};",
                    'class' => 'btn',
                    'data'  => [
                        'toggle' => "modal",
                        'target' => "#sx-status-change",
                    ],
                ]); ?>
                <?php if ($model->shopOrderStatus->description) : ?>
                    <i class="far fa-question-circle" title="<?php echo $model->shopOrderStatus->description; ?>"></i>
                <?php endif; ?>
                <small style="color: gray;"><?php echo \Yii::$app->formatter->asDatetime($model->status_at); ?></small>


            </div>
        </div>
        <div class="row sx-data-row">
            <div class="col-3">Оплата</div>
            <div class="col-9">
                <a href="#" data-toggle="modal" data-target="#sx-allow-payment" class="sx-dashed">
                    <?php if ($model->paySystem) : ?>
                        <?php echo \skeeks\cms\helpers\StringHelper::ucfirst($model->paySystem->name); ?>
                    <?php else: ?>
                        Не выбрана
                    <?php endif; ?>
                </a>


                <span style="margin-left: 20px;">
                    <?php if ($model->paid_at) : ?>
                        <span style='color: green;'>Оплачен</span>
                    <?php else: ?>
                        <span style='color: gray;'>Не оплачен</span>
                    <?php endif; ?>
                </span>

                <span style="margin-left: 20px;">
                    <? if ($model->shopOrderStatus->is_payment_allowed) : ?>
                        <span style='color: gray;'>Оплата разрешена</span>
                    <? else : ?>
                        <span style='color: gray;'>Оплата не разрешена</span>
                    <? endif; ?>
                </span>


            </div>
        </div>

        <div class="row sx-data-row">
            <div class="col-3">Доставка</div>
            <div class="col-9">
                <a href="#" data-toggle="modal" data-target="#sx-allow-delivery" class="sx-dashed">
                    <?php if ($model->shopDelivery) : ?>
                        <?php echo $model->shopDelivery->name; ?>
                    <?php else: ?>
                        Не выбрана
                    <?php endif; ?>


                </a>

                <?php if ((float) $model->moneyDelivery->amount > 0) : ?>
                <span style="margin-left: 10px;">
                        <?php echo $model->moneyDelivery; ?>
                </span>
                    <? endif; ?>

            </div>
        </div>

        <?php if ($model->lastStatusLog && $model->lastStatusLog->comment) : ?>
            <div class="row" style="margin-top: 20px;">
                <div class="col-12">
                    <div style="margin-bottom: 0; color: gray;"><small><b>Комментарий к статусу:</b></small></div>
                </div>
                <div class="col-12">
                    <div class="g-brd-primary" style="background: #fafafa; border-left: 5px solid; padding: 20px; 10px;">
                        <?php echo nl2br($model->lastStatusLog->comment); ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($model->shopOrderStatus->order_page_description) : ?>
            <div class="row" style="margin-top: 20px;">
                <div class="col-12">
                    <div style="margin-bottom: 0; color: gray;"><small><b>Эту информацию видит клиент на странице заказа:</b></small></div>
                </div>
                <div class="col-12">
                    <div class="g-brd-primary" style="background: #fafafa; border-left: 5px solid; padding: 20px; 10px;">
                        <?php echo $model->shopOrderStatus->order_page_description; ?>
                    </div>
                </div>

            </div>
        <?php endif; ?>

    </div>
</div>


<?php if ($model->shopBuyer) : ?>
    <div class="sx-buyer-info" style="
                    margin-top: 20px;
                    /*background: #f8f8f8;*/
                    /*padding: 20px;*/
                ">
        <div class="row">
            <div class="col-12">
                <h4>Данные покупателя
                    <span style="color: gray;" title="Пользователь сайта">
                        [
                <?php if ($model->cmsUser) : ?>
                    <?php echo $model->cmsUser->shortDisplayName; ?>
                <?php else: ?>
                    пользователь неавторизован
                <?php endif; ?>
                        ]
                    </span>
                </h4>
            </div>
        </div>
        <div class="sx-data">
            <div class="col-12">
                <?php foreach ($model->shopBuyer->relatedPropertiesModel->toArray() as $k => $v) : ?>
                    <div class="row sx-data-row">
                        <div class="col-3"><?php echo \yii\helpers\ArrayHelper::getValue($model->shopBuyer->relatedPropertiesModel->attributeLabels(), $k); ?>
                        </div>
                        <div class="col-9">
                            <?php echo $v; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
<?php endif; ?>


<div style="height: 20px;"></div>
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
]);

?>


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
                        'label' => \Yii::t('skeeks/shop/app', 'Weight'),
                        'value' => $model->weightFormatted,
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
    'id'     => 'sx-status-change',
    'header' => 'Изменение статуса',
    'size'   => \yii\bootstrap\Modal::SIZE_LARGE,
]); ?>
<?php $form = \skeeks\cms\base\widgets\ActiveFormAjaxSubmit::begin([
    'enableClientValidation' => false,
    'validationUrl'          => \skeeks\cms\helpers\UrlHelper::construct([
        'shop/admin-order/validate',
        'pk' => $model->id,
    ])->enableAdmin()->toString(),
    'action'                 => \skeeks\cms\helpers\UrlHelper::construct([
        'shop/admin-order/save',
        'pk' => $model->id,
    ])->enableAdmin()->toString(),

    'clientCallback' => new \yii\web\JsExpression(<<<JS
    function (ActiveFormAjaxSubmit) {
    
        ActiveFormAjaxSubmit.on('success', function(e, response) {
            ActiveFormAjaxSubmit.jForm.closest(".modal").find(".close").click();
            _.delay(function() {
                $.pjax.reload('#sx-pjax-order-wrapper', {});
            }, 200);
        });
    }
JS

    /*'afterValidateCallback' => new \yii\web\JsExpression(<<<JS
            function(jForm, ajax){
                new sx.classes.OrderCallback(jForm, ajax);
            };
JS*/
    ),

]); ?>

<?= $form->fieldSelect($model, 'shop_order_status_id', \yii\helpers\ArrayHelper::map(
    \skeeks\cms\shop\models\ShopOrderStatus::find()->orderBy(['priority' => SORT_ASC])->all(), 'id', 'name'
)); ?>

<?php echo $form->field($model, 'statusComment')->textarea([
    'rows' => 10,
]); ?>
<?php echo $form->field($model, 'isNotifyChangeStatus')->checkbox(); ?>

<button class="btn btn-primary">Сохранить</button>

<?php \skeeks\cms\base\widgets\ActiveFormAjaxSubmit::end(); ?>
<? $modal::end(); ?>




<? $modal = \yii\bootstrap\Modal::begin([
    'id'     => 'sx-allow-payment',
    'header' => 'Способ оплаты',
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

    'clientCallback' => new \yii\web\JsExpression(<<<JS
    function (ActiveFormAjaxSubmit) {
    
        ActiveFormAjaxSubmit.on('success', function(e, response) {
            console.log("111");
            ActiveFormAjaxSubmit.jForm.closest(".modal").find(".close").click();
            _.delay(function() {
                $.pjax.reload('#sx-pjax-order-wrapper', {});
            }, 200);
        });
    }
JS
    ),

]); ?>

<?=
$form->fieldSelect($model, 'shop_pay_system_id', \yii\helpers\ArrayHelper::map(
    $model->paySystems, 'id', 'name'
));
?>

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

    'clientCallback' => new \yii\web\JsExpression(<<<JS
    function (ActiveFormAjaxSubmit) {
    
    console.log('111');
        ActiveFormAjaxSubmit.on('success', function(e, response) {
            ActiveFormAjaxSubmit.jForm.closest(".modal").find(".close").click();
            _.delay(function() {
                $.pjax.reload('#sx-pjax-order-wrapper', {});
            }, 200);
        });
    }
JS
)

]); ?>

<?=
$form->fieldSelect($model, 'shop_delivery_id', \yii\helpers\ArrayHelper::map(
    \skeeks\cms\shop\models\ShopDelivery::find()->active()->all(), 'id', 'name'
))->label(false);
?>
<?=
$form->field($model, 'delivery_amount')->label("Стоимость доставки");
?>

<button class="btn btn-primary">Сохранить</button>

<?php \skeeks\cms\base\widgets\ActiveFormAjaxSubmit::end(); ?>

<? $modal::end(); ?>


<div style="display: none;">
    <?=
    \skeeks\cms\backend\widgets\SelectModelDialogContentElementWidget::widget([
        'dialogRoute'            => ['/shop/admin-cms-content-element/index'],
        'name'                   => 'sx-add-product',
        'id'                     => 'sx-add-product',
        'closeDialogAfterSelect' => false,
    ]);
    ?>
</div>
<?
\skeeks\cms\widgets\Pjax::end();
?>
