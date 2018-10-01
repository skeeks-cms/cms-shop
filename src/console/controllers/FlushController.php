<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 22.03.2016
 */

namespace skeeks\cms\shop\console\controllers;

use skeeks\cms\shop\models\ShopProductPriceChange;
use yii\console\Controller;
use yii\helpers\Console;

/**
 * Массовая читска данных
 *
 * @package skeeks\cms\shop\console\controllers
 */
class FlushController extends Controller
{
    /**
     * Чистка лога изменения цен
     * @param int $countDay за последние количество дней
     */
    public function actionPriceChanges($countDay = 30)
    {
        if ($count = ShopProductPriceChange::find()->where([
            '<=',
            'created_at',
            time() - 3600 * 24 * $countDay,
        ])->count()
        ) {
            $this->stdout("Total price changes for delete: {$count}\n", Console::BOLD);
            $totalDeleted = ShopProductPriceChange::deleteAll(['<=', 'created_at', time() - 3600 * 24 * $countDay]);
            $this->stdout("Total deleted: {$totalDeleted}\n");
        } else {
            $this->stdout("Нечего удалять\n", Console::BOLD);
        }
    }
}