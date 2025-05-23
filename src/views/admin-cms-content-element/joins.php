<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */
/* @var $this yii\web\View */

/* @var $controller \skeeks\cms\shop\controllers\AdminCmsContentElementController */
/* @var $model \skeeks\cms\shop\models\ShopCmsContentElement */
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

$ajaxBackendUrl = \yii\helpers\Url::to(['joins-dettach', 'content_id' => $model->content_id]);
$modelId = $model->id;

$this->registerJs(<<<JS
    var modelId = {$modelId};
    $("body").on("click", ".sx-btn-dettach", function() {
        var jProduct = $(this).closest(".product-item");
        var product2_id = jProduct.data("key");
        
        var Ajax = sx.ajax.preparePostQuery("$ajaxBackendUrl", {
            'product_id': product2_id,
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
<div class="sx-block">
    <? $form = \yii\widgets\ActiveForm::begin(); ?>

    <p>Выберите товары, которые попадут в блок "Варианты товара" и нажмите кнопку "Связать"</p>
    <div class="row">
        <div class="col-auto my-auto">
            <?
            echo \skeeks\cms\backend\widgets\SelectModelDialogContentElementWidget::widget([
                'name'                   => 'product_ids',
                'multiple'               => true,
                'closeDialogAfterSelect' => false,
                'selectBtn'              => [
                    'content' => '<i class="fa fa-list" aria-hidden="true"></i> Выбрать товары',
                ],
                'content_id'             => $model->content_id,
                'dialogRoute'            => [
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
    /*print_r($product_ids);die;*/
    if ($product_ids) {

        $t = \Yii::$app->db->beginTransaction();

        try {
            if (!$model->shopProduct->shop_product_model_id) {
                $spModel = new \skeeks\cms\shop\models\ShopProductModel();
                $spModel->save();

                $model->shopProduct->shop_product_model_id = $spModel->id;
                $model->shopProduct->update(false, ['shop_product_model_id']);
            } else {
                $spModel = $model->shopProduct->shopProductModel;
            }


            foreach ($product_ids as $product_id) {
                if ($product_id) {

                    if ($sp = \skeeks\cms\shop\models\ShopProduct::findOne($product_id)) {
                        $sp->shop_product_model_id = $spModel->id;
                        $sp->update(false, ['shop_product_model_id']);
                    }

                }

            }

            $t->commit();
        } catch (\Exception $exception) {
            $t->rollBack();
            throw $exception;
        }

    }
}




if ($model->shopProduct->shop_product_model_id) {

    $dataProvider = new \yii\data\ActiveDataProvider();
    $dataProvider->query = \skeeks\cms\shop\models\ShopCmsContentElement::find();
    $dataProvider->query->joinWith('shopProduct as sp');

    $dataProvider->query->andWhere(['sp.shop_product_model_id' => $model->shopProduct->shop_product_model_id]);
    //$dataProvider->query->andWhere(['!=', 'sp.id', $model->shopProduct->id]);
    $dataProvider->query->groupBy(['sp.id']);
}


if ($model->shopProduct->shop_product_model_id) {
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
    ]);

}
?>

