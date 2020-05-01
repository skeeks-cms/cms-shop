<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 14.09.2015
 */

namespace skeeks\cms\shop\models;

use skeeks\cms\components\Cms;
use skeeks\cms\measure\models\CmsMeasure;
use skeeks\cms\models\behaviors\HasJsonFieldsBehavior;
use skeeks\cms\models\CmsContentElement;
use skeeks\modules\cms\money\models\Currency;
use yii\base\Exception;
use yii\db\AfterSaveEvent;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * This is the model class for table "{{%shop_product}}".
 *
 * @property integer                     $id
 * @property integer                     $created_by
 * @property integer                     $updated_by
 * @property integer                     $created_at
 * @property integer                     $updated_at
 * @property double                      $quantity
 * @property double                      $weight
 * @property double                      $measure_ratio
 * @property integer                     $vat_id
 * @property string                      $vat_included
 * @property double                      $quantity_reserved
 * @property string                      $measure_code
 * @property double                      $width
 * @property double                      $length
 * @property double                      $height
 *
 * @property integer|null                $main_pid
 * @property integer|null                $offers_pid
 *
 * @property string                      $product_type
 * @property array|null                  $supplier_external_jsondata
 * @property array|null                  $measure_matches_jsondata
 *
 * @property string                      $productTypeAsText
 * @property CmsMeasure                  $measure
 * @property ShopCmsContentElement       $cmsContentElement
 * @property ShopTypePrice               $trialPrice
 * @property ShopVat                     $vat
 * @property Currency                    $purchasingCurrency
 * @property ShopProductPrice[]          $shopProductPrices
 * @property ShopViewedProduct[]         $shopViewedProducts
 *
 * @property string                      $baseProductPriceValue
 * @property string                      $baseProductPriceCurrency
 *
 * @property ShopProductPrice            $baseProductPrice
 * @property ShopProductPrice            $minProductPrice
 * @property ShopProductPrice[]          $viewProductPrices
 * @property ShopProductQuantityChange[] $shopProductQuantityChanges
 * @property ShopQuantityNoticeEmail[]   $shopQuantityNoticeEmails
 * @property ShopStoreProduct[]          $shopStoreProducts
 *
 * @property ShopCmsContentElement[]     $tradeOffers
 * @property ShopOrderItem[]             $shopOrderItems
 * @property ShopOrder[]                 $shopOrders
 * @property ShopSupplier                $shopSupplier
 * @property ShopTypePrice               $shopTypePrices
 *
 *
 * @property ShopProduct                 $shopMainProduct
 * @property ShopProduct[]               $shopAttachedProducts
 * @property ShopProduct[]               $shopSupplierProducts
 * @property ShopProduct[]               $shopSellerProducts
 *
 * @property ShopProduct                 $shopProductWhithOffers Товар с предложениями для текущего товара
 * @property ShopProduct[]               $shopProductOffers Предложения для текущего товара
 *
 *
 * @property boolean                     $isSubProduct
 *
 * @property boolean                     $isSimpleProduct
 * @property boolean                     $isOfferProduct
 * @property boolean                     $isOffersProduct
 * @property array                       $measureMatches
 *
 * @property ShopFavoriteProduct[]       $shopFavoriteProducts
 */
