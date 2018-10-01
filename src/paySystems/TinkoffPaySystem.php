<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 12.10.2015
 */

namespace skeeks\cms\shop\paySystems;

use skeeks\cms\shop\components\PaySystemHandlerComponent;
use skeeks\cms\shop\models\ShopBill;
use skeeks\cms\shop\models\ShopOrder;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;

/**
 * Class TinkoffPaySystem
 * @package skeeks\cms\shop\paySystems
 */
class TinkoffPaySystem extends PaySystemHandlerComponent
{
    public $terminal_key;

    /**
     * Можно задать название и описание компонента
     * @return array
     */
    static public function descriptorConfig()
    {
        return array_merge(parent::descriptorConfig(), [
            'name' => \Yii::t('skeeks/shop/app', 'Tinkoff'),
        ]);
    }


    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['terminal_key'], 'string'],
        ]);
    }

    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'terminal_key' => 'Идентификатор магазина из ЛК',
        ]);
    }

    public function attributeHints()
    {
        return ArrayHelper::merge(parent::attributeHints(), [
            'isLive' => '',
        ]);
    }


    /**
     * @param ShopOrder $shopOrder
     * @return $this
     * @deprecated 
     */
    public function paymentResponse(ShopOrder $shopOrder)
    {
        return \Yii::$app->response->redirect(['shop/tinkoff/order-form', 'key' => $shopOrder->key]);;
    }

    /**
     * @param ShopOrder $shopOrder
     * @return $this
     */
    public function actionPaymentResponse(ShopBill $shopBill)
    {
        return \Yii::$app->response->redirect(['shop/tinkoff/bill-form', 'code' => $shopBill->code]);;
    }
    
    public function renderConfigForm(ActiveForm $activeForm)
    {
        echo $activeForm->field($this, 'terminal_key');
    }
}