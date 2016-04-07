<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 12.10.2015
 */
namespace skeeks\cms\shop\paySystems;
use skeeks\cms\shop\components\PaySystemHandlerComponent;
use skeeks\cms\shop\models\ShopOrder;
use yii\base\Component;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;

/**
 * Class PayPalPaySystem
 * @package skeeks\cms\shop\paySystems
 */
class PayPalPaySystem extends PaySystemHandlerComponent
{
    public $payNowButtonUrl         = 'https://www.sandbox.paypal.com/cgi-bin/websc'; //https://auth.robokassa.ru/Merchant/Index.aspx
    public $receiverEmail           = 'semenov-facilitator@skeeks.com';
    public $sMerchantPass1  = '';
    public $sMerchantPass2  = '';

    /**
     * Можно задать название и описание компонента
     * @return array
     */
    static public function descriptorConfig()
    {
        return array_merge(parent::descriptorConfig(), [
            'name'          =>  \skeeks\cms\shop\Module::t('app', 'PayPal'),
        ]);
    }

    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['payNowButtonUrl'], 'string'],
            [['payNowButtonUrl'], 'url'],
            [['receiverEmail'], 'string'],
        ]);
    }

    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'payNowButtonUrl'                   => \skeeks\cms\shop\Module::t('app', 'Road to the api service paypal'),
            'receiverEmail'                     => 'PayPal account email',
        ]);
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
        $merchant = \Yii::createObject(ArrayHelper::merge($this->toArray(['baseUrl', 'sMerchantLogin', 'sMerchantPass1', 'sMerchantPass2']), [
            'class' => '\skeeks\cms\shop\paySystems\robokassa\Merchant',
        ]));

        return $merchant;
    }

    /**
     * @param ShopOrder $shopOrder
     * @return $this
     */
    public function paymentResponse(ShopOrder $shopOrder)
    {
        return \Yii::$app->response->redirect(['shop/order/pay-pal', 'id' => $shopOrder->id]);

        /*return \Yii::$app->view->render('@skeeks/cms/shop/views/pay-system/pay-pal', [
            'model' => $shopOrder
        ], $this);*/
    }

    public function renderConfigForm(ActiveForm $activeForm)
    {
        echo $activeForm->field($this, 'payNowButtonUrl')->textInput();
        echo $activeForm->field($this, 'receiverEmail')->textInput();
    }
}