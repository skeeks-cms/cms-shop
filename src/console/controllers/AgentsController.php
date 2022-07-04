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
        $q = ShopSite::find();
        if ($cms_site_id) {
            $q->andWhere(['id' => $cms_site_id]);
        }
        /**
         * @var $shopSite ShopSite
         */
        if ($count = $q->count()) {
            $this->stdout("Найдено сайтов получателей: " . $count . "\n");
            foreach ($q->each(10) as $shopSite) {
                $this->stdout("\tСайт: " . $shopSite->id . "\n");
                ShopComponent::updateProductPrices($shopSite->cmsSite);
            }
        }
    }

    /**
     * Добавляет новые товары на сайты получатели
     *
     * @throws \yii\base\Exception
     */
    public function actionUpdateReceiverSites()
    {
        $q = ShopSite::find()->where(['is_receiver' => 1])->orderBy(['id' => SORT_DESC]);
        /**
         * @var $shopSite ShopSite
         */
        if ($q->count()) {
            $this->stdout("Найдено сайтов получателей: " . $q->count() . "\n");
            foreach ($q->each(10) as $shopSite) {
                $this->stdout("\tСайт: " . $shopSite->id . "\n");
                \common\modules\sitika\components\ShopComponent::importNewProductsOnSite($shopSite->cmsSite);
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
            $shopSites->andWhere(['id' => $cms_site_id]);
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
                    //die;

                    //Модель
                    $mainCmsContentElement = $shopCmsContentElement->mainCmsContentElement;
                    $mainCmsContentElement->relatedPropertiesModel->initAllProperties();
                    $mainData = $mainCmsContentElement->relatedPropertiesModel->toArray();
                    if (!$mainData) {
                        continue;
                    }

                    //Текущий товар
                    $newElementProperties = $shopCmsContentElement->relatedPropertiesModel;
                    $newElementProperties->initAllProperties();
                    $newData = $newElementProperties->toArray();

                    foreach ($newData as $code => $valueNull)
                    {
                        $value = ArrayHelper::getValue($mainData, $code);
                        /**
                         * @var CmsContentElementProperty $property
                         */
                        $property = $mainCmsContentElement->relatedPropertiesModel->getRelatedProperty($code);
                        $propertyNew = $newElementProperties->getRelatedProperty($code);
                        if ($property->property_type == PropertyType::CODE_ELEMENT) {


                            if (is_array($value)) {
                                $newValue = [];
                                foreach ($value as $valueId)
                                {
                                    $element = CmsContentElement::find()->cmsSite($shopSite->cmsSite)->andWhere(['main_cce_id' => (int) $valueId])->one();
                                    if (!$element) {
                                        $mainValueElement = CmsContentElement::find()->cmsSite($defaultCmsSite)->andWhere(['id' => (int) $valueId])->one();

                                        $element = new CmsContentElement();
                                        $element->content_id = $mainValueElement->content_id;
                                        $element->cms_site_id = $shopSite->cmsSite->id;
                                        $element->main_cce_id = (int) $valueId;
                                        $element->name = $mainValueElement->name;
                                        if (!$element->save()) {
                                            print_r($element->errors, true);
                                        }
                                    }

                                    $newValue[] = $element->id;
                                }
                            } else {
                                $newValue = null;
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

                            $newElementProperties->setAttribute($code, $newValue);

                        } elseif ($property->property_type == PropertyType::CODE_LIST) {
                            if (is_array($value)) {
                                $newValue = [];
                                foreach ($value as $valueId)
                                {

                                    $mainEnum = $property->getEnums()->andWhere(['id' => $valueId])->one();
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
                                }
                            } else {
                                $newValue = null;
                                $mainEnum = $property->getEnums()->andWhere(['id' => $valueId])->one();
                                $enum = $propertyNew->getEnums()->andWhere(['code' => $mainEnum->code])->one();
                                if (!$enum) {
                                    $mainEnum = CmsContentPropertyEnum::find()->where(['property_id' => $property->id])->andWhere(['code' => $valueId])->one();

                                    $enum = new CmsContentPropertyEnum();
                                    $enum->property_id = $propertyNew->id;
                                    $enum->code = $mainEnum->code;
                                    $enum->value = $mainEnum->value;
                                    if (!$enum->save()) {
                                        print_r($enum->errors, true);
                                    }
                                }

                                $newValue = $enum->id;
                            }

                            $newElementProperties->setAttribute($code, $newValue);
                        } else {
                            $newElementProperties->setAttribute($code, $value);
                        }
                    }




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
            $shopSites->andWhere(['id' => $cms_site_id]);
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
        $result = \Yii::$app->db->createCommand(<<<SQL
UPDATE 
	`shop_product` as update_product 
	INNER JOIN (
		SELECT 
			sp.id, 
			FLOOR(shop_site_settings.generate_min_product_rating_count + RAND() * (shop_site_settings.generate_max_product_rating_count - shop_site_settings.generate_min_product_rating_count)) as calc_rating_count,
			ROUND(FLOOR(shop_site_settings.generate_min_product_rating_value + RAND() * (shop_site_settings.generate_max_product_rating_value - shop_site_settings.generate_min_product_rating_value)) + RAND(), 4)  as calc_rating_value
		FROM 
			`shop_product` as sp
			INNER JOIN cms_content_element as cce on cce.id = sp.id
			INNER JOIN shop_site as shop_site_settings on shop_site_settings.id = cce.cms_site_id
        WHERE 
                shop_site_settings.is_generate_product_rating = 1
            AND 
                sp.rating_count = 0 
            AND
                sp.rating_value = 0 
	) as result_sp ON result_sp.id = update_product.id 
SET 
	update_product.rating_count = result_sp.calc_rating_count,
	update_product.rating_value = result_sp.calc_rating_value
SQL
        )->execute();
    }

    /**
     * Обновление цен которые рассчитываются автоматически
     */
    public function actionUpdateAutoPrices()
    {
        $q = ShopTypePrice::find()->where(['is_auto' => 1]);

        $this->stdout("Найдено автообновляемых цен: " . $q->count() . "\n");

        /**
         * @var $shopTypePrice ShopTypePrice
         */
        foreach ($q->each(10) as $shopTypePrice) {
            $type_price_id = $shopTypePrice->id;
            $cms_site_id = $shopTypePrice->cms_site_id;
            $base_auto_shop_type_price_id = $shopTypePrice->base_auto_shop_type_price_id;
            $auto_extra_charge = $shopTypePrice->auto_extra_charge;

            $result = \Yii::$app->db->createCommand(<<<SQL
INSERT IGNORE
    INTO shop_product_price (`created_at`,`updated_at`,`product_id`, `type_price_id`, `price`, `currency_code`)
    SELECT 
        UNIX_TIMESTAMP(),
        UNIX_TIMESTAMP(),
        spp.product_id,
        {$type_price_id},
        ROUND(spp.price * {$auto_extra_charge} / 100),
        spp.currency_code
    FROM 
        shop_product_price as spp
    WHERE
        spp.type_price_id = {$base_auto_shop_type_price_id}
SQL
        )->execute();



        $result = \Yii::$app->db->createCommand(<<<SQL
UPDATE 
	`shop_product_price` as update_price 
	INNER JOIN (
		SELECT 
			spp.id, 
			spp.currency_code, 
			UNIX_TIMESTAMP() as updated_at_now, 
			(
				SELECT 
					ROUND(
						calc_price.price * stp.auto_extra_charge / 100
					) 
				FROM 
					shop_product_price as calc_price 
				WHERE 
					calc_price.product_id = spp.product_id 
					AND calc_price.type_price_id = stp.base_auto_shop_type_price_id
			) as new_price, 
			spp.price as old_price 
		FROM 
			`shop_product_price` as spp 
			INNER JOIN (
				SELECT 
					* 
				FROM 
					shop_type_price as inner_stp 
				WHERE 
					inner_stp.is_auto = 1
			) as stp ON stp.id = spp.type_price_id 
			LEFT JOIN shop_type_price as baseTypePrice on baseTypePrice.id = stp.base_auto_shop_type_price_id
	) as calced_price ON calced_price.id = update_price.id 
SET 
	update_price.price = calced_price.new_price
SQL
        )->execute();

        }
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
        \Yii::$app->shop->updateAllTypes();
    }

    /**
     * Удаление пустых корзин старше
     * @param int $days количество дней
     */
    public function actionDeleteEmptyCarts($days = 3)
    {
        $condition = [
            //'and',
            //['shop_order.is_created' => 0],
            //['<=', 'shop_order.created_at', time()-3600*24*$days],
            //['shop_order.is_created' => 0],
            /*['shop_order.person_type_id' => null],
            ['shop_fuser.pay_system_id' => null],
            ['shop_fuser.delivery_id' => null],
            ['shop_fuser.buyer_id' => null],*/
            /*new Expression(<<<SQL
            (SELECT count(id) as count FROM shop_order_item WHERE shop_order_item.shop_order_id = shop_order.id) = 0
SQL
            ),*/
        ];
        //$forDelete = ShopOrder::find()->where($condition)->count(1);
        $forDeleteQuery = ShopOrder::find()
            //->joinWith('shopOrderItems as shopOrderItems')
            ->andWhere([
                'and',
                ['shop_order.is_created' => 0], //Не созданные заказы
                ['<=', 'shop_order.created_at', time() - 3600 * 24 * $days] //старше 1 дня
            ])
            //->andWhere(['shopOrderItems.id' => null])//У которых нет ничего в корзине
            ->limit(5000)
            ->orderBy(['shop_order.id' => SORT_ASC])
            ->select(["shop_order.id"])
            ->asArray()
            ->all();

        $ids = ArrayHelper::map($forDeleteQuery, 'id', 'id');


        /*
                $counter = 0;
                $models = $query->all();
                $allCount = count($models);
                Console::startProgress(0, $allCount);
        
                foreach ($query->each() as $model)
                {
                    // $users is indexed by the "username" column
                    $counter ++;
                    $model->delete();
                    Console::updateProgress($counter, $allCount);
                }
        
                Console::endProgress();*/

        if ($ids) {
            $this->stdout("Empty orders for delete: ".count($ids)."\n");
            $deleted = ShopOrder::deleteAll(['id' => $ids]);
            $this->stdout("Removed empty orders: ".$deleted."\n");
        } else {
            $this->stdout("Not found orders for delete\n");
        }

        $deleted = ShopUser::deleteAll([
            'shop_order_id' => null,
        ]);
        $this->stdout("Removed empty carts: ".$deleted."\n");
    }
}