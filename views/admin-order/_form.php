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
                    'value' => $form->fieldSelect($model, 'status', \yii\helpers\ArrayHelper::map(
                        \skeeks\cms\shop\models\ShopOrderStatus::find()->all(), 'code', 'name'
                    ))->label(false),
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
                    'value'     => $model->user->displayName,
                ],

                [                      // the owner name of the model
                    'label' => 'Тип плательщика',
                    'format' => 'raw',
                    'value' => $model->personType->name,
                ],

                [                      // the owner name of the model
                    'label' => 'Профиль покупателя',
                    'format' => 'raw',
                    'value' => $model->buyer->name,
                ],


            ]
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

<?= $form->fieldSetEnd(); ?>

<?= $form->buttonsCreateOrUpdate($model); ?>
<?php ActiveForm::end(); ?>
