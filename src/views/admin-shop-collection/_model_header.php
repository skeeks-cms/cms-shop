<?php
/**
 * @var yii\web\View $this
 * @var \skeeks\cms\shop\models\ShopCollection $model
 */

use skeeks\cms\shop\widgets\admin\ShopCatalogModelHeader;

$supplierUrl = $model->sx_id && isset(Yii::$app->skeeksSuppliersApi)
    ? Yii::$app->skeeksSuppliersApi->getCollectionUrl($model->sx_id)
    : null;

echo ShopCatalogModelHeader::widget([
    'model'       => $model,
    'supplierUrl' => $supplierUrl,
    'publicUrl'   => $model->absoluteUrl,
]);
