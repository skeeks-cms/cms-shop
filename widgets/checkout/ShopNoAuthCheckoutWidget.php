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

        if ($post = \Yii::$app->request->post())
        {
            $this->shopFuser->load($post);
            $this->shopFuser->save();
        }

        $shopBuyer = $this->shopBuyer;
        if ($shopBuyer)
        {
            if ($post = \Yii::$app->request->post())
            {
                $this->shopBuyer->load($post);
            }
        }

        if ($rr->isRequestPjaxPost())
        {
            if (!\Yii::$app->request->post($this->notSubmitParam))
            {

            }
        }

        return $this->render($this->viewFile);
    }

    /**
     * @return \skeeks\cms\shop\models\ShopBuyer
     * @throws \skeeks\cms\shop\models\InvalidParamException
     */
    public function getShopBuyer()
    {
        return $this->shopFuser->personType->createModelShopBuyer();
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
