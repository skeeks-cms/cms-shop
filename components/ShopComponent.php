<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 10.09.2015
 */
namespace skeeks\cms\shop\components;
use skeeks\cms\base\Component;
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
use skeeks\cms\shop\models\ShopPersonType;
use skeeks\cms\shop\models\ShopTypePrice;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;

/**
 * @property CartComponent $cart
 * @property ShopTypePrice $baseTypePrice
 * @property ShopPersonType[] $shopPersonTypes
 *
 * @property ShopFuser $shopFuser
 *
 * Class ShopComponent
 * @package skeeks\cms\shop\components
 */
class ShopComponent extends Component
{
    /**
     * @var CartComponent
     */
    private $_cart = null;

    /**
     * Можно задать название и описание компонента
     * @return array
     */
    static public function descriptorConfig()
    {
        return array_merge(parent::descriptorConfig(), [
            'name'          => 'Магазин',
        ]);
    }

    public function init()
    {
        parent::init();

        \Yii::$app->on(AdminController::EVENT_INIT, function (AdminInitEvent $e) {

            if ($e->controller instanceof AdminCmsContentElementController || $e->controller instanceof \skeeks\cms\shop\controllers\AdminCmsContentElementController)
            {
                /**
                 * @var $model CmsContentElement
                 */
                $model = $e->controller->model;

                if ( ShopContent::find()->where(['content_id' => $model->content_id])->exists() )
                {
                    $e->controller->eventActions = ArrayHelper::merge($e->controller->eventActions, [
                        'shop' =>
                            [
                                'class'         => AdminContentElementShopAction::className(),
                                'name'          => 'Для магазина',
                                'priority'      => 1000,
                            ],
                    ]);
                }
            }
        });
    }


    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
        ]);
    }

    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
        ]);
    }


    /**
     *
     * Тип цены по умолчанию
     *
     * @return ShopTypePrice
     */
    public function getBaseTypePrice()
    {
        return ShopTypePrice::find()->def()->one();
    }


    /**
     * @return ShopPersonType[]
     */
    public function getShopPersonTypes()
    {
        return ShopPersonType::find()->active()->all();
    }




    /**
     * @var ShopFuser
     */
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
        if ($this->_shopFuser instanceof ShopFuser)
        {
            return $this->_shopFuser;
        }

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
     * @param ShopFuser $shopFuser
     * @return $this
     */
    public function setShopFuser(ShopFuser $shopFuser)
    {
        $this->_shopFuser = $shopFuser;
        return $this;
    }
}