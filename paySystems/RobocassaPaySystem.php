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

/**
 * Class RobocassaPaySystem
 * @package skeeks\cms\shop\paySystems
 */
class RobocassaPaySystem extends PaySystemHandlerComponent
{
    public $baseUrl         = 'http://test.robokassa.ru/Index.aspx'; //https://auth.robokassa.ru/Merchant/Index.aspx
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
            'name'          => 'Робокасса',
        ]);
    }

    /**
     * Файл с формой настроек, по умолчанию
     *
     * @return string
     */
    public function getConfigFormFile()
    {
        return __DIR__ . '/forms/robocassa.php';
    }

    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['baseUrl'], 'string'],
            [['baseUrl'], 'url'],
            [['sMerchantLogin'], 'string'],
            [['sMerchantPass1'], 'string'],
            [['sMerchantPass2'], 'string'],
        ]);
    }

    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'baseUrl'                           => 'Путь на api сервиса robocassa',
            'sMerchantLogin'                    => 'sMerchantLogin',
            'sMerchantPass1'                    => 'sMerchantPass1',
            'sMerchantPass2'                    => 'sMerchantPass2',
        ]);
    }

    /**
     * @return \robokassa\Merchant
     * @throws \yii\base\InvalidConfigException
     */
    public function getMerchant()
    {
        /**
         * @var \robokassa\Merchant $merchant
         */
        $merchant = \Yii::createObject(ArrayHelper::merge($this->toArray(['baseUrl', 'sMerchantLogin', 'sMerchantPass1', 'sMerchantPass2']), [
            'class' => '\robokassa\Merchant',
        ]));

        return $merchant;
    }

    /**
     * @param ShopOrder $shopOrder
     * @return $this
     */
    public function paymentResponse(ShopOrder $shopOrder)
    {
        return $this->getMerchant()->payment($shopOrder->price, $shopOrder->id, 'Оплата заказа', null, $shopOrder->user->email);
    }

}