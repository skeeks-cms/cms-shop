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
?>

<?php $form = ActiveForm::begin(); ?>

<?= $form->fieldSet('Основное'); ?>


    <?= $form->field($model, 'name')->textInput(['maxlength' => 255]); ?>
    <?= $form->fieldRadioListBoolean($model, 'active'); ?>
    <?= $form->field($model, 'siteCodes')->checkboxList(
        \yii\helpers\ArrayHelper::map(\skeeks\cms\models\CmsSite::find()->all(), 'code', 'name')
    ); ?>
    <?= $form->fieldInputInt($model, 'priority'); ?>

<?= $form->fieldSetEnd(); ?>


<? if (!$model->isNewRecord) : ?>
    <?= $form->fieldSet('Свойства') ?>
        <?= \skeeks\cms\modules\admin\widgets\RelatedModelsGrid::widget([
            'label'             => "Свойства плательщика",
            'hint'              => "У каждого плательщика, можно задать свой набор свойств, которые он будет задавать при оформлении заказа.",
            'parentModel'       => $model,
            'relation'          => [
                'shop_person_type_id' => 'id'
            ],

            'sort'              => [
                'defaultOrder' =>
                [
                    'priority' => SORT_DESC
                ]
            ],

            'dataProviderCallback' => function($dataProvider)
            {
                /**
                 * @var \yii\data\BaseDataProvider $dataProvider
                */
                $dataProvider->getPagination()->defaultPageSize   = 5000;
            },

            'controllerRoute'   => 'shop/admin-person-type-property',
            'gridViewOptions'   => [
                'sortable' => true,
                'columns' => [
                    [
                        'attribute'     => 'name',
                        'enableSorting' => false
                    ],

                    [
                        'class'         => \skeeks\cms\grid\BooleanColumn::className(),
                        'attribute'     => 'active',
                        'falseValue'    => \skeeks\cms\components\Cms::BOOL_N,
                        'trueValue'     => \skeeks\cms\components\Cms::BOOL_Y,
                        'enableSorting' => false
                    ],

                    [
                        'attribute'     => 'code',
                        'enableSorting' => false
                    ],

                    [
                        'attribute'     => 'priority',
                        'enableSorting' => false
                    ],
                ],
            ],
        ]); ?>
    <?= $form->fieldSetEnd(); ?>
<? endif; ?>


<?= $form->buttonsCreateOrUpdate($model); ?>
<?php ActiveForm::end(); ?>
