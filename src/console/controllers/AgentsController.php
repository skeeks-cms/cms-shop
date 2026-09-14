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
use skeeks\cms\models\CmsContentProperty;
use skeeks\cms\models\CmsContentPropertyEnum;
use skeeks\cms\models\CmsSite;
use skeeks\cms\models\CmsTree;
use skeeks\cms\relatedProperties\PropertyType;
use skeeks\cms\shop\components\ShopComponent;
use skeeks\cms\shop\models\BrandCmsContentElement;
use skeeks\cms\shop\models\ShopCmsContentElement;
use skeeks\cms\shop\models\ShopSite;
use skeeks\cms\shop\models\ShopTypePrice;
use skeeks\cms\shop\models\ShopUser;
use skeeks\cms\shop\models\ShopOrder;
use yii\console\Controller;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class AgentsController extends Controller
{

    /**
     * Добавляет новые товары на сайты получатели
     * 
     * @throws \yii\base\Exception
     */
    public function actionUpdateProductPricesFromStoreProducts($cms_site_id = null)
    {
        $result = (new \skeeks\cms\shop\services\ScheduledMaintenance())->updateStorePrices($cms_site_id ? (int)$cms_site_id : null);
        $this->stdout(json_encode($result, JSON_UNESCAPED_UNICODE).PHP_EOL);
        return \yii\console\ExitCode::OK;
    }

    /**
     * @deprecated 
     * @return false
     */
    public function actionUpdateQuantity()
    {
        return false;
    }

    /**
     * Добавляет новые товары на сайты получатели
     *
     * @throws \yii\base\Exception
     */
    public function actionUpdateReceiverSites()
    {
        $q = \skeeks\cms\shop\models\CmsSite::find()
            ->active()
            ->innerJoinWith("shopSite as shopSite")
            ->andWhere(['shopSite.is_receiver' => 1])
            ->orderBy(['id' => SORT_DESC]);
            
        /*$q = ShopSite::find()
            ->where(['is_receiver' => 1])
            ->orderBy(['id' => SORT_DESC]);*/
        /**
         * @var $shopSite ShopSite
         */
        if ($q->count()) {
            $this->stdout("Найдено сайтов получателей: " . $q->count() . "\n");
            foreach ($q->each(10) as $cmsSite) {
                $this->stdout("\tСайт: " . $cmsSite->id . "\n");
                \common\modules\sitika\components\ShopComponent::importNewProductsOnSite($cmsSite);
            }
        }
    }


    /**
     * @param null $cms_site_id какой сайт обновлять
     * @param int  $is_all 1 - все товары, 0 - только новые
     * @return bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionUpdateProductsReceiverSites($cms_site_id = null, $is_all = 0)
    {
        ini_set("memory_limit", "2048M");
        
        $this->actionUpdatePropertyReceiverSites($cms_site_id);
        /**
         * @var $shopSites ShopSite[]
         */
        $shopSitesQ = ShopSite::find()->andWhere(['is_receiver' => 1])->orderBy(['id' => SORT_DESC]);
        //$shopSites->andWhere(['>=', 'id', 103]);
        if ($cms_site_id) {
            $shopSitesQ->andWhere(['id' => $cms_site_id]);
        }

        $defaultCmsSite = CmsSite::find()->default()->one();
        if (!$defaultCmsSite) {
            $this->stdout("\tНет сайта по умолчанию\n");
            return false;
        }

        //Сначала нужно создать характеристики
        if ($shopSitesQ->count()) {
            $this->stdout("Найдено сайтов получателей: ".$shopSitesQ->count()."\n");
            foreach ($shopSitesQ->each(10) as $shopSite) {
                $this->stdout("Сайт: ".$shopSite->id."\n");

                $query = ShopCmsContentElement::find()
                    ->cmsSite($shopSite->cmsSite)
                    ->joinWith("shopProduct as sp", true, "INNER JOIN")
                    ->joinWith("mainCmsContentElement as mainCCE", true, "INNER JOIN")
                ;
                
                if ($is_all == 0) {
                    $query->andWhere([
                        'or',
                        ['>=', "mainCCE.updated_at", new Expression(CmsContentElement::tableName(). ".updated_at")],
                        [CmsContentElement::tableName(). ".updated_at" => null]
                    ]);
                }
                
                $this->stdout("\tТоваров: " . $query->count() . "\n");
                //$this->stdout("\tСтарт через 5 сек... \n");
                //print_r($query->createCommand()->rawSql);die;
                //sleep(5);
                $total = $query->count();
                Console::startProgress(0,$total);



                /**
                 * @var $shopCmsContentElement ShopCmsContentElement
                 */
                $counter = 0;
                foreach ($query->each(10) as $shopCmsContentElement)
                {
                    $counter ++;
                    Console::updateProgress($counter,$total);
                    //$this->stdout("\tТовар: {$shopCmsContentElement->id}\n");
                    //continue;
                    //die;

                    //Модель
                    $mainCmsContentElement = $shopCmsContentElement->mainCmsContentElement;

                    $q = CmsTree::find()->cmsSite($shopSite->cmsSite)->andWhere(['main_cms_tree_id' => $mainCmsContentElement->tree_id]);
                    $needCmsTree = $q->one();
                    if ($needCmsTree && $shopCmsContentElement->tree_id != $needCmsTree->id) {
                        //$this->stdout("\t\tаздел не тот\n");
                        /*var_dump($shopCmsContentElement->tree_id);
                        var_dump($needCmsTree->id);                    
                        var_dump($shopCmsContentElement->id);
                        die;*/
                        $shopCmsContentElement->tree_id = $needCmsTree->id;
                        if (!$shopCmsContentElement->update(false, ['tree_id'])) {
                            print_r($shopCmsContentElement->errors, true);
                            die;
                        }
                        $shopCmsContentElement->refresh();
                    }


                    $mainCmsContentElement->relatedPropertiesModel->initAllProperties();
                    $mainData = $mainCmsContentElement->relatedPropertiesModel->toArray();
                    if (!$mainData) {
                        continue;
                    }

                    //Текущий товар
                    $newElementProperties = $shopCmsContentElement->relatedPropertiesModel;
                    $newElementProperties->initAllProperties();
                    $newData = $newElementProperties->toArray();

                    //Если у товара задан бренда
                    if ($mainCmsContentElement->shopProduct->brand_id && 1 == 2) {

                        //Если бренд не задан, у получаемого товара
                        if (!$shopCmsContentElement->shopProduct->brand_id) {
                            /**
                             * @var $brandElement BrandCmsContentElement
                             */
                            $brandElement = BrandCmsContentElement::find()->cmsSite($shopSite->cmsSite)->andWhere(['main_cce_id' => (int) $mainCmsContentElement->shopProduct->brand_id])->one();
                            if (!$brandElement) {
                                $mainBrandElement = BrandCmsContentElement::find()->cmsSite($defaultCmsSite)->andWhere(['id' => (int) $shopCmsContentElement->shopProduct->brand_id])->one();
                                if ($mainBrandElement) {
                                    $brandElement = new BrandCmsContentElement();
                                    $brandElement->content_id = $mainBrandElement->content_id;
                                    $brandElement->cms_site_id = $shopSite->cmsSite->id;
                                    $brandElement->main_cce_id = (int) $shopCmsContentElement->shopProduct->brand_id;
                                    $brandElement->name = $mainBrandElement->name;
                                    if (!$brandElement->save()) {
                                        print_r($element->errors, true);
                                    }


                                    /*$shopBrand = $brandElement->shopBrand;
                                    $shopBrand->country_id = $brandElement->shopBrand->country_id*/


                                }
                            }

                            $mainCmsContentElement->shopProduct->brand_id = $brandElement->id;
                        }



                    }

                    //$this->stdout("\t\tтут1\n");

                    foreach ($newData as $code => $valueNull)
                    {
                        $value = ArrayHelper::getValue($mainData, $code);
                        /**
                         * @var CmsContentElementProperty $property
                         */
                        $property = $mainCmsContentElement->relatedPropertiesModel->getRelatedProperty($code);
                        $propertyNew = $newElementProperties->getRelatedProperty($code);
                        
                        if (!$property) {
                            continue;
                        }

                        if ($property->property_type == PropertyType::CODE_ELEMENT) {


                            if (is_array($value)) {
                                $newValue = [];
                                foreach ($value as $valueId)
                                {
                                    if ($valueId) {
                                        $element = CmsContentElement::find()->cmsSite($shopSite->cmsSite)->andWhere(['main_cce_id' => (int) $valueId])->one();
                                        if (!$element) {
                                            $mainValueElement = CmsContentElement::find()->cmsSite($defaultCmsSite)->andWhere(['id' => (int) $valueId])->one();

                                            if ($mainValueElement) {
                                                $element = new CmsContentElement();
                                                $element->content_id = $mainValueElement->content_id;
                                                $element->cms_site_id = $shopSite->cmsSite->id;
                                                $element->main_cce_id = (int) $valueId;
                                                $element->name = $mainValueElement->name;
                                                if (!$element->save()) {
                                                    print_r($element->errors, true);
                                                }
                                            }

                                        }

                                        if ($element) {
                                            $newValue[] = $element->id;
                                        }

                                    }

                                }
                            } else {
                                $newValue = null;
                                if ($value) {
                                    $element = CmsContentElement::find()->cmsSite($shopSite->cmsSite)->andWhere(['main_cce_id' => (int) $value])->one();
                                    if (!$element) {
                                        $mainValueElement = CmsContentElement::find()->cmsSite($defaultCmsSite)->andWhere(['id' => (int) $value])->one();
    
                                        $element = new CmsContentElement();
                                        $element->content_id = $mainValueElement->content_id;
                                        $element->cms_site_id = $shopSite->cmsSite->id;
                                        $element->main_cce_id = (int) $value;
                                        $element->name = $mainValueElement->name;
                                        if (!$element->save()) {
                                            print_r($element->errors, true);
                                        }
                                    }
    
                                    $newValue = $element->id;
                                }
                                
                            }

                            $newElementProperties->setAttribute($code, $newValue);

                        } elseif ($property->property_type == PropertyType::CODE_LIST) {
                            if (is_array($value)) {

                                /*print_r($value);
                                echo "\n";*/

                                $newValue = [];
                                foreach ($value as $valueId)
                                {

                                    $mainEnum = $property->getEnums()->andWhere(['id' => $valueId])->one();
                                    if ($mainEnum) {


                                        $enum = $propertyNew->getEnums()->andWhere(['code' => $mainEnum->code])->one();
                                        if (!$enum) {

                                            $enum = new CmsContentPropertyEnum();
                                            $enum->property_id = $propertyNew->id;
                                            $enum->code = $mainEnum->code;
                                            $enum->value = $mainEnum->value;
                                            if (!$enum->save()) {
                                                print_r($enum->errors, true);
                                            }
                                        }

                                        $newValue[] = $enum->id;
                                                                                //print_r($newValue);

                                    }

                                }

                                $newElementProperties->setAttribute($code, $newValue);

                            } else {
                                $newValue = null;
                                $mainEnum = $property->getEnums()->andWhere(['id' => $value])->one();
                                /*if (!$mainEnum) {
                                    print_r($property->name);
                                    var_dump($value);
                                    die;
                                }*/
                                if ($mainEnum) {
                                    $enum = $propertyNew->getEnums()->andWhere(['code' => $mainEnum->code])->one();
                                    if (!$enum) {
                                        //$mainEnum = CmsContentPropertyEnum::find()->where(['property_id' => $property->id])->andWhere(['code' => $valueId])->one();
    
                                        $enum = new CmsContentPropertyEnum();
                                        $enum->property_id = $propertyNew->id;
                                        $enum->code = $mainEnum->code;
                                        $enum->value = $mainEnum->value;
                                        if (!$enum->save()) {
                                            print_r($enum->errors, true);
                                        }
                                    }
    
                                    $newValue = $enum->id;
                                    
                                    $newElementProperties->setAttribute($code, $newValue);
                                }
                                
                            }

                        } else {
                            $newElementProperties->setAttribute($code, $value);
                        }
                    }

                    //$this->stdout("\t\tтут\n");
                    if (!$newElementProperties->save())
                    {
                        $this->stdout("model: {$mainCmsContentElement->id}\n");
                        $this->stdout("product: {$shopCmsContentElement->id}\n");
                        $this->stdout("error!!!\n");
                        print_r($newElementProperties->errors);

                        $error = print_r($newElementProperties->errors, true);
                        \Yii::error("Ошибка сохранения свойств model: {$mainCmsContentElement->id}, product: {$shopCmsContentElement->id}, error: {$error}", self::class);
                        continue;
                    }

                    $shopCmsContentElement->updated_at = time();
                    $shopCmsContentElement->update(['updated_at']);

                    //die;


                }

                Console::endProgress("end".PHP_EOL);

            }
        }
    }

    public function actionUpdatePropertyReceiverSites($cms_site_id = null)
    {
        $shopSitesQ = ShopSite::find()->where(['is_receiver' => 1])->orderBy(['id' => SORT_DESC]);
        if ($cms_site_id) {
            $shopSitesQ->andWhere(['id' => $cms_site_id]);
        }
        
        //Сначала нужно создать характеристики
        if ($shopSitesQ->count()) {
            $this->stdout("Найдено сайтов получателей: " . $shopSitesQ->count() . "\n");
            foreach ($shopSitesQ->each(10) as $shopSite) {
                $this->stdout("\tСайт: " . $shopSite->id . "\n");
                \common\modules\sitika\components\ShopComponent::importPropertiesOnSite($shopSite->cmsSite);
            }
        }
    }

    public function actionUpdateProductRating()
    {
        $result = (new \skeeks\cms\shop\services\ScheduledMaintenance())->updateProductRating();
        $this->stdout(json_encode($result, JSON_UNESCAPED_UNICODE).PHP_EOL);
        return \yii\console\ExitCode::OK;
    }

    /**
     * Обновление цен которые рассчитываются автоматически
     */
    public function actionUpdateAutoPrices()
    {
        $result = (new \skeeks\cms\shop\services\ScheduledMaintenance())->updateAutoPrices();
        $this->stdout(json_encode($result, JSON_UNESCAPED_UNICODE).PHP_EOL);
        return \yii\console\ExitCode::OK;
    }

    /**
     * Товарные данные обновляются из главных товаров
     * Габариты, вес, соответствие величин
     *
     * @throws \yii\db\Exception
     */
    public function actionUpdateSubproducts()
    {
        \Yii::$app->shop->updateAllSubproducts();
    }
    
    /**
     * Проверка и исправление типа товара
     * Если у
     * @throws \yii\db\Exception
     */
    public function actionUpdateProductType()
    {
        $result = (new \skeeks\cms\shop\services\ScheduledMaintenance())->updateProductType();
        $this->stdout(json_encode($result, JSON_UNESCAPED_UNICODE).PHP_EOL);
        return \yii\console\ExitCode::OK;
    }

    /**
     * Удаление пустых корзин старше
     * @param int $days количество дней
     */
    public function actionDeleteEmptyCarts($days = 3)
    {
        $result = (new \skeeks\cms\shop\services\ScheduledMaintenance())->deleteEmptyCarts((int)$days);
        $this->stdout(json_encode($result, JSON_UNESCAPED_UNICODE).PHP_EOL);
        return \yii\console\ExitCode::OK;
    }
}