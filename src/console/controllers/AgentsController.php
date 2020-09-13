<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 22.03.2016
 */

namespace skeeks\cms\shop\console\controllers;

use skeeks\cms\shop\components\ShopComponent;
use skeeks\cms\shop\models\ShopSite;
use skeeks\cms\shop\models\ShopTypePrice;
use skeeks\cms\shop\models\ShopUser;
use skeeks\cms\shop\models\ShopOrder;
use yii\console\Controller;
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
    public function actionUpdateReceiverSites()
    {
        /**
         * @var $shopSite ShopSite
         */
        if ($shopSites = ShopSite::find()->where(['is_receiver' => 1])->all()) {
            $this->stdout("Найдено сайтов получателей: " . count($shopSites) . "\n");
            foreach ($shopSites as $shopSite) {
                ShopComponent::importNewProductsOnSite($shopSite->cmsSite);
            }
        }
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
     * Обновление количества товаров
     * 
     * @throws \yii\db\Exception
     */
    public function actionUpdateQuantity()
    {
        //Обновление количества товаров у которых заданы склады
        /*\Yii::$app->db->createCommand("
            UPDATE 
                `shop_product` as sp
                LEFT JOIN shop_store_product ssp on ssp.shop_product_id = sp.id 
            SET 
                sp.`quantity` = (select sum(ssp_inner.quantity) from shop_store_product as ssp_inner WHERE ssp_inner.shop_product_id = sp.id )
            WHERE 
                ssp.id is not null
        ")->execute();*/


        \Yii::$app->shop->updateAllQuantities();
        
    }

    /**
     * Удаление пустых корзин старше
     * @param int $days количество дней
     */
    public function actionDeleteEmptyCarts($days = 1)
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
        $forDeleteQuery = ShopOrder::find()->joinWith('shopOrderItems as shopOrderItems')
            ->andWhere([
                'and',
                ['shop_order.is_created' => 0], //Не созданные заказы
                ['<=', 'shop_order.created_at', time() - 3600 * 24 * $days] //старше 1 дня
            ])
            ->andWhere(['shopOrderItems.id' => null])//У которых нет ничего в корзине
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