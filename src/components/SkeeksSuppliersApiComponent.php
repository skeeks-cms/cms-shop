<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */

namespace skeeks\cms\shop\components;

use skeeks\cms\models\CmsAgent;
use skeeks\cms\models\CmsContentElement;
use skeeks\cms\shop\models\ShopBrand;
use skeeks\cms\shop\models\ShopCollection;
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
    public $api_url = "https://gpd-api.skeeks.com/v1";

    /**
     * @var string базовый адрес каталога SkeekS Market
     */
    public $market_url = "https://skeeks-market.ru";

    /**
     * @var string ключ полученный в API сервисе
     */
    public $api_key = "";

    /**
     * @var int время ожидания ответа, прежде чем будет считаться неуспешным
     */
    public $timeout = 20;


    /**
     * @var bool скачивать изображения на сервер?
     */
    public $is_download_images = false;

    /**
     * @param int|string $sx_id
     * @return string
     */
    public function getProductUrl($sx_id)
    {
        return $this->getMarketUrl("/p-".(int)$sx_id);
    }

    /**
     * @param int|string $sx_id
     * @return string
     */
    public function getCollectionUrl($sx_id)
    {
        return $this->getMarketUrl("/p-c".(int)$sx_id);
    }

    /**
     * @param int|string $sx_id
     * @return string
     */
    public function getBrandUrl($sx_id)
    {
        return $this->getMarketUrl("/p-b".(int)$sx_id);
    }

    /**
     * @param mixed $model
     * @return string|null
     */
    public function getModelUrl($model)
    {
        if (!$model || !isset($model->sx_id) || !$model->sx_id) {
            return null;
        }

        if ($model instanceof ShopBrand) {
            return $this->getBrandUrl($model->sx_id);
        }

        if ($model instanceof ShopCollection) {
            return $this->getCollectionUrl($model->sx_id);
        }

        if ($model instanceof CmsContentElement) {
            return $this->getProductUrl($model->sx_id);
        }

        return null;
    }

    /**
     * @param string $path
     * @return string
     */
    public function getMarketUrl($path = "")
    {
        if (preg_match('#^https?://#i', (string)$path)) {
            return (string)$path;
        }

        return rtrim($this->market_url, "/")."/".ltrim((string)$path, "/");
    }

    /**
     * @param string $src
     * @return string
     */
    public function getImageUrl($src)
    {
        if (!$src) {
            return "";
        }

        return $this->getMarketUrl($src);
    }

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
    public function methodCategories($params = [])
    {
        return $this->request("/categories", $params);
    }

    /**
     * @return ApiResponse
     */
    public function methodProperties($params = [])
    {
        return $this->request("/properties", $params);
    }

    /**
     * @return ApiResponse
     */
    public function methodBrands($params = [])
    {
        return $this->request("/brands", $params);
    }

    /**
     * @return ApiResponse
     */
    public function methodCollections($params = [])
    {
        return $this->request("/collections", $params);
    }

    /**
     * @return ApiResponse
     */
    public function methodStoreItems($params = [])
    {
        return $this->request("/store-items", $params);
    }

    /**
     * @return ApiResponse
     */
    public function methodProducts($params = [])
    {
        return $this->request("/products", $params);
    }
}
