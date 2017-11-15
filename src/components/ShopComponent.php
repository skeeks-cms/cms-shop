<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 10.09.2015
 */

namespace skeeks\cms\shop\components;

use skeeks\cms\base\Component;
use skeeks\cms\components\Cms;
use skeeks\cms\controllers\AdminCmsContentElementController;
use skeeks\cms\helpers\UrlHelper;
use skeeks\cms\kladr\models\KladrLocation;
use skeeks\cms\models\CmsAgent;
use skeeks\cms\models\CmsContent;
use skeeks\cms\models\CmsContentElement;
use skeeks\cms\models\CmsUser;
use skeeks\cms\modules\admin\actions\modelEditor\AdminOneModelEditAction;
use skeeks\cms\modules\admin\controllers\AdminController;
use skeeks\cms\reviews2\actions\AdminOneModelMessagesAction;
use skeeks\cms\shop\actions\AdminContentElementShopAction;
use skeeks\cms\shop\models\ShopContent;
use skeeks\cms\shop\models\ShopFuser;
use skeeks\cms\shop\models\ShopPersonType;
use skeeks\cms\shop\models\ShopTypePrice;
use yii\base\Application;
use yii\base\Event;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;

/**
 * @property ShopTypePrice $baseTypePrice
 * @property ShopPersonType[] $shopPersonTypes
 * @property ShopTypePrice[] $shopTypePrices
 *
 * @property ShopFuser $shopFuser
 * @property ShopFuser $adminShopFuser
 *
 * @property CmsContent $shopContents
 * @property CmsContent $storeContent
 * @property CmsContent $stores
 *
 * @property array $notifyEmails
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
     * @var string Email отдела продаж
     */
    public $email = "";

    /**
     * Максимальное допустимое количество товаров
     * @var float
     */
    public $maxQuantity = 999999;

    /**
     * Минимально допустимое количество товаров
     * @var float
     */
    public $minQuantity = 0.01;


    /**
     * Оплата заказов онлайн системами, только после проверки менеджером
     * @var string
     */
    public $payAfterConfirmation = Cms::BOOL_N;


    /**
     * @var Контент который будет использоваться в качестве складов
     */
    public $storeCmsContentId;

    /**
     * @var Кого уведомить о новых товарах
     */
    public $notify_emails;


    /**
     * Можно задать название и описание компонента
     * @return array
     */
    static public function descriptorConfig()
    {
        return array_merge(parent::descriptorConfig(), [
            'name' => \Yii::t('skeeks/shop/app', 'Shop'),
        ]);
    }


    public function renderConfigForm(ActiveForm $form)
    {
        echo $form->fieldSet(\Yii::t('skeeks/shop/app', 'Main'));

        //echo $form->field($this, 'email')->textInput()->hint(\Yii::t('skeeks/shop/app', 'Email of sales department'));

        echo $form->field($this, 'notify_emails')->textarea(['rows' => 3]);

        echo $form->fieldRadioListBoolean($this, 'payAfterConfirmation');
        echo $form->field($this, 'storeCmsContentId')->listBox(array_merge(['' => ' - '],
            CmsContent::getDataForSelect()), ['size' => 1]);


        echo $form->fieldSetEnd();
    }

    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['email'], 'string'],
            [['payAfterConfirmation'], 'string'],
            [['storeCmsContentId'], 'integer'],
            ['notify_emails', 'string'],
        ]);
    }

    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'email' => 'Email',
            'payAfterConfirmation' => \Yii::t('skeeks/shop/app',
                'Include payment orders only after the manager approval'),
            'storeCmsContentId' => \Yii::t('skeeks/shop/app', 'Content storage'),
            'notify_emails' => \Yii::t('skeeks/shop/app', 'Email notification address'),
        ]);
    }

    public function attributeHints()
    {
        return ArrayHelper::merge(parent::attributeHints(), [
            'notify_emails' => \Yii::t('skeeks/shop/app',
                'Enter email addresses, separated by commas, they will come on new orders information'),
        ]);
    }


    /**
     * @var ShopTypePrice
     */
    protected $_baseTypePrice;
    /**
     * @var array
     */
    protected $_shopTypePrices = [];

    /**
     *
     * Тип цены по умолчанию
     *
     * @return ShopTypePrice
     */
    public function getBaseTypePrice()
    {
        if (!$this->_baseTypePrice) {
            $this->_baseTypePrice = ShopTypePrice::find()->def()->one();
        }

        return $this->_baseTypePrice;
    }


    /**
     * @return ShopPersonType[]
     */
    public function getShopPersonTypes()
    {
        return ShopPersonType::find()->active()->all();
    }

    /**
     * Все типы цен магазина
     * @return ShopTypePrice[]
     */
    public function getShopTypePrices()
    {
        if (!$this->_shopTypePrices) {
            $this->_shopTypePrices = ShopTypePrice::find()->all();
        }

        return $this->_shopTypePrices;
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
        if ($this->_shopFuser instanceof ShopFuser) {
            return $this->_shopFuser;
        }

        //Если пользователь гость
        if (isset(\Yii::$app->user) && \Yii::$app->user && \Yii::$app->user->isGuest) {
            //Проверка сессии
            if (\Yii::$app->getSession()->offsetExists($this->sessionFuserName)) {
                $fuserId = \Yii::$app->getSession()->get($this->sessionFuserName);
                $shopFuser = ShopFuser::find()->where(['id' => $fuserId])->one();
                //Поиск юзера
                if ($shopFuser) {
                    $this->_shopFuser = $shopFuser;
                }
            }

            if (!$this->_shopFuser) {
                $shopFuser = new ShopFuser();
                $shopFuser->save();

                \Yii::$app->getSession()->set($this->sessionFuserName, $shopFuser->id);
                $this->_shopFuser = $shopFuser;
            }
        } else {
            if (\Yii::$app instanceof \yii\console\Application) {
                return null;
            }

            $this->_shopFuser = ShopFuser::find()->where(['user_id' => \Yii::$app->user->identity->id])->one();
            //Если у авторизовнного пользоывателя уже есть пользователь корзины
            if ($this->_shopFuser) {
                //Проверка сессии, а было ли чего то в корзине
                if (\Yii::$app->getSession()->offsetExists($this->sessionFuserName)) {
                    $fuserId = \Yii::$app->getSession()->get($this->sessionFuserName);
                    $shopFuser = ShopFuser::find()->where(['id' => $fuserId])->one();

                    /**
                     * @var $shopFuser ShopFuser
                     */
                    if ($shopFuser) {
                        $this->_shopFuser->addBaskets($shopFuser->shopBaskets);
                        $shopFuser->delete();
                    }

                    //Эти данные в сессии больше не нужны
                    \Yii::$app->getSession()->remove($this->sessionFuserName);
                }
            } else {
                //Проверка сессии, а было ли чего то в корзине
                if (\Yii::$app->getSession()->offsetExists($this->sessionFuserName)) {
                    $fuserId = \Yii::$app->getSession()->get($this->sessionFuserName);
                    $shopFuser = ShopFuser::find()->where(['id' => $fuserId])->one();
                    //Поиск юзера
                    /**
                     * @var $shopFuser ShopFuser
                     */
                    if ($shopFuser) {
                        $shopFuser->user_id = \Yii::$app->user->identity->id;
                        $shopFuser->save();
                    }

                    $this->_shopFuser = $shopFuser;
                    \Yii::$app->getSession()->remove($this->sessionFuserName);
                } else {
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

    /**
     * @return $this
     */
    public function getShopContents()
    {
        $query = \skeeks\cms\models\CmsContent::find()->orderBy("priority ASC")->andWhere([
            'id' => \yii\helpers\ArrayHelper::map(\skeeks\cms\shop\models\ShopContent::find()->all(), 'content_id',
                'content_id')
        ]);

        $query->multiple = true;
        return $query->all();
    }

    /**
     * TODO: is @deprecated remove it!
     * @return array
     */
    public function getArrayForSelectElement()
    {

        if (!$data = CmsContent::getDataForSelect()) {
            return [];
        }

        $ids = ArrayHelper::map($this->shopContents, 'id', 'id');

        $result = [];
        foreach ($data as $typeKey => $type) {
            if ($type) {
                $contents = [];
                foreach ($type as $key => $value) {
                    if (in_array($key, $ids)) {
                        $contents[$key] = $value;
                    }
                }

                if ($contents) {
                    $result[$typeKey] = $contents;
                }
            }
        }

        return $result;
    }


    /**
     * @return CmsContent
     */
    public function getStoreContent()
    {
        if (!$contentId = (int)$this->storeCmsContentId) {
            return null;
        }

        return CmsContent::findOne($contentId);
    }

    /**
     * @return array
     */
    public function getStores()
    {
        if ($this->storeContent) {
            return $this->storeContent->getCmsContentElements()->all();
        }

        return [];
    }


    /**
     * @return array
     */
    public function getNotifyEmails()
    {
        $emailsAll = [];
        if ($this->notify_emails) {
            $emails = explode(",", $this->notify_emails);

            foreach ($emails as $email) {
                $emailsAll[] = trim($email);
            }
        }

        return $emailsAll;
    }

}