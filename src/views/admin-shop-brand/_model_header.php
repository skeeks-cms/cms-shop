<?php
/**
 * @var yii\web\View $this
 * @var \skeeks\cms\shop\models\ShopBrand $model
 */

use skeeks\cms\shop\widgets\admin\ShopCatalogModelHeader;

$supplierUrl = $model->sx_id && isset(Yii::$app->skeeksSuppliersApi)
    ? Yii::$app->skeeksSuppliersApi->getBrandUrl($model->sx_id)
    : null;

echo ShopCatalogModelHeader::widget([
    'model'       => $model,
    'supplierUrl' => $supplierUrl,
    'publicUrl'   => $model->absoluteUrl,
]);
