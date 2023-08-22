<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */
/**
 * @var $this yii\web\View
 */
/* @var $this yii\web\View */
/* @var $model \skeeks\cms\shop\models\ShopStore */
/* @var $controller \skeeks\cms\backend\controllers\BackendModelController */
/* @var $action \skeeks\cms\backend\actions\BackendModelCreateAction|\skeeks\cms\backend\actions\IHasActiveForm */
$controller = $this->context;
$action = $controller->action;
$model = $action->model;

$this->render("@skeeks/cms/shop/views/admin-shop-store-doc-move/view-css");

$totalNomencul = \skeeks\cms\shop\models\ShopStoreProduct::find()
    ->andWhere(['shop_store_id' => $model->id])
    ->andWhere(['>', 'quantity', 0])
    ->groupBy(['shop_product_id'])
    ->count();


$totalNomenculMinus = \skeeks\cms\shop\models\ShopStoreProduct::find()
    ->andWhere(['shop_store_id' => $model->id])
    ->andWhere(['<', 'quantity', 0])
    ->groupBy(['shop_product_id'])
    ->count();

$totalProducts = \skeeks\cms\shop\models\ShopStoreProduct::find()
    ->select([
        'sum' => new \yii\db\Expression("sum(quantity)"),
    ])
    ->andWhere(['shop_store_id' => $model->id])
    ->andWhere(['>', 'quantity', 0])
    ->asArray()
    ->one();


$totalProductsByCats = \skeeks\cms\shop\models\ShopStoreProduct::find()
    ->addSelect([
        "tree_id"   => 'cmsTree.name',
        "tree_name" => 'cmsTree.name',
        'cmsContentElement.tree_id',
        'total'     => new \yii\db\Expression("count(cmsContentElement.id)"),
    ])
    ->joinWith("shopProduct as shopProduct")
    ->joinWith("shopProduct.cmsContentElement as cmsContentElement")
    ->joinWith("shopProduct.cmsContentElement.cmsTree as cmsTree")
    ->andWhere(['shop_store_id' => $model->id])
    ->andWhere(['>', \skeeks\cms\shop\models\ShopStoreProduct::tableName().'.quantity', 0])
    ->groupBy([
        'cmsContentElement.tree_id',
        /*\skeeks\cms\shop\models\ShopStoreProduct::tableName() . '.shop_product_id'*/
    ])
    ->orderBy(['total' => SORT_DESC])
    ->asArray()
    ->all();

$baseTypePrice = \Yii::$app->shop->baseTypePrice;
$purchaseTypePrice = \Yii::$app->shop->purchaseTypePrice;

if ($purchaseTypePrice) {

    $result = \Yii::$app->db->createCommand(<<<SQL
    SELECT 
        sp.id,
        sum(ssp.quantity * price.price) as total
    FROM 
        shop_store_product as ssp
        INNER JOIN shop_product as sp ON sp.id = ssp.shop_product_id
        INNER JOIN shop_product_price as price ON price.product_id = sp.id AND price.type_price_id = {$purchaseTypePrice->id}
    WHERE 
        ssp.shop_store_id = {$model->id}
    AND 
        ssp.quantity > 0
    /*GROUP BY sp.id*/
SQL
    )->queryOne();

    $purchaseSum = \yii\helpers\ArrayHelper::getValue($result, 'total');
}

if ($baseTypePrice) {

    $result = \Yii::$app->db->createCommand(<<<SQL
    SELECT 
        sp.id,
        sum(ssp.quantity * price.price) as total
    FROM 
        shop_store_product as ssp
        INNER JOIN shop_product as sp ON sp.id = ssp.shop_product_id
        INNER JOIN shop_product_price as price ON price.product_id = sp.id AND price.type_price_id = {$baseTypePrice->id}
    WHERE 
        ssp.shop_store_id = {$model->id}
    AND 
        ssp.quantity > 0
    /*GROUP BY sp.id*/
SQL
    )->queryOne();


    $baseSum = \yii\helpers\ArrayHelper::getValue($result, 'total');
}
?>

