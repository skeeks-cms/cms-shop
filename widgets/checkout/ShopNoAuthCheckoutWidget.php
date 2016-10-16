<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 14.10.2016
 */
namespace skeeks\cms\shop\widgets\checkout;

use skeeks\cms\helpers\RequestResponse;
use skeeks\cms\shop\models\ShopBuyer;
use skeeks\cms\shop\models\ShopFuser;
use skeeks\cms\shop\models\ShopOrder;
use yii\base\Exception;
use yii\base\Widget;

/**
 * @property bool shopIsReady
 * @property ShopBuyer shopBuyer
 *
 * Class ShopNoAuthCheckoutWidget
 * @package skeeks\cms\shop\widgets\checkout
 */
class ShopNoAuthCheckoutWidget extends Widget
{
    public static $autoIdPrefix = 'ShopCheckoutWidget';

    public $viewFile = 'checkout-no-auth';

    public $options = [];

    /**
     * @var ShopFuser
     */
    public $shopFuser = null;

    /**
     * @var ShopBuyer
     */
    public $shopBuyer = null;

    public $shopErrors = [];

    public $notSubmitParam = 'sx-not-submit';

    public function init()
    {
        parent::init();

        $this->options['id'] = $this->id;

        $this->shopFuser = \Yii::$app->shop->shopFuser;
        $this->shopFuser->loadDefaultValues();
    }

    public function run()
    {
        $rr = new RequestResponse();
        $errors = [];

        if ($post = \Yii::$app->request->post())
        {
            $this->shopFuser->load($post);
            $this->shopFuser->save();
        }

        $this->shopBuyer = $this->shopFuser->personType->createModelShopBuyer();

        $shopBuyer = $this->shopBuyer;
        if ($shopBuyer)
        {
            if ($post = \Yii::$app->request->post())
            {
                $this->shopBuyer->load($post);
                $this->shopBuyer->relatedPropertiesModel->load($post);
            }
        }

        if ($rr->isRequestPjaxPost())
        {
            if (!\Yii::$app->request->post($this->notSubmitParam))
            {
                if ($this->shopFuser->validate() && $this->shopBuyer->validate() && $this->shopBuyer->relatedPropertiesModel->validate())
                {
                    if ($this->shopBuyer->isNewRecord)
                    {
                        if (!$this->shopBuyer->save())
                        {
                            throw new Exception('Not save buyer');
                        }
                    }

                    if (!$this->shopBuyer->relatedPropertiesModel->save())
                    {
                        throw new Exception('Not save buyer data');
                    }

                    $this->shopFuser->buyer_id = $this->shopBuyer->id;

                    $newOrder = ShopOrder::createOrderByFuser($this->shopFuser);
                    $orderUrl = $newOrder->publicUrl;
                    $this->view->registerJs(<<<JS
location.href='{$orderUrl}';
JS
);

                } else
                {
                    print_r($this->shopFuser->errors);
                    print_r($this->shopBuyer->errors);
                    print_r($this->shopBuyer->relatedPropertiesModel->errors);
                }
            }
        }

        return $this->render($this->viewFile);
    }


    /**
     * @return bool
     */
    public function getShopIsReady()
    {
        $this->shopErrors = [];

        if (!\Yii::$app->shop->shopPersonTypes)
        {
            $this->shopErrors[] = 'Не заведены типы профилей покупателей';
        }

        if ($this->shopErrors)
        {
            return false;
        }

        return true;
    }
}
