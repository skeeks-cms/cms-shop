<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */

namespace skeeks\cms\shop\components;

use skeeks\cms\models\CmsAgent;
use skeeks\cms\shop\models\ShopStore;
use yii\base\Component;
use yii\base\Exception;
use yii\httpclient\Client;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 *
 * @property ShopStore $backendShopStore
 */
class WbComponent extends Component
{
    public $api_url = "https://suppliers-api.wildberries.ru";

    public $api_key = "";


    /*public $api_stat_key = "";*/
    public $api_stat_url = "https://statistics-api.wildberries.ru";


    /**
     * @param string $api_url
     * @param string $api_key
     * @param string $api_method
     * @param array  $data
     * @param string $method
     * @return ApiResponse
     */
    private function _createRequest(string $api_url, string $api_key, string $api_method, array $data = [], string $method = "GET")
    {
        $apiResponse = new ApiResponse();

        try {

            $url = $api_url.$api_method;
            $headers = [
                'Authorization' => $api_key,
            ];
            $options = [
                'timeout'      => 20,
                'maxRedirects' => 2,
            ];

            $apiResponse->request_data = $data;
            $apiResponse->request_method = $method;
            $apiResponse->request_url = $url;
            $apiResponse->request_options = $options;
            $apiResponse->request_headers = $headers;

            $start = time();

            $client = new Client([
                'requestConfig' => [
                    'format' => Client::FORMAT_JSON,
                ],
            ]);

            $request = $client->createRequest()
                ->setMethod($method)
                ->setHeaders($headers)
                ->setOptions($options);

            if ($data) {
                if ($method == "POST") {
                    $request->setData($data);
                } else {
                    $url = $url . "?" . http_build_query($data);
                }
            }


            $request->setUrl($url);

            $response = $request->send();

            $apiResponse->code = $response->statusCode;
            $apiResponse->content = $response->content;


            if ($response->isOk) {
                if ($response->data && is_array($response->data)) {
                    $apiResponse->data = $response->data;
                }
                $apiResponse->isOk = true;
            } else {
                throw new Exception("Ответ сервера api: " . $response->statusCode);
            }


        } catch (\Exception $exception) {
            $apiResponse->error_message = $exception->getMessage();
        }
        $apiResponse->time = time() - $start;

        return $apiResponse;
    }

    /**
     * @param string $api_method
     * @param array  $data
     * @return ApiResponse
     */
    public function _createPostRequest(string $api_method, array $data = [])
    {
        return $this->_createRequest($this->api_url, $this->api_key, $api_method, $data, "POST");
    }

    /**
     * @param string $api_method
     * @param array  $data
     * @return ApiResponse
     */
    public function _createGetRequest(string $api_method, array $data = [])
    {
        return $this->_createRequest($this->api_url, $this->api_key, $api_method, $data, "GET");
    }
    
    
    /**
     * @param string $api_method
     * @param array  $data
     * @return ApiResponse
     */
    public function _createStatPostRequest(string $api_method, array $data = [])
    {
        return $this->_createRequest($this->api_stat_url, $this->api_key, $api_method, $data, "POST");
    }

    /**
     * @param string $api_method
     * @param array  $data
     * @return ApiResponse
     */
    public function _createStatGetRequest(string $api_method, array $data = [])
    {
        return $this->_createRequest($this->api_stat_url, $this->api_key, $api_method, $data, "GET");
    }

    /**
     * @param $data
     * @return ApiResponse
     */
    public function methodContentCardsList($data = [])
    {
        if (!$data) {
            $data = [
                'sort' => [
                    "cursor" =>
                        [
                            "limit" => 1000,
                        ],
                    "filter" =>
                        [
                            "withPhoto" => -1,
                        ],
                ],
            ];
        }


        return $this->_createPostRequest("/content/v1/cards/cursor/list", $data);
    }

    /**
     * Получение информации по номенклатурам, их ценам, скидкам и промокодам. Если не указывать фильтры, вернётся весь товар.
     *
     * @see https://openapi.wildberries.ru/prices/api/ru/#tag/Ceny/paths/~1public~1api~1v1~1info/get
     *
     * @param $data
     * @return ApiResponse
     */
    public function methodContentGetPrices($data = [])
    {
        return $this->_createGetRequest("/public/api/v1/info", $data);
    }

    /**
     * @see https://openapi.wildberries.ru/content/api/ru/#tag/Konfigurator/paths/~1content~1v1~1object~1all/get
     *
     * @param array $data
     * @return ApiResponse
     */
    public function methodContentAll(array $data = [])
    {
        return $this->_createGetRequest("/content/v1/object/all", $data);
    }

    /**
     *
     * @see https://openapi.wildberries.ru/content/api/ru/#tag/Konfigurator/paths/~1content~1v1~1object~1all/get
     *
     * @param array $data
     * @return ApiResponse
     */
    public function methodContentWarehouses(array $data = [])
    {
        return $this->_createGetRequest("/api/v3/warehouses", $data);
    }

    /**
     *
     * @see https://openapi.wildberries.ru/content/api/ru/#tag/Konfigurator/paths/~1content~1v1~1object~1all/get
     *
     * @param array $data
     * @return ApiResponse
     */
    public function methodOrders(array $data = [])
    {
        return $this->_createGetRequest("/api/v3/orders", $data);
    }

    /**
     * Заказы
     *
     * @see https://openapi.wildberries.ru/statistics/api/ru/#tag/Statistika/paths/~1api~1v1~1supplier~1orders/get
     *
     * @param array $data
     * @return ApiResponse
     */
    public function methodStatSupplierSales(array $data = [])
    {
        return $this->_createStatGetRequest("/api/v1/supplier/sales", $data);
    }
    /**
     * Отчет о продажах по реализации
     *
     * @see https://openapi.wildberries.ru/statistics/api/ru/#tag/Statistika/paths/~1api~1v1~1supplier~1reportDetailByPeriod/get
     *
     * @param array $data
     * @return ApiResponse
     */
    public function methodStatReportDetailByPeriod(array $data = [])
    {
        return $this->_createStatGetRequest("/api/v1/supplier/reportDetailByPeriod", $data);
    }

    /**
     * Продажи и возвраты.
     *
     * @see https://openapi.wildberries.ru/statistics/api/ru/#tag/Statistika/paths/~1api~1v1~1supplier~1sales/get
     *
     * @param array $data
     * @return ApiResponse
     */
    public function methodStatSupplierOrders(array $data = [])
    {
        return $this->_createStatGetRequest("/api/v1/supplier/orders", $data);
    }

    /**
     * Поставки
     *
     * @see https://openapi.wildberries.ru/statistics/api/ru/#tag/Statistika/paths/~1api~1v1~1supplier~1sales/get
     *
     * @param array $data
     * @return ApiResponse
     */
    public function methodStatSupplierIncomes(array $data = [])
    {
        return $this->_createGetRequest("/api/v1/supplier/incomes", $data);
    }
    /**
     * Остатки на складах WB
     *
     * @see https://openapi.wildberries.ru/statistics/api/ru/#tag/Statistika/paths/~1api~1v1~1supplier~1stocks/get
     *
     * @param array $data
     * @return ApiResponse
     */
    public function methodStatSupplierStocks(array $data = [])
    {
        return $this->_createGetRequest("/api/v1/supplier/stocks", $data);
    }
}