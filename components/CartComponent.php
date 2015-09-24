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
use skeeks\cms\shop\models\ShopBasket;
use skeeks\cms\shop\models\ShopContent;
use skeeks\cms\shop\models\ShopFuser;
use skeeks\cms\shop\models\ShopTypePrice;
use skeeks\modules\cms\money\Money;
use yii\base\Arrayable;
use yii\base\ArrayableTrait;
use yii\base\Exception;
use yii\helpers\ArrayHelper;

/**
 * @property int $countShopBaskets
 * @property ShopBasket[] $shopBaskets
 * @property ShopFuser $shopFuser
 *
 * @property Money $money
 * @property Money $moneyNoDiscount
 * @property Money $moneyDiscount
 * @property Money $moneyDelivery
 *
 * Class CartComponent
 * @package skeeks\cms\shop\components
 */
class CartComponent extends \yii\base\Component implements Arrayable
{
    use ArrayableTrait;

    private $_shopFuser = null;

    /**
     * @var string
     */
    public $sessionFuserName = 'SKEEKS_CMS_SHOP';

    public function extraFields()
    {
        return [
            'countShopBaskets',
            'shopBaskets',
            'shopFuser',
        ];
    }

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
                        $this->_shopFuser->addBaskets($shopFuser->shopBaskets);
                        $shopFuser->delete();
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

    /**
     * TODO: реализовать
     * @param $shopFuser
     * @throws Exception
     */
    public function setShopFuser($shopFuser)
    {
        throw new Exception('Не реализовано');
    }


    /**
     * Позиции корзины
     *
     * @return \skeeks\cms\shop\models\ShopBasket[]
     */
    public function getShopBaskets()
    {
        return $this->shopFuser->shopBaskets;
    }

    /**
     * Количество позиций в корзине
     *
     * @return int
     */
    public function getCountShopBaskets()
    {
        return count($this->shopBaskets);
    }

    /**
     *
     * Итоговая стоимость корзины с учетом скидок, то что будет платить человек
     *
     * @return Money
     */
    public function getMoney()
    {
        $money = \Yii::$app->money->newMoney();

        foreach ($this->shopBaskets as $shopBasket)
        {
            $money = $money->add($shopBasket->money);
        }

        return $money;
    }

    /**
     *
     * Итоговая стоимость корзины, без учета скидок
     *
     * @return Money
     */
    public function getMoneyNoDiscount()
    {
        $money = \Yii::$app->money->newMoney();

        foreach ($this->shopBaskets as $shopBasket)
        {
            $money = $money->add($shopBasket->moneyNoDiscount);
        }

        return $money;
    }

    /**
     *
     * Итоговая скидка по всей корзине
     *
     * @return Money
     */
    public function getMoneyDiscount()
    {
        $money = \Yii::$app->money->newMoney();
        return $money;
    }

    /**
     *
     * Итоговая скидка по всей корзине
     *
     * @return Money
     */
    public function getMoneyDelivery()
    {
        $money = \Yii::$app->money->newMoney();
        return $money;
    }





    /**
     * @return bool
     */
    public function isEmpty()
    {
        return (bool) $this->countShopBaskets == 0;
    }
}