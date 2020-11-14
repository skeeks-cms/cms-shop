<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 14.10.2015
 */
/* @var $this yii\web\View */

$controller = $this->context;
/* @var $controller \skeeks\cms\shop\controllers\AdminCmsContentElementController */
$cmsContent = $controller->content;

$model = new \skeeks\cms\shop\models\ShopProduct();
?>
<? $form = \skeeks\cms\modules\admin\widgets\ActiveForm::begin(); ?>

<? if ($cmsContent) : ?>
    <?= $form->field($model, 'offers_pid')->widget(
        \skeeks\cms\widgets\AjaxSelectModel::class,
        [
            'modelClass'  => \skeeks\cms\shop\models\ShopCmsContentElement::class,
            'searchQuery' => function ($word = '') {
                $query = \skeeks\cms\shop\models\ShopCmsContentElement::find()->cmsSite()->joinWith("shopProduct as sp");
                $query->andWhere(['sp.product_type' => \skeeks\cms\shop\models\ShopProduct::TYPE_OFFERS]);

                if ($word) {
                    $query->search($word);
                }

                return $query;
            },
        ]
    )
        ->label('Товар содержащий модификации');
    ?>
<? endif; ?>


<?= $form->buttonsStandart($model, ['save']); ?>

<? \skeeks\cms\modules\admin\widgets\ActiveForm::end(); ?>


<? $alert = \yii\bootstrap\Alert::begin([
    'options' => [
        'class' => 'alert-info',
    ],
]) ?>

<? $alert::end(); ?>