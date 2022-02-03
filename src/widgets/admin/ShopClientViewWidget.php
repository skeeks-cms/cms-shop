<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */

namespace skeeks\cms\shop\widgets\admin;

use common\models\User;
use skeeks\crm\models\CrmContractor;
use yii\base\Widget;

class ShopClientViewWidget extends Widget
{
    /**
     * @var ShopClient
     */
    public $shopClient = null;

    /**
     * @var bool Показывать только название
     */
    public $isShowOnlyName = false;

    /**
     * @var string
     */
    public $tagName = "a";

    /**
     * @var string
     */
    public $append = "";

    /**
     * @var array
     */
    public $tagNameOptions = [];

    /**
     * @var int
     */
    public $prviewImageSize = 40;

    public function run()
    {
        if ($this->shopClient) {
            $cache = \Yii::$app->cache->get("shopClient{$this->shopClient->id}" . $this->tagName . $this->prviewImageSize);
            if ($cache) {
                return $cache;
            }

            $result = $this->render('shop-client-view');
            \Yii::$app->cache->set("shopClient{$this->shopClient->id}", $result, 2);
            return $result;
        } else {
            return "";
        }

    }
}