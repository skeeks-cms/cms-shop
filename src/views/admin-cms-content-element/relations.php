<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */
/* @var $this yii\web\View */

/* @var $controller \skeeks\cms\shop\controllers\AdminCmsContentElementController */
$controller = $this->context;
$model = $controller->model;

$this->registerCss(<<<CSS
    .card-prod--photo img {
        max-width: 100%;
    }
    .card-prod {
        margin-right: 1rem;
        margin-bottom: 2rem;
    }
.card-prod--title {
    line-height: 1.1;
}
.card-prod--photo {
    margin-bottom: 0.5rem;
}
.card-prod--photo img {
    border-radius: 0.5rem;
}
CSS
);

$ajaxBackendUrl = \yii\helpers\Url::to(['relations-dettach', 'content_id' => $model->content_id]);
$modelId = $model->id;

$this->registerJs(<<<JS
    var modelId = {$modelId};
    $("body").on("click", ".sx-btn-dettach", function() {
        var jProduct = $(this).closest(".product-item");
        var product2_id = jProduct.data("key");
        
        var Ajax = sx.ajax.preparePostQuery("$ajaxBackendUrl", {
            'product1_id': modelId,
            'product2_id': product2_id,
        });
        Ajax.execute();
        
        jProduct.fadeOut("500", function() {
            jProduct.remove();
        });
        return false; 
    });
JS
);

?>
    <div style="background: #f9f9f9; margin-bottom: 1rem; margin-top: 1rem;">
        <? $form = \yii\widgets\ActiveForm::begin(); ?>
        <div class="row">
        <div class="col-12">
            <div class="alert alert-default">
                Выберите товары, которые попадут в блок "с этим товаром покупают" и нажмите кнопку "связать"
            </div>
            </div>
        </div>
        <div class="row">
            <div class="col-auto my-auto">
                <?
                echo \skeeks\cms\backend\widgets\SelectModelDialogContentElementWidget::widget([
                    'name'        => 'product_ids',
                    'multiple'    => true,
                    'closeDialogAfterSelect'    => false,
                    'selectBtn'   => [
                        'content' => '<i class="fa fa-list" aria-hidden="true"></i> Выбрать товары',
                    ],
                    'content_id'  => $model->content_id,
                    'dialogRoute' => [
                        '/shop/admin-cms-content-element',
                    ],
                ]);
                ?>
            </div>
            <div class="col-auto my-auto">
                <button type="submit" class="btn btn-primary">Связать</button>
            </div>
        </div>
        <? $form::end(); ?>
    </div>


<?


if ($product_ids = \Yii::$app->request->post("product_ids")) {
    if ($product_ids) {
        foreach ($product_ids as $product_id) {
            if ($product_id) {
                $shopProductRelation = new \skeeks\cms\shop\models\ShopProductRelation();
                $shopProductRelation->shop_product1_id = $model->id;
                $shopProductRelation->shop_product2_id = $product_id;
                if (!$shopProductRelation->save()) {
                    /*print_r($shopProductRelation->errors);
                    die;*/
                }
            }

        }
    }
}



$dataProvider = new \yii\data\ActiveDataProvider();
$dataProvider->query = \skeeks\cms\shop\models\ShopCmsContentElement::find();
$dataProvider->query->joinWith('shopProduct as sp');
$site_id = \Yii::$app->skeeks->site->id;
$dataProvider->query->andWhere(['cms_site_id' => $site_id]);
$dataProvider->query->andWhere([
    'in',
    'sp.product_type',
    [
        \skeeks\cms\shop\models\ShopProduct::TYPE_SIMPLE,
        \skeeks\cms\shop\models\ShopProduct::TYPE_OFFERS,
    ],
]);

$dataProvider->query->joinWith("shopProduct.shopProductRelations1 as shopProductRelations1")
        ->joinWith("shopProduct.shopProductRelations2 as shopProductRelations2")
        ->andWhere([
            '!=', 'sp.id', $model->id
        ])
        ->andWhere([
            'or',
            ["shopProductRelations1.shop_product1_id" => $model->id],
            ["shopProductRelations1.shop_product2_id" => $model->id],
            ["shopProductRelations2.shop_product1_id" => $model->id],
            ["shopProductRelations2.shop_product2_id" => $model->id],
        ]);
$dataProvider->query->groupBy(['shopProduct.id']);

echo \yii\widgets\ListView::widget([
    'dataProvider' => $dataProvider,
    'itemView'     => 'product-item',
    'emptyText'    => '',
    'options'      => [
        'class' => '',
        'tag'   => 'div',
    ],
    'itemOptions'  => [
        'tag'   => 'div',
        'class' => 'col-lg-2 col-sm-6 product-item',
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
]) ?>