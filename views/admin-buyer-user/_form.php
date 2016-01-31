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
/* @var $model \skeeks\cms\models\CmsUser */



$result = [];

if ($roles = \Yii::$app->authManager->getRolesByUser($model->id))
{
    foreach ($roles as $role)
    {
        $result[] = $role->description . " ({$role->name})";
    }
}

$roles = implode(', ', $result);
?>

<?php $form = ActiveForm::begin(); ?>

<?= $form->fieldSet(\skeeks\cms\shop\Module::t('app', 'Main')); ?>

    <?= \skeeks\cms\modules\admin\widgets\BlockTitleWidget::widget([
        'content' => 'Общая информация'
    ])?>

        <?= \yii\widgets\DetailView::widget([
            'model' => $model,
            'template'   => "<tr><th style='width: 50%; text-align: right;'>{label}</th><td>{value}</td></tr>",
            'attributes' =>
            [
                [                      // the owner name of the model
                    'label' => \skeeks\cms\shop\Module::t('app', 'User site'),
                    'format' => 'raw',
                    'value' => ($model->avatarSrc ? Html::img($model->avatarSrc) . " " : "") . $model->username,
                ],

                'email',
                [                      // the owner name of the model
                    'label' => \skeeks\cms\shop\Module::t('app', 'Date of registration'),
                    'format' => 'raw',
                    'value' =>\Yii::$app->formatter->asDatetime($model->created_at),
                ],

                [                      // the owner name of the model
                  'label' => \skeeks\cms\shop\Module::t('app', 'Date of last login'),
                  'format' => 'raw',
                  'value' => \Yii::$app->formatter->asDatetime($model->logged_at),
                ],
                [                      // the owner name of the model
                  'label' => \skeeks\cms\shop\Module::t('app', 'User groups'),
                  'value' => $roles,
                ],
            ]
        ])?>

    <?= \skeeks\cms\modules\admin\widgets\BlockTitleWidget::widget([
        'content' => 'Статистика заказов'
    ])?>

        <?

            $money = \Yii::$app->money->newMoney();

            $payedOrders = \skeeks\cms\shop\models\ShopOrder::find()->where([
                'user_id'   => $model->id,
                'payed'     => \skeeks\cms\components\Cms::BOOL_Y
            ])->all();

            if ($payedOrders)
            {
                foreach ($payedOrders as $shopOrder)
                {
                    /**
                     * @var $shopOrder \skeeks\cms\shop\models\ShopOrder
                     */
                    $money = $money->add($shopOrder->money);
                }
            }


            $userStatistics = [
                'total'         => \skeeks\cms\shop\models\ShopOrder::find()->where(['user_id' => $model->id])->count(),
                'totalPayed'         => \skeeks\cms\shop\models\ShopOrder::find()->where([
                    'user_id'   => $model->id,
                    'payed'     => \skeeks\cms\components\Cms::BOOL_Y
                ])->count(),
            ];


            $average = "-";
            if (\yii\helpers\ArrayHelper::getValue($userStatistics, 'totalPayed'))
            {
                $average = \Yii::$app->money->intlFormatter()->format($money->multiply(
                    (1/\yii\helpers\ArrayHelper::getValue($userStatistics, 'totalPayed'))
                ));
            }

        ?>
        <?= \yii\widgets\DetailView::widget([
            'model'         => $userStatistics,
            'template'      => "<tr><th style='width: 50%; text-align: right;'>{label}</th><td>{value}</td></tr>",
            'attributes'    =>
            [
                [                      // the owner name of the model
                    'label'     => \skeeks\cms\shop\Module::t('app', 'Orders (paid / total)'),
                    'format'    => 'raw',
                    'value'     => \yii\helpers\ArrayHelper::getValue($userStatistics, 'totalPayed') . "/" . \yii\helpers\ArrayHelper::getValue($userStatistics, 'total'),
                ],

                [                      // the owner name of the model
                    'label'     => \skeeks\cms\shop\Module::t('app', 'Paid orders worth'),
                    'format'    => 'raw',
                    'value'     => \Yii::$app->money->intlFormatter()->format($money),
                ],

                [                      // the owner name of the model
                    'label'     => \skeeks\cms\shop\Module::t('app', 'The average price paid orders'),
                    'format'    => 'raw',
                    'value'     => $average
                ],

            ]
        ])?>





