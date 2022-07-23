<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */
namespace skeeks\cms\shop\cloudkassa;

use skeeks\cms\IHasConfigForm;
use skeeks\cms\models\CmsSmsMessage;
use skeeks\cms\models\CmsSmsProvider;
use skeeks\cms\shop\models\ShopCheck;
use skeeks\cms\shop\models\ShopOrder;
use skeeks\cms\traits\HasComponentDescriptorTrait;
use skeeks\cms\traits\TConfigForm;
use yii\base\Exception;
use yii\base\Model;
use yii\widgets\ActiveForm;

/**
 * @property Model $checkoutModel
 *
 * @author Semenov Alexander <semenov@skeeks.com>
 */
abstract class CloudkassaHandler extends Model implements IHasConfigForm
{
    use HasComponentDescriptorTrait;
    use TConfigForm;

    /**
     * Касса готова к работе?
     * @return bool
     */
    public function isReady() {
        return true;
    }

    /**
     * Массив данных о состоянии кассы и ее данным
     * @return array
     */
    public function getInfoData()
    {
        return [];
    }


    /**
     * Создает чек и отправляет в сервис кассы
     *
     * @param ShopCheck $shopCheck
     * @return $this
     */
    public function createFiscalCheck(ShopCheck $shopCheck)
    {
        return $this;
    }

    /**
     * Обновляет статус чеку и нужные данные
     *
     * @param ShopCheck $shopCheck
     * @return $this
     */
    public function updateStatus(ShopCheck $shopCheck)
    {
        return $this;
    }
}