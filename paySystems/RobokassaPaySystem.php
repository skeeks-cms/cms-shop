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
 * @property string $baseUrl
 * Class RobokassaPaySystem
 * @package skeeks\cms\shop\paySystems
 */
class RobokassaPaySystem extends PaySystemHandlerComponent
{
    public $isLive          = true; //https://auth.robokassa.ru/Merchant/Index.aspx
    public $sMerchantLogin  = '';
    public $sMerchantPass1  = '';
    public $sMerchantPass2  = '';

    /**
     * Можно задать название и описание компонента
     * @return array
     */
    static public function descriptorConfig()
    {
        return array_merge(parent::descriptorConfig(), [
            'name'          =>  \skeeks\cms\shop\Module::t('app', 'Robokassa'),
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
            'isLive'                            => 'Is live',
            'sMerchantLogin'                    => 'sMerchantLogin',
            'sMerchantPass1'                    => 'sMerchantPass1',
            'sMerchantPass2'                    => 'sMerchantPass2',
        ]);
    }

    public function attributeHints()
    {
        return ArrayHelper::merge(parent::attributeHints(), [
            'isLive' => 'If is live used: https://auth.robokassa.ru/Merchant/Index.aspx else http://test.robokassa.ru/Index.aspx',
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
        $merchant = \Yii::createObject(ArrayHelper::merge($this->toArray(['sMerchantLogin', 'sMerchantPass1', 'sMerchantPass2']), [
            'class' => '\skeeks\cms\shop\paySystems\robokassa\Merchant',
            'baseUrl' => $this->baseUrl,
            'isLive' => (bool) $this->isLive,
        ]));

        return $merchant;
    }

    /**
     * @param ShopOrder $shopOrder
     * @return $this
     */
    public function paymentResponse(ShopOrder $shopOrder)
    {
        return $this->getMerchant()->payment($shopOrder->price, $shopOrder->id, \skeeks\cms\shop\Module::t('app', 'Payment order'), null, $shopOrder->user->email);
    }

    public function renderConfigForm(ActiveForm $activeForm)
    {
        echo $activeForm->field($this, 'isLive')->checkbox();
        echo $activeForm->field($this, 'sMerchantLogin')->textInput();
        echo $activeForm->field($this, 'sMerchantPass1')->textInput();
        echo $activeForm->field($this, 'sMerchantPass2')->textInput();
    }
}