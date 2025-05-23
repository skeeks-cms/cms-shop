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
use skeeks\cms\models\CmsCountry;
use skeeks\cms\money\Money;
use skeeks\cms\query\CmsActiveQuery;
use skeeks\modules\cms\money\models\Currency;
use yii\base\Exception;
use yii\base\InvalidArgumentException;
use yii\db\AfterSaveEvent;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * This is the model class for table "{{%shop_product}}".
 *
 * @property integer                   $id
 * @property integer                   $created_by
 * @property integer                   $updated_by
 * @property integer                   $created_at
 * @property integer                   $updated_at
 * @property double                    $quantity
 * @property double                    $weight
 * @property double                    $measure_ratio
 * @property double                    $measure_ratio_min
 * @property integer                   $vat_id
 * @property string                    $vat_included
 * @property string                    $measure_code
 * @property double                    $width
 * @property double                    $length
 * @property double                    $height
 * @property double                    $rating_value
 * @property integer                   $rating_count
 * @property integer                   $brand_id
 * @property integer|null                   $shop_product_model_id
 * @property string                    $brand_sku
 * @property integer|null              $country_alpha2
 *
 * @property integer                   $expiration_time
 * @property string                    $expiration_time_comment
 *
 * @property integer                   $service_life_time
 * @property string                    $service_life_time_comment
 *
 * @property integer                   $warranty_time
 * @property string                    $warranty_time_comment
 *
 * @property integer|null              $offers_pid
 *
 * @property string                    $product_type
 * @property array|null                $supplier_external_jsondata
 * @property array|null                $measure_matches_jsondata
 *
 * @property string                    $productTypeAsText
 * @property CmsMeasure                $measure
 * @property ShopCmsContentElement     $cmsContentElement
 * @property ShopTypePrice             $trialPrice
 * @property ShopVat                   $vat
 * @property Currency                  $purchasingCurrency
 * @property ShopProductPrice[]        $shopProductPrices
 * @property ShopViewedProduct[]       $shopViewedProducts
 *
 * @property string                    $baseProductPriceValue
 * @property string                    $baseProductPriceCurrency
 *
 * @property ShopProductPrice          $baseProductPrice
 * @property ShopProductPrice          $purchaseProductPrice
 * @property ShopProductPrice          $minProductPrice
 * @property ShopProductPrice[]        $viewProductPrices
 * @property ShopQuantityNoticeEmail[] $shopQuantityNoticeEmails
 * @property ShopStoreProduct[]        $shopStoreProducts
 *
 * @property ShopCmsContentElement[]   $tradeOffers
 * @property ShopOrderItem[]           $shopOrderItems
 * @property ShopOrder[]               $shopOrders
 * @property ShopTypePrice             $shopTypePrices
 *
 *
 * @property ShopProductModel          $shopProductModel
 *
 * @property ShopProduct               $shopProductWhithOffers Товар с предложениями для текущего товара
 * @property ShopProduct[]             $shopProductOffers Предложения для текущего товара
 *
 *
 * @property CmsCountry                $country
 * @property ShopBrand                 $brand
 * @property ShopCollection[]          $collections
 *
 * @property boolean                   $isSubProduct
 * @property string                    $weightFormatted
 * @property string                    $WeightPerOneMeasure Вес за 1 единицу измерений (например 1 м2)
 * @property string                    $WeightPerOneMeasureFormatted
 * @property string                    $lengthFormatted
 * @property string                    $widthFormatted
 * @property string                    $heightFormatted
 * @property string                    $dimensionsFormated
 *
 * @property boolean                   $isSimpleProduct
 * @property boolean                   $isOfferProduct
 * @property boolean                   $isOffersProduct
 * @property array                     $measureMatches
 *
 * @property ShopFeedback[]            $shopFeedbacks
 * @property ShopFavoriteProduct[]     $shopFavoriteProducts
 * @property ShopProductBarcode[]      $shopProductBarcodes
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
            \skeeks\cms\behaviors\RelationalBehavior::class,
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
            static::TYPE_OFFERS => \Yii::t('skeeks/shop/app', 'С модификациями'),
            static::TYPE_OFFER  => \Yii::t('skeeks/shop/app', 'Это модификация другого товара'),
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

        $this->on(self::EVENT_BEFORE_UPDATE, [$this, "_beforeUpdateEvent"]);

        $this->on(self::EVENT_BEFORE_INSERT, [$this, "_beforeSaveEvent"]);
        $this->on(self::EVENT_BEFORE_UPDATE, [$this, "_beforeSaveEvent"]);

        $this->on(self::EVENT_AFTER_INSERT, [$this, "_afterSaveEvent"]);
        $this->on(self::EVENT_AFTER_UPDATE, [$this, "_afterSaveEvent"]);

        $this->on(self::EVENT_AFTER_INSERT, [$this, "_updateParentAfterInsert"]);

    }

    /**
     * @return \yii\db\ActiveQuery
     * @throws \yii\base\InvalidConfigException
     */
    public function getCollections()
    {
        return $this->hasMany(ShopCollection::class, ['id' => 'shop_collection_id'])
            ->viaTable('shop_product2collection', ['shop_product_id' => 'id']);
    }


    public function _beforeUpdateEvent($event)
    {
        if ($this->isAttributeChanged('offers_pid')) {

            $element = $this->cmsContentElement;
            $offersElement = $this->shopProductWhithOffers->cmsContentElement;

            if ($element->tree_id != $offersElement->tree_id) {
                throw new Exception("Товар с модификацией должен иметь такой же раздел. {$offersElement->tree_id} != {$element->tree_id}");
            }
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
     * @return array|\skeeks\cms\query\CmsActiveQuery|\skeeks\cms\query\CmsContentElementActiveQuery
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

        /**
         *
         */
        if ($this->_barcodes !== null) {

            $values = ArrayHelper::map((array)$this->_barcodes, 'value', 'value');

            if ($values) {
                ShopProductBarcode::deleteAll([
                    'and',
                    ['shop_product_id' => $this->id],
                    ['not in', 'value', $values],
                ]);
            } else {
                ShopProductBarcode::deleteAll([
                    'shop_product_id' => $this->id,
                ]);
            }


            foreach ((array)$this->_barcodes as $barcodeData) {
                $value = ArrayHelper::getValue($barcodeData, "value");
                if (!$value) {
                    continue;
                }
                $type = ArrayHelper::getValue($barcodeData, "barcode_type");

                if (!$shopProductBarcode = $this->getShopProductBarcodes()->andWhere([
                    'value' => $value,
                ])->one()) {
                    $shopProductBarcode = new ShopProductBarcode();
                    $shopProductBarcode->shop_product_id = $this->id;
                }

                $shopProductBarcode->value = $value;
                if ($type) {
                    $shopProductBarcode->barcode_type = $type;
                }

                //print_r($shopProductBarcode->toArray());
                if (!$shopProductBarcode->save()) {
                    throw new Exception("Ошибка сохранения кода: {$shopProductBarcode->value} (несуществующий или некорректный)");
                    //throw new Exception("Ошибка сохранения кода: {$shopProductBarcode->value}" . print_r($shopProductBarcode->errors, true));
                }
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
     *
     * Базовая цена по умолчанию
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPurchaseProductPrice()
    {
        $result = $this->hasOne(ShopProductPrice::class, [
            'product_id' => 'id',
        ]);

        if (\Yii::$app->shop->purchaseTypePrice) {
            $result->andWhere(['type_price_id' => \Yii::$app->shop->purchaseTypePrice->id]);
        } else {
            return null;
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
                    'vat_id',

                    'expiration_time',
                    'service_life_time',
                    'warranty_time',
                    'shop_product_model_id',
                ],
                'integer',
            ],
            [
                [
                    'quantity',
                    'weight',
                    'width',
                    'length',
                    'height',
                    'measure_ratio',
                    'measure_ratio_min',
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

            [['rating_value'], 'number', 'min' => 0, 'max' => 5],
            [['rating_count'], 'integer', 'min' => 0],
            [['collections'], 'safe'],
            /*[['collections'], 'required', "when" => function(self $model) {
                if ($model->cmsContentElement && $model->cmsContentElement->cmsTree) {
                    if ($model->cmsContentElement->cmsTree->shop_has_collections) {
                        return true;
                    }
                }

                return false;
            }],*/

            /*[['weight'], 'required', "when" => function(self $model) {
                if ($model->cmsContentElement && $model->cmsContentElement->cmsSite->shopSite->required_product_fields) {

                    if (in_array("weight", (array) $model->cmsContentElement->cmsSite->shopSite->required_product_fields)) {
                        return true;
                    }
                }

                return false;
            }],*/

            [
                [
                    'brand_id',
                    'country_alpha2',
                    'brand_sku',
                ],
                "required",
                "when" => function ($model, $attribute) {
                    if ($this->cmsContentElement && $this->cmsContentElement->cmsSite->shopSite->required_product_fields) {
                        if (in_array($attribute, (array)$this->cmsContentElement->cmsSite->shopSite->required_product_fields)) {
                            return true;
                        }
                    }

                    return false;
                },
            ],

            [['brand_id'], 'integer'],
            [['country_alpha2'], 'string'],
            [['country_alpha2'], 'default', 'value' => null],
            [['brand_sku'], 'string'],

            //[['brand_id', 'brand_sku', 'country_id'], 'default', 'value' => null],

            /*[['brand_id'], function($attribute) {
                print_r($this->cmsContentElement->cmsSite->shopSite->required_product_fields);die;
                if ($this->cmsContentElement && $this->cmsContentElement->cmsSite->shopSite->required_product_fields) {


                    if (in_array($attribute, (array) $this->cmsContentElement->cmsSite->shopSite->required_product_fields) && !$this->{$attribute}) {
                        $this->addError($attribute, "Необходимо указать «" . $this->getAttributeLabel($attribute) . "»");
                        return false;
                    }
                }

                return true;
            }],*/

            [
                ['weight'],
                function ($attribute) {
                    if ($this->cmsContentElement && $this->cmsContentElement->cmsSite->shopSite->required_product_fields) {

                        if (in_array($attribute, (array)$this->cmsContentElement->cmsSite->shopSite->required_product_fields)) {

                            if ($this->{$attribute} > 0) {

                            } else {
                                $this->addError($attribute, "Необходимо указать «".$this->getAttributeLabel($attribute)."»");
                                return false;
                            }

                        }
                    }

                    return true;
                },
            ],

            [
                [
                    "height",
                    "width",
                    "length",
                ],
                function ($attribute) {
                    if ($this->cmsContentElement && $this->cmsContentElement->cmsSite->shopSite->required_product_fields) {
                        if (in_array("dimensions", (array)$this->cmsContentElement->cmsSite->shopSite->required_product_fields)) {

                            if ($this->{$attribute} > 0) {

                            } else {
                                $this->addError($attribute, "Необходимо указать «".$this->getAttributeLabel($attribute)."»");
                                return false;
                            }

                        }
                    }

                    return true;
                },
            ],


            [
                ['rating_count'],
                'required',
                'when' => function () {
                    return $this->rating_value;
                },
            ],

            [
                ['rating_count'],
                function ($attribute) {
                    if ($this->rating_value > 0 && $this->rating_count == 0) {
                        $this->addError($attribute, "Необходимо указать количество отзывов. Потому что указан рейтинг товара.");
                        return false;
                    }

                    return true;
                },
            ],

            [
                ['measure_ratio_min'],
                'default',
                'value' => function () {
                    return $this->measure_ratio;
                },
            ],
            [['measure_ratio_min'], 'number', 'min' => 0.0001, 'max' => 9999999],
            [
                ['measure_ratio_min'],
                function () {
                    if ($this->measure_ratio > $this->measure_ratio_min) {
                        $this->measure_ratio_min = $this->measure_ratio;
                        //$this->addError("measure_ratio_min", "Минимальное количество продажи должно быть больше чем шаг продажи");
                        //return false;
                    }
                    /*print_r($this->measure_ratio_min);
                                die;*/

                },
            ],
            [
                ['measure_ratio_min'],
                function () {
                    if ((float)$this->measure_ratio != 0 && (float)$this->measure_ratio_min != 0) {
                        try {
                            $one = (float)$this->measure_ratio_min;
                            $two = (float)$this->measure_ratio;
                            if ($one < 1 || $two < 1) {
                                $one = $one * 10000;
                                $two = $two * 10000;
                            }
                            if ($one % $two != 0) {
                                $this->addError("measure_ratio_min", "Минимальное количество продажи должно быть кратно шагу продажи.");
                                return false;
                            }
                        } catch (\Exception $e) {
                        }

                    }
                },
            ],

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
                        $this->addError("measure_code", "Указан код валюты '{$this->measure_code}', которой нет в базе.");
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


            [['measure_matches_jsondata'], 'string'],
            [['measure_matches_jsondata'], 'default', 'value' => null],

            [['shop_product_model_id'], 'default', 'value' => null],
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

            [['offers_pid'], 'integer'],
            [
                ['offers_pid'],
                function ($attribute) {

                },
            ],

            [['barcodes'], 'safe'],


            [
                [
                    'expiration_time_comment',
                    'service_life_time_comment',
                    'warranty_time_comment',
                ],
                'string',
            ],

            [
                [
                    'expiration_time_comment',
                    'service_life_time_comment',
                    'warranty_time_comment',
                ],
                'default',
                'value' => null,
            ],

            [
                ['barcodes'],
                function ($attribute) {
                    if ($this->barcodes !== null) {
                        foreach ((array)$this->barcodes as $barcodeData) {

                            if (!ArrayHelper::getValue($barcodeData, 'value')) {
                                continue;
                            }

                            if (!$this->isNewRecord) {
                                if (!$shopProductBarcode = $this->getShopProductBarcodes()->andWhere([
                                    'value' => ArrayHelper::getValue($barcodeData, 'value'),
                                ])->one()) {
                                    $shopProductBarcode = new ShopProductBarcode();
                                    $shopProductBarcode->shop_product_id = $this->id;
                                }
                                $validateAttributes = [];
                            } else {
                                $shopProductBarcode = new ShopProductBarcode();
                                $shopProductBarcode->shop_product_id = $this->id;
                                $validateAttributes = ['value', 'barcode_type'];
                            }

                            $shopProductBarcode->value = ArrayHelper::getValue($barcodeData, 'value');
                            $shopProductBarcode->barcode_type = ArrayHelper::getValue($barcodeData, 'barcode_type');

                            if (!$shopProductBarcode->validate($validateAttributes)) {
                                $this->addError($attribute, print_r($barcodeData, true)." — некорректный штрихкод: ".Json::encode($shopProductBarcode->errors));
                                return false;
                            }
                        }
                    }

                },
            ],
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
            'quantity'          => \Yii::t('skeeks/shop/app', 'Количество под заказ'),
            'weight'            => \Yii::t('skeeks/shop/app', 'Вес'),
            'vat_id'            => \Yii::t('skeeks/shop/app', 'VAT rate'),
            'vat_included'      => \Yii::t('skeeks/shop/app', 'VAT included in the price'),
            'measure_code'      => \Yii::t('skeeks/shop/app', 'Unit of measurement'),
            'measure_ratio'     => \Yii::t('skeeks/shop/app', 'Шаг количества продажи'),
            'measure_ratio_min' => \Yii::t('skeeks/shop/app', 'Минимальное количество продажи'),
            'width'             => \Yii::t('skeeks/shop/app', 'Width'),
            'length'            => \Yii::t('skeeks/shop/app', 'Length'),
            'height'            => \Yii::t('skeeks/shop/app', 'Height'),
            'barcodes'          => "Штрихкоды",
            'rating_value'      => "Рейтинг товара",
            'rating_count'      => "Количество отзывов",
            'product_type'      => \Yii::t('skeeks/shop/app', 'Product type'),

            'expiration_time'         => 'Срок годности',
            'expiration_time_comment' => 'Комментарий к сроку годности',

            'service_life_time'         => 'Срок службы',
            'service_life_time_comment' => 'Комментарий к сроку службы',

            'warranty_time'         => 'Срок гарантии',
            'warranty_time_comment' => 'Комментарий к сроку гарантии',
            'brand_id'              => 'Бренд',
            'country_alpha2'        => 'Страна производитель',
            'brand_sku'             => 'Артикул бренда',
            'collections'           => 'Коллекции',

            'supplier_external_jsondata' => \Yii::t('skeeks/shop/app', 'Данные по товару от поставщика'),
            'measure_matches_jsondata'   => \Yii::t('skeeks/shop/app', 'Упаковка'),
        ];
    }


    public function attributeHints()
    {
        return [
            'measure_code'      => \Yii::t('skeeks/shop/app', 'Единица в которой ведется учет товара. Цена указывается за еденицу товара в этой величине.'),
            'measure_ratio'     => \Yii::t('skeeks/shop/app', 'Задайте минимальное количество, которое разрешено класть в корзину'),
            'measure_ratio_min' => \Yii::t('skeeks/shop/app', 'Нажимая кнопку плюс и минус для добавления в корзину будет добавлятся именно это количество'),

            'expiration_time'         => 'Через какое время товар станет непригоден для использования. Например, срок годности есть у таких категорий, как продукты питания и медицинские препараты.',
            'expiration_time_comment' => 'Можно указать условия хранения.',

            'service_life_time'         => 'В течение этого периода изготовитель готов нести ответственность за существенные недостатки товара, обеспечивать наличие запчастей и возможность обслуживания и ремонта. Например, срок службы устанавливается для детских игрушек и климатической техники.',
            'service_life_time_comment' => 'Можно указать условия использования.',
            'brand_sku'                 => 'Заполняется если у бренда есть артикул. Он бывает не у всех брендов.',
            'country_alpha2'            => 'Страна где произведен этот товар',

            'warranty_time'         => 'В течение этого периода возможны обслуживание и ремонт товара, возврат денег.',
            'warranty_time_comment' => 'Можно дать инструкцию для наступления гарантийного случая.',

            'collections' => 'Коллекция товаров например плитка, ламинат, обои',
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
        $shopViewdProduct->shop_user_id = \Yii::$app->shop->shopUser->id;

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
     * @return ShopBrand
     */
    public function getBrand()
    {
        return $this->hasOne(ShopBrand::class, ['id' => 'brand_id'])->from(['shopBrand' => ShopBrand::tableName()]);

    }
    /**
     * @return ShopBrand
     */
    public function getShopProductModel()
    {
        return $this->hasOne(ShopProductModel::class, ['id' => 'shop_product_model_id'])->from(['shopProductModel' => ShopProductModel::tableName()]);

    }
    /**
     * @return CmsCountry|null
     */
    public function getCountry()
    {
        return $this->hasOne(CmsCountry::class, ['alpha2' => 'country_alpha2']);
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
    public function getShopProductWhithOffers()
    {
        return $this->hasOne(ShopProduct::class, ['id' => 'offers_pid'])->from(['shopProductWhithOffers' => ShopProduct::tableName()]);
    }


    /**
     * @return \yii\db\ActiveQuery
     * @deprecated
     */
    public function getShopProductOffers()
    {
        return $this->hasOne(ShopProduct::class, ['offers_pid' => 'id'])->from(['shopProductOffers' => ShopProduct::tableName()]);
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
    public function getShopStoreProducts($stores = [])
    {
        $condition = ['shop_product_id' => 'id'];
        $q = $this->hasMany(ShopStoreProduct::class, $condition);

        if ($stores) {

            $sotreIds = ArrayHelper::map($stores, "id", "id");
            $q->andWhere(['shop_store_id' => $sotreIds]);
            //$condition = ['shop_product_id' => 'id', 'shop_store_id' => $sotreIds];
        }

        return $q;
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
     * @param null $shopUser
     * @return $this
     */
    public function getViewProductPrices($shopUser = null)
    {
        if ($shopUser === null) {
            $shopUser = \Yii::$app->shop->shopUser;
        }

        return $this->hasMany(ShopProductPrice::class, [
            'product_id' => 'id',
        ])->andWhere([
            'type_price_id' => ArrayHelper::map($shopUser->viewTypePrices, 'id', 'id'),
        ])->orderBy(['price' => SORT_ASC]);
    }

    /**
     *
     * Лучшая цена по которой может купить этот товар пользователь, среди всех доступных
     *
     * @param null $shopUser
     * @return $this
     */
    public function getMinProductPrice($shopUser = null)
    {
        if ($shopUser === null) {
            $shopUser = \Yii::$app->shop->shopUser;
        }


        /*if (!$shopUser) {
            $basPriceTypes = [\Yii::$app->shop->baseTypePrice];
        } else {
            $basPriceTypes = $shopUser->buyTypePrices;
        }*/


        if ($shopUser && $shopUser->cmsUser) {
            $basPriceTypes = \Yii::$app->shop->getCanBuyTypePrices($shopUser->cmsUser);
        } else {
            $basPriceTypes = \Yii::$app->shop->canBuyTypePrices;
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
            /*->orWhere(
                ['type_price_id' => \Yii::$app->shop->baseTypePrice->id]
            )*/
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
     * TODO: это не очень хорошая функция
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


    /**
     * Gets query for [[ShopProductRelations]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getShopProductRelations1()
    {
        return $this->hasMany(ShopProductRelation::className(), ['shop_product1_id' => 'id']);
    }

    /**
     * Gets query for [[ShopProductRelations]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getShopProductRelations2()
    {
        return $this->hasMany(ShopProductRelation::className(), ['shop_product2_id' => 'id']);
    }


    /**
     * Gets query for [[ShopProductRelations]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getShopProductRelations()
    {
        $q = self::find()
            ->joinWith("shopProductRelations1 as shopProductRelations1")
            ->joinWith("shopProductRelations2 as shopProductRelations2")
            ->andWhere([
                'or',
                ["shopProductRelations1.shop_product1_id" => $this->id],
                ["shopProductRelations1.shop_product2_id" => $this->id],
                ["shopProductRelations2.shop_product1_id" => $this->id],
                ["shopProductRelations2.shop_product2_id" => $this->id],
            ]);

        $q->multiple = true;

        return $q;
    }


    /**
     * Получить цену по товару
     *
     * @param ShopTypePrice|int $shopTypePrice
     * @return ShopProductPrice|null
     */
    public function getPrice($shopTypePrice)
    {
        $typePriceId = null;
        if ($shopTypePrice instanceof ShopTypePrice) {
            $typePriceId = $shopTypePrice->id;
        } else {
            $typePriceId = (int)$shopTypePrice;
        }

        if (!$productPrice = $this->getShopProductPrices()->andWhere([
            'type_price_id' => $typePriceId,
        ])->one()) {
            return null;
        }

        return $productPrice;
    }

    /**
     * @param $shopStore
     * @return ShopStoreProduct|null
     */
    public function getStoreProduct($shopStore)
    {
        $typePriceId = null;
        if ($shopStore instanceof ShopStore) {
            $typePriceId = $shopStore->id;
        } else {
            $typePriceId = (int)$shopStore;
        }

        if (!$productPrice = $this->getShopStoreProducts()->andWhere([
            'shop_store_id' => $typePriceId,
        ])->one()) {
            return null;
        }

        return $productPrice;
    }


    /**
     * @param $shopStore
     * @return Money
     */
    public function getRetailPriceMoney($shopStore)
    {
        $store = null;
        if ($shopStore instanceof ShopStore) {
            $store = $shopStore->id;
        } else {
            $store = (int)$shopStore;
        }

        $storeProduct = $this->getStoreProduct($store);
        if ($storeProduct && $storeProduct->shopStore->is_personal_price && $storeProduct->selling_price) {
            return new Money($storeProduct->selling_price, \Yii::$app->money->currency_code);
        } else {
            return $this->baseProductPrice->money;
        }
    }


    /**
     * @param $shopStore
     * @return Money
     */
    public function getPurchasePriceMoney($shopStore)
    {
        $store = null;
        if ($shopStore instanceof ShopStore) {
            $store = $shopStore->id;
        } else {
            $store = (int)$shopStore;
        }

        $storeProduct = $this->getStoreProduct($store);
        if ($storeProduct && $storeProduct->shopStore->is_personal_price && $storeProduct->purchase_price) {
            return new Money($storeProduct->purchase_price, \Yii::$app->money->currency_code);
        } else {
            return $this->purchaseProductPrice->money;
        }
    }

    /**
     * @param      $shopTypePrice
     * @param      $value
     * @param null $curencyCode
     * @return array|ShopProductPrice|\yii\db\ActiveRecord|null
     */
    public function savePrice($shopTypePrice, $value, $curencyCode = null)
    {
        $typePriceId = null;
        if ($shopTypePrice instanceof ShopTypePrice) {
            $typePriceId = $shopTypePrice->id;
        } else {
            $typePriceId = (int)$shopTypePrice;
        }

        if (!$typePriceId) {
            throw new InvalidArgumentException("Need type price id");
        }

        if (!$productPrice = $this->getShopProductPrices()->andWhere([
            'type_price_id' => $typePriceId,
        ])->one()) {
            $productPrice = new ShopProductPrice();
            $productPrice->product_id = $this->id;
            $productPrice->type_price_id = $typePriceId;
        }

        $productPrice->price = $value;

        if ($curencyCode) {
            $productPrice->currency_code = $curencyCode;
        }

        if (!$productPrice->save()) {
            throw new Exception(print_r($productPrice->errors, true));
        }

        return $productPrice;
    }

    /**
     * @param $shopStore
     * @param $quantity
     * @return ShopStoreProduct|null
     * @throws Exception
     */
    public function saveStoreQuantity($shopStore, $quantity)
    {
        $shopStoreId = null;
        if ($shopStore instanceof ShopStore) {
            $shopStoreId = $shopStore->id;
        } else {
            $shopStoreId = (int)$shopStore;
        }

        if (!$shopStoreId) {
            throw new InvalidArgumentException("Need shop store id");
        }

        if (!$storeProduct = $this->getStoreProduct($shopStoreId)) {
            $storeProduct = new ShopStoreProduct();
            $storeProduct->shop_product_id = $this->id;
            $storeProduct->shop_store_id = $shopStoreId;
        }

        $storeProduct->quantity = $quantity;

        if (!$storeProduct->save()) {
            throw new Exception(print_r($storeProduct->errors, true));
        }

        return $storeProduct;
    }


    /**
     * Gets query for [[ShopProductBarcodes]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getShopProductBarcodes()
    {
        return $this->hasMany(ShopProductBarcode::className(), ['shop_product_id' => 'id']);
    }


    /**
     * @return CmsActiveQuery
     */
    public function getShopFeedbacks()
    {
        return $this->hasMany(ShopFeedback::className(), ['shop_product_id' => 'id']);
    }


    /**
     * @var null
     */
    protected $_barcodes = null;

    /**
     * @param $barcodes
     * @return $this
     */
    public function setBarcodes($barcodes)
    {
        if (is_string($barcodes)) {

            $value = trim((string)$barcodes);

            $barcodes = [
                [
                    'value' => $value,
                    //'barcode_type' => ShopProductBarcode::TYPE_EAN13,
                ],
            ];

            /*if (StringHelper::strlen($value) == 13) {
                $barcodes = [
                    [
                        'value'        => $value,
                        'barcode_type' => ShopProductBarcode::TYPE_EAN13,
                    ],
                ];

            } elseif (StringHelper::strlen($value) == 12) {

                $barcodes = [
                    [
                        'value'        => $value,
                        'barcode_type' => ShopProductBarcode::TYPE_UPC,
                    ],
                ];
            }*/


        } elseif (is_array($barcodes)) {


            foreach ($barcodes as $key => $barcodeData) {
                if (is_string($barcodeData) || is_int($barcodeData)) {
                    $value = trim((string)$barcodeData);

                    $barcodes[$key] = [
                        'value' => $value,
                        //'barcode_type' => ShopProductBarcode::TYPE_EAN13,
                    ];

                    /*if (StringHelper::strlen($value) == 13) {
                        $barcodes[$key] = [
                            'value'        => $value,
                            'barcode_type' => ShopProductBarcode::TYPE_EAN13,
                        ];
                    } elseif (StringHelper::strlen($value) == 12) {
                        $barcodes[$key] = [
                            'value'        => $value,
                            'barcode_type' => ShopProductBarcode::TYPE_UPC,
                        ];
                    }*/
                }
            }
        } else {
            throw new \http\Exception\InvalidArgumentException("barcodes must do string or array");
        }


        $this->_barcodes = $barcodes;

        return $this;
    }

    /**
     * @return array|null
     */
    public function getBarcodes()
    {
        if ($this->_barcodes === null) {
            if ($this->shopProductBarcodes) {
                $this->_barcodes = ArrayHelper::map($this->shopProductBarcodes, "value", function ($model) {
                    /**
                     * @var ShopProductBarcode $model
                     */
                    return [
                        'barcode_type' => $model->barcode_type,
                        'value'        => $model->value,
                    ];
                });
            } else {
                $this->_barcodes = [];
            }
        }

        return $this->_barcodes;
    }

    /**
     * @return float
     */
    public function getWeightPerOneMeasure()
    {
        if ($this->measure_ratio == 0) {
            return 0;
        }

        if ($this->measure_ratio == 1) {
            return $this->weight;
        }

        return (float)($this->weight / $this->measure_ratio);
    }
    /**
     * @return float
     */
    public function getWeightPerOneMeasureFormatted()
    {
        if ($this->WeightPerOneMeasure >= 1000 && $this->WeightPerOneMeasure <= 1000000) {
            return \Yii::$app->formatter->asDecimal(($this->WeightPerOneMeasure / 1000))." кг.";
        } elseif ($this->WeightPerOneMeasure >= 1000000) {
            return \Yii::$app->formatter->asDecimal(($this->WeightPerOneMeasure / 1000000))." т.";
        } else {
            return \Yii::$app->formatter->asDecimal(($this->WeightPerOneMeasure))." г.";
        }
    }

    /**
     * @return string
     */
    public function getWeightFormatted()
    {
        if ($this->weight >= 1000 && $this->weight <= 1000000) {
            return \Yii::$app->formatter->asDecimal(($this->weight / 1000))." кг";
        } elseif ($this->weight >= 1000000) {
            return \Yii::$app->formatter->asDecimal(($this->weight / 1000000))." т";
        } else {
            return \Yii::$app->formatter->asDecimal(($this->weight))." г";
        }
    }

    /**
     * @return string
     */
    public function getLengthFormatted()
    {
        if ($this->length >= 10 && $this->length <= 1000) {
            return \Yii::$app->formatter->asDecimal(($this->length / 10))." см.";
        } elseif ($this->length >= 1000) {
            return \Yii::$app->formatter->asDecimal(($this->length / 1000))." м";
        } else {
            return \Yii::$app->formatter->asDecimal(($this->length))." мм";
        }
    }

    /**
     * @return string
     */
    public function getWidthFormatted()
    {
        if ($this->width >= 10 && $this->width <= 1000) {
            return \Yii::$app->formatter->asDecimal(($this->width / 10))." см";
        } elseif ($this->width >= 1000) {
            return \Yii::$app->formatter->asDecimal(($this->width / 1000))." м";
        } else {
            return \Yii::$app->formatter->asDecimal(($this->width))." мм";
        }
    }

    /**
     * @return string
     */
    public function getHeightFormatted()
    {
        if ($this->height >= 10 && $this->height <= 1000) {
            return \Yii::$app->formatter->asDecimal(($this->height / 10))." см";
        } elseif ($this->width >= 1000) {
            return \Yii::$app->formatter->asDecimal(($this->height / 1000))." м";
        } else {
            return \Yii::$app->formatter->asDecimal(($this->height))." мм";
        }
    }

    /**
     * @return string
     */
    public function getDimensionsFormated()
    {
        if ($this->height && $this->width && $this->length) {
            return \Yii::$app->formatter->asDecimal(($this->length / 10))."x".\Yii::$app->formatter->asDecimal(($this->height / 10))."x".\Yii::$app->formatter->asDecimal(($this->width / 10))." см";
        }

        return '';
    }

    static public function formatExperationTime(int $value)
    {
        if ($value >= 24 && $value < 720) {
            return \Yii::$app->formatter->asDecimal(($value / 24))." дней";
        } elseif ($value >= 720 && $value < 8640) {
            return \Yii::$app->formatter->asDecimal(($value / 720))." месяцев";
        } elseif ($value >= 8640) {
            return \Yii::$app->formatter->asDecimal(($value / 8640))." год";
        } else {
            return \Yii::$app->formatter->asDecimal($value)." часов";
        }
    }
}