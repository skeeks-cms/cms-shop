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

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class AgentsController extends Controller
{

    public function actionUpdateQuantity()
    {
        //Обновление количества товаров у которых заданы склады
        \Yii::$app->db->createCommand("
            UPDATE 
                `shop_product` as sp
                LEFT JOIN shop_store_product ssp on ssp.shop_product_id = sp.id 
            SET 
                sp.`quantity` = (select sum(ssp_inner.quantity) from shop_store_product as ssp_inner WHERE ssp_inner.shop_product_id = sp.id )
            WHERE 
                ssp.id is not null
        ")->execute();


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