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
use yii\bootstrap\Alert;
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
    const SECURITY_MD5      = 'MD5';
    const SECURITY_PKCS7    = 'PKCS7';

    public $isLive          = false;
    public $shop_password;
    public $security_type   = self::SECURITY_MD5;
    public $shop_id;
    public $scid;
    public $payment_type = "";

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
            [['payment_type'], 'string'],
        ]);
    }

    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'isLive'                    => 'Рабочий режим',
            'sMerchantPass2'            => 'sMerchantPass2',
        ]);
    }

    public function attributeHints()
    {
        return ArrayHelper::merge(parent::attributeHints(), [
            'isLive' => '',
            'payment_type' => 'Смотреть https://tech.yandex.ru/money/doc/payment-solution/reference/payment-type-codes-docpage/',
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
        echo $activeForm->field($this, 'shop_password');
        echo $activeForm->field($this, 'security_type');
        echo $activeForm->field($this, 'shop_id');
        echo $activeForm->field($this, 'scid');
        echo $activeForm->field($this, 'payment_type');

        echo Alert::widget([
            'options' => [
                'class' => 'alert-info',
            ],
            'body' => <<<HTML
<a target="_blank" href="https://tech.yandex.ru/money/doc/payment-solution/shop-config/intro-docpage/">Подключение магазина</a><br />
В настройках вашего магазина на yandex укажите: <br />
Укажите checkUrl: /shop/yandex-kassa/check-order<br />
Укажите avisoUrl: /shop/yandex-kassa/payment-aviso<br />
<hr />
<a target="_blank" href="https://tech.yandex.ru/money/doc/payment-solution/examples/examples-test-data-docpage/">Тестовые данные</a><br />
HTML
,
        ]);

    }







    /**
     * Checking the MD5 sign.
     * @param  array $request payment parameters
     * @return bool true if MD5 hash is correct
     */
    public function checkRequestMD5($request) {

        //return true;

        $str = $request['action'] . ";" .
            $request['orderSumAmount'] . ";" . $request['orderSumCurrencyPaycash'] . ";" .
            $request['orderSumBankPaycash'] . ";" . $request['shopId'] . ";" .
            $request['invoiceId'] . ";" . $request['customerNumber'] . ";" . $this->shop_password;

        \Yii::info("String to md5: " . $str, static::class);

        $md5 = strtoupper(md5($str));
        if ($md5 != strtoupper($request['md5'])) {
            \Yii::error("Wait for md5:" . $md5 . ", recieved md5: " . $request['md5'], self::class);
            return false;
        }
        return true;
    }

    /**
     * @param $request
     *
     * @return bool
     */
    public function checkRequest($request)
    {
        if ($this->security_type == static::SECURITY_MD5)
        {
            if ($this->checkRequestMD5($request))
            {
                return true;
            }
        } else if ($yandexKassa->security_type == YandexKassaPaySystem::SECURITY_PKCS7)
        {
            //TODO:; make it's
            \Yii::error('SECURITY_PKCS7 — todo:: not realized', YandexKassaPaySystem::class);
        }

        return true;
    }

    /**
     * Building XML response.
     * @param  string $functionName  "checkOrder" or "paymentAviso" string
     * @param  string $invoiceId     transaction number
     * @param  string $result_code   result code
     * @param  string $message       error message. May be null.
     * @return string                prepared XML response
     */
    public function buildResponse($functionName, $invoiceId, $result_code, $message = null) {
        try {
            $performedDatetime = self::formatDate(new \DateTime());
            $response = '<?xml version="1.0" encoding="UTF-8"?><' . $functionName . 'Response performedDatetime="' . $performedDatetime .
                '" code="' . $result_code . '" ' . ($message != null ? 'message="' . $message . '"' : "") . ' invoiceId="' . $invoiceId . '" shopId="' . $this->shop_id . '"/>';
            return $response;
        } catch (\Exception $e) {
            \Yii::error($e->getMessage(), static::class);
        }
        return null;
    }



    public static function formatDate(\DateTime $date) {
        $performedDatetime = $date->format("Y-m-d") . "T" . $date->format("H:i:s") . ".000" . $date->format("P");
        return $performedDatetime;
    }

    public static function formatDateForMWS(\DateTime $date) {
        $performedDatetime = $date->format("Y-m-d") . "T" . $date->format("H:i:s") . ".000Z";
        return $performedDatetime;
    }
}