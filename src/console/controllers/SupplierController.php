<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 22.03.2016
 */

namespace skeeks\cms\shop\console\controllers;

use skeeks\cms\shop\models\ShopProduct;
use skeeks\cms\shop\models\ShopProductQuantityChange;
use skeeks\cms\shop\models\ShopQuantityNoticeEmail;
use skeeks\cms\shop\models\ShopSupplier;
use skeeks\cms\shop\models\ShopSupplierProperty;
use yii\console\Controller;
use yii\helpers\Console;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class SupplierController extends Controller
{
    public function actionLoadProperties($supplier_id)
    {
        /**
         * @var $shopSupplier ShopSupplier
         */
        $shopSupplier = ShopSupplier::find()->where(['id' => $supplier_id])->one();
        if (!$shopSupplier) {
            $this->stdout("Такого поставщика нет\n");
            return false;
        }

        $this->stdout("Поставщик: {$shopSupplier->asText}\n");

        $shopProductsQuery = ShopProduct::find()->where(['shop_supplier_id' => $supplier_id]);
        $this->stdout("Products: " . $shopProductsQuery->count() . "\n");

        if (!$shopProductsQuery->count()) {
            $this->stdout("Товаров нет\n");
            return false;
        }

        /**
         * @var $shopProduct ShopProduct
         */
        foreach ($shopProductsQuery->each(10) as $shopProduct)
        {
            if ($shopProduct->supplier_external_jsondata) {
                foreach ($shopProduct->supplier_external_jsondata as $key => $value)
                {
                    $key = trim($key);
                    if (!$shopSupplier->getShopSupplierProperties()->andWhere(['external_code' => $key])->one()) {
                        $shopSupplierProperty = new ShopSupplierProperty();
                        $shopSupplierProperty->external_code = $key;
                        $shopSupplierProperty->shop_supplier_id = $shopSupplier->id;

                        if ($shopSupplierProperty->save()) {
                             $this->stdout("Создано: {$key}\n", Console::FG_GREEN);
                        } else {
                            $this->stdout("Не сохранено: {$key} " . print_r($shopSupplier->errors, true) . "\n", Console::FG_RED);
                        }
                    }
                }
            }
        }
    }
}