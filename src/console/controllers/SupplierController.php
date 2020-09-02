<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 22.03.2016
 */

namespace skeeks\cms\shop\console\controllers;

use skeeks\cms\models\CmsContentElement;
use skeeks\cms\models\CmsContentElementProperty;
use skeeks\cms\models\CmsContentPropertyEnum;
use skeeks\cms\relatedProperties\propertyTypes\PropertyTypeElement;
use skeeks\cms\relatedProperties\propertyTypes\PropertyTypeList;
use skeeks\cms\shop\models\ShopCmsContentElement;
use skeeks\cms\shop\models\ShopCmsContentProperty;
use skeeks\cms\shop\models\ShopProduct;
use skeeks\cms\shop\models\ShopSupplier;
use skeeks\cms\shop\models\ShopSupplierProperty;
use skeeks\cms\shop\models\ShopSupplierPropertyOption;
use yii\base\Exception;
use yii\console\Controller;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;

/**
 * Работа с товарами поставщика
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class SupplierController extends Controller
{

    public function actionCheckModels()
    {

        /*SELECT
        count(1) as c,
          properties1.value,
          properties2.value,
          cms_content_element.*
        FROM
          cms_content_element
          LEFT JOIN shop_product sp ON cms_content_element.id = sp.id
          LEFT JOIN cms_site cmsSite ON cms_content_element.cms_site_id = cmsSite.id
          LEFT JOIN cms_content_element_property properties1 ON cms_content_element.id = properties1.element_id
          LEFT JOIN cms_content_element_property properties2 ON cms_content_element.id = properties2.element_id
        WHERE
          (cmsSite.is_default = 1)
          AND (properties1.property_id = 28)
          AND (properties2.property_id = 60)

        GROUP BY
            properties1.value,
            properties2.value
            HAVING
            c>1
            ORDER BY c DESC*/

        $q = ShopCmsContentElement::find()
            ->joinWith("shopProduct as sp")
            ->joinWith("cmsSite as cmsSite")
            //->joinWith("cmsContentElementProperties as properties1")
            //->joinWith("cmsContentElementProperties as properties2")
            ->leftJoin(
                ['p1' => CmsContentElementProperty::find()->where(['property_id' => 28])], ['p1.element_id' => new \yii\db\Expression(ShopCmsContentElement::tableName() . '.id')]
            )

            ->andWhere(['cmsSite.is_default' => 1])
            //->andWhere(['properties1.property_id' => 28])
            //->andWhere(['properties2.property_id' => 60])
        ;

        echo $q->createCommand()->rawSql . PHP_EOL;
    }

    /**
     *
     * Берет товары производителя и связывает их с моделями по артикулу и производителю
     *
     * @param null $cms_site_id
     */
    public function actionJoinProducts($cms_site_id = null)
    {
        /**
         * @var $vendor ShopCmsContentProperty
         * @var $vendorCode ShopCmsContentProperty
         */
        $vendor = ShopCmsContentProperty::find()->where(['is_vendor' => 1])->one();
        $vendorCode = ShopCmsContentProperty::find()->where(['is_vendor_code' => 1])->one();

        if (!$vendor || !$vendorCode) {
            $this->stdout("Не настроено свойство производителя и артикула производителя", Console::FG_RED);
            die;
        }


        $q = ShopCmsContentElement::find()
            ->joinWith("shopProduct as sp")
            //->andWhere(['cms_site_id' => 8])
            ->joinWith("cmsSite.shopSite as shopSite")
            ->andWhere(['shopSite.is_supplier' => 1]) //только товары поставщиков
            //->andWhere(['content_id' => 2])
            ->andWhere(['sp.main_pid' => null]) //которые не привязаны к моделям
        ;
        
        if ($cms_site_id) {
            $q->andWhere(['cms_site_id' => $cms_site_id]);
        }


        $query1 = CmsContentElementProperty::find()->select(['element_id as id'])
            ->where([
                //"value_element_id"  => $e->field->value,
                "property_id" => $vendor->cmsContentProperty->id,
            ]);

        $query2 = CmsContentElementProperty::find()->select(['element_id as id'])
            ->where([
                "property_id" => $vendorCode->cmsContentProperty->id
            ])
            ->andWhere(["!=", "value" , "-"])
        ;

        $q->andWhere([
            CmsContentElement::tableName().".id" => $query1,
        ]);

        $q->andWhere([
            CmsContentElement::tableName().".id" => $query2,
        ]);


        $this->stdout("Товаров: {$q->count()}\n");
        sleep(3);

        /**
         * @var ShopCmsContentElement $model
         */
        foreach ($q->each(10) as $model)
        {

            $modelVendorId = $model->relatedPropertiesModel->getAttribute($vendor->cmsContentProperty->code);
            $modelVendorCode = $model->relatedPropertiesModel->getAttribute($vendorCode->cmsContentProperty->code);

            $this->stdout("\tProduct: {$model->id} ($modelVendorId - $modelVendorCode)");

            //Ищем среди моделей, товар с таким же производителем и артикулом.


            $find = ShopCmsContentElement::find()
                ->joinWith("shopProduct as sp")
                ->joinWith("cmsSite as cmsSite")
                ->andWhere(['cmsSite.is_default' => 1]) //только товары поставщиков
            ;


            $find1 = CmsContentElementProperty::find()->select(['element_id as id'])
                        ->where([
                            "value_element_id"  => $modelVendorId,
                            "property_id" => $vendor->cmsContentProperty->id,
                        ]);
            $find2 = CmsContentElementProperty::find()->select(['element_id as id'])
                        ->where([
                            "value"  => $modelVendorCode,
                            "property_id" => $vendorCode->cmsContentProperty->id,
                        ]);

            $find->andWhere([
                CmsContentElement::tableName().".id" => $find1,
            ]);

            $find->andWhere([
                CmsContentElement::tableName().".id" => $find2,
            ]);

            /**
             * @var $globalModel ShopCmsContentElement
             */
            if ($globalModel = $find->one()) {
                $this->stdout("Найдена модель: {$globalModel->id}\n", Console::FG_GREEN);
                $sp = $model->shopProduct;
                $sp->main_pid = $globalModel->id;
                if ($sp->save()) {
                    $this->stdout("\t\t Связана\n", Console::FG_GREEN);
                } else {
                    $this->stdout("\t\t Не связана!" . print_r($sp->errors, true) . "\n", Console::FG_RED);
                    $this->stdout("\t\t Ожидание 5 сек..." . "\n", Console::FG_RED);
                    sleep(5);
                }
                /*die;*/
            } else {
                $this->stdout("Не найдена модель\n", Console::FG_RED);
            }
        }
    }

    /**
     *
     * Загружает свойства поставщика в свойства cms
     * Например если нужно заполнить бренд или артикул бренда
     *
     * @param $external_property_code
     * @return bool
     */
    public function actionInsertCmsProperty($external_property_code)
    {
        if ($external_property_code == "brand") {
            $external_property_code = "Производитель ";
        }
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

        sleep(5);

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
                                } elseif ($supplierOption->cmsContentPropertyEnum) {

                                    $cmsElement = $shopProduct->cmsContentElement;
                                    $cmsElement->relatedPropertiesModel->setAttribute($shopSupplierProperty->cmsContentProperty->code, $supplierOption->cmsContentPropertyEnum->id);

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


    /**
     * Связывает опции поставщика и опции cms
     *
     * @param int $is_auto_create будет создавать опции в cms или нет?
     * @return bool
     * @throws Exception
     */
    public function actionConnectOptions($is_auto_create = 0)
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
                    } else {
                        if ($is_auto_create) {
                            $enum = new CmsContentPropertyEnum();
                            $enum->value = $option->name;
                            $enum->property_id = $contentProperty->id;

                            if (!$enum->save()) {
                                throw new Exception("Не создалась опция: " . print_r($enum->errors, true));
                            }

                            $this->stdout("\t\tСоздана характеристика\n", Console::FG_GREEN);

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