<?php
/* @var $model \skeeks\cms\models\CmsUser */
/* @var $this yii\web\View */
/* @var $controller \skeeks\cms\controllers\AdminCmsContentElementController
 * /* @var $action \skeeks\cms\backend\actions\BackendModelCreateAction|\skeeks\cms\backend\actions\IHasActiveForm
 */
/* @var $model \common\models\User */
$controller = $this->context;
$action = $controller->action;
$content = $controller->content;


$q = \skeeks\cms\models\CmsContentElement::find()->from([
    'c' => \skeeks\cms\models\CmsContentElement::tableName(),
])
    ->cmsSite()
    //->joinWith('createdBy as createdBy')
    ->andWhere(['c.content_id' => $content->id])
    ->groupBy(['c.created_by'])
    ->select([
        'count'      => new \yii\db\Expression("count(1)"),
        "created_by" => 'c.created_by',
    ]);


$qProducts = \skeeks\cms\shop\models\ShopCmsContentElement::find()
    ->from([
        'c' => \skeeks\cms\models\CmsContentElement::tableName(),
    ])
    ->addSelect(['c.*'])
    ->addSelect(['barcode' => 'shopProductBarcodes.value'])
    ->addSelect(['count' => new \yii\db\Expression("count(*)")])
    ->cmsSite()
    ->andWhere(['c.content_id' => $content->id])
    ->joinWith("shopProduct as shopProduct", true, "INNER JOIN")
    ->joinWith("shopProduct.shopProductBarcodes as shopProductBarcodes", true, "INNER JOIN")
    ->groupBy('shopProductBarcodes.value')
    ->orderBy(['count' => SORT_DESC])
    ->having(['>', 'count', 1]);

$qProducts = \skeeks\cms\shop\models\ShopCmsContentElement::find()
    ->from([
        'c' => \skeeks\cms\models\CmsContentElement::tableName(),
    ])
    ->addSelect(['c.*'])
    ->addSelect(['barcode' => 'shopProductBarcodes.value'])
    ->addSelect(['count' => new \yii\db\Expression("count(*)")])
    ->cmsSite()
    ->andWhere(['c.content_id' => $content->id])
    ->joinWith("shopProduct as shopProduct", true, "INNER JOIN")
    ->joinWith("shopProduct.shopProductBarcodes as shopProductBarcodes", true, "INNER JOIN")
    ->groupBy('shopProductBarcodes.value')
    ->orderBy(['count' => SORT_DESC])
    ->having(['>', 'count', 1]);


$qProductsName = \skeeks\cms\shop\models\ShopCmsContentElement::find()
    ->from([
        'c' => \skeeks\cms\models\CmsContentElement::tableName(),
    ])
    ->addSelect(['c.*'])
    ->addSelect(['count' => new \yii\db\Expression("count(*)")])
    ->cmsSite()
    ->andWhere(['c.content_id' => $content->id])
    ->joinWith("shopProduct as shopProduct", true, "INNER JOIN")
    ->groupBy('c.name')
    ->orderBy(['count' => SORT_DESC])
    ->having(['>', 'count', 1]);


?>
<div class="sx-box sx-bg-secondary" style="padding: 10px; margin-bottom: 20px;">
    <h3>Дубли по штрихкоду</h3>
    <?php if ($qProducts->count() > 0) : ?>
        <?
        echo \skeeks\cms\backend\widgets\GridViewWidget::widget([
            'dataProvider'   => new \yii\data\ActiveDataProvider([
                'query' => $qProducts,
            ]),
            'visibleColumns' => [
                'barcode',
                'count',
                'products',
            ],
            'columns'        => [
                'barcode'  => [
                    'label'         => 'Штрихкод',
                    'value'         => function ($model) {
                        return $model->raw_row['barcode'];
                    },
                    'headerOptions' => [
                        'style' => 'width: 200px; ',
                    ],
                ],
                'count'    => [
                    'label'         => 'Количество товаров',
                    'value'         => function ($model) {
                        return $model->raw_row['count'];
                    },
                    'headerOptions' => [
                        'style' => 'width: 50px;',
                    ],
                ],
                'products' => [
                    'label'  => 'Дубли',
                    'format' => 'raw',
                    'value'  => function ($model) {
                        return \Yii::$app->view->render("_dublicate-item", [
                            'model' => $model,
                        ]);
                    },
                ],
            ],
        ]);
        ?>
    <?php else: ?>
        <p style="color: green;">Нет дублей! Отлично!</p>
    <?php endif; ?>
</div>
<div class="sx-box sx-bg-secondary" style="padding: 10px;">
    <h3>Дубли по названию</h3>
    <?php $widget = \yii\bootstrap\Alert::begin([
        'closeButton' => false,
    ]); ?>
    <p style="color: red; font-weight: bold; margin-bottom: 5px;">Избавляйтесь от одинаковых названий!</p>
    <ul>
        <li>Во первых одинаковые названия приводят к ошибкам в работе, кассиров и менеджеров магазина!</li>
        <li>Во вторых одинаковые названия это очень плохо для продвижения вашего сайта!</li>
    </ul>
    <?php $widget::end(); ?>
    <?php if ($qProductsName->count() > 0) : ?>
        <?
        echo \skeeks\cms\backend\widgets\GridViewWidget::widget([
            'dataProvider'   => new \yii\data\ActiveDataProvider([
                'query'      => $qProductsName,

            ]),
            'visibleColumns' => [
                'name',
                'count',
                'products',
            ],

            'columns'        => [
                'name'     => [
                    'label'         => 'Название - дубль',
                    'value'         => function ($model) {
                        return $model->name;
                    },
                    'headerOptions' => [
                        'style' => 'width: 200px; ',
                    ],
                ],
                'count'    => [
                    'label'         => 'Количество',
                    'value'         => function ($model) {
                        return $model->raw_row['count'];
                    },
                    'headerOptions' => [
                        'style' => 'width: 50px;',
                    ],
                ],
                'products' => [
                    'label'  => 'Товары',
                    'format' => 'raw',
                    'value'  => function ($model) {
                        return \Yii::$app->view->render("_dublicate-item-name", [
                            'model' => $model,
                        ]);
                    },
                ],
            ],

        ]);
        ?>
    <?php else: ?>
        <p>Нет дублей! Отлично!</p>
    <?php endif; ?>
</div>
