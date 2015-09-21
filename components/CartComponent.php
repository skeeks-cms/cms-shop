<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 10.09.2015
 */
namespace skeeks\cms\shop\components;
use skeeks\cms\controllers\AdminCmsContentElementController;
use skeeks\cms\kladr\models\KladrLocation;
use skeeks\cms\models\CmsContentElement;
use skeeks\cms\modules\admin\actions\modelEditor\AdminOneModelEditAction;
use skeeks\cms\modules\admin\controllers\AdminController;
use skeeks\cms\modules\admin\controllers\events\AdminInitEvent;
use skeeks\cms\reviews2\actions\AdminOneModelMessagesAction;
use skeeks\cms\shop\actions\AdminContentElementShopAction;
use skeeks\cms\shop\models\ShopContent;
use skeeks\cms\shop\models\ShopFuser;
use skeeks\cms\shop\models\ShopTypePrice;
use yii\base\Exception;
use yii\helpers\ArrayHelper;

/**
 * @property ShopFuser $shopFuser
 *
 * Class CartComponent
 * @package skeeks\cms\shop\components
 */
class CartComponent extends \yii\base\Component
{
    private $_shopFuser = null;

    /**
     * @var string
     */
    public $sessionFuserName = 'SKEEKS_CMS_SHOP';

    /**
     * Если нет будет создан
     *
     * @return ShopFuser
     */
    public function getShopFuser()
    {
        //Если пользователь гость
        if (\Yii::$app->user->isGuest)
        {
            //Проверка сессии
            if (\Yii::$app->getSession()->offsetExists($this->sessionFuserName))
            {
                $fuserId    = \Yii::$app->getSession()->get($this->sessionFuserName);
                $shopFuser  = ShopFuser::find()->where(['id' => $fuserId])->one();
                //Поиск юзера
                if ($shopFuser)
                {
                    $this->_shopFuser = $shopFuser;
                }
            }

            if (!$this->_shopFuser)
            {
                $shopFuser = new ShopFuser();
                $shopFuser->save();

                \Yii::$app->getSession()->set($this->sessionFuserName, $shopFuser->id);
                $this->_shopFuser = $shopFuser;
            }
        } else
        {
            $this->_shopFuser = ShopFuser::find()->where(['user_id' => \Yii::$app->user->identity->id])->one();
            //Если у авторизовнного пользоывателя уже есть пользователь корзины
            if ($this->_shopFuser)
            {
                //Проверка сессии, а было ли чего то в корзине
                if (\Yii::$app->getSession()->offsetExists($this->sessionFuserName))
                {
                    $fuserId    = \Yii::$app->getSession()->get($this->sessionFuserName);
                    $shopFuser  = ShopFuser::find()->where(['id' => $fuserId])->one();

                    /**
                     * @var $shopFuser ShopFuser
                     */
                    if ($shopFuser)
                    {
                        $this->shopFuser->addBaskets($shopFuser->shopBaskets);
                    }

                    //Эти данные в сессии больше не нужны
                    \Yii::$app->getSession()->remove($this->sessionFuserName);
                }
            } else
            {
                //Проверка сессии, а было ли чего то в корзине
                if (\Yii::$app->getSession()->offsetExists($this->sessionFuserName))
                {
                    $fuserId    = \Yii::$app->getSession()->get($this->sessionFuserName);
                    $shopFuser  = ShopFuser::find()->where(['id' => $fuserId])->one();
                    //Поиск юзера
                    /**
                     * @var $shopFuser ShopFuser
                     */
                    if ($shopFuser)
                    {
                        $shopFuser->user_id = \Yii::$app->user->identity->id;
                        $shopFuser->save();
                    }

                    $this->_shopFuser = $shopFuser;
                    \Yii::$app->getSession()->remove($this->sessionFuserName);
                } else
                {
                    $shopFuser = new ShopFuser([
                        'user_id' => \Yii::$app->user->identity->id
                    ]);

                    $shopFuser->save();
                    $this->_shopFuser = $shopFuser;
                }
            }
        }

        return $this->_shopFuser;
    }

    public function setShopFuser($shopFuser)
    {
        throw new Exception('Не реализовано');
    }
}