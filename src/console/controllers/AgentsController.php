<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 22.03.2016
 */

namespace skeeks\cms\shop\console\controllers;

use skeeks\cms\shop\models\ShopFuser;
use yii\console\Controller;
use yii\db\Expression;

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
    public function actionDeleteEmptyCarts()
    {
        $deleted = ShopFuser::deleteAll([
            'and',
            ['shop_fuser.user_id' => null],
            ['shop_fuser.person_type_id' => null],
            ['shop_fuser.pay_system_id' => null],
            ['shop_fuser.delivery_id' => null],
            ['shop_fuser.buyer_id' => null],
            new Expression(<<<SQL
            (SELECT count(id) as count FROM shop_order_item WHERE shop_order_item.fuser_id = shop_fuser.id) = 0
SQL
            ),
        ]);
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

        $this->stdout("Removed empty baskets: ".$deleted."\n");
        \Yii::info("Removed empty baskets: ".$deleted);
    }
}