<?= $form->fieldSetEnd(); ?>

<?= $form->fieldSet("Профили покупателя"); ?>

    <?= \skeeks\cms\modules\admin\widgets\BlockTitleWidget::widget([
        'content' => 'Все профили покупателя'
    ])?>

        <?= \skeeks\cms\modules\admin\widgets\GridView::widget([
            'dataProvider' => new \yii\data\ActiveDataProvider([
                'query' => \skeeks\cms\shop\models\ShopBuyer::find()->where([
                    'cms_user_id' => $model->id
                ])
            ]),
            'columns' =>
            [
                'id',
                'name',

                [
                    'class'         => \yii\grid\DataColumn::className(),
                    'attribute'     => 'shop_person_type_id',
                    'format'        => 'raw',
                    'value'         => function(\skeeks\cms\shop\models\ShopBuyer $model)
                    {
                        return $model->shopPersonType->name;
                    }
                ],

                [
                    'class'         => \skeeks\cms\grid\DateTimeColumnData::className(),
                    'attribute'     => 'created_at',
                ],

            ]
        ]); ?>

<?= $form->fieldSetEnd(); ?>

<?
$view = $this;
?>
<?= $form->fieldSet("Заказы (" . \skeeks\cms\shop\models\ShopOrder::find()->where([
                'user_id' => $model->id
            ])->count() .  ")"); ?>

    <?= \skeeks\cms\modules\admin\widgets\BlockTitleWidget::widget([
        'content' => 'Последние заказы'
    ])?>

    <?= \skeeks\cms\modules\admin\widgets\GridView::widget([
        'dataProvider' => new \yii\data\ActiveDataProvider([
            'query' => \skeeks\cms\shop\models\ShopOrder::find()->where([
                'user_id' => $model->id
            ])
        ]),
        'columns' =>
        [
            'id',

            [
                'class'     => \skeeks\cms\grid\CreatedAtColumn::className(),
            ],

            [
                'class'         => \yii\grid\DataColumn::className(),
                'attribute'     => 'status_code',
                'format'        => 'raw',
                'value'         => function(\skeeks\cms\shop\models\ShopOrder $order)
                {
                    return Html::label($order->status->name, null, [
                        'style' => "background: {$order->status->color}",
                        'class' => "label"
                    ]) . "<br />" .
                        Html::tag("small", \Yii::$app->formatter->asDatetime($order->status_at) . " (" . \Yii::$app->formatter->asRelativeTime($order->status_at) . ")")
                    ;
                }
            ],

            [
                'class'         => \skeeks\cms\grid\BooleanColumn::className(),
                'attribute'     => 'payed',
                'format'        => 'raw',
            ],


            [
                'class'         => \yii\grid\DataColumn::className(),
                'attribute'     => "canceled",
                'format'        => "raw",
                'filter'        => [
                    'Y' => \Yii::t('app', 'Yes'),
                    'N' => \Yii::t('app', 'No'),
                ],

                'value' => function(\skeeks\cms\shop\models\ShopOrder $shopOrder, $key, $index) use ($view)
                {
                    $reuslt = "<div>";
                    if ($shopOrder->canceled == "Y")
                    {
                        $view->registerJs(<<<JS
$('tr[data-key={$key}]').addClass('sx-tr-red');
JS
);

                        $view->registerCss(<<<CSS
tr.sx-tr-red, tr.sx-tr-red:nth-of-type(odd), tr.sx-tr-red td
{
background: #FFECEC !important;
}
CSS
);
                        $reuslt = "<div style='color: red;'>";
                    }

                    $reuslt .=  $shopOrder->canceled == "Y" ? \Yii::t('app', 'Yes') : \Yii::t('app', 'No');
                    $reuslt .= "</div>";
                    return $reuslt;
                }
            ],


            [
                'class'         => \yii\grid\DataColumn::className(),
                'format'        => 'raw',
                'attribute'     => 'price',
                'label'         => \skeeks\cms\shop\Module::t('app', 'Sum'),
                'value'         => function(\skeeks\cms\shop\models\ShopOrder $model)
                {
                    return \Yii::$app->money->intlFormatter()->format($model->money);
                },
            ],

            [
                'class'         => \yii\grid\DataColumn::className(),
                'attribute'     => 'site_id',
                'format'        => 'raw',
                'label'         => \skeeks\cms\shop\Module::t('app', 'Site'),
                'value'         => function(\skeeks\cms\shop\models\ShopOrder $model)
                {
                    return $model->site->name . " [{$model->site->code}]";
                },
            ],


        ]
    ]); ?>
<?= $form->fieldSetEnd(); ?>



<?
    $fuser = \skeeks\cms\shop\models\ShopFuser::find()->where(['user_id' => $model->id])->one();
?>

<?= $form->fieldSet(\skeeks\cms\shop\Module::t('app', 'Basket').' (' . \skeeks\cms\shop\models\ShopBasket::find()->where([
                'fuser_id' => $fuser->id
            ])->count() . ")"); ?>

    <?= \skeeks\cms\modules\admin\widgets\BlockTitleWidget::widget([
        'content' => \skeeks\cms\shop\Module::t('app', 'At the moment the user in a basket')
    ]); ?>

    <?= \skeeks\cms\modules\admin\widgets\GridView::widget([
        'dataProvider' => new \yii\data\ActiveDataProvider([
            'query' => \skeeks\cms\shop\models\ShopBasket::find()->where([
                'fuser_id' => $fuser->id
            ])
        ]),
        'columns' =>
        [
            [
                'class' => \skeeks\cms\grid\DateTimeColumnData::className(),
                'attribute' => 'created_at'
            ],

            [
                'class'     => \yii\grid\DataColumn::className(),
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

<?= $form->fieldSetEnd(); ?>


<?= $form->fieldSet(\skeeks\cms\shop\Module::t('app', 'Viewed products')." (" . \skeeks\cms\shop\models\ShopViewedProduct::find()->where([
                'shop_fuser_id' => $fuser->id
            ])->count() .  ")"); ?>

    <?= \skeeks\cms\modules\admin\widgets\GridView::widget([
        'dataProvider' => new \yii\data\ActiveDataProvider([
            'query' => \skeeks\cms\shop\models\ShopViewedProduct::find()->where([
                'shop_fuser_id' => $fuser->id
            ])->orderBy(['created_at' => SORT_DESC])
        ]),
        'columns' =>
        [
            [
                'class' => \skeeks\cms\grid\CreatedAtColumn::className(),
                'label' => \skeeks\cms\shop\Module::t('app', 'Date views'),
            ],

            [
                'class' => \yii\grid\DataColumn::className(),
                'format' => 'raw',
                'label' => \skeeks\cms\shop\Module::t('app', 'Good'),
                'value' => function(\skeeks\cms\shop\models\ShopViewedProduct $shopViewedProduct)
                {
                    if ($shopViewedProduct->shopProduct)
                    {
                        return Html::a($shopViewedProduct->shopProduct->cmsContentElement->name, $shopViewedProduct->shopProduct->cmsContentElement->url, [
                            'target' => '_blank',
                            'titla' => "Смотреть на сайте",
                            'data-pjax' => 0
                        ]);
                    }

                    return null;
                },
            ],

        ]
    ]); ?>

<?= $form->fieldSetEnd(); ?>

<?= $form->buttonsCreateOrUpdate($model); ?>
<?php ActiveForm::end(); ?>
