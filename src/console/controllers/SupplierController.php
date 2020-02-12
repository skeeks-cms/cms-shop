<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 22.03.2016
 */

namespace skeeks\cms\shop\console\controllers;

use skeeks\cms\shop\models\ShopProduct;
use skeeks\cms\shop\models\ShopSupplier;
use skeeks\cms\shop\models\ShopSupplierProperty;
use skeeks\cms\shop\models\ShopSupplierPropertyOption;
use yii\base\Exception;
use yii\console\Controller;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class SupplierController extends Controller
{
    public function actionLoadOptions($supplier_id)
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
        $this->stdout("Products: ".$shopProductsQuery->count()."\n");

        if (!$shopProductsQuery->count()) {
            $this->stdout("Товаров нет\n");
            return false;
        }

        /**
         * @var $properties ShopSupplierProperty[]
         */
        if (!$properties = $shopSupplier->getShopSupplierProperties()->andWhere(['property_type' => ShopSupplierProperty::PROPERTY_TYPE_LIST])->all()) {
            $this->stdout("Нет свойств типа список\n");
            return false;
        }
        


        /**
         * @var $shopProduct ShopProduct
         */
        foreach ($shopProductsQuery->each(10) as $shopProduct) {
            if ($shopProduct->supplier_external_jsondata) {
                foreach ($properties as $property)
                {
                    $value = ArrayHelper::getValue($shopProduct->supplier_external_jsondata, $property->external_code);
                    if (is_string($value)) {
                        $value = [trim($value)];
                    } elseif (is_array($value)) {
                        $value = (array) $value;
                    } else {
                        continue;
                    }
                    
                    foreach ($value as $val)
                    {
                        if (!$val) {
                            continue;
                        }
                        
                        if (!$option = $property->getShopSupplierPropertyOptions()->andWhere(['name' => $val])->one()) {
                            $option = new ShopSupplierPropertyOption();
                            $option->name = $val;
                            $option->shop_supplier_property_id = $property->id;
                            if (!$option->save()) {
                                throw new Exception("Option not save! " . print_r($option->errors, true));
                            } else {
                                $this->stdout("added option: {$val} \n");
                            }
                        }
                    }
                    
                    
                }
            }
        }

    }

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
        $this->stdout("Products: ".$shopProductsQuery->count()."\n");

        if (!$shopProductsQuery->count()) {
            $this->stdout("Товаров нет\n");
            return false;
        }

        /**
         * @var $shopProduct ShopProduct
         */
        foreach ($shopProductsQuery->each(10) as $shopProduct) {
            if ($shopProduct->supplier_external_jsondata) {
                foreach ($shopProduct->supplier_external_jsondata as $key => $value) {
                    $key = trim($key);
                    if (!$shopSupplier->getShopSupplierProperties()->andWhere(['external_code' => $key])->one()) {
                        $shopSupplierProperty = new ShopSupplierProperty();
                        $shopSupplierProperty->external_code = $key;
                        $shopSupplierProperty->shop_supplier_id = $shopSupplier->id;

                        if ($shopSupplierProperty->save()) {
                            $this->stdout("Создано: {$key}\n", Console::FG_GREEN);
                        } else {
                            $this->stdout("Не сохранено: {$key} ".print_r($shopSupplier->errors, true)."\n", Console::FG_RED);
                        }
                    }
                }
            }
        }
    }
}