class ShopProduct extends \skeeks\cms\models\Core
{
    const TYPE_SIMPLE = 'simple';
    const TYPE_OFFERS = 'offers';
    const TYPE_OFFER = 'offer';
    static public $instances = [];
    private $_baseProductPriceValue = null;
    private $_baseProductPriceCurrency = null;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%shop_product}}';
    }

    public function behaviors()
    {
        $behaviors = ArrayHelper::merge(parent::behaviors(), [
            HasJsonFieldsBehavior::class => [
                'class'  => HasJsonFieldsBehavior::class,
                'fields' => [
                    'supplier_external_jsondata',
                    /*'measure_matches_jsondata',*/
                ],
            ],
        ]);

        return $behaviors;
    }

    public function getProductTypeAsText()
    {
        return ArrayHelper::getValue(self::possibleProductTypes(), $this->product_type);
    }
    /**
     * @return array
     */
    static public function possibleProductTypes()
    {
        return [
            static::TYPE_SIMPLE => \Yii::t('skeeks/shop/app', 'Plain'),
            static::TYPE_OFFERS => \Yii::t('skeeks/shop/app', 'With quotations'),
            static::TYPE_OFFER  => \Yii::t('skeeks/shop/app', 'Товар-предложение'),
        ];
    }
    /**
     * @param CmsContentElement $cmsContentElement
     * @return static
     */
    static public function getInstanceByContentElement(CmsContentElement $cmsContentElement)
    {
        if ($self = ArrayHelper::getValue(static::$instances, $cmsContentElement->id)) {
            return $self;
        }

        /**
         * @version SkeekS CMS > 2.6.0
         */
        if ($cmsContentElement instanceof \skeeks\cms\shop\models\ShopCmsContentElement) {
            static::$instances[$cmsContentElement->id] = $cmsContentElement->shopProduct;
            return $cmsContentElement->shopProduct;
        }

        if (!$self = static::find()->where(['id' => $cmsContentElement->id])->one()) {
            $self = new static([
                'id' => $cmsContentElement->id,
            ]);

            $self->save();
            $self->refresh();
        }

        static::$instances[$cmsContentElement->id] = $self;

        return $self;
    }
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->on(self::EVENT_BEFORE_INSERT, [$this, "_beforeSaveEvent"]);
        $this->on(self::EVENT_BEFORE_UPDATE, [$this, "_beforeSaveEvent"]);

        $this->on(self::EVENT_AFTER_INSERT, [$this, "_afterSaveEvent"]);
        $this->on(self::EVENT_AFTER_UPDATE, [$this, "_afterSaveEvent"]);

        $this->on(self::EVENT_AFTER_INSERT, [$this, "_updateParentAfterInsert"]);

        $this->on(self::EVENT_AFTER_INSERT, [$this, "_logQuantityInsert"]);
        $this->on(self::EVENT_BEFORE_UPDATE, [$this, "_logQuantityUpdate"]);
    }
    public function _logQuantityInsert($event)
    {
        $log = new ShopProductQuantityChange();

        $log->shop_product_id = $this->id;
        $log->quantity = $this->quantity;
        $log->quantity_reserved = $this->quantity_reserved;
        $log->measure_code = $this->measure_code;
        $log->measure_ratio = $this->measure_ratio;

        $log->save();
    }

    public function _logQuantityUpdate($event)
    {
        if (
            ($this->isAttributeChanged('quantity', false)
                || $this->isAttributeChanged('quantity_reserved', false)
                || $this->isAttributeChanged('measure_code', false)
                || $this->isAttributeChanged('measure_ratio', false))
            ||
            !$this->shopProductQuantityChanges
        ) {
            $log = new ShopProductQuantityChange();

            $log->shop_product_id = $this->id;
            $log->quantity = $this->quantity;
            $log->quantity_reserved = $this->quantity_reserved;
            $log->measure_code = $this->measure_code;
            $log->measure_ratio = $this->measure_ratio;

            $log->save();
        }
    }
    /**
     * Перед сохранением модели, всегда следим за типом товара
     * @param $event
     */
    public function _beforeSaveEvent($event)
    {
        //Проверка измененного типа
        if ($this->isAttributeChanged('product_type')) {
            //Выставили что у него есть предложения
            if ($this->product_type == self::TYPE_OFFER) {
                $offersProduct = $this->shopProductWhithOffers;
                //Если товар к которому привязываем текущия является предложением, то этого делать нельзя!
                if ($offersProduct->isOfferProduct) {
                    throw new Exception("К товару предложению, нельзя привязать другое предложение");
                }
                //Если товар к которому привязываем, является простым, то ему можно изменить тип
                if (!$offersProduct->isOffersProduct) {
                    $offersProduct->product_type = self::TYPE_OFFERS;
                    $offersProduct->update(false, ['product_type']);
                }
            }
        }
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTradeOffers()
    {
        if ($this->isNewRecord) {
            return [];
        }

        $q = ShopCmsContentElement::find()
            ->joinWith("shopProduct as shopProduct")
            ->where(['shopProduct.offers_pid' => $this->id]);
        $q->multiple = true;

        return $q;

        /*$childContentId = null;
        if ($this->cmsContentElement && $this->cmsContentElement->shopContent) {
            $childContentId = $this->cmsContentElement->shopContent->children_content_id;
        }

        return $this
            ->hasMany(ShopCmsContentElement::class, ['parent_content_element_id' => 'id'])
            ->andWhere([ShopCmsContentElement::tableName().".content_id" => $childContentId])
            ->orderBy(['priority' => SORT_ASC]);*/
    }
    /**
     * После сохранения следим за ценами создаем если нет
     * @param $event
     */
    public function _afterSaveEvent(AfterSaveEvent $event)
    {
        //Prices update
        if ($this->_baseProductPriceCurrency !== null || $this->_baseProductPriceValue !== null) {
            /**
             * @var $baseProductPrice ShopProductPrice
             */
            $baseProductPrice = $this->getBaseProductPrice()->one();

            if (!$baseProductPrice) {
                $baseProductPrice = new ShopProductPrice([
                    'product_id' => $this->id,
                ]);

                $baseProductPrice->type_price_id = \Yii::$app->shop->baseTypePrice->id;

                if ($this->_baseProductPriceValue) {
                    $baseProductPrice->price = $this->_baseProductPriceValue;
                }

                if ($this->_baseProductPriceCurrency) {
                    $baseProductPrice->currency_code = $this->_baseProductPriceCurrency;
                }

                $baseProductPrice->save();
            } else {
                $isChanged = false;
                //Установка и сохранение только если что то изменилось
                if ($this->_baseProductPriceValue !== null && $this->_baseProductPriceValue != $baseProductPrice->price) {
                    $baseProductPrice->price = $this->_baseProductPriceValue;
                    $isChanged = true;
                }

                if ($this->_baseProductPriceCurrency && $this->_baseProductPriceCurrency != $baseProductPrice->currency_code) {
                    $baseProductPrice->currency_code = $this->_baseProductPriceCurrency;
                    $isChanged = true;
                }

                if ($isChanged) {
                    $baseProductPrice->save();
                }
            }


        } else {
            if (!$this->baseProductPrice) {
                $baseProductPrice = new ShopProductPrice([
                    'product_id' => $this->id,
                ]);

                $baseProductPrice->type_price_id = \Yii::$app->shop->baseTypePrice->id;
                $baseProductPrice->save();
            }
        }

        if (in_array('product_type', array_keys((array)$event->changedAttributes))) {
            if ($this->product_type == self::TYPE_OFFER) {
                if (!$this->shopProductWhithOffers->isOffersProduct) {
                    $sp = $this->shopProductWhithOffers;
                    $sp->product_type = self::TYPE_OFFERS;
                    $sp->save();
                }
            }
        }


    }
    /**
     *
     * Базовая цена по умолчанию
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBaseProductPrice()
    {
        $result = $this->hasOne(ShopProductPrice::class, [
            'product_id' => 'id',
        ]);

        if (\Yii::$app->shop->baseTypePrice) {
            $result->andWhere(['type_price_id' => \Yii::$app->shop->baseTypePrice->id]);
        };

        return $result;
    }
    /**
     * Если втавленный элемент является дочерним для другого то родительскому нужно изменить тип
     * @param $event
     */
    public function _updateParentAfterInsert($event)
    {
        //Если есть родительский элемент
        if ($this->cmsContentElement->parent_content_element_id) {
            $parentProduct = $this->cmsContentElement->parentContentElement->shopProduct;
            $parentProduct->setAttribute('product_type', self::TYPE_OFFERS);
            $parentProduct->save();
        }
    }

    /**
     * @return array
     */
    public function getMeasureMatches()
    {
        if ($this->measure_matches_jsondata) {
            return (array)Json::decode($this->measure_matches_jsondata);
        }

        return [];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                [
                    'created_by',
                    'updated_by',
                    'created_at',
                    'updated_at',
                    'vat_id',
                ],
                'integer',
            ],
            [
                [
                    'quantity',
                    'weight',
                    'quantity_reserved',
                    'width',
                    'length',
                    'height',
                    'measure_ratio',
                ],
                'number',
            ],
            [
                [
                    'vat_included',
                ],
                'string',
                'max' => 1,
            ],
            [['weight', 'width', 'length', 'height'], 'default', 'value' => 0],
            [['measure_ratio'], 'default', 'value' => 1],
            [['measure_ratio'], 'number', 'min' => 0.0001, 'max' => 9999999],

            [['baseProductPriceValue'], 'number'],
            [['baseProductPriceValue'], 'default', 'value' => 0.00],
            [
                ['baseProductPriceValue'],
                function () {
                    if ($this->baseProductPriceValue == "0") {
                        $this->baseProductPriceValue = 0.00;
                    }
                },
            ],


            [['baseProductPriceCurrency'], 'string', 'max' => 3],

            [['vat_included'], 'default', 'value' => Cms::BOOL_Y],

            [['measure_code'], 'string'],
            [
                ['measure_code'],
                'default',
                'value' => function () {
                    return \Yii::$app->measure->default_measure_code;
                },
            ],
            [
                ['measure_code'],
                function ($model) {
                    if (!$this->measure) {
                        $this->addError("measure_code", "Указан код валюты которой нет в базе.");
                    }

                    //Если у товара есть товары поставщика
                    if ($this->shopAttachedProducts) {
                        foreach ($this->shopAttachedProducts as $shopSupplierProduct) {
                            if ($shopSupplierProduct->measure_code != $this->measure_code) {
                                $m = \Yii::$app->measureClassifier->getMeasureByCode($shopSupplierProduct->measure_code);

                                $this->addError("measure_code", "У товара задан товар поставщика с единицей измерения: {$m->symbol}. Укажите у текущего товара такую же единицу измерения.");
                            }
                        }
                    }
                },
            ],

            [['product_type'], 'string', 'max' => 10],
            [
                ['product_type'],
                'default',
                'value' => function () {
                    //Если указан товар с предложениями, то текущий товар должен быть предложением
                    if ($this->offers_pid) {
                        self::TYPE_OFFER;
                    }

                    //По умолчанию товар простой
                    return self::TYPE_SIMPLE;
                },
            ],

            [
                'product_type',
                function ($attribute) {
                    //Если выбран тип товар предлжение, то должен быть указан товар с предложением
                    if ($this->{$attribute} == self::TYPE_OFFER && !$this->offers_pid) {
                        $this->addError($attribute, "Для того чтобы товар был предложением, нужно выбрать общий товар в который он будет вложен.");
                    }
                    //Если указан товар с предложением, то тип должен быть оффер
                    if ($this->offers_pid) {
                        $this->{$attribute} = self::TYPE_OFFER;
                    }
                },

            ],


            [['quantity'], 'default', 'value' => 1],
            [['quantity_reserved'], 'default', 'value' => 0],


            [['measure_matches_jsondata'], 'string'],
            [['measure_matches_jsondata'], 'default', 'value' => null],
            [
                ['measure_matches_jsondata'],
                function () {
                    if ($this->measure_matches_jsondata) {
                        try {
                            $data = Json::decode($this->measure_matches_jsondata);
                            foreach ($data as $measure_code => $value) {
                                if ($measure_code == $this->measure_code) {
                                    $this->addError("measure_matches_jsondata", "Соответствие не должно повторятся с базовой единицей измерения");
                                    return false;
                                }

                                $value = (float)$value;
                                $data[$measure_code] = $value;
                                if (!$value) {
                                    $this->addError("measure_matches_jsondata", "Не заполнено значение для одного из соответствий");
                                    return false;
                                }
                            }

                            $this->measure_matches_jsondata = Json::encode($data);

                        } catch (\Exception $e) {
                            $this->addError("measure_matches_jsondata", "Указано некорректное занчение");
                        }
                    }
                },
            ],

            [['supplier_external_jsondata'], 'safe'],
            [['supplier_external_jsondata'], 'default', 'value' => null],

            [['main_pid'], 'integer'],
            [
                ['main_pid'],
                function ($attribute) {

                    /**
                     * @var $shopProduct ShopProduct
                     */
                    $shopProduct = ShopProduct::find()->where(['id' => $this->main_pid])->one();
                    if (!in_array($shopProduct->product_type, [
                        self::TYPE_SIMPLE,
                        self::TYPE_OFFER,
                    ])) {
                        $this->addError("main_pid", "Родительский товар должен быть простым или предложением.");
                    }
                },
            ],

            [['offers_pid'], 'integer'],
        ];
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'                => \Yii::t('skeeks/shop/app', 'ID'),
            'created_by'        => \Yii::t('skeeks/shop/app', 'Created By'),
            'updated_by'        => \Yii::t('skeeks/shop/app', 'Updated By'),
            'created_at'        => \Yii::t('skeeks/shop/app', 'Created At'),
            'updated_at'        => \Yii::t('skeeks/shop/app', 'Updated At'),
            'quantity'          => \Yii::t('skeeks/shop/app', 'Available quantity'),
            'weight'            => \Yii::t('skeeks/shop/app', 'Вес'),
            'vat_id'            => \Yii::t('skeeks/shop/app', 'VAT rate'),
            'vat_included'      => \Yii::t('skeeks/shop/app', 'VAT included in the price'),
            'quantity_reserved' => \Yii::t('skeeks/shop/app', 'Reserved quantity'),
            'measure_code'      => \Yii::t('skeeks/shop/app', 'Unit of measurement'),
            'measure_ratio'     => \Yii::t('skeeks/shop/app', 'Минимальное количество продажи'),
            'width'             => \Yii::t('skeeks/shop/app', 'Width'),
            'length'            => \Yii::t('skeeks/shop/app', 'Length'),
            'height'            => \Yii::t('skeeks/shop/app', 'Height'),
            'product_type'      => \Yii::t('skeeks/shop/app', 'Product type'),
            'main_pid'          => \Yii::t('skeeks/shop/app', 'Главный товар'),

            'supplier_external_jsondata' => \Yii::t('skeeks/shop/app', 'Данные по товару от поставщика'),
            'measure_matches_jsondata'   => \Yii::t('skeeks/shop/app', 'Упаковка'),
        ];
    }

    public function attributeHints()
    {
        return [
            'measure_code'  => \Yii::t('skeeks/shop/app', 'Единица в которой ведется учет товара. Цена указывается за еденицу товара в этой величине.'),
            'measure_ratio' => \Yii::t('skeeks/shop/app', 'Задайте минимальное количество, которое разрешено класть в корзину'),
        ];
    }
    /**
     *
     * Отметить просмотр текущего товара согласно текущим данным
     *
     * @return bool
     */
    public function createNewView()
    {
        if ($this->isNewRecord) {
            return false;
        }

        $shopViewdProduct = new ShopViewedProduct();
        $shopViewdProduct->shop_product_id = $this->id;
        $shopViewdProduct->site_id = \Yii::$app->skeeks->site->id;
        $shopViewdProduct->shop_fuser_id = \Yii::$app->shop->cart->id;

        return $shopViewdProduct->save();
    }

    /**
     * @return \skeeks\yii2\measureClassifier\models\Measure|null
     */
    public function getMeasure()
    {
        return $this->hasOne(CmsMeasure::class, ['code' => 'measure_code']);

    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCmsContentElement()
    {
        return $this->hasOne(ShopCmsContentElement::class, ['id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopMainProduct()
    {
        return $this->hasOne(ShopProduct::class, ['id' => 'main_pid'])->from(['shopMainProduct' => ShopProduct::tableName()]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopProductWhithOffers()
    {
        return $this->hasOne(ShopProduct::class, ['id' => 'offers_pid'])->from(['shopProductWhithOffers' => ShopProduct::tableName()]);
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopProductOffers()
    {
        return $this->hasOne(ShopProduct::class, ['offers_pid' => 'id'])->from(['shopProductOffers' => ShopProduct::tableName()]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopAttachedProducts()
    {
        return $this->hasMany(ShopProduct::class, ['main_pid' => 'id']);
    }

    /**
     * Кто поставляет текущий товар
     *
     * @return \yii\db\ActiveQuery
     */
    public function getShopSupplierProducts()
    {
        $q = $this->getShopAttachedProducts()
            ->joinWith("cmsContentElement as cmsContentElement")
            ->joinWith("cmsContentElement.cmsSite as cmsSite")
            ->joinWith("cmsContentElement.cmsSite.shopSite as shopSite")
            ->andWhere(['shopSite.is_supplier' => 1]);
        return $q;
    }
    /**
     * Кто получает и продает текущий товар на сайте
     *
     * @return \yii\db\ActiveQuery
     */
    public function getShopSellerProducts()
    {
        $q = $this->getShopAttachedProducts()
            ->joinWith("cmsContentElement as cmsContentElement")
            ->joinWith("cmsContentElement.cmsSite as cmsSite")
            ->joinWith("cmsContentElement.cmsSite.shopSite as shopSite")
            ->andWhere(['shopSite.is_receiver' => 1]);
        return $q;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVat()
    {
        return $this->hasOne(ShopVat::class, ['id' => 'vat_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopProductPrices()
    {
        return $this->hasMany(ShopProductPrice::class, ['product_id' => 'id'])->from(['prices' => ShopProductPrice::tableName()]);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopViewedProducts()
    {
        return $this->hasMany(ShopViewedProduct::class, ['shop_product_id' => 'id']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopProductQuantityChanges()
    {
        return $this->hasMany(ShopProductQuantityChange::class,
            ['shop_product_id' => 'id'])->orderBy(['created_at' => SORT_DESC]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopStoreProducts()
    {
        return $this->hasMany(ShopStoreProduct::class, ['shop_product_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopQuantityNoticeEmails()
    {
        return $this->hasMany(ShopQuantityNoticeEmail::class, ['shop_product_id' => 'id']);
    }

    /**
     * Цены доступные к просмотру
     *
     * @param null $shopFuser
     * @return $this
     */
    public function getViewProductPrices($shopFuser = null)
    {
        if ($shopFuser === null) {
            $shopFuser = \Yii::$app->shop->cart;
        }

        return $this->hasMany(ShopProductPrice::class, [
            'product_id' => 'id',
        ])->andWhere([
            'type_price_id' => ArrayHelper::map($shopFuser->viewTypePrices, 'id', 'id'),
        ])->orderBy(['price' => SORT_ASC]);
    }

    /**
     *
     * Лучшая цена по которой может купить этот товар пользователь, среди всех доступных
     *
     * @param null $shopFuser
     * @return $this
     */
    public function getMinProductPrice($shopFuser = null)
    {
        if ($shopFuser === null) {
            $shopFuser = \Yii::$app->shop->cart;
        }


        if (!$shopFuser) {
            $basPriceTypes = [\Yii::$app->shop->baseTypePrice->id];
        } else {
            $basPriceTypes = $shopFuser->buyTypePrices;
        }

        return $this->hasOne(ShopProductPrice::class, [
            'product_id' => 'id',
        ])
            ->select([
                'shop_product_price.*',
                'realPrice' => '( (SELECT course FROM money_currency WHERE money_currency.code = shop_product_price.currency_code) * shop_product_price.price )',
            ])
            ->leftJoin('money_currency', 'money_currency.code = shop_product_price.currency_code')
            ->orWhere([
                'and',
                ['>', 'price', 0],
                ['type_price_id' => ArrayHelper::map($basPriceTypes, 'id', 'id')],
            ])
            ->orWhere(
                ['type_price_id' => \Yii::$app->shop->baseTypePrice->id]
            )
            ->orderBy(['realPrice' => SORT_ASC]);
    }

    /**
     * Значение базовой цены
     *
     * @return string
     */
    public function getBaseProductPriceValue()
    {
        if ($this->_baseProductPriceValue !== null) {
            return $this->_baseProductPriceValue;
        } else {
            $this->_baseProductPriceValue = $this->baseProductPrice ? $this->baseProductPrice->price : null;
        }

        return $this->_baseProductPriceValue;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setBaseProductPriceValue($value)
    {
        $this->_baseProductPriceValue = $value;
        return $this;
    }
    /**
     * Валюта базовой цены
     *
     * @return string
     */
    public function getBaseProductPriceCurrency()
    {
        if ($this->_baseProductPriceCurrency) {
            return $this->_baseProductPriceCurrency;
        } else {
            $this->_baseProductPriceCurrency = $this->baseProductPrice ? $this->baseProductPrice->currency_code : \Yii::$app->money->currencyCode;
        }

        return $this->_baseProductPriceCurrency;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setBaseProductPriceCurrency($value)
    {
        $this->_baseProductPriceCurrency = $value;
        return $this;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopOrderItems()
    {
        return $this->hasMany(ShopOrderItem::className(), ['shop_product_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopOrders()
    {
        return $this->hasMany(ShopOrder::className(), ['id' => 'shop_order_id'])->via('shopOrderItems');
    }

    /**
     * @return \skeeks\cms\query\CmsActiveQuery
     */
    public function getShopTypePrices()
    {
        $query = \Yii::$app->skeeks->site->getShopTypePrices();

        $query->orderBy(['priority' => SORT_ASC]);
        $query->multiple = true;
        return $query;
    }


    /**
     * Является второстепенным товаром?
     * То есть не продается на главном сайте.
     *
     * @return bool
     */
    public function getIsSubProduct()
    {
        if ($this->cmsContentElement && $this->cmsContentElement->cms_site_id) {
            if (!$this->cmsContentElement->cmsSite->is_default) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    public function getIsSimpleProduct()
    {
        return (bool)($this->product_type === self::TYPE_SIMPLE);
    }

    /**
     * @return bool
     */
    public function getIsOfferProduct()
    {
        return (bool)($this->product_type === self::TYPE_OFFER);
    }
    /**
     * @return bool
     */
    public function getIsOffersProduct()
    {
        return (bool)($this->product_type === self::TYPE_OFFERS);
    }


    /**
     * Gets query for [[ShopFavoriteProducts]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getShopFavoriteProducts()
    {
        return $this->hasMany(ShopFavoriteProduct::className(), ['shop_product_id' => 'id']);
    }


    public function asText()
    {
        return $this->cmsContentElement->asText;
    }
}