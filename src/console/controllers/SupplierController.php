<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 22.03.2016
 */

namespace skeeks\cms\shop\console\controllers;

use skeeks\cms\models\CmsContentElement;
use skeeks\cms\relatedProperties\propertyTypes\PropertyTypeElement;
use skeeks\cms\relatedProperties\propertyTypes\PropertyTypeList;
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


    public function actionInsertCmsProperty($external_property_code)
    {
        /**
         * @var $shopSupplier ShopSupplier
         */
        $siteName = \Yii::$app->skeeks->site->name;
        $this->stdout("Поставщик: {$siteName}\n");

        /**
         * @var $shopSupplierProperty ShopSupplierProperty
         */
        if (!$shopSupplierProperty = ShopSupplierProperty::find()->cmsSite()->andWhere(['external_code' => $external_property_code])->one()) {
            $this->stdout("Свойство не найдено\n", Console::FG_RED);
            return false;
        }
        
        if (!$shopSupplierProperty->cmsContentProperty) {
            $this->stdout("Не настроено соответствие со свойством cms\n", Console::FG_RED);
            return false;
        }
        
      
        
        $shopProductsQuery = ShopProduct::find()->joinWith('cmsContentElement as cmsContentElement')
            //->andWhere(['cmsContentElement.id' => 47432])
            ->andWhere(['cmsContentElement.cms_site_id' => \Yii::$app->skeeks->site->id]);
        $this->stdout("Products: ".$shopProductsQuery->count()."\n");

        if (!$shopProductsQuery->count()) {
            $this->stdout("Товаров нет\n");
            return false;
        }
        
        /**
         * @var $shopProduct ShopProduct
         */
        foreach ($shopProductsQuery->each(10) as $shopProduct) {
            $this->stdout("\tProduct: {$shopProduct->id}\n");
            if ($shopProduct->supplier_external_jsondata) {
                foreach ($shopProduct->supplier_external_jsondata as $key => $value) {
                    $key = trim($key);
                    if ($key == $shopSupplierProperty->external_code) {
                        $this->stdout("\t\t$key: {$value}\n");
                        if (!trim($value)) {
                            $this->stdout("\t\tЗначение не заполнено\n", Console::FG_RED);
                            //sleep(5);
                            continue;
                        }
                        
                        if ($shopSupplierProperty->property_type == ShopSupplierProperty::PROPERTY_TYPE_LIST) {
                            
                            
                            /**
                             * @var $supplierOption ShopSupplierPropertyOption
                             */
                            if ($supplierOption = $shopSupplierProperty->getShopSupplierPropertyOptions()->andWhere(['name' => trim($value)])->one()) {
                                $this->stdout("\t\tОпция найдена в базе\n");
                                /*if (!$supplierOption->cmsContentElement || !$supplierOption->cmsContentPropertyEnum) {
                                    $this->stdout("\t\tДля опции не настроена связь с cms\n", Console::FG_RED);
                                    continue;
                                }*/
                                
                                if ($supplierOption->cmsContentElement) {
                                    $cmsElement = $shopProduct->cmsContentElement;
                                    $cmsElement->relatedPropertiesModel->setAttribute($shopSupplierProperty->cmsContentProperty->code, $supplierOption->cmsContentElement->id);
                                    if ($cmsElement->relatedPropertiesModel->save(true, [$shopSupplierProperty->cmsContentProperty->code])) {
                                                                        
                                        $this->stdout("\t\tЗначение свойства обновлено\n", Console::FG_GREEN);
                                        continue;
                                    } else {
                                        $this->stdout("\t\tЗначение свойства не сохранено!!!\n", Console::FG_RED);
                                        die;
                                        continue;
                                    }
                                }
                                
                                $this->stdout("\t\tНе проработанный вариант\n", Console::FG_RED);
                                continue;
                            } else {
                                $this->stdout("\t\tОпция не найдена в базе\n", Console::FG_RED);
                                sleep(5);
                                continue;
                            }
                            
                        } elseif ($shopSupplierProperty->property_type == ShopSupplierProperty::PROPERTY_TYPE_STRING) {
                            
                            $cmsElement = $shopProduct->cmsContentElement;
                            $cmsElement->relatedPropertiesModel->setAttribute($shopSupplierProperty->cmsContentProperty->code, trim($value));
                            if ($cmsElement->relatedPropertiesModel->save(true, [$shopSupplierProperty->cmsContentProperty->code])) {
                                                                
                                $this->stdout("\t\tЗначение свойства обновлено\n", Console::FG_GREEN);
                                continue;
                            } else {
                                $this->stdout("\t\tЗначение свойства не сохранено!!!\n", Console::FG_RED);
                                die;
                                continue;
                            }
                        }


                        
                    }
                }
            }
            
        }
    }
    
    public function actionConnectOptions()
    {
        /**
         * @var $shopSupplier ShopSupplier
         */
        $siteName = \Yii::$app->skeeks->site->name;
        $this->stdout("Поставщик: {$siteName}\n");

        /**
         * @var $properties ShopSupplierProperty[]
         * @var $option ShopSupplierPropertyOption
         */
        if (!$properties = ShopSupplierProperty::find()->cmsSite()
            ->andWhere(['property_type' => ShopSupplierProperty::PROPERTY_TYPE_LIST])
            ->andWhere(['is not', 'cms_content_property_id', null])
            ->all()) {
            $this->stdout("Нет свойств типа список\n");
            return false;
        }

        foreach ($properties as $property)
        {
            $this->stdout($property->asText . "\n");
            
            $queryOptions = $property->getShopSupplierPropertyOptions()->where([
                'and',
                ['cms_content_property_enum_id' => null],
                ['cms_content_element_id' => null],
            ]);
            $count = $queryOptions->count();
            if (!$count) {
                $this->stdout("\tВсе опции связаны\n");
                continue;
            }
            $this->stdout("\tНе связанных опций: {$count}\n");

            $content_id = null;
            $contentProperty = $property->cmsContentProperty;
            if ($property->cmsContentProperty->handler instanceof PropertyTypeList) {

            } elseif ($property->cmsContentProperty->handler instanceof PropertyTypeElement) {
                $content_id = $property->cmsContentProperty->handler->content_id;
            }


            foreach ($queryOptions->each(10) as $option)
            {
                $this->stdout("\tОпция: {$option->asText}\n");

                if ($content_id) {
                    if ($element = CmsContentElement::find()->andWhere(['content_id' => $content_id])->andWhere(['name' => $option->name])->one()) {
                        $option->cms_content_element_id = $element->id;
                        if ($option->save()) {
                            $this->stdout("\t\tСвязана\n", Console::FG_GREEN);
                        } else {
                            $this->stdout("\t\tНе связана " . print_r($option->errors, true) . "\n", Console::FG_RED);
                        }
                    }
                } else {
                    if ($enum = $contentProperty->getEnums()->andWhere(['value' => $option->name])->one()) {
                        $option->cms_content_property_enum_id = $enum->id;
                        if ($option->save()) {
                            $this->stdout("\t\tСвязана\n", Console::FG_GREEN);
                        } else {
                            $this->stdout("\t\tНе связана " . print_r($option->errors, true) . "\n", Console::FG_RED);
                        }
                    }
                }
            }
            
        }
        
    }


    public function actionLoadOptions()
    {
        $siteName = \Yii::$app->skeeks->site->name;
        $this->stdout("Поставщик: {$siteName}\n");

        $shopProductsQuery = ShopProduct::find()->joinWith('cmsContentElement as cmsContentElement')->andWhere(['cmsContentElement.cms_site_id' => \Yii::$app->skeeks->site->id]);
        $this->stdout("Products: ".$shopProductsQuery->count()."\n");

        if (!$shopProductsQuery->count()) {
            $this->stdout("Товаров нет\n");
            return false;
        }
        

        
        /**
         * @var $properties ShopSupplierProperty[]
         */
        if (!$properties = ShopSupplierProperty::find()->cmsSite()->andWhere(['property_type' => ShopSupplierProperty::PROPERTY_TYPE_LIST])->all()) {
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
                        if ($property->import_delimetr) {
                            $value = explode($property->import_delimetr, $value);
                        } else {
                            $value = [trim($value)];
                        }
                    } elseif (is_array($value)) {
                        $value = (array) $value;
                    } else {
                        continue;
                    }
                    
                    foreach ($value as $val)
                    {
                        $val = trim($val);
                        
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

    public function actionLoadProperties()
    {
    
        $siteName = \Yii::$app->skeeks->site->name;
        $this->stdout("Поставщик: {$siteName}\n");

        $shopProductsQuery = ShopProduct::find()->joinWith('cmsContentElement as cmsContentElement')->andWhere(['cmsContentElement.cms_site_id' => \Yii::$app->skeeks->site->id]);
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
                    if (! ShopSupplierProperty::find()->cmsSite()->andWhere(['external_code' => $key])->one()) {
                        $shopSupplierProperty = new ShopSupplierProperty();
                        $shopSupplierProperty->external_code = $key;

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