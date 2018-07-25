<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */

namespace skeeks\cms\shop\paySystems;

use skeeks\cms\shop\components\PaySystemHandlerComponent;
use skeeks\cms\shop\models\ShopBill;
use skeeks\cms\shop\models\ShopOrder;
use yii\base\Component;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class SberbankPaySystem extends PaySystemHandlerComponent
{
    public $isLive = true; //https://auth.robokassa.ru/Merchant/Index.aspx

    public $gatewayUrl = 'https://securepayments.sberbank.ru/payment/rest/';
    public $gatewayTestUrl = 'https://3dsec.sberbank.ru/payment/rest/';
    public $thanksUrl = '/main/spasibo-za-zakaz';
    public $failUrl = '/main/problema-s-oplatoy';
    public $currency = 'RUB';
    public $username = '';
    public $password = '';

    /**
     * Можно задать название и описание компонента
     * @return array
     */
    static public function descriptorConfig()
    {
        return array_merge(parent::descriptorConfig(), [
            'name' => \Yii::t('skeeks/shop/app', 'Sberbank'),
        ]);
    }


    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['isLive'], 'boolean'],
            [['username'], 'string'],
            [['password'], 'string'],
        ]);
    }

    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'username' => 'Идентификатор магазина из ЛК',
            'password' => 'Пароль',
            'isLive' => 'Рабочий режим (не тестовый!)',
        ]);
    }

    public function attributeHints()
    {
        return ArrayHelper::merge(parent::attributeHints(), [
            'isLive' => 'Будет использован url: https://securepayments.sberbank.ru/payment/rest/ (тестовый: https://3dsec.sberbank.ru/payment/rest/)',
        ]);
    }



    /**
     * @param ShopOrder $shopOrder
     * @return $this
     */
    public function actionPaymentResponse(ShopBill $shopBill)
    {
        return \Yii::$app->response->redirect(['shop/sberbank/bill-form', 'code' => $shopBill->code]);;
    }


    public function renderConfigForm(ActiveForm $activeForm)
    {
        echo $activeForm->field($this, 'isLive')->checkbox();
        echo $activeForm->field($this, 'username')->textInput();
        echo $activeForm->field($this, 'password')->textInput();
    }


    /**
     * @param $method
     * @param $data
     * @return mixed
     */
    public function gateway($method, $data) {
        $curl = curl_init(); // Инициализируем запрос
        curl_setopt_array($curl, array(
            CURLOPT_URL => ($this->isLive?$this->gatewayUrl:$this->gatewayTestUrl).$method, // Полный адрес метода
            CURLOPT_RETURNTRANSFER => true, // Возвращать ответ
            CURLOPT_POST => true, // Метод POST
            CURLOPT_POSTFIELDS => http_build_query($data) // Данные в запросе
        ));
        $response = curl_exec($curl); // Выполненяем запрос
        $response = json_decode($response, true); // Декодируем из JSON в массив
        curl_close($curl); // Закрываем соединение
        return $response; // Возвращаем ответ
    }
}