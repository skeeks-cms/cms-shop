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
use skeeks\cms\models\CmsUser;
use skeeks\cms\shop\models\ShopCart;
use skeeks\cms\shop\models\ShopOrderStatus;
use skeeks\cms\shop\models\ShopPersonType;
use skeeks\cms\shop\models\ShopSupplier;
use skeeks\cms\shop\models\ShopTypePrice;
use skeeks\yii2\form\fields\BoolField;
use skeeks\yii2\form\fields\FieldSet;
use skeeks\yii2\form\fields\SelectField;
use skeeks\yii2\form\fields\TextareaField;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 *
 * @property ShopTypePrice    $baseTypePrice
 * @property ShopPersonType[] $shopPersonTypes
 * @property ShopTypePrice[]  $shopTypePrices
 * @property ShopTypePrice[]  $canBuyTypePrices
 *
 * @property ShopCart         $cart
 *
 * @property CmsContent       $shopContents
 *
 * @property array            $notifyEmails
 *
 * Class ShopComponent
 * @package skeeks\cms\shop\components
 */
class ShopComponent extends Component
{
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
     * Оплата заказов онлайн системами, только после проверки менеджером
     * @var string
     */
    public $payAfterConfirmation = Cms::BOOL_N;
    /**
     * @var Кого уведомить о новых товарах
     */
    public $notify_emails;
    /**
     * @var string
     */
    public $sessionFuserName = 'SKEEKS_CMS_SHOP';
    /**
     * @var ShopTypePrice
     */
    protected $_baseTypePrice;
    /**
     * @var array
     */
    protected $_shopTypePrices = [];
    /**
     * @var CartComponent
     */
    private $_cart = null;
    /**
     * @var ShopCart
     */
    private $_shopCart = null;

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
     * Показывать фильтры если есть подкатегории?
     * @var bool
     */
    public $is_show_filters_has_subtree = 1;

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
     * @var null Закупочная цена
     */
    public $type_price_purchase_id = null;

    /**
     * @var null Розничная цена
     */
    public $type_price_retail_id = null;

    /**
     * @var null Минимальная розничная
     */
    public $type_price_mrc_id = null;

    /**
     * @var array
     */
    public $offers_properties = [];

