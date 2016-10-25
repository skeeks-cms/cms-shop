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
 *
 * Class YandexKassaPaySystem
 * @package skeeks\cms\shop\paySystems
 */
class YandexKassaPaySystem extends PaySystemHandlerComponent
{
    public $isLive          = false;
    public $shop_password;
    public $security_type   = 'MD5';
    public $shop_id;
    public $scid;

    public function getBaseUrl()
    {
        return $this->isLive ? 'https://demomoney.yandex.ru/eshop.xml' : 'https://demomoney.yandex.ru/eshop.xml';
    }
    /**
     * Можно задать название и описание компонента
     * @return array
     */
    static public function descriptorConfig()
    {
        return array_merge(parent::descriptorConfig(), [
            'name'          =>  \Yii::t('skeeks/shop/app', 'YandexKassa'),
        ]);
    }


    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['shop_password'], 'string'],
            [['security_type'], 'string'],
            [['shop_id'], 'string'],
            [['scid'], 'string'],
        ]);
    }

    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'sMerchantPass2'                    => 'sMerchantPass2',
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
     */
    public function paymentResponse(ShopOrder $shopOrder)
    {
        return \Yii::$app->response->redirect(['shop/yandex-kassa/order-form', 'key' => $shopOrder->key]);;
    }

    public function renderConfigForm(ActiveForm $activeForm)
    {
        echo $activeForm->field($this, 'isLive')->checkbox();
        //echo $activeForm->field($this, 'shop_password');
        echo $activeForm->field($this, 'security_type');
        echo $activeForm->field($this, 'shop_id');
        echo $activeForm->field($this, 'scid');
    }
}