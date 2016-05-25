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

<?= $form->fieldSet(\Yii::t('skeeks/shop/app', 'Main')); ?>

    <?= \skeeks\cms\modules\admin\widgets\BlockTitleWidget::widget([
        'content' => \Yii::t('skeeks/shop/app','General information')
    ])?>

        <?= \yii\widgets\DetailView::widget([
            'model' => $model,
            'template'   => "<tr><th style='width: 50%; text-align: right;'>{label}</th><td>{value}</td></tr>",
            'attributes' =>
            [
                [                      // the owner name of the model
                    'label' => \Yii::t('skeeks/shop/app', 'User site'),
                    'format' => 'raw',
                    'value' => ($model->avatarSrc ? Html::img($model->avatarSrc) . " " : "") . $model->username,
                ],

                'email',
                [                      // the owner name of the model
                    'label' => \Yii::t('skeeks/shop/app', 'Date of registration'),
                    'format' => 'raw',
                    'value' =>\Yii::$app->formatter->asDatetime($model->created_at),
                ],

                [                      // the owner name of the model
                  'label' => \Yii::t('skeeks/shop/app', 'Date of last login'),
                  'format' => 'raw',
                  'value' => \Yii::$app->formatter->asDatetime($model->logged_at),
                ],
                [                      // the owner name of the model
                  'label' => \Yii::t('skeeks/shop/app', 'User groups'),
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
                    'label'     => \Yii::t('skeeks/shop/app', 'Orders (paid / total)'),
                    'format'    => 'raw',
                    'value'     => \yii\helpers\ArrayHelper::getValue($userStatistics, 'totalPayed') . "/" . \yii\helpers\ArrayHelper::getValue($userStatistics, 'total'),
                ],

                [                      // the owner name of the model
                    'label'     => \Yii::t('skeeks/shop/app', 'Paid orders worth'),
                    'format'    => 'raw',
                    'value'     => \Yii::$app->money->intlFormatter()->format($money),
                ],

                [                      // the owner name of the model
                    'label'     => \Yii::t('skeeks/shop/app', 'The average price paid orders'),
                    'format'    => 'raw',
                    'value'     => $average
                ],

            ]
        ])?>





<?= $form->fieldSetEnd(); ?>

<?= $form->fieldSet("Профили покупателя (" . \skeeks\cms\shop\models\ShopBuyer::find()->where([
                'cms_user_id' => $model->id
            ])->count() .  ")"); ?>

    <?= \skeeks\cms\modules\admin\widgets\BlockTitleWidget::widget([
        'content' => 'Все профили покупателя'
    ])?>

        <?= \skeeks\cms\modules\admin\widgets\RelatedModelsGrid::widget([
                'label'             => "",
                'parentModel'       => $model,
                'relation'          => [
                    'cms_user_id'      => 'id',
                ],

                'sort'              => [
                    'defaultOrder' =>
                    [
                        'updated_at' => SORT_DESC
                    ]
                ],

                'controllerRoute'   => 'shop/admin-buyer',
                'gridViewOptions'   =>
                [
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
                ]
        ]);
        ?>


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
            ]),

            'sort' =>
            [
                'defaultOrder' =>
                [
                    'created_at' => SORT_DESC
                ]
            ],
        ]),

        'columns' =>
        [
            [
                'class' => \skeeks\cms\modules\admin\grid\ActionColumn::className(),
                'controller' => \Yii::$app->createController('/shop/admin-order')[0],
            ],


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
                    'Y' => \Yii::t('skeeks/shop/app', 'Yes'),
                    'N' => \Yii::t('skeeks/shop/app', 'No'),
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

                    $reuslt .=  $shopOrder->canceled == "Y" ? \Yii::t('skeeks/shop/app', 'Yes') : \Yii::t('skeeks/shop/app', 'No');
                    $reuslt .= "</div>";
                    return $reuslt;
                }
            ],


            [
                'class'         => \yii\grid\DataColumn::className(),
                'filter'        => false,
                'format'        => 'raw',
                'label'         => \Yii::t('skeeks/shop/app', 'Good'),
                'value'         => function(\skeeks\cms\shop\models\ShopOrder $model)
                {
                    if ($model->shopBaskets)
                    {
                        $result = [];
                        foreach ($model->shopBaskets as $shopBasket)
                        {
                            $money = \Yii::$app->money->intlFormatter()->format($shopBasket->money);
                            $result[] = Html::a($shopBasket->name, $shopBasket->product->cmsContentElement->url, [
                                    'target' => '_blank',
                                    'data-pjax' => '0'
                                ]) . <<<HTML
    — $shopBasket->quantity $shopBasket->measure_name
HTML;

                        }
                        return implode('<hr style="margin: 0px;"/>', $result);
                    }
                },
            ],

            [
                'class'         => \yii\grid\DataColumn::className(),
                'format'        => 'raw',
                'attribute'     => 'price',
                'label'         => \Yii::t('skeeks/shop/app', 'Sum'),
                'value'         => function(\skeeks\cms\shop\models\ShopOrder $model)
                {
                    return \Yii::$app->money->intlFormatter()->format($model->money);
                },
            ],


        ]
    ]); ?>
<?= $form->fieldSetEnd(); ?>



<?
    $fuser = \skeeks\cms\shop\models\ShopFuser::find()->where(['user_id' => $model->id])->one();
?>

<?= $form->fieldSet(\Yii::t('skeeks/shop/app', 'Basket').' (' . \skeeks\cms\shop\models\ShopBasket::find()->where([
                'fuser_id' => $fuser->id
            ])->count() . ")"); ?>

    <?= \skeeks\cms\modules\admin\widgets\BlockTitleWidget::widget([
        'content' => \Yii::t('skeeks/shop/app', 'At the moment the user in a basket')
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
                'class' => \skeeks\cms\grid\CreatedAtColumn::className()
            ],

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
    ]); ?>

<?= $form->fieldSetEnd(); ?>


<?= $form->fieldSet(\Yii::t('skeeks/shop/app', 'Viewed products')." (" . \skeeks\cms\shop\models\ShopViewedProduct::find()->where([
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
                'class' => \skeeks\cms\modules\admin\grid\ActionColumn::className(),
                'controller' => \Yii::$app->createController('/shop/admin-viewed-product')[0],
            ],

            [
                'class' => \skeeks\cms\grid\CreatedAtColumn::className(),
                'label' => \Yii::t('skeeks/shop/app', 'Date views'),
            ],

            [
                'class' => \yii\grid\DataColumn::className(),
                'format' => 'raw',
                'label' => \Yii::t('skeeks/shop/app', 'Good'),
                'value' => function(\skeeks\cms\shop\models\ShopViewedProduct $shopViewedProduct)
                {
                    return (new \skeeks\cms\modules\admin\widgets\AdminImagePreviewWidget([
                        'image' => $shopViewedProduct->shopProduct->cmsContentElement->image,
                        'maxWidth' => "25px"
                    ]))->run() . " " . Html::a($shopViewedProduct->shopProduct->cmsContentElement->name, $shopViewedProduct->shopProduct->cmsContentElement->url, [
                        'target' => "_blank",
                        'data-pjax' => 0,
                    ] );

                    return null;
                },
            ],

        ]
    ]); ?>

<?= $form->fieldSetEnd(); ?>

<div style="text-align: center; margin-top: 15px;">
    <a data-pjax="0" href="<?= \skeeks\cms\helpers\UrlHelper::construct(['/shop/admin-order/create-order', 'cmsUserId' => $model->id])->enableAdmin()->toString()?>" class="btn btn-primary">Создать заказ</a>
</div>

<?php ActiveForm::end(); ?>