    /**
     * @var array
     */
    public $visible_shop_supplier_ids = [];


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
    /**
     * @return ShopCart
     * @deprecated
     */
    public function getShopFuser()
    {
        return $this->cart;
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
                    'payAfterConfirmation'  => [
                        'class'      => BoolField::class,
                        'trueValue'  => "Y",
                        'falseValue' => "N",
                    ],


                    'type_price_purchase_id'    => [
                        'class' => SelectField::class,
                        'items' => ArrayHelper::map(ShopTypePrice::find()->orderBy(['priority' => SORT_ASC])->all(), 'id', 'asText'),
                    ],
                    'type_price_retail_id'      => [
                        'class' => SelectField::class,
                        'items' => ArrayHelper::map(ShopTypePrice::find()->orderBy(['priority' => SORT_ASC])->all(), 'id', 'asText'),
                    ],
                    'type_price_mrc_id'         => [
                        'class' => SelectField::class,
                        'items' => ArrayHelper::map(ShopTypePrice::find()->orderBy(['priority' => SORT_ASC])->all(), 'id', 'asText'),
                    ],
                    'offers_properties'         => [
                        'class'    => SelectField::class,
                        'multiple' => true,
                        'items'    => ArrayHelper::map(
                            CmsContentProperty::find()->all(), 'code', 'asText'
                        ),
                    ],
                    'visible_shop_supplier_ids' => [
                        'class'    => SelectField::class,
                        'multiple' => true,
                        'items'    => ArrayHelper::map(
                            ShopSupplier::find()->all(), 'id', 'asText'
                        ),
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
                    'is_show_filters_has_subtree' => [
                        'class'       => BoolField::class,
                        'allowNull'   => false,
                        'formElement' => BoolField::ELEMENT_RADIO_LIST,
                    ],

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
            [['visible_shop_supplier_ids'], 'safe'],
            [['offers_properties'], 'safe'],
            [['show_filter_property_ids'], 'safe'],
            [['open_filter_property_ids'], 'safe'],
            [['email'], 'string'],
            [['payAfterConfirmation'], 'string'],
            [['start_order_status_id'], 'integer'],
            [['end_order_status_id'], 'integer'],

            [['type_price_purchase_id'], 'integer'],
            [['type_price_retail_id'], 'integer'],
            [['type_price_mrc_id'], 'integer'],

            ['notify_emails', 'string'],
            ['start_order_status_id', 'required'],
            ['end_order_status_id', 'required'],
            [
                [
                    'is_show_product_no_price',
                    'is_show_button_no_price',
                    'is_show_product_only_quantity',
                    'is_show_filters_has_subtree',
                    'is_show_quantity_product',
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
            'payAfterConfirmation'          => \Yii::t('skeeks/shop/app',
                'Include payment orders only after the manager approval'),
            'notify_emails'                 => \Yii::t('skeeks/shop/app', 'Email notification address'),
            'is_show_product_no_price'      => "Показывать товары с нулевыми ценами?",
            'is_show_button_no_price'       => "Показывать кнопку «добавить в корзину» для товаров с нулевыми ценами?",
            'is_show_product_only_quantity' => "Показывать товары только в наличии на сайте?",
            'show_filter_property_ids'      => "Какие фильтры разрешено показывать на сайте?",
            'open_filter_property_ids'      => "Какие фильтры по умолчанию открыты на сайте?",
            'is_show_filters_has_subtree'   => "Показывать фильтры если есть подкатегории?",
            'is_show_quantity_product'      => "Показывать оставшееся количество товаров на складе?",
            'type_price_purchase_id'        => "Закупочная цена",
            'type_price_retail_id'          => "Розничная цена",
            'type_price_mrc_id'             => "Минимальная розничная цена",
            'offers_properties'             => "Свойства предложений",
            'visible_shop_supplier_ids'     => "Отображать товары поставщиков",
        ]);
    }

    public function attributeHints()
    {
        return ArrayHelper::merge(parent::attributeHints(), [
            'start_order_status_id'         => "Статус, который присваивается заказу сразу после его оформления",
            'end_order_status_id'           => "Статус, который присваивается заказу после завершения работы с ним",
            'notify_emails'                 => \Yii::t('skeeks/shop/app',
                'Enter email addresses, separated by commas, they will come on new orders information'),
            'is_show_product_no_price'      => "Если выбрано «да», то товары с нулевой ценой будут показывать на сайте",
            'is_show_button_no_price'       => "Если у товара цена 0, и выбрано да, то кнопка «добавить в корзину», будет показываться рядом с товаром",
            'show_filter_property_ids'      => "Если не указано, то показываются все фильтры доступные в разделе. Если выбраны фильтры, то в разделе будут показаны только те фильтры по которым есть товары.",
            'is_show_filters_has_subtree'   => "Если каталог большой то лучше для производительности не показывать фильтры в категориях где есть подкатегории",
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
            if ($this->type_price_retail_id) {
                $typePrice = ShopTypePrice::find()->where(['id' => $this->type_price_retail_id])->limit(1)->one();
                if (!$typePrice) {
                    $typePrice = ShopTypePrice::find()->orderBy(["priority" => SORT_ASC])->limit(1)->one();
                }
                $this->_baseTypePrice = $typePrice;
            } else {
                $typePrice = ShopTypePrice::find()->orderBy(["priority" => SORT_ASC])->limit(1)->one();
                $this->_baseTypePrice = $typePrice;
            }
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
            $this->_shopTypePrices = ShopTypePrice::find()->orderBy(["priority" => SORT_ASC])->all();
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
     * @return array|null|ShopCart
     */
    public function getCart()
    {
        if (\Yii::$app instanceof \yii\console\Application) {
            return null;
        }

        if ($this->_shopCart instanceof ShopCart) {
            return $this->_shopCart;
        }

        if (isset(\Yii::$app->user) && \Yii::$app->user && \Yii::$app->user->isGuest) {
            //Если пользователь гость
            //Проверка сессии
            if (\Yii::$app->getSession()->offsetExists($this->sessionFuserName)) {
                $fuserId = \Yii::$app->getSession()->get($this->sessionFuserName);
                $shopCart = ShopCart::find()->where(['id' => $fuserId])->one();
                //Поиск юзера
                if ($shopCart) {
                    $this->_shopCart = $shopCart;
                }
            }

            if (!$this->_shopCart) {
                $shopCart = new ShopCart();
                //$shopCart->save();
                //\Yii::$app->getSession()->set($this->sessionFuserName, $shopCart->id);
                $this->_shopCart = $shopCart;
            }
        } else {
            //Если пользователь авторизован
            $this->_shopCart = ShopCart::find()->where(['cms_user_id' => \Yii::$app->user->identity->id])->one();
            //Если у авторизовнного пользоывателя уже есть пользователь корзины
            if ($this->_shopCart) {
                //Проверка сессии, а было ли чего то в корзине
                if (\Yii::$app->getSession()->offsetExists($this->sessionFuserName)) {
                    $fuserId = \Yii::$app->getSession()->get($this->sessionFuserName);
                    $shopCart = ShopCart::find()->where(['id' => $fuserId])->one();

                    /**
                     * @var $shopCart ShopCart
                     */
                    if ($shopCart) {
                        $this->_shopCart->shopOrder->addShopOrderItems($shopCart->shopOrder->shopOrderItems);
                        $shopCart->delete();
                    }

                    //Эти данные в сессии больше не нужны
                    \Yii::$app->getSession()->remove($this->sessionFuserName);
                }
            } else {
                //Проверка сессии, а было ли чего то в корзине
                if (\Yii::$app->getSession()->offsetExists($this->sessionFuserName)) {
                    $fuserId = \Yii::$app->getSession()->get($this->sessionFuserName);
                    $shopCart = ShopCart::find()->where(['id' => $fuserId])->one();
                    //Поиск юзера
                    /**
                     * @var $shopCart ShopCart
                     */
                    if ($shopCart) {
                        $shopCart->cms_user_id = \Yii::$app->user->identity->id;
                        $shopCart->save();
                    }

                    $this->_shopCart = $shopCart;
                    \Yii::$app->getSession()->remove($this->sessionFuserName);
                } else {
                    $shopCart = new ShopCart([
                        'cms_user_id' => \Yii::$app->user->identity->id,
                    ]);

                    $shopCart->save();
                    $this->_shopCart = $shopCart;
                }
            }
        }

        /**
         * Если у корзины нет заказа, нужно его создать
         */
        /*if (!$this->_shopCart->shop_order_id) {
            $shopOrder = new ShopOrder();
            $shopOrder->cms_site_id = \Yii::$app->cms->site->id;
            if (!$shopOrder->save()) {
                throw new UserException("Заказ-черновик не создан: ".print_r($shopOrder->errors, true));
            }
            $this->_shopCart->shop_order_id = $shopOrder->id;
            $this->_shopCart->save(false);
        }*/

        return $this->_shopCart;
    }

    /**
     * @param ShopCart $shopCart
     * @return $this
     */
    public function setCart(ShopCart $shopCart)
    {
        $this->_shopCart = $shopCart;
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

        /*if ($this->visible_shop_supplier_ids) {
            $activeQuery->andWhere([
                'or',
                ['shopProduct.shop_supplier_id' => null],
                ['in', 'shopProduct.shop_supplier_id', $this->visible_shop_supplier_ids],
            ]);
        } else {
            $activeQuery->andWhere(
                ['shopProduct.shop_supplier_id' => null]
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
     * Обновление данныех по товарам из главных товаров
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
                cce.parent_content_element_id is null
SQL
        )->execute();

        //Товары у которых есть дочерние - товарами с предложенями
        $result = \Yii::$app->db->createCommand(<<<SQL
            UPDATE 
                `shop_product` as sp 
                INNER JOIN
                (
                    /*Товары которые являются общими*/
                   SELECT cce.parent_content_element_id as inner_sp_id
                   FROM shop_product inner_sp
                       LEFT JOIN cms_content_element cce on cce.id = inner_sp.id 
                   WHERE cce.parent_content_element_id is not null
                   GROUP BY cce.parent_content_element_id
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
                       LEFT JOIN cms_content_element cce on cce.id = inner_sp.id 
                   WHERE cce.parent_content_element_id is not null
                   GROUP BY inner_sp.id
                ) sp_has_parent ON sp.id = sp_has_parent.inner_sp_id
            SET 
                sp.`product_type` = "offer"
SQL
        )->execute();

        return $this;
    }

    /**
     * @return $this
     * @throws \yii\db\Exception
     */
    public function updateAllQuantities()
    {
        //Обновление количества товаров у которых задан поставщик, информация берется со складов
        $result = \Yii::$app->db->createCommand(<<<SQL
            UPDATE 
                `shop_product` as sp 
                INNER JOIN
                (
                    /*Товары у которых задан поставщик и количество на их складах*/
                   SELECT inner_sp.id as inner_sp_id, SUM(ssp.quantity) as sum_quantity
                   FROM shop_product inner_sp
                       LEFT JOIN shop_store_product ssp on ssp.shop_product_id = inner_sp.id 
                       WHERE inner_sp.shop_supplier_id is not null
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
                   SELECT main_pid, SUM(quantity) as sum_quantity
                   FROM shop_product 
                   GROUP BY main_pid
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
                   SELECT cce.parent_content_element_id as inner_sp_id, SUM(inner_sp.quantity) as sum_quantity
                   FROM shop_product inner_sp
                       LEFT JOIN cms_content_element cce on cce.id = inner_sp.id 
                   WHERE cce.parent_content_element_id is not null
                   GROUP BY cce.parent_content_element_id
                ) sp_has_parent ON sp.id = sp_has_parent.inner_sp_id
            SET 
                sp.`quantity` = sp_has_parent.sum_quantity
        ")->execute();

        return $this;
    }
}