<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 06.11.2015
 */
/* @var $this yii\web\View */
/* @var $widget \skeeks\cms\shop\widgets\admin\PropductPriceChangeAdminWidget */
?>
<? if ($widget->productPrice) : ?>
    <a href="#sx-price-change-<?= $widget->id; ?>" class="btn btn-default sx-fancybox"><i class="glyphicon glyphicon-eye-open"></i> История изменений</a>
    <div style="display: none;">
        <div class="" id="sx-price-change-<?= $widget->id; ?>">
            <h2>История изменения цены: "<?= ($widget->productPrice && $widget->productPrice->typePrice) ? $widget->productPrice->typePrice->name : "Базовая цена"; ?>"</h2>
            <hr />
            <?= \yii\grid\GridView::widget([
                'dataProvider' => new \yii\data\ArrayDataProvider([
                    'allModels' => $widget->productPrice->shopProductPriceChanges,
                    'pagination' => [
                      'pageSize' => 20,
                    ],
                ]),
                'columns' =>
                [
                    [
                        'class' => \skeeks\cms\grid\CreatedAtColumn::className(),
                        'label' => 'Дата и время изменения'
                    ],

                    [
                        'class' => \skeeks\cms\grid\CreatedByColumn::className()
                    ],

                    [
                        'class' => \yii\grid\DataColumn::className(),
                        'label' => "Значение",
                        'value' => function(\skeeks\cms\shop\models\ShopProductPriceChange $model)
                        {
                            return \Yii::$app->money->intlFormatter()->format($model->money);
                        }
                    ]
                ]
            ])?>
        </div>
    </div>
<? endif; ?>