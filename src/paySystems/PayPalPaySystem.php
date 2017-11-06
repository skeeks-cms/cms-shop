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
 * @property string $payPalUrl
 *
 * Class PayPalPaySystem
 * @package skeeks\cms\shop\paySystems
 */
class PayPalPaySystem extends PaySystemHandlerComponent
{
    public $receiverEmail           = 'semenov-facilitator@skeeks.com';
    public $isLive                    = true;

    /**
     * @return string
     */
    public function getPayPalUrl()
    {
        return $this->isLive ? 'https://www.paypal.com/cgi-bin/websc' : 'https://www.sandbox.paypal.com/cgi-bin/websc';
    }

    /**
     * Можно задать название и описание компонента
     * @return array
     */
    static public function descriptorConfig()
    {
        return array_merge(parent::descriptorConfig(), [
            'name'          =>  \Yii::t('skeeks/shop/app', 'PayPal'),
        ]);
    }

    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['isLive'], 'boolean'],
            [['receiverEmail'], 'string'],
        ]);
    }

    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'receiverEmail'                     => \Yii::t('skeeks/shop/app', 'PayPal account email'),
            'isLive'                            => \Yii::t('skeeks/shop/app', 'Is live'),
        ]);
    }

    public function attributeHints()
    {
        return ArrayHelper::merge(parent::attributeHints(), [
            'isLive'                            => 'If is live used url: https://www.paypal.com/cgi-bin/websc else https://www.sandbox.paypal.com/cgi-bin/websc',
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
        echo $activeForm->field($this, 'isLive')->checkbox();
        echo $activeForm->field($this, 'receiverEmail')->textInput();
    }

    private $debug = true;
    private $service;
    private $projectName = 'test';

    /**
     * @throws Exception
     */
    public function initIpn(){
        $postData = file_get_contents('php://input');
        $transactionType = $this->getPaymentType($postData);

        //$config = Config::get();

		// в зависимости от типа платежа выбираем клас
        if($transactionType == "web_accept"){
            //$this->service = new PaypalSinglePayment();
        }
        /*elseif($transactionType == PaypalTransactionType::TRANSACTION_TYPE_SUBSCRIPTION){
            $this->service = new PaypalSubscription($config);
        }*/
        else{
            \Yii::error('Wrong payment type', 'paypal');
            //throw new Exception('Wrong payment type');
        }

        $raw_post_data = file_get_contents('php://input');

        $raw_post_array = explode('&', $raw_post_data);
        $myPost = array();
        foreach ($raw_post_array as $keyval) {
            $keyval = explode ('=', $keyval);
            if (count($keyval) == 2)
                $myPost[$keyval[0]] = urldecode($keyval[1]);
        }

        $customData = $customData = json_decode($myPost['custom'],true);
        $userId = $customData['user_id'];

        // read the post from PayPal system and add 'cmd'
        $req = 'cmd=_notify-validate';
        if(function_exists('get_magic_quotes_gpc')) {
            $get_magic_quotes_exists = true;
        }
        else{
            $get_magic_quotes_exists = false;
        }


        foreach ($myPost as $key => $value) {
            if($get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1) {
                $value = urlencode(stripslashes($value));
            } else {
                $value = urlencode($value);
            }
            $req .= "&$key=$value";
        }

        $myPost['customData'] = $customData;

        $paypal_url = $this->payPalUrl;
        //$paypal_url = 'https://www.paypal.com/cgi-bin/websc';

		// проверка подлинности IPN запроса
        $res = $this->sendRequest($paypal_url,$req);

        // Inspect IPN validation result and act accordingly
        // Split response headers and payload, a better way for strcmp
        $tokens = explode("\r\n\r\n", trim($res));
        $res = trim(end($tokens));

        /**/
        if (strcmp ($res, "VERIFIED") == 0) {
            \Yii::info("VERIFIED", 'paypal');
			// продолжаем обраюотку запроса
            //$this->service->processPayment($myPost);
            return true;
        } else if (strcmp ($res, "INVALID") == 0) {
            // запрос не прощел проверку
            \Yii::error("Invalid IPN: $req", 'paypal');
            /*self::log([
                'message' => "Invalid IPN: $req" . PHP_EOL,
                'level' => self::LOG_LEVEL_ERROR
            ], $myPost);*/
        }
        /**/
    }

    private function sendRequest($paypal_url,$req){
        $debug = $this->debug;

        $ch = curl_init($paypal_url);
        if ($ch == FALSE) {
            return FALSE;
        }
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $req);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);

        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
        if($debug == true) {
            curl_setopt($ch, CURLOPT_HEADER, 1);
            curl_setopt($ch, CURLINFO_HEADER_OUT, 1);
        }

        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);

		//передаем заголовок, указываем User-Agent - название нашего приложения. Необходимо для работы в live режиме
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close', 'User-Agent: ' . $this->projectName));

        $res = curl_exec($ch);
        curl_close($ch);

        return $res;
    }

	public function getPaymentType($rawPostData){
        $post = $this->getPostFromRawData($rawPostData);

        if(isset($post['subscr_id'])){
            return "subscr_payment";
        }
        else{
            return "web_accept";
        }
    }

    /**
     * @param $raw_post_data
     * @return array
     */
    public function getPostFromRawData($raw_post_data){
        $raw_post_array = explode('&', $raw_post_data);
        $myPost = array();
        foreach ($raw_post_array as $keyval) {
            $keyval = explode ('=', $keyval);
            if(count($keyval) == 2)
                $myPost[$keyval[0]] = urldecode($keyval[1]);
        }

        return $myPost;
    }
}