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

<?= $form->fieldSet('Общая информация'); ?>

    <?= \skeeks\cms\modules\admin\widgets\BlockTitleWidget::widget([
        'content' => 'Заказ'
    ])?>

        <?= \yii\widgets\DetailView::widget([
            'model' => $model,
            'template'   => "<tr><th style='width: 50%; text-align: right;'>{label}</th><td>{value}</td></tr>",
            'attributes' =>
            [
                [                      // the owner name of the model
                    'label' => 'Номер заказа',
                    'format' => 'raw',
                    'value' => $model->id,
                ],

                [                      // the owner name of the model
                    'label' => 'Создан',
                    'format' => 'raw',
                    'value' => \Yii::$app->formatter->asDatetime($model->created_at),
                ],

                [                      // the owner name of the model
                    'label' => 'Последнее изменение',
                    'format' => 'raw',
                    'value' => \Yii::$app->formatter->asDatetime($model->updated_at),
                ],

                [                      // the owner name of the model
                    'label' => 'Статус',
                    'format' => 'raw',
                    'value' => "<p>" . $form->fieldSelect($model, 'status_code', \yii\helpers\ArrayHelper::map(
                                    \skeeks\cms\shop\models\ShopOrderStatus::find()->all(), 'code', 'name'
                                ))->label(false) . "</p>"
                ],

                [                      // the owner name of the model
                    'label' => 'Отменен',
                    'format' => 'raw',
                    'value' => "<p>" . $form->fieldRadioListBoolean($model, 'canceled')->label(false) . "</p><p>" .
                            $form->field($model, 'reason_canceled')->textarea(['rows' => 5])
                        . "</p>",
                ],

                [                      // the owner name of the model
                    'label' => 'Дата изменения статуса',
                    'format' => 'raw',
                    'value' => \Yii::$app->formatter->asDatetime($model->status_at),
                ],

            ]
        ])?>

    <?= \skeeks\cms\modules\admin\widgets\BlockTitleWidget::widget([
        'content' => 'Покупатель'
    ])?>

        <?= \yii\widgets\DetailView::widget([
            'model' => $model,
            'template'   => "<tr><th style='width: 50%; text-align: right;'>{label}</th><td>{value}</td></tr>",
            'attributes' =>
            [
                [                      // the owner name of the model
                    'label'     => 'Пользователь',
                    'format'    => 'raw',
                    'value'     => (new \skeeks\cms\shop\widgets\AdminBuyerUserWidget(['user' => $model->user]))->run()
                ],

                [                      // the owner name of the model
                    'label' => 'Тип плательщика',
                    'format' => 'raw',
                    'value' => $model->personType->name,
                ],

                [                      // the owner name of the model
                    'label' => 'Профиль покупателя',
                    'format' => 'raw',
                    'value' => Html::a($model->buyer->name . " [{$model->buyer->id}]", \skeeks\cms\helpers\UrlHelper::construct(['/shop/admin-buyer/update', 'pk' => $model->buyer->id ])->enableAdmin(), [
                        'data-pjax' => 0
                    ] ),
                ],


            ]
        ])?>

    <?= \skeeks\cms\modules\admin\widgets\BlockTitleWidget::widget([
        'content' => 'Данные покупателя'
    ])?>
        <?= \yii\widgets\DetailView::widget([
            'model' => $model->buyer->relatedPropertiesModel,
            'template'   => "<tr><th style='width: 50%; text-align: right;'>{label}</th><td>{value}</td></tr>",
            'attributes' => array_keys($model->buyer->relatedPropertiesModel->attributeValues())

        ])?>


    <?= \skeeks\cms\modules\admin\widgets\BlockTitleWidget::widget([
        'content' => 'Оплата'
    ])?>
        <?= \yii\widgets\DetailView::widget([
            'model' => $model,
            'template'   => "<tr><th style='width: 50%; text-align: right;'>{label}</th><td>{value}</td></tr>",
            'attributes' =>
            [
                [                      // the owner name of the model
                    'label'     => 'Способ оплаты',
                    'format'    => 'raw',
                    'value'     => $model->paySystem->name,
                ],

                [                      // the owner name of the model
                    'label' => 'Дата',
                    'format' => 'raw',
                    'value' => \Yii::$app->formatter->asDatetime($model->payed_at),
                ],

                [                      // the owner name of the model
                    'label' => 'Оплачен',
                    'format' => 'raw',
                    'value' => $model->payed,
                ],

                [                      // the owner name of the model
                    'label' => 'Разрешить оплату',
                    'format' => 'raw',
                    'value' => $form->fieldRadioListBoolean($model, 'allow_payment')->label(false),
                ],


            ]
        ])?>

    <?= \skeeks\cms\modules\admin\widgets\BlockTitleWidget::widget([
        'content' => 'Доставка'
    ])?>

        <?= \yii\widgets\DetailView::widget([
            'model' => $model,
            'template'   => "<tr><th style='width: 50%; text-align: right;'>{label}</th><td>{value}</td></tr>",
            'attributes' =>
            [
                [                      // the owner name of the model
                    'label'     => 'Служба доставки',
                    'format'    => 'raw',
                    'value'     => $model->delivery->id,
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
                    'label'     => 'Комментарий',
                    'format'    => 'raw',
                    'value'     => $form->field($model, 'comments')->textarea([
                        'rows' => 5
                    ])->hint('Внутренний комментарий, клиент (покупатель) не видит')->label(false),
                ],
            ]
        ])?>


    <?= \skeeks\cms\modules\admin\widgets\BlockTitleWidget::widget([
        'content' => 'Состав заказа'
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
                    'label' => 'Цена',
                    'attribute' => 'price',
                    'format' => 'raw',
                    'value' => function(\skeeks\cms\shop\models\ShopBasket $shopBasket)
                    {
                        return \Yii::$app->money->intlFormatter()->format($shopBasket->money) . "<br />" . Html::tag('small', $shopBasket->notes);
                    }
                ],
                [
                    'class' => \yii\grid\DataColumn::className(),
                    'label' => 'Сумма',
                    'attribute' => 'price',
                    'format' => 'raw',
                    'value' => function(\skeeks\cms\shop\models\ShopBasket $shopBasket)
                    {
                        return \Yii::$app->money->intlFormatter()->format($shopBasket->moneySumm);
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
                            'label' => 'Общая стоимость товаров',
                            'value' => \Yii::$app->money->intlFormatter()->format($model->money),
                        ],

                        [
                            'label' => 'Скидка, наценка',
                            'value' => "",
                        ],

                        [
                            'label' => 'Доставка',
                            'value' => \Yii::$app->money->intlFormatter()->format($model->moneyDelivery),
                        ],

                        [
                            'label' => 'Налог',
                            'value' => \Yii::$app->money->intlFormatter()->format($model->moneyVat),
                        ],

                        [
                            'label' => 'Вес',
                            'value' => $model->weight . " г.",
                        ],

                        [
                            'label' => 'Уже оплачено',
                            'value' => \Yii::$app->money->intlFormatter()->format($model->moneySummPaid),
                        ],

                        [
                            'label' => 'Итого',
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



<?= $form->fieldSet('История изменений'); ?>

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
                    'label'     => 'Пользователь',
                    'format'    => 'raw',
                    'value'     => function(\skeeks\cms\shop\models\ShopOrderChange $shopOrderChange)
                    {
                        return (new \skeeks\cms\shop\widgets\AdminBuyerUserWidget(['user' => $shopOrderChange->createdBy]))->run();
                    }
                ],

                [
                    'class' => \yii\grid\DataColumn::className(),
                    'attribute' => 'type',
                    'label' => 'Операция',
                    'format' => 'raw',
                    'value' => function(\skeeks\cms\shop\models\ShopOrderChange $shopOrderChange)
                    {
                        return \skeeks\cms\shop\models\ShopOrderChange::types()[$shopOrderChange->type];
                    }
                ],
                [
                    'class' => \yii\grid\DataColumn::className(),
                    'attribute' => 'type',
                    'label' => 'Описание',
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
