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
$dm = new \skeeks\cms\base\DynamicModel([
    'from',
    'to',
]);
$dm->addRule(['from', 'to'], 'string');
$dm->load(\Yii::$app->request->get());

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
    ->having(['>', 'count', 1])
;

//print_r($qProducts->createCommand()->rawSql);die;

?>
<?php /*$form = \yii\widgets\ActiveForm::begin([
    'method' => 'get',
]); */?><!--
<div class="sx-bg-secondary">
<div class="row" style="padding: 15px; padding-bottom: 0px;">
    <div class="col">
        <?php /*echo $form->field($dm, 'from')->textInput(['type' => 'date'])->label("Начало периода"); */?>
    </div>
    <div class="col">
        <?php /*echo $form->field($dm, 'to')->textInput(['type' => 'date'])->label("Конец периода"); */?>
    </div>
    <div class="col my-auto">
        <button type="submit" class="btn btn-primary">Отправить</button>
    </div>
</div>
</div>
--><?php /*$form::end(); */?>



<?
echo \skeeks\cms\widgets\GridView::widget([
    'dataProvider' => new \yii\data\ActiveDataProvider([
        'query' => $qProducts
    ]),
    'visibleColumns' => [
        'barcode',
        'count',
        'products',
    ],
    'columns' => [
        'barcode' => [
            'label' => 'Штрихкод',
            'value' => function($model) {
                return $model->raw_row['barcode'];
            },
            'headerOptions' => [
                'style' => 'width: 200px; '
            ]
        ],
        'count' => [
            'label' => 'Количество товаров',
            'value' => function($model) {
                return $model->raw_row['count'];
            },
            'headerOptions' => [
                'style' => 'width: 50px;'
            ]
        ],
        'products' => [
            'label' => 'Дубли',
            'format' => 'raw',
            'value' => function($model) {
                return \Yii::$app->view->render("_dublicate-item", [
                    'model' => $model
                ]);
            }
        ]
    ]
]);
/*
echo \yii\widgets\ListView::widget([
    'dataProvider' => new \yii\data\ActiveDataProvider([
        'query' => $qProducts
    ]),
    'itemView'     => '_dublicate-item.php',
    'emptyText'    => '',
    'options'      => [
        'class' => '',
        'tag'   => 'div',
    ],
    'itemOptions'  => [
        'tag'   => 'div',
        'class' => 'col-12 product-item',
    ],
    'pager'        => [
        'container' => '.list-view-products',
        'item'      => '.product-item',
        'class'     => \skeeks\cms\themes\unify\widgets\ScrollAndSpPager::class,
    ],
    //"\n{items}<div class=\"box-paging\">{pager}</div>{summary}<div class='sx-js-pagination'></div>",
    'layout'       => '<div class="row"><div class="col-md-12">{summary}</div></div>
<div class="no-gutters row list-view-products">{items}</div>
<div class="row"><div class="col-md-12">{pager}</div></div>',
])*/ ?>
