<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */

namespace skeeks\cms\shop\console\controllers;

use skeeks\cms\shop\models\ShopFuser;
use skeeks\cms\shop\models\ShopProductPriceChange;
use yii\console\Controller;
use yii\db\Exception;
use yii\db\Expression;
use yii\helpers\Console;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class UtilsController extends Controller
{

    /**
     * Создает недостающие shopProduct у товаров
     */
    public function actionProductsNormalize()
    {

    }
}