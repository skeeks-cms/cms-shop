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
                    'value'     => Html::a($model->user->displayName. " [{$model->user->id}]", \skeeks\cms\helpers\UrlHelper::construct(['/cms/admin-user/update', 'pk' => $model->user->id ])->enableAdmin(), [
                        'data-pjax' => 0
                    ] ),
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
                    'label'     => 'Сбособ оплаты',
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


<?= $form->fieldSetEnd(); ?>

<?= $form->buttonsCreateOrUpdate($model); ?>
<?php ActiveForm::end(); ?>
