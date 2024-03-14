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
use yii\httpclient\Client;
use yii\web\HeaderCollection;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class ApiResponse extends Component
{
    public $isOk = false;

    public $request_url = "";
    public $request_data = [];
    public $request_method = [];
    public $request_options = [];
    public $request_headers = [];

    /**
     * @var int Код ответа сервера
     */
    public $code;
    /**
     * @var string контент ответа
     */
    public $content;

    /**
     * @var array преобразованный ответ сервера в массив
     */
    public $data;

    /**
     * @var int время ответа сервера
     */
    public $time;

    /**
     * @var HeaderCollection заголовки ответа
     */
    public $headers;

    /**
     * @var string сообщение об ошибке
     */
    public $error_message;
}