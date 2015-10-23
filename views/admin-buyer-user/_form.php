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
?>

<?php $form = ActiveForm::begin(); ?>

<?= $form->fieldSet('Общая информация'); ?>

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
                  'value' => '',
                ],
            ]
        ])?>

    <?= \skeeks\cms\modules\admin\widgets\BlockTitleWidget::widget([
        'content' => 'Статистика заказов'
    ])?>

        <?
            $userStatistics = [
                'total'         => \skeeks\cms\shop\models\ShopOrder::find()->where(['user_id' => $model->id])->count(),
                'totalPayed'         => \skeeks\cms\shop\models\ShopOrder::find()->where([
                    'user_id'   => $model->id,
                    'payed'     => \skeeks\cms\components\Cms::BOOL_Y
                ])->count(),
            ];
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
                    'value'     => "",
                ],

                [                      // the owner name of the model
                    'label'     => \skeeks\cms\shop\Module::t('app', 'The average price paid orders'),
                    'format'    => 'raw',
                    'value'     => "",
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
                'class'         => \skeeks\cms\grid\BooleanColumn::className(),
                'attribute'     => 'canceled',
                'format'        => 'raw',
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

            [
                'class'     => \skeeks\cms\grid\CreatedAtColumn::className(),
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
                'class' => \yii\grid\DataColumn::className(),
                'attribute' => 'name'
            ],

            [
                'class' => \yii\grid\DataColumn::className(),
                'label' => \skeeks\cms\shop\Module::t('app', 'Price'),
                'value' => function(\skeeks\cms\shop\models\ShopBasket $shopBasket)
                {
                    return \Yii::$app->money->intlFormatter()->format($shopBasket->money);
                }
            ],

            [
                'class' => \yii\grid\DataColumn::className(),
                'attribute' => 'quantity'
            ],

            [
                'class'     => \yii\grid\DataColumn::className(),
                'attribute' => 'site_id'
            ]

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
            ])
        ]),
        'columns' =>
        [
            [
                'class' => \skeeks\cms\grid\CreatedAtColumn::className(),
                'label' => \skeeks\cms\shop\Module::t('app', 'Date views'),
            ],

            [
                'class' => \yii\grid\DataColumn::className(),
                'label' => \skeeks\cms\shop\Module::t('app', 'Good'),
                'value' => function(\skeeks\cms\shop\models\ShopViewedProduct $shopViewedProduct)
                {
                    if ($shopViewedProduct->shopProduct)
                    {
                        return $shopViewedProduct->shopProduct->cmsContentElement->name;
                    }

                    return null;
                },
            ],

        ]
    ]); ?>

<?= $form->fieldSetEnd(); ?>

<?= $form->buttonsCreateOrUpdate($model); ?>
<?php ActiveForm::end(); ?>