<div class="row">
    <div class="col-md-6">
        <div class="alert-default alert">
            <h1>Оценка склада</h1>

            <div class="sx-properties-wrapper sx-columns-1">
                <ul class="sx-properties sx-bg-secondary" style="padding: 10px;">
                    <li>
                <span class="sx-properties--name">
                    Номенкулатура
                    <i class="far fa-question-circle" style="margin-left: 5px;" data-toggle="tooltip"
                       title="Столько всего товаров разного наименования в магазине/складе">
                    </i>
                </span>
                        <span class="sx-properties--value">
                    <?php echo \Yii::$app->formatter->asDecimal($totalNomencul); ?> шт.
                </span>
                    </li>
                    <li>
                        <span class="sx-properties--name">
                        Товары
                        <i class="far fa-question-circle" style="margin-left: 5px;" data-toggle="tooltip"
                           title="Столько всего единиц товара в магазине/складе на полках">
                        </i>
                        </span>
                        <span class="sx-properties--value">
                            <?php
                            $total = \yii\helpers\ArrayHelper::getValue($totalProducts, "sum");
                            echo \Yii::$app->formatter->asDecimal($total); ?> ед.
                        </span>


                    </li>


                    <?php if ($purchaseTypePrice) : ?>
                        <li>
                            <span class="sx-properties--name">
                                Цена всех товаров по закупочным ценам
                                <i class="far fa-question-circle" style="margin-left: 5px;" data-toggle="tooltip"
                                   title="Рассчет по текущим закупочным ценам, на сегодняшнюю дату. Если сейчас вам купить все товары на склад, то потребуется такая сумма.">
                                </i>
                            </span>
                            <span class="sx-properties--value">
                                <?php
                                $money = new \skeeks\cms\money\Money((string)$purchaseSum, \Yii::$app->money->currency_code);
                                echo $money;
                                ?>
                            </span>
                        </li>
                    <?php endif; ?>

                    <?php if ($baseTypePrice) : ?>
                        <li>
                            <span class="sx-properties--name">
                                Цена всех товаров по розничным ценам
                                <i class="far fa-question-circle" style="margin-left: 5px;" data-toggle="tooltip"
                                   title="Рассчет по текущим розничным ценам, на сегодняшнюю дату. Если сейчас вам продать весь товар, то вы получите такую сумму.">
                                </i>
                            </span>
                            <span class="sx-properties--value">
                                <?php
                                $money = new \skeeks\cms\money\Money((string)$baseSum, \Yii::$app->money->currency_code);
                                echo $money;
                                ?>
                            </span>
                        </li>
                    <?php endif; ?>


                </ul>
            </div>

        </div>
    </div>
    <div class="col-md-6">
        <div class="alert-default alert">
            <h1>Проблемы</h1>

            <div class="sx-properties-wrapper sx-columns-1">
                <ul class="sx-properties sx-bg-secondary" style="padding: 10px;">
                    <li>
                <span class="sx-properties--name">
                    Номенкулатура с отрицательным остатком
                    <i class="far fa-question-circle" style="margin-left: 5px;" data-toggle="tooltip"
                       title="Столько всего товаров разного наименования в магазине/складе с количеством меньше нуля!">
                    </i>
                </span>
                        <span class="sx-properties--value">
                    <?php echo \Yii::$app->formatter->asDecimal($totalNomenculMinus); ?> шт.
                </span>
                    </li>


                </ul>
            </div>
        </div>
    </div>


    <div class="col-md-6">
        <div class="alert-default alert">
            <h1>Данные по категориям</h1>


            <div class="sx-properties-wrapper sx-columns-1">
                <ul class="sx-properties sx-bg-secondary" style="padding: 10px;">
                    <li>
                        <span class="sx-properties--name">
                        Товары
                        <i class="far fa-question-circle" style="margin-left: 5px;" data-toggle="tooltip"
                           title="Столько всего единиц товара в магазине/складе на полках">
                        </i>
                        </span>
                        <span class="sx-properties--value">
                            <?php
                            $total = \yii\helpers\ArrayHelper::getValue($totalProducts, "sum");
                            echo \Yii::$app->formatter->asDecimal($total); ?> ед.

                            <?php if($purchaseTypePrice) : ?>
                             / <span class="sx-money"  style="color: gray;"><?php
                                $money = new \skeeks\cms\money\Money((string)$purchaseSum, \Yii::$app->money->currency_code);
                                echo $money;
                                    ?></span>
                            <? endif; ?>
                        </span>


                    </li>

                    <?php if ($totalProductsByCats) : ?>
                        <? foreach ($totalProductsByCats as $data) : ?>
                            <li style="padding-left: 20px;">
                                <span class="sx-properties--name">

                                <?php

                                $tree_id = \yii\helpers\ArrayHelper::getValue($data, "tree_id");
                                $cmsTree = \skeeks\cms\models\CmsTree::findOne($tree_id);
                                $treeName = \yii\helpers\ArrayHelper::getValue($data, "tree_name", 'Без категории');
                                /**
                                 * @var \skeeks\cms\models\CmsTree $cmsTree
                                 */
                                ?>
                                    <?php if($cmsTree) : ?>
                                        <a href="<?php echo $cmsTree->url; ?>" title="<?php echo $cmsTree->fullName; ?>" target="_blank"><?php echo $cmsTree->name; ?></a>
                                    <?php else : ?>
                                        Без категории
                                    <?php endif; ?>

                                </span>
                                <span class="sx-properties--value">
                                    <?php
                                    echo \Yii::$app->formatter->asDecimal(\yii\helpers\ArrayHelper::getValue($data, "total")); ?> ед.

                                    <?php if($purchaseTypePrice) : ?>

                                    <?


                                    if ($tree_id) {
                                        $sqlTree = "cce.tree_id = {$tree_id}";
                                    } else {
                                        $sqlTree = "cce.tree_id is null";
                                    }
                                    $resultByCat = \Yii::$app->db->createCommand(<<<SQL
    SELECT 
        sp.id,
        sum(ssp.quantity * price.price) as total
    FROM 
        shop_store_product as ssp
        INNER JOIN shop_product as sp ON sp.id = ssp.shop_product_id
        LEFT JOIN cms_content_element as cce ON sp.id = cce.id

        INNER JOIN shop_product_price as price ON price.product_id = sp.id AND price.type_price_id = {$purchaseTypePrice->id}
    WHERE 
        ssp.shop_store_id = {$model->id}
    AND 
        ssp.quantity > 0
    AND {$sqlTree}
        
    /*GROUP BY sp.id*/
SQL
    )->queryOne();

    $purchaseSum = \yii\helpers\ArrayHelper::getValue($resultByCat, 'total');
    ?>
                                    / <span class="sx-money" style="color: gray;"><?php
                                $money = new \skeeks\cms\money\Money((string) round((float)$purchaseSum), \Yii::$app->money->currency_code);
                                echo $money;
                                    ?></span>

                                    <? endif; ?>
                                </span>


                            </li>
                        <? endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>

</div>



