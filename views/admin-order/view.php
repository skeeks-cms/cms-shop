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
?>

<?php $form = ActiveForm::begin(); ?>

<?= $form->fieldSet(\skeeks\cms\shop\Module::t('app', 'General information')); ?>

    <?= \skeeks\cms\modules\admin\widgets\BlockTitleWidget::widget([
        'content' => \skeeks\cms\shop\Module::t('app', 'Order')

    ])?>

        <?= \yii\widgets\DetailView::widget([
            'model' => $model,
            'template'   => "<tr><th style='width: 50%; text-align: right;'>{label}</th><td>{value}</td></tr>",
            'attributes' =>
            [
                [                      // the owner name of the model
                    'label' => \skeeks\cms\shop\Module::t('app', 'Number of order'),
                    'format' => 'raw',
                    'value' => $model->id,
                ],

                [                      // the owner name of the model
                    'label' => \skeeks\cms\shop\Module::t('app', 'Created At'),
                    'format' => 'raw',
                    'value' => \Yii::$app->formatter->asDatetime($model->created_at),
                ],

                [                      // the owner name of the model
                    'label' => \skeeks\cms\shop\Module::t('app', 'Last modified'),
                    'format' => 'raw',
                    'value' => \Yii::$app->formatter->asDatetime($model->updated_at),
                ],

                [                      // the owner name of the model
                    'label' => \skeeks\cms\shop\Module::t('app', 'Status'),
                    'format' => 'raw',
                    'value' => "<p>" . $form->fieldSelect($model, 'status_code', \yii\helpers\ArrayHelper::map(
                                    \skeeks\cms\shop\models\ShopOrderStatus::find()->all(), 'code', 'name'
                                ))->label(false) . "</p>"
                ],

                [                      // the owner name of the model
                    'label' => \skeeks\cms\shop\Module::t('app', 'Canceled'),
                    'format' => 'raw',
                    'value' => "<p>" . $form->fieldRadioListBoolean($model, 'canceled')->label(false) . "</p><p>" .
                            $form->field($model, 'reason_canceled')->textarea(['rows' => 5])
                        . "</p>",
                ],

                [                      // the owner name of the model
                    'label' => \skeeks\cms\shop\Module::t('app', 'Date of status change'),
                    'format' => 'raw',
                    'value' => \Yii::$app->formatter->asDatetime($model->status_at),
                ],

            ]
        ])?>

    <?= \skeeks\cms\modules\admin\widgets\BlockTitleWidget::widget([
        'content' => \skeeks\cms\shop\Module::t('app', 'Buyer')
    ])?>

        <?= \yii\widgets\DetailView::widget([
            'model' => $model,
            'template'   => "<tr><th style='width: 50%; text-align: right;'>{label}</th><td>{value}</td></tr>",
            'attributes' =>
            [
                [                      // the owner name of the model
                    'label'     => \skeeks\cms\shop\Module::t('app', 'User'),
                    'format'    => 'raw',
                    'value'     => (new \skeeks\cms\shop\widgets\AdminBuyerUserWidget(['user' => $model->user]))->run()
                ],

                [                      // the owner name of the model
                    'label' => \skeeks\cms\shop\Module::t('app', 'Type payer'),
                    'format' => 'raw',
                    'value' => $model->personType->name,
                ],

                [                      // the owner name of the model
                    'label' => \skeeks\cms\shop\Module::t('app', 'Profile of buyer'),
                    'format' => 'raw',
                    'value' => Html::a($model->buyer->name . " [{$model->buyer->id}]", \skeeks\cms\helpers\UrlHelper::construct(['/shop/admin-buyer/update', 'pk' => $model->buyer->id ])->enableAdmin(), [
                        'data-pjax' => 0
                    ] ),
                ],


            ]
        ])?>

    <?= \skeeks\cms\modules\admin\widgets\BlockTitleWidget::widget([
        'content' => \skeeks\cms\shop\Module::t('app', 'Customer data')
    ])?>
        <?= \yii\widgets\DetailView::widget([
            'model' => $model->buyer->relatedPropertiesModel,
            'template'   => "<tr><th style='width: 50%; text-align: right;'>{label}</th><td>{value}</td></tr>",
            'attributes' => array_keys($model->buyer->relatedPropertiesModel->attributeValues())

        ])?>


    <?= \skeeks\cms\modules\admin\widgets\BlockTitleWidget::widget([
        'content' => \skeeks\cms\shop\Module::t('app', 'Payment order')
    ])?>
        <?= \yii\widgets\DetailView::widget([
            'model' => $model,
            'template'   => "<tr><th style='width: 50%; text-align: right;'>{label}</th><td>{value}</td></tr>",
            'attributes' =>
            [
                [                      // the owner name of the model
                    'label'     => \skeeks\cms\shop\Module::t('app', 'Payment method'),
                    'format'    => 'raw',
                    'value'     => $model->paySystem->name,
                ],

                [                      // the owner name of the model
                    'label' => \skeeks\cms\shop\Module::t('app', 'Date'),
                    'format' => 'raw',
                    'value' => \Yii::$app->formatter->asDatetime($model->payed_at),
                ],

                [                      // the owner name of the model
                    'label' => \skeeks\cms\shop\Module::t('app', 'Payed'),
                    'format' => 'raw',
                    'value' => \Yii::$app->formatter->asBoolean( ($model->payed == \skeeks\cms\components\Cms::BOOL_Y))
                ],

                [                      // the owner name of the model
                    'label' => \skeeks\cms\shop\Module::t('app', 'Allow payment'),
                    'format' => 'raw',
                    'value' => $form->fieldRadioListBoolean($model, 'allow_payment')->label(false),
                ],

                [                      // the owner name of the model
                    'label' => "",
                    'format' => 'raw',
                    'value' => $this->render('_payment', [
                        'model' => $model
                    ])

                ],


            ]
        ])?>

    <?= \skeeks\cms\modules\admin\widgets\BlockTitleWidget::widget([
        'content' => \skeeks\cms\shop\Module::t('app', 'Shipping')
    ])?>

        <?= \yii\widgets\DetailView::widget([
            'model' => $model,
            'template'   => "<tr><th style='width: 50%; text-align: right;'>{label}</th><td>{value}</td></tr>",
            'attributes' =>
            [
                [                      // the owner name of the model
                    'label'     => \skeeks\cms\shop\Module::t('app', 'Delivery service'),
                    'format'    => 'raw',
                    'value'     => $model->delivery->name,
                ],


                [                      // the owner name of the model
                    'label' => 'Разрешить доставку',
                    'format' => 'raw',
                    'value' => $form->fieldRadioListBoolean($model, 'allow_delivery')->label(false),
                ],
            ]
        ])?>

    <?= \skeeks\cms\modules\admin\widgets\BlockTitleWidget::widget([
        'content' => 'Комментарий'
    ])?>
    <?= \yii\widgets\DetailView::widget([
            'model' => $model,
            'template'   => "<tr><th style='width: 50%; text-align: right;'>{label}</th><td>{value}</td></tr>",
            'attributes' =>
            [
                [                      // the owner name of the model
                    'label'     => \skeeks\cms\shop\Module::t('app', 'Comment'),
                    'format'    => 'raw',
                    'value'     => $form->field($model, 'comments')->textarea([
                        'rows' => 5
                    ])->hint(\skeeks\cms\shop\Module::t('app', 'Internal comment, the customer (buyer) does not see'))->label(false),
                ],
            ]
        ])?>


    <?= \skeeks\cms\modules\admin\widgets\BlockTitleWidget::widget([
        'content' => \skeeks\cms\shop\Module::t('app', 'The composition of the order')
    ])?>

        <?= \skeeks\cms\modules\admin\widgets\GridView::widget([
            'dataProvider' => new \yii\data\ArrayDataProvider([
                'models' => $model->shopBaskets
            ]),

            'layout' => "{items}\n{pager}",

            'columns' =>
            [
                [
                    'class' => \yii\grid\SerialColumn::className()
                ],

                [
                    'class'     => \yii\grid\DataColumn::className(),
                    'attribute' => 'name',
                    'format'    => 'raw',
                    'value'     => function(\skeeks\cms\shop\models\ShopBasket $shopBasket)
                    {
                        $widget = new \skeeks\cms\modules\admin\widgets\AdminImagePreviewWidget([
                            'image' => $shopBasket->product->cmsContentElement->image
                        ]);
                        return $widget->run();
                    }
                ],
                [
                    'class' => \yii\grid\DataColumn::className(),
                    'attribute' => 'name',
                    'format' => 'raw',
                    'value' => function(\skeeks\cms\shop\models\ShopBasket $shopBasket)
                    {
                        if ($shopBasket->product)
                        {
                            return Html::a($shopBasket->name, $shopBasket->product->cmsContentElement->url, [
                                'target' => '_blank',
                                'titla' => "Смотреть на сайте",
                                'data-pjax' => 0
                            ]);
                        } else
                        {
                            return $shopBasket->name;
                        }

                    }
                ],

                [
                    'class' => \yii\grid\DataColumn::className(),
                    'attribute' => 'quantity',
                    'value' => function(\skeeks\cms\shop\models\ShopBasket $shopBasket)
                    {
                        return $shopBasket->quantity . " " . $shopBasket->measure_name;
                    }
                ],

                [
                    'class' => \yii\grid\DataColumn::className(),
                    'label' => \skeeks\cms\shop\Module::t('app', 'Price'),
                    'attribute' => 'price',
                    'format' => 'raw',
                    'value' => function(\skeeks\cms\shop\models\ShopBasket $shopBasket)
                    {
                        if ($shopBasket->discount_value)
                        {
                            return "<span style='text-decoration: line-through;'>" . \Yii::$app->money->intlFormatter()->format($shopBasket->moneyOriginal) . "</span><br />". Html::tag('small', $shopBasket->notes) . "<br />" . \Yii::$app->money->intlFormatter()->format($shopBasket->money) . "<br />" . Html::tag('small', \skeeks\cms\shop\Module::t('app', 'Discount').": " . $shopBasket->discount_value);
                        } else
                        {
                            return \Yii::$app->money->intlFormatter()->format($shopBasket->money) . "<br />" . Html::tag('small', $shopBasket->notes);
                        }

                    }
                ],
                [
                    'class' => \yii\grid\DataColumn::className(),
                    'label' => \skeeks\cms\shop\Module::t('app', 'Sum'),
                    'attribute' => 'price',
                    'format' => 'raw',
                    'value' => function(\skeeks\cms\shop\models\ShopBasket $shopBasket)
                    {
                        return \Yii::$app->money->intlFormatter()->format($shopBasket->money->multiply($shopBasket->quantity));
                    }
                ],
            ]
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
                            'value' => \Yii::$app->money->intlFormatter()->format($model->moneyOriginal),
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
                            'label' => \skeeks\cms\shop\Module::t('app', 'Already paid'),
                            'value' => \Yii::$app->money->intlFormatter()->format($model->moneySummPaid),
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


<?= $form->fieldSetEnd(); ?>



<?= $form->fieldSet(\skeeks\cms\shop\Module::t('app', 'Транзакции по заказу')); ?>

    <?= \skeeks\cms\modules\admin\widgets\GridView::widget([
    'dataProvider' => new \yii\data\ArrayDataProvider([
        'models' => $model->shopUserTransacts
    ]),

    'columns' =>
    [
        [
            'class' => \skeeks\cms\grid\CreatedAtColumn::className()
        ],

        [
            'class'     => \yii\grid\DataColumn::className(),
            'label'     => \skeeks\cms\shop\Module::t('app', 'User'),
            'format'    => 'raw',
            'value'     => function(\skeeks\cms\shop\models\ShopUserTransact $shopUserTransact)
            {
                return (new \skeeks\cms\shop\widgets\AdminBuyerUserWidget(['user' => $shopUserTransact->cmsUser]))->run();
            }
        ],

        [
            'class' => \yii\grid\DataColumn::className(),
            'attribute' => 'type',
            'label' => \skeeks\cms\shop\Module::t('app', 'Сумма'),
            'format' => 'raw',
            'value' => function(\skeeks\cms\shop\models\ShopUserTransact $shopUserTransact)
            {
                return $shopUserTransact->amount;
            }
        ],

        'description'
    ]
]); ?>

<?= $form->fieldSetEnd(); ?>

<?= $form->fieldSet(\skeeks\cms\shop\Module::t('app', 'History of changes')); ?>

        <?= \skeeks\cms\modules\admin\widgets\GridView::widget([
            'dataProvider' => new \yii\data\ArrayDataProvider([
                'models' => $model->shopOrderChanges
            ]),

            'columns' =>
            [
                [
                    'class' => \skeeks\cms\grid\UpdatedAtColumn::className()
                ],

                [
                    'class'     => \yii\grid\DataColumn::className(),
                    'label'     => \skeeks\cms\shop\Module::t('app', 'User'),
                    'format'    => 'raw',
                    'value'     => function(\skeeks\cms\shop\models\ShopOrderChange $shopOrderChange)
                    {
                        return (new \skeeks\cms\shop\widgets\AdminBuyerUserWidget(['user' => $shopOrderChange->createdBy]))->run();
                    }
                ],

                [
                    'class' => \yii\grid\DataColumn::className(),
                    'attribute' => 'type',
                    'label' => \skeeks\cms\shop\Module::t('app', 'Transaction'),
                    'format' => 'raw',
                    'value' => function(\skeeks\cms\shop\models\ShopOrderChange $shopOrderChange)
                    {
                        return \skeeks\cms\shop\models\ShopOrderChange::types()[$shopOrderChange->type];
                    }
                ],
                [
                    'class' => \yii\grid\DataColumn::className(),
                    'attribute' => 'type',
                    'label' => \skeeks\cms\shop\Module::t('app', 'Description'),
                    'format' => 'raw',
                    'value' => function(\skeeks\cms\shop\models\ShopOrderChange $shopOrderChange)
                    {
                        return $shopOrderChange->description;
                    }
                ],


            ]
        ]); ?>

<?= $form->fieldSetEnd(); ?>

<?= $form->buttonsCreateOrUpdate($model); ?>
<?php ActiveForm::end(); ?>


<div style="display: none;">
    <div id="sx-payment-container" style="min-width: 500px;">
        <h2>Оплата заказа:</h2><hr />
        <?php $form = \skeeks\cms\base\widgets\ActiveFormAjaxSubmit::begin([
            'validationUrl'     => \skeeks\cms\helpers\UrlHelper::construct(['shop/admin-order/pay-validate', 'pk' => $model->id])->enableAdmin()->toString(),
            'action'            => \skeeks\cms\helpers\UrlHelper::construct(['shop/admin-order/pay', 'pk' => $model->id])->enableAdmin()->toString(),

            'afterValidateCallback'                     => new \yii\web\JsExpression(<<<JS
                function(jForm, ajax)
                {
                    var handler = new sx.classes.AjaxHandlerStandartRespose(ajax, {
                        'blockerSelector' : '#' + jForm.attr('id'),
                        'enableBlocker' : true,
                    });

                    handler.bind('success', function(response)
                    {
                        window.location.reload();
                    });
                }
JS
    ),

        ]); ?>

            <?= $form->fieldSelect($model, 'status_code', \yii\helpers\ArrayHelper::map(
                \skeeks\cms\shop\models\ShopOrderStatus::find()->all(), 'code', 'name'
            )); ?>

            <?= $form->field($model, 'pay_voucher_num'); ?>
            <?= $form->field($model, 'pay_voucher_at'); ?>

            <button class="btn btn-primary">Сохранить</button>

        <?php \skeeks\cms\base\widgets\ActiveFormAjaxSubmit::end(); ?>

    </div>
</div>