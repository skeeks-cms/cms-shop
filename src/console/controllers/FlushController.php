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
        $result = (new \skeeks\cms\shop\services\ScheduledMaintenance())->deletePriceChanges((int)$countDay);
        $this->stdout(json_encode($result, JSON_UNESCAPED_UNICODE).PHP_EOL);
        return \yii\console\ExitCode::OK;
    }
}