<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 22.03.2016
 */

namespace skeeks\cms\shop\console\controllers;

use skeeks\cms\shop\models\ShopCart;
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
     * Проверка и исправление типа товара
     * @throws \yii\db\Exception
     */
    public function actionUpdateProductType()
    {
        //Товары у которых не задан родительский элемент делаем простыми
        $result = \Yii::$app->db->createCommand(<<<SQL
            UPDATE 
                `shop_product` as sp 
                LEFT JOIN cms_content_element cce on cce.id = sp.id 
            SET 
                sp.`product_type` = "simple"
            WHERE 
                cce.parent_content_element_id is null
SQL
        )->execute();

        //Товары у которых есть дочерние - товарами с предложенями
        $result = \Yii::$app->db->createCommand(<<<SQL
            UPDATE 
                `shop_product` as sp 
                INNER JOIN
                (
                    /*Товары которые являются общими*/
                   SELECT cce.parent_content_element_id as inner_sp_id
                   FROM shop_product inner_sp
                       LEFT JOIN cms_content_element cce on cce.id = inner_sp.id 
                   WHERE cce.parent_content_element_id is not null
                   GROUP BY cce.parent_content_element_id
                ) sp_has_parent ON sp.id = sp_has_parent.inner_sp_id
            SET 
                sp.`product_type` = "offers"
SQL
        )->execute();

        //Товар-предложение
        $result = \Yii::$app->db->createCommand(<<<SQL
            UPDATE 
                `shop_product` as sp 
                INNER JOIN
                (
                    /*Товары которые являются предложениями */
                   SELECT inner_sp.id as inner_sp_id
                   FROM shop_product inner_sp
                       LEFT JOIN cms_content_element cce on cce.id = inner_sp.id 
                   WHERE cce.parent_content_element_id is not null
                   GROUP BY inner_sp.id
                ) sp_has_parent ON sp.id = sp_has_parent.inner_sp_id
            SET 
                sp.`product_type` = "offer"
SQL
        )->execute();


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


        //Обновление количества товаров у которых задан поставщик, информация берется со складов
        $result = \Yii::$app->db->createCommand(<<<SQL
            UPDATE 
                `shop_product` as sp 
                INNER JOIN
                (
                    /*Товары у которых задан поставщик и количество на их складах*/
                   SELECT inner_sp.id as inner_sp_id, SUM(ssp.quantity) as sum_quantity
                   FROM shop_product inner_sp
                       LEFT JOIN shop_store_product ssp on ssp.shop_product_id = inner_sp.id 
                       WHERE inner_sp.shop_supplier_id is not null
                   GROUP BY inner_sp.id
                ) sp_has_supplier ON sp.id = sp_has_supplier.inner_sp_id
            SET 
                sp.`quantity` = if(sp_has_supplier.sum_quantity is null, 0, sp_has_supplier.sum_quantity)
SQL
        )->execute();
        

        //Обновление количества у главных товаров, к которым привязаны товары поставщиков
        \Yii::$app->db->createCommand("
            UPDATE 
                `shop_product` as sp 
                INNER JOIN
                (
                   SELECT main_pid, SUM(quantity) as sum_quantity
                   FROM shop_product 
                   GROUP BY main_pid
                ) sp_has_main ON sp.id = sp_has_main.main_pid
            SET 
                sp.`quantity` = sp_has_main.sum_quantity
            WHERE 
                sp_has_main.main_pid is not null
        ")->execute();


        //Обновления количества у общих товаров
        \Yii::$app->db->createCommand("
            UPDATE 
                `shop_product` as sp 
                INNER JOIN
                (
                    /*Товары у которых задан общий товар*/
                   SELECT cce.parent_content_element_id as inner_sp_id, SUM(inner_sp.quantity) as sum_quantity
                   FROM shop_product inner_sp
                       LEFT JOIN cms_content_element cce on cce.id = inner_sp.id 
                   WHERE cce.parent_content_element_id is not null
                   GROUP BY cce.parent_content_element_id
                ) sp_has_parent ON sp.id = sp_has_parent.inner_sp_id
            SET 
                sp.`quantity` = sp_has_parent.sum_quantity
        ")->execute();
        
        
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

        $deleted = ShopCart::deleteAll([
            'shop_order_id' => null,
        ]);
        $this->stdout("Removed empty carts: ".$deleted."\n");
    }
}