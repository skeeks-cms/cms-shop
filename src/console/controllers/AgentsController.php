<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 22.03.2016
 */

namespace skeeks\cms\shop\console\controllers;

use skeeks\cms\shop\models\ShopCart;
use skeeks\cms\shop\models\ShopFuser;
use skeeks\cms\shop\models\ShopOrder;
use yii\console\Controller;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

/**
 * Module shop agents
 *
 * Class AgentsController
 * @package skeeks\cms\shop\console\controllers
 */
class AgentsController extends Controller
{

    /**
     * Просмотр созданных бекапов баз данных
     */
    public function actionDeleteEmptyCarts($days = 7)
    {
        $condition = [
            'and',
            ['shop_order.is_created' => 0],
            ['<=', 'shop_order.created_at', time()-3600*24*$days],
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
        /*$forDeleteQuery = ShopOrder::find()->joinWith('shopOrderItems as shopOrderItems')
            ->andWhere($condition)
            ->andWhere(['shopOrderItems.id' => null])
            ->limit(1000)
            ->orderBy(['shop_order.id' => SORT_ASC])
            ->select(["shop_order.id"])
            ->asArray()
            ->all()
        ;

        $ids = ArrayHelper::map($forDeleteQuery, 'id', 'id');*/

        /*
        $query = ShopFuser::find()
                    ->andWhere(['shop_fuser.user_id' => null])
                    ->andWhere(['shop_fuser.person_type_id' => null])
                    ->andWhere(['shop_fuser.pay_system_id' => null])
                    ->andWhere(['shop_fuser.delivery_id' => null])
                    ->andWhere(['shop_fuser.buyer_id' => null])

                    ->andWhere(new Expression(<<<SQL
                    (SELECT count(id) as count FROM shop_basket WHERE shop_basket.fuser_id = shop_fuser.id) = 0
SQL
                    ))
                    ;

        echo $query->createCommand()->sql;*/
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

                $deleted = ShopOrder::deleteAll($condition);
                $this->stdout("Removed empty orders: ".$deleted."\n");

                $deleted = ShopCart::deleteAll([
                    'shop_order_id' => null
                ]);
                $this->stdout("Removed empty orders: ".$deleted."\n");
    }
}