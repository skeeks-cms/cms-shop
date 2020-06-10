<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */

namespace skeeks\cms\shop\components;

use skeeks\cms\backend\widgets\ActiveFormBackend;
use skeeks\cms\base\Component;
use skeeks\cms\components\Cms;
use skeeks\cms\models\CmsAgent;
use skeeks\cms\models\CmsContent;
use skeeks\cms\models\CmsContentProperty;
use skeeks\cms\models\CmsTree;
use skeeks\cms\models\CmsUser;
use skeeks\cms\shop\models\CmsSite;
use skeeks\cms\shop\models\ShopCmsContentProperty;
use skeeks\cms\shop\models\ShopOrderStatus;
use skeeks\cms\shop\models\ShopPersonType;
use skeeks\cms\shop\models\ShopTypePrice;
use skeeks\cms\shop\models\ShopUser;
use skeeks\yii2\form\fields\BoolField;
use skeeks\yii2\form\fields\FieldSet;
use skeeks\yii2\form\fields\SelectField;
use skeeks\yii2\form\fields\TextareaField;
use yii\base\Exception;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 *
 * @property ShopTypePrice        $baseTypePrice
 * @property ShopPersonType[]     $shopPersonTypes
 * @property ShopTypePrice[]      $shopTypePrices
 * @property ShopTypePrice[]      $canBuyTypePrices
 * @property CmsContentProperty[] $offerCmsContentProperties
 *
 * @property ShopUser             $shopUser
 *
 * @property CmsContent           $shopContents
 *
 * @property array                $notifyEmails
 *
 * Class ShopComponent
 * @package skeeks\cms\shop\components
 */
class ShopComponent extends Component
{
    const SESSION_SHOP_USER_NAME = 'SKEEKS_CMS_SHOP_USER';

    /**
     * @var string Email отдела продаж
     */
    public $email = "";

    /**
     * Начальный статус после создания заказа
     * @var string
     */
    public $start_order_status_id = "";
    /**
     * Конечный статус заказа
     * @var string
     */
    public $end_order_status_id = "";

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
     * @var Кого уведомить о новых товарах
     */
    public $notify_emails;
    /**
     * @var ShopTypePrice
     */
    protected $_baseTypePrice;
    /**
     * @var array
     */
    protected $_shopTypePrices = [];
    /**
     * @var ShopUser
     */
    private $_shopUser = null;

    /**
     * @var bool
     */
    public $is_show_product_no_price = 1;

    /**
     * @var bool
     */
    public $is_show_button_no_price = 1;

    /**
     * Какие фильтры показывать на сайте?
     * @var array
     */
    public $show_filter_property_ids = [];

    /**
     * @var array Фильтры отрктые по умолчанию
     */
    public $open_filter_property_ids = [];


    /**
     * Показывать товары только в наличии?
     * @var int
     */
    public $is_show_product_only_quantity = 1;

    /**
     * Показывать у товаров оставшееся количество на складе? Актуально для агрегаторов
     * @var int
     */
    public $is_show_quantity_product = 1;


    /**
     * @var array
     */
    public $offers_properties = [];

    /**
     * @var array
     */
    //public $visible_shop_supplier_ids = [];
    //public $is_show_products_has_main = false;


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


    public function getSessionFuserName()
    {
        return static::SESSION_SHOP_USER_NAME."_".\Yii::$app->skeeks->site->id;
    }

    /**
     * @return ActiveForm
     */
    public function beginConfigForm()
    {
        return ActiveFormBackend::begin();
    }

    public function getConfigFormFields()
    {
        return [
            'main' => [
                'class' => FieldSet::class,
                'name'  => \Yii::t('skeeks/shop/app', 'Main'),

                'fields' => [


                    'notify_emails'         => [
                        'class' => TextareaField::class,
                    ],
                    'start_order_status_id' => [
                        'class' => SelectField::class,
                        'items' => ArrayHelper::map(ShopOrderStatus::find()->all(), 'id', 'asText'),
                    ],
                    'end_order_status_id'   => [
                        'class' => SelectField::class,
                        'items' => ArrayHelper::map(ShopOrderStatus::find()->all(), 'id', 'asText'),
                    ],

                ],
            ],


            'catalog' => [
                'class' => FieldSet::class,
                'name'  => \Yii::t('skeeks/shop/app', 'Каталог'),

                'fields' => [

                    'is_show_product_no_price'      => [
                        'class'       => BoolField::class,
                        'allowNull'   => false,
                        'formElement' => BoolField::ELEMENT_RADIO_LIST,
                    ],
                    'is_show_product_only_quantity' => [
                        'class'       => BoolField::class,
                        'allowNull'   => false,
                        'formElement' => BoolField::ELEMENT_RADIO_LIST,
                    ],
                    'is_show_button_no_price'       => [
                        'class'       => BoolField::class,
                        'allowNull'   => false,
                        'formElement' => BoolField::ELEMENT_RADIO_LIST,
                    ],
                    'is_show_quantity_product'      => [
                        'class'       => BoolField::class,
                        'allowNull'   => false,
                        'formElement' => BoolField::ELEMENT_RADIO_LIST,
                    ],


                ],
            ],


            'filters' => [
                'class' => FieldSet::class,
                'name'  => \Yii::t('skeeks/shop/app', 'Фильтры'),

                'fields' => [
                 
                    'show_filter_property_ids' => [
                        'class'    => SelectField::class,
                        'multiple' => true,
                        'items'    => ArrayHelper::map(CmsContentProperty::find()->orderBy(['priority' => SORT_ASC])->all(), 'id', 'asText'),
                    ],

                    'open_filter_property_ids' => [
                        'class'    => SelectField::class,
                        'multiple' => true,
                        'items'    => ArrayHelper::map(CmsContentProperty::find()->orderBy(['priority' => SORT_ASC])->all(), 'id', 'asText'),
                    ],
                ],

            ],

        ];
    }


    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['show_filter_property_ids'], 'safe'],
            [['open_filter_property_ids'], 'safe'],
            [['email'], 'string'],
            [['start_order_status_id'], 'integer'],
            [['end_order_status_id'], 'integer'],

            ['notify_emails', 'string'],
            ['start_order_status_id', 'required'],
            ['end_order_status_id', 'required'],
            [
                [
                    'is_show_product_no_price',
                    'is_show_button_no_price',
                    'is_show_product_only_quantity',
                    'is_show_quantity_product',
                    //'is_show_products_has_main',
                ],
                'boolean',
            ],
        ]);
    }
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'start_order_status_id'         => 'Начальный статус заказа',
            'end_order_status_id'           => 'Конечный статус заказа',
            'email'                         => 'Email',
            'notify_emails'                 => \Yii::t('skeeks/shop/app', 'Email notification address'),
            'is_show_product_no_price'      => "Показывать товары с нулевыми ценами?",
            'is_show_button_no_price'       => "Показывать кнопку «добавить в корзину» для товаров с нулевыми ценами?",
            'is_show_product_only_quantity' => "Показывать товары только в наличии на сайте?",
            'show_filter_property_ids'      => "Какие фильтры разрешено показывать на сайте?",
            'open_filter_property_ids'      => "Какие фильтры по умолчанию открыты на сайте?",
            'is_show_quantity_product'      => "Показывать оставшееся количество товаров на складе?",
            //'is_show_products_has_main'     => "Отображать только товары которые привязаны к главным?",
        ]);
    }

    public function attributeHints()
    {
        return ArrayHelper::merge(parent::attributeHints(), [
            //'is_show_products_has_main'     => "Если выбрано да, то будут показываться только оформленные товары",
            'start_order_status_id'         => "Статус, который присваивается заказу сразу после его оформления",
            'end_order_status_id'           => "Статус, который присваивается заказу после завершения работы с ним",
            'notify_emails'                 => \Yii::t('skeeks/shop/app',
                'Enter email addresses, separated by commas, they will come on new orders information'),
            'is_show_product_no_price'      => "Если выбрано «да», то товары с нулевой ценой будут показывать на сайте",
            'is_show_button_no_price'       => "Если у товара цена 0, и выбрано да, то кнопка «добавить в корзину», будет показываться рядом с товаром",
            'show_filter_property_ids'      => "Если не указано, то показываются все фильтры доступные в разделе. Если выбраны фильтры, то в разделе будут показаны только те фильтры по которым есть товары.",
            'is_show_product_only_quantity' => "Если выбрано «да», то товары которых нет в наличии НЕ будут показываться на сайте.",
            'is_show_quantity_product'      => "Если выбрано «да», то на странице товара будет отображено количество товаров, указанное в админке. Если «нет», наличие отображаться не будет.",
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
        if (!$this->_baseTypePrice) {
            $this->_baseTypePrice = \Yii::$app->skeeks->site->getShopTypePrices()->andWhere(['is_default' => 1])->one();
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
            $this->_shopTypePrices = \Yii::$app->skeeks->site->getShopTypePrices()->orderBy(["priority" => SORT_ASC])->all();
        }

        return $this->_shopTypePrices;
    }

    /**
     * Типы цен по которым можно купить товар на сайте пользователю
     *
     * @param null|CmsUser $user
     * @return array
     */
    public function getCanBuyTypePrices($user = null)
    {
        $result = [];

        if (!$user) {
            $user = \Yii::$app->user->identity;
        }

        foreach ($this->shopTypePrices as $typePrice) {
            if (\Yii::$app->authManager->checkAccess($user ? $user->id : null, $typePrice->buyPermissionName)
                || $typePrice->isDefault
            ) {
                $result[$typePrice->id] = $typePrice;
            }
        }

        return $result;
    }


    /**
     * @return array|ShopUser|\yii\db\ActiveRecord|null
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function getShopUser()
    {
        if (\Yii::$app instanceof \yii\console\Application) {
            return null;
        }

        if ($this->_shopUser instanceof ShopUser) {
            return $this->_shopUser;
        }

        //Если пользователь гость
        if (isset(\Yii::$app->user) && \Yii::$app->user && \Yii::$app->user->isGuest) {
            //Проверка сессии
            if (\Yii::$app->getSession()->offsetExists($this->sessionFuserName)) {
                $fuserId = \Yii::$app->getSession()->get($this->sessionFuserName);
                $shopCart = ShopUser::find()->where(['id' => $fuserId])->one();
                //Поиск юзера
                if ($shopCart) {
                    $this->_shopUser = $shopCart;
                }
            }

            if (!$this->_shopUser) {
                $shopCart = new ShopUser();
                //$shopCart->save();
                //\Yii::$app->getSession()->set($this->sessionFuserName, $shopCart->id);
                $this->_shopUser = $shopCart;
            }
        } else {
            //Если пользователь авторизован
            $this->_shopUser = ShopUser::find()
                ->where(['cms_user_id' => \Yii::$app->user->identity->id])
                ->andWhere(['cms_site_id' => \Yii::$app->skeeks->site->id])
                ->one();

            //Если у авторизовнного пользоывателя уже есть пользователь корзины
            if ($this->_shopUser) {
                //Проверка сессии, а было ли чего то в корзине
                if (\Yii::$app->getSession()->offsetExists($this->sessionFuserName)) {
                    $fuserId = \Yii::$app->getSession()->get($this->sessionFuserName);
                    $shopCart = ShopUser::find()->where(['id' => $fuserId])->one();

                    /**
                     * @var $shopCart ShopUser
                     */
                    if ($shopCart) {
                        $this->_shopUser->shopOrder->addShopOrderItems($shopCart->shopOrder->shopOrderItems);
                        $shopCart->delete();
                    }

                    //Эти данные в сессии больше не нужны
                    \Yii::$app->getSession()->remove($this->sessionFuserName);
                }
            } else {
                //Проверка сессии, а было ли чего то в корзине
                if (\Yii::$app->getSession()->offsetExists($this->sessionFuserName)) {
                    $fuserId = \Yii::$app->getSession()->get($this->sessionFuserName);
                    $shopCart = ShopUser::find()->where(['id' => $fuserId])->one();
                    //Поиск юзера
                    /**
                     * @var $shopCart ShopUser
                     */
                    if ($shopCart) {
                        $shopCart->cms_user_id = \Yii::$app->user->identity->id;
                        $shopCart->save();
                    }

                    $this->_shopUser = $shopCart;
                    \Yii::$app->getSession()->remove($this->sessionFuserName);
                } else {
                    $shopCart = new ShopUser([
                        'cms_user_id' => \Yii::$app->user->identity->id,
                    ]);

                    if (!$shopCart->save()) {
                        throw new Exception(print_r($shopCart->errors, true));
                    }

                    $this->_shopUser = $shopCart;
                }
            }
        }

        return $this->_shopUser;
    }

    /**
     * @param ShopUser $shopCart
     * @return $this
     */
    public function setCart(ShopUser $shopCart)
    {
        $this->_shopUser = $shopCart;
        return $this;
    }

    /**
     * @return $this
     */
    public function getShopContents()
    {
        $query = \skeeks\cms\models\CmsContent::find()->orderBy("priority ASC")->andWhere([
            'id' => \yii\helpers\ArrayHelper::map(\skeeks\cms\shop\models\ShopContent::find()->all(), 'content_id', 'content_id'),
        ]);

        $query->multiple = true;
        return $query->all();
    }

    /**
     * TODO: is @return array
     * @deprecated remove it!
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


    /**
     *
     * Фильтрация базового запроса на выборку товаров с учетом настроек магазина.
     *
     * @param ActiveQuery $activeQuery
     * @return $this
     */
    public function filterBaseContentElementQuery(ActiveQuery $activeQuery)
    {
        $activeQuery->joinWith("shopProduct");

        $activeQuery->andWhere([
            '!=',
            'shopProduct.product_type',
            \skeeks\cms\shop\models\ShopProduct::TYPE_OFFER,
        ]);

        /*if ($this->is_show_products_has_main) {
            $activeQuery->andWhere(
                ['is not', 'shopProduct.main_pid', null]
            );
        }*/

        return $this;
    }


    /**
     *
     * Фильтрация базового запроса на выборку товаров с учетом настроек магазина.
     *
     * @param ActiveQuery $activeQuery
     * @return $this
     */
    public function filterByPriceContentElementQuery(ActiveQuery $activeQuery)
    {
        if (!$this->is_show_product_no_price) {
            $activeQuery->joinWith('shopProduct.shopProductPrices as pricesFilter');
            $activeQuery->andWhere(['>', '`pricesFilter`.price', 0]);
        }

        return $this;
    }


    /**
     * Товарные данные обновляются из главных товаров
     * Габариты, вес, соответствие величин
     *
     * @return $this
     * @throws \yii\db\Exception
     */
    public function updateAllSubproducts()
    {
        $result = \Yii::$app->db->createCommand(<<<SQL
            UPDATE 
                `shop_product` as sp 
                INNER JOIN (
                    /*Товары у которых задан главный товар*/
                    SELECT 
                        inner_sp.id as inner_sp_id 
                    FROM 
                        shop_product inner_sp 
                    WHERE 
                        inner_sp.main_pid is not null
                ) sp_has_main_pid ON sp_has_main_pid.inner_sp_id = sp.id 
                LEFT JOIN shop_product as sp_main on sp_main.id = sp.main_pid 
            SET 
                sp.`measure_ratio` = sp_main.measure_ratio, 
                sp.`measure_matches_jsondata` = sp_main.measure_matches_jsondata, 
                sp.`measure_code` = sp_main.measure_code, 
                sp.`width` = sp_main.width, 
                sp.`length` = sp_main.length, 
                sp.`height` = sp_main.height, 
                sp.`weight` = sp_main.weight
SQL
        )->execute();

        return $this;
    }


    /**
     *
     * Товары у которых не задан родительский элемент делает простыми
     * Товары у которых есть дочерние делает товарами с предложенями
     * Обновляет раздел для товаров предложений. Раздел должнен совпадать с родительским, общим товаром
     * Товары у которых задан общий делает товарами-предложениями
     *
     * @return $this
     * @throws \yii\db\Exception
     */
    public function updateAllTypes()
    {
        //Товары у которых не задан родительский элемент делаем простыми
        $result = \Yii::$app->db->createCommand(<<<SQL
            UPDATE 
                `shop_product` as sp 
                LEFT JOIN cms_content_element cce on cce.id = sp.id 
            SET 
                sp.`product_type` = "simple"
            WHERE 
                sp.offers_pid is null
SQL
        )->execute();

        //Товары у которых есть дочерние - товарами с предложенями
        $result = \Yii::$app->db->createCommand(<<<SQL
            UPDATE 
                `shop_product` as sp 
                INNER JOIN
                (
                    /*Товары которые являются общими*/
                   SELECT inner_sp.offers_pid as inner_sp_id
                   FROM shop_product inner_sp
                   WHERE inner_sp.offers_pid is not null
                   GROUP BY inner_sp.offers_pid
                ) sp_has_parent ON sp.id = sp_has_parent.inner_sp_id
            SET 
                sp.`product_type` = "offers"
SQL
        )->execute();

        //Товар-предложение
        $result = \Yii::$app->db->createCommand(<<<SQL
            UPDATE 
                `shop_product` as sp 
                INNER JOIN
                (
                    /*Товары которые являются предложениями */
                   SELECT inner_sp.id as inner_sp_id
                   FROM shop_product inner_sp
                   WHERE inner_sp.offers_pid is not null
                   GROUP BY inner_sp.id
                ) sp_has_parent ON sp.id = sp_has_parent.inner_sp_id
            SET 
                sp.`product_type` = "offer"
SQL
        )->execute();



        //У товаров предложений раздел должнен совпадать с родительским
        $result = \Yii::$app->db->createCommand(<<<SQL
            UPDATE 
                `cms_content_element` as cce 
                INNER JOIN
                (
                    /*Товары которые являются предложениями */
                   SELECT 
                       inner_sp.id as inner_sp_id,
                        inner_sp.offers_pid,
                        inner_sp.product_type
                   FROM shop_product inner_sp
                   WHERE inner_sp.offers_pid is not null
                   GROUP BY inner_sp.id
                ) sp_has_parent ON cce.id = sp_has_parent.inner_sp_id
                LEFT JOIN shop_product as offers_sp on offers_sp.id = sp_has_parent.offers_pid
                LEFT JOIN cms_content_element as offers_cce on offers_cce.id = offers_sp.id
            SET 
                cce.`tree_id` = offers_cce.tree_id
SQL
        )->execute();

        return $this;
    }

    /**
     * Обновление цен у общих товаров с пердложениями
     *
     * @return $this
     * @throws \yii\db\Exception
     */
    public function updateOffersPrice()
    {
        /**
         * Вставка недостающих цен для общих товаров и их обновление
         */
        \Yii::$app->db->createCommand(<<<SQL
/*Создание недостающих цен у товаров с пердложениями*/
INSERT IGNORE
    INTO shop_product_price (`created_at`,`updated_at`,`product_id`, `type_price_id`, `price`, `currency_code`)
select
	/*sp.offers_pid,
    sp_price.type_price_id,
    min(sp_price.price) as min_price,
    sp_price.**/

    UNIX_TIMESTAMP(),
    UNIX_TIMESTAMP(),
    sp.offers_pid,
    sp_price.type_price_id,
    min(sp_price.price) as min_price,
    sp_price.currency_code
from
	shop_product_price as sp_price
	LEFT JOIN shop_product as sp on sp.id = sp_price.product_id
	LEFT JOIN shop_product as sp_with_offers on sp_with_offers.id = sp.offers_pid
WHERE
	sp.offers_pid is not null
GROUP BY
	sp.offers_pid,
	sp_price.type_price_id
ORDER BY `sp`.`offers_pid`  DESC
SQL
        )->execute();

        \Yii::$app->db->createCommand(<<<SQL
/*Обновление цены от у товаров с предложениями*/
UPDATE
	`shop_product_price` as price
	INNER JOIN (
		select

			/*sp.offers_pid,
						    sp_price.type_price_id,
						    min(sp_price.price) as min_price,
						    sp_price.**/
			sp_with_offers_price.id as price_id,
			sp.offers_pid as offers_pid,
			sp_price.type_price_id,
			min(sp_price.price) as min_price,
			sp_price.currency_code
		from
			shop_product_price as sp_price
			LEFT JOIN shop_product as sp on sp.id = sp_price.product_id
			LEFT JOIN shop_product as sp_with_offers on sp_with_offers.id = sp.offers_pid
			LEFT JOIN shop_product_price as sp_with_offers_price on sp_with_offers_price.product_id = sp_with_offers.id
		WHERE
			sp.offers_pid is not null
			AND sp_with_offers_price.id is not null
		GROUP BY
			sp.offers_pid,
			sp_price.type_price_id
		ORDER BY
			`sp`.`offers_pid` DESC
	) sp_with_offers_price ON sp_with_offers_price.price_id = price.id
SET
	price.`price` = sp_with_offers_price.min_price,
	price.`currency_code` = sp_with_offers_price.currency_code

SQL
        )->execute();

        return $this;
    }


    /**
     * Обновляет наличие у товаров поставщиков у которых есть склады.
     * Обновляет количество у главных товаров, к которым привязаны товары поставщиков.
     * Обновляет количество у общих товаров (складывает предложения)
     *
     * @return $this
     * @throws \yii\db\Exception
     */
    public function updateAllQuantities()
    {

        //Обновляет количество у товаров у которых есть склады, и они являются поставщиками
        $result = \Yii::$app->db->createCommand(<<<SQL
            UPDATE 
                `shop_product` as sp 
                INNER JOIN
                (
                    /*Товары у которых задан поставщик и количество на их складах*/
                   SELECT inner_sp.id as inner_sp_id, SUM(ssp.quantity) as sum_quantity
                   FROM shop_product inner_sp
                       LEFT JOIN shop_store_product ssp on ssp.shop_product_id = inner_sp.id 
                       LEFT JOIN cms_content_element cce on inner_sp.id = cce.id 
                       LEFT JOIN cms_site as site on site.id = cce.cms_site_id 
                       LEFT JOIN shop_site as shopSite on site.id = shopSite.id 
                       WHERE shopSite.is_supplier = 1
                   GROUP BY inner_sp.id
                ) sp_has_supplier ON sp.id = sp_has_supplier.inner_sp_id
            SET 
                sp.`quantity` = if(sp_has_supplier.sum_quantity is null, 0, sp_has_supplier.sum_quantity)
SQL
        )->execute();


        //Обновление количества у главных товаров, к которым привязаны товары поставщиков
        \Yii::$app->db->createCommand("
            UPDATE 
                `shop_product` as sp 
                INNER JOIN
                (
                   SELECT inner_sp.main_pid, SUM(inner_sp.quantity) as sum_quantity
                   FROM shop_product as inner_sp
                   LEFT JOIN cms_content_element as inner_cce ON inner_cce.id = inner_sp.id
                   LEFT JOIN cms_site as inner_cms_site_id ON inner_cms_site_id.id = inner_cce.cms_site_id
                   LEFT JOIN shop_site as inner_shop_site ON inner_shop_site.id = inner_cms_site_id.id
                   WHERE inner_shop_site.is_supplier = 1
                   GROUP BY inner_sp.main_pid
                ) sp_has_main ON sp.id = sp_has_main.main_pid
            SET 
                sp.`quantity` = sp_has_main.sum_quantity
            WHERE 
                sp_has_main.main_pid is not null
        ")->execute();


        //Обновления количества у общих товаров
        \Yii::$app->db->createCommand("
            UPDATE 
                `shop_product` as sp 
                INNER JOIN
                (
                    /*Товары у которых задан общий товар*/
                   SELECT inner_sp.offers_pid as inner_sp_id, SUM(inner_sp.quantity) as sum_quantity
                   FROM shop_product inner_sp
                   WHERE inner_sp.offers_pid is not null
                   GROUP BY inner_sp.offers_pid
                ) sp_has_parent ON sp.id = sp_has_parent.inner_sp_id
            SET 
                sp.`quantity` = sp_has_parent.sum_quantity
        ")->execute();

        return $this;
    }


    /**
     * @param CmsSite|null $cmsSite
     */
    static public function importNewProductsOnSite(CmsSite $cmsSite = null)
    {
        ini_set("memory_limit", "1024M");

        if ($cmsSite === null) {
            $cmsSite = \Yii::$app->skeeks->site;
        }

        if (!$cmsSite->shopSite) {
            throw new Exception("Сайт не настроен зайдите в основные настройким магазина и заполните недостающие настройки магазина");
        }

        if (!$cmsSite->shopSite->catalogCmsTree) {
            throw new Exception("В основных настройках сайта укажите каталог для товаров");
        }

        //1) Создаем необходимые категории на сайте
        $data = \Yii::$app->db->createCommand(<<<SQL
        SELECT 
            * 
        FROM 
            (
                /*Выбор раздеов из товаров с предложениями*/
                
                SELECT 
                    offers_tree.* 
                FROM 
                    cms_content_element as ce 
                    LEFT JOIN shop_product as sp ON sp.id = ce.id 
                    LEFT JOIN shop_product as sp_main ON sp_main.id = sp.main_pid 
                    LEFT JOIN shop_product as sp_main_with_offers ON sp_main_with_offers.id = sp_main.offers_pid 
                    LEFT JOIN cms_content_element as ce_main_with_offers ON ce_main_with_offers.id = sp_main_with_offers.id 
                    LEFT JOIN cms_tree as offers_tree ON offers_tree.id = ce_main_with_offers.tree_id 
                WHERE 
                    
                    /*Импорт только элементов заданных в настройках сайта*/
                    ce.cms_site_id in (
                        SELECT 
                            shop_import_cms_site.sender_cms_site_id 
                        FROM 
                            shop_import_cms_site 
                        WHERE 
                            shop_import_cms_site.cms_site_id = {$cmsSite->id}
                    ) 
                    /*Только товары которые привязаны к моделям*/
                    AND sp.main_pid is not null 
                    AND offers_tree.id is not null 
                    AND sp_main_with_offers.product_type = "offers"
                GROUP BY 
                    offers_tree.id 
                UNION ALL 
                
                /*Выбор раздело из товаров*/
                
                SELECT 
                    tree.* 
                FROM 
                    cms_content_element as ce 
                    LEFT JOIN shop_product as sp ON sp.id = ce.id 
                    LEFT JOIN shop_product as sp_main ON sp_main.id = sp.main_pid 
                    LEFT JOIN cms_content_element as ce_main ON sp_main.id = ce_main.id 
                    LEFT JOIN cms_tree as tree ON tree.id = ce_main.tree_id 
                WHERE 
                    
                    /*Импорт только элементов заданных в настройках сайта*/
                    ce.cms_site_id in (
                        SELECT 
                            shop_import_cms_site.sender_cms_site_id 
                        FROM 
                            shop_import_cms_site 
                        WHERE 
                            shop_import_cms_site.cms_site_id = {$cmsSite->id}
                    ) 
                    /*Только товары которые привязаны к моделям*/
                    AND sp.main_pid is not null 
                    AND tree.id is not null 
                    AND sp_main.product_type = 'simple'
                GROUP BY 
                    tree.id
            ) as all_tree 
        GROUP BY 
            all_tree.id
SQL
        )->queryAll();

        if ($data) {
            foreach ($data as $row) {
                $source = CmsTree::find()->where(['id' => $row['id']])->one();
                $parent = $cmsSite->shopSite->catalogCmsTree;

                if (!CmsTree::find()
                    ->andWhere(['cms_site_id' => $cmsSite->id])
                    ->andWhere(['main_cms_tree_id' => $source->id])
                    ->exists()) {
                    $tree = new CmsTree();
                    $tree->name = $source->name;
                    $tree->main_cms_tree_id = $source->id;

                    if (!$tree->appendTo($parent)->save()) {
                        throw new Exception("Раздел не создан: ".print_r($tree->errors, true));
                    }
                }

            }
        }

        /**
         * 2 вставка товаров
         */

        $sqlFile = \Yii::getAlias('@skeeks/cms/shop/sql/insert-new-products.sql');
        $sql = file_get_contents($sqlFile);
        $sql = str_replace("{site_id}", $cmsSite->id, $sql);
        $sql = str_replace("{limit}", 5000, $sql);

        \Yii::$app->db->createCommand($sql)->execute();

        \Yii::$app->shop->updateOffersPrice();


    }


    /**
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getOfferCmsContentProperties()
    {
        return ShopCmsContentProperty::findCmsContentProperties()->all();
    }


    /**
     * @return ShopUser
     * @deprecated
     */
    public function getCart()
    {
        return $this->shopUser;
    }

    /**
     * @return ShopUser
     * @deprecated
     */
    public function getShopFuser()
    {
        return $this->shopUser;
    }
}