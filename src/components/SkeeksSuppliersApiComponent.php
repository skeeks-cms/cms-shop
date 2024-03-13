<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */

namespace skeeks\cms\shop\components;

use skeeks\cms\models\CmsAgent;
use yii\base\Component;
use yii\base\Exception;
use yii\httpclient\Client;

/**
 * @see https://suppliers-api.skeeks.com/
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class SkeeksSuppliersApiComponent extends Component
{
    /**
     * @var string путь к api включая версию
     */
    public $api_url = "https://suppliers-api.skeeks.com/v1";

    /**
     * @var string ключ полученный в API сервисе
     */
    public $api_key = "";

    /**
     * @var int время ожидания ответа, прежде чем будет считаться неуспешным
     */
    public $timeout = 20;

    /**
     * @param string $api_method метод API полученный
     * @param array  $data
     * @param string $request_method
     *
     * @return ApiResponse
     */
    public function request(string $api_method, array $data = [], string $request_method = "GET")
    {
        $apiResponse = new ApiResponse();

        try {

            $url = $this->api_url.$api_method;

            $headers = [
                'Authorization' => $this->api_key,
            ];

            $options = [
                'timeout'      => $this->timeout,
                'maxRedirects' => 2,
            ];

            $apiResponse->request_data = $data;
            $apiResponse->request_method = $request_method;
            $apiResponse->request_url = $url;
            $apiResponse->request_options = $options;
            $apiResponse->request_headers = $headers;

            $start = microtime(true);

            $client = new Client([
                'requestConfig' => [
                    'format' => Client::FORMAT_JSON,
                ],
            ]);

            $request = $client->createRequest()
                ->setMethod($request_method)
                ->setHeaders($headers)
                ->setOptions($options);

            if ($data) {
                if ($request_method == "POST") {
                    $request->setData($data);
                } else {
                    $url = $url."?".http_build_query($data);
                }
            }

            $request->setUrl($url);

            $response = $request->send();

            $apiResponse->code = $response->statusCode;
            $apiResponse->content = $response->content;
            $apiResponse->headers = $response->headers;

            if ($response->isOk) {
                if ($response->data && is_array($response->data)) {
                    $apiResponse->data = $response->data;
                }
                $apiResponse->isOk = true;
            } else {
                throw new Exception("Ответ сервера api: ".$response->statusCode);
            }


        } catch (\Exception $exception) {
            $apiResponse->error_message = $exception->getMessage();
        }

        $apiResponse->time = round(microtime(true) - $start, 3);

        return $apiResponse;
    }

    /**
     * Вернет информацию по партнеру, тарифный план, доступное количество товаров и прочую информацию
     *
     * @return ApiResponse
     */
    public function methodProfile()
    {
        return $this->request("/profile");
    }

    /**
     * Вернет доступные склады всех поставщиков с которым ведется работа, с которыми вы заключили договор и работаете.
     *
     * @return ApiResponse
     */
    public function methodStores()
    {
        return $this->request("/stores");
    }

    /**
     * Справочник стран.
     *
     * @return ApiResponse
     */
    public function methodCountries()
    {
        return $this->request("/countries");
    }

    /**
     *
     * Справочник едениц измерения в формате ОКЕИ — Общероссийский классификатор единиц измерения.
     *
     * @return ApiResponse
     */
    public function methodMeasures()
    {
        return $this->request("/measures");
    }

    /**
     * @return ApiResponse
     */
    public function methodCategories()
    {
        return $this->request("/categories");
    }

    /**
     * @return ApiResponse
     */
    public function methodProperties()
    {
        return $this->request("/properties");
    }

    /**
     * @return ApiResponse
     */
    public function methodBrands()
    {
        return $this->request("/brands");
    }

    /**
     * @return ApiResponse
     */
    public function methodCollections()
    {
        return $this->request("/collections");
    }

    /**
     * @return ApiResponse
     */
    public function methodStoreItems()
    {
        return $this->request("/store-items");
    }

    /**
     * @return ApiResponse
     */
    public function methodProducts()
    {
        return $this->request("/products");
    }
}