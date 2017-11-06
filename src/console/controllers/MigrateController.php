<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 22.03.2016
 */
namespace skeeks\cms\shop\console\controllers;
use skeeks\cms\shop\models\ShopFuser;
use skeeks\cms\shop\models\ShopProductPrice;
use skeeks\cms\shop\models\ShopProductPriceChange;
use yii\console\Controller;
use yii\db\Exception;
use yii\db\Expression;
use yii\helpers\Console;

/**
 * Module shop agents
 *
 * Class AgentsController
 * @package skeeks\cms\shop\console\controllers
 */
class MigrateController extends Controller
{

    /**
     * Просмотр созданных бекапов баз данных
     */
    public function actionRemoveDublicatePrices()
    {
        /**
         * @var ShopProductPrice $price
         */
        foreach (ShopProductPrice::find()->each(1000) as $price)
        {
            $prices = ShopProductPrice::find()
                    ->where(['type_price_id' => $price->type_price_id])
                    ->andWhere(['product_id' => $price->product_id])
                    ->andWhere(["!=", 'id', $price->id])->all();

            $this->stdout("price: {$price->id}\n");
            if (!$prices)
            {
                continue;
            } else
            {
                $total = count($prices);

                $removePrices = ShopProductPrice::find()->where(['type_price_id' => $price->type_price_id])
                    ->andWhere(['product_id' => $price->product_id])
                    ->orderBy(['price' => SORT_ASC])
                    ->limit($total)
                    //->andWhere(["!=", 'id', $price->id])
                    ->all();

                $forRemove = count($removePrices);
                foreach ($removePrices as $price)
                {
                    $price->delete();
                }

                $this->stdout("\tЕсть дубли, удалено {$forRemove}\n", Console::FG_RED);
            }
        }
    }
}