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
use yii\bootstrap\Alert;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/**
 * @property string $baseUrl
 * Class RobokassaPaySystem
 * @package skeeks\cms\shop\paySystems
 */
class RobokassaPaySystem extends PaySystemHandlerComponent
{
    public $isLive = true; //https://auth.robokassa.ru/Merchant/Index.aspx
    public $sMerchantLogin = '';
    public $sMerchantPass1 = '';
    public $sMerchantPass2 = '';

    /**
     * Можно задать название и описание компонента
     * @return array
     */
    static public function descriptorConfig()
    {
        return array_merge(parent::descriptorConfig(), [
            'name' => \Yii::t('skeeks/shop/app', 'Robokassa'),
        ]);
    }

    public function getBaseUrl()
    {
        return "https://auth.robokassa.ru/Merchant/Index.aspx";
    }

    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['sMerchantLogin'], 'string'],
            [['sMerchantPass1'], 'string'],
            [['sMerchantPass2'], 'string'],
            [['isLive'], 'boolean'],
        ]);
    }

    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'isLive'         => 'Боевой режим?',
            'sMerchantLogin' => 'sMerchantLogin',
            'sMerchantPass1' => 'sMerchantPass1',
            'sMerchantPass2' => 'sMerchantPass2',
        ]);
    }

    public function attributeHints()
    {
        return ArrayHelper::merge(parent::attributeHints(), [
            'isLive' => 'Боевой режим использует адрес: https://auth.robokassa.ru/Merchant/Index.aspx, не боевой — http://test.robokassa.ru/Index.aspx',
        ]);
    }
    /**
     * @param ShopOrder $shopOrder
     * @return $this
     */
    /*public function paymentResponse(ShopOrder $shopOrder)
    {
        return $this->getMerchant()->payment($shopOrder->price, $shopOrder->id, \Yii::t('skeeks/shop/app', 'Payment order'), null, $shopOrder->user->email);
    }*/
    
    /**
     * @param ShopOrder $shopOrder
     * @return $this
     */
    public function actionPaymentResponse(ShopBill $shopBill)
    {
        return $this->getMerchant()->payment($shopBill->money->amount, $shopBill->id, \Yii::t('skeeks/shop/app', 'Payment order'), null, $shopBill->shopOrder->email);
    }
    
    /**
     * @return \skeeks\cms\shop\paySystems\robokassa\Merchant
     * @throws \yii\base\InvalidConfigException
     */
    public function getMerchant()
    {
        /**
         * @var \skeeks\cms\shop\paySystems\robokassa\Merchant $merchant
         */
        $merchant = \Yii::createObject(ArrayHelper::merge($this->toArray([
            'sMerchantLogin',
            'sMerchantPass1',
            'sMerchantPass2',
        ]), [
            'class'   => '\skeeks\cms\shop\paySystems\robokassa\Merchant',
            'baseUrl' => $this->baseUrl,
            'isLive'  => (bool)$this->isLive,
        ]));

        return $merchant;
    }


    public function renderConfigForm(ActiveForm $activeForm)
    {
        $successUrl = Url::to(['/shop/robokassa/success'], true);
        $resultUrl = Url::to(['/shop/robokassa/result'], true);
        $failUrl = Url::to(['/shop/robokassa/fail'], true);
        echo Alert::widget([
            'closeButton' => false,
            'options'     => [
                'class' => 'alert-info',
            ],

            'body' => <<<HTML
<p>В личном кабинете <a href="https://partner.robokassa.ru/" target="_blank">https://partner.robokassa.ru/</a>, выбирите нужный магазин и пропишите настройки:</p> 
<p>Result Url: <b>{$resultUrl}</b></p> 
<p>Success Url: <b>{$successUrl}</b></p> 
<p>Fail Url: <b>{$failUrl}</b></p> 
HTML
            ,
        ]);
        echo $activeForm->field($this, 'isLive')->checkbox();
        echo $activeForm->field($this, 'sMerchantLogin')->textInput();
        echo $activeForm->field($this, 'sMerchantPass1')->textInput();
        echo $activeForm->field($this, 'sMerchantPass2')->textInput();
    }
}