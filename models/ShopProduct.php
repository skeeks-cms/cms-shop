<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 14.09.2015
 */
namespace skeeks\cms\shop\models;

use skeeks\cms\components\Cms;
use skeeks\cms\measure\models\Measure;
use skeeks\cms\models\CmsContentElement;
use skeeks\modules\cms\money\models\Currency;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%shop_product}}".
 *
 * @property integer $id
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $created_at
 * @property integer $updated_at
 * @property double $quantity
 * @property string $quantity_trace
 * @property double $weight
 * @property string $price_type
 * @property string $measure_ratio
 * @property integer $recur_scheme_length
 * @property string $recur_scheme_type
 * @property integer $trial_price_id
 * @property string $without_order
 * @property string $select_best_price
 * @property integer $vat_id
 * @property string $vat_included
 * @property string $tmp_id
 * @property string $can_buy_zero
 * @property string $negative_amount_trace
 * @property string $barcode_multi
 * @property string $purchasing_price
 * @property string $purchasing_currency
 * @property double $quantity_reserved
 * @property integer $measure_id
 * @property double $width
 * @property double $length
 * @property double $height
 * @property string $subscribe
 * @property string $product_type
 *
 * @property Measure                $measure
 * @property ShopCmsContentElement  $cmsContentElement
 * @property ShopTypePrice      $trialPrice
 * @property ShopVat            $vat
 * @property Currency           $purchasingCurrency
 * @property ShopProductPrice[] $shopProductPrices
 * @property ShopViewedProduct[] $shopViewedProducts
 *
 * @property string   $baseProductPriceValue
 * @property string   $baseProductPriceCurrency
 *
 * @property ShopProductPrice   $baseProductPrice
 * @property ShopProductPrice   $minProductPrice
 * @property ShopProductPrice[]   $viewProductPrices
 * @property ShopProductQuantityChange[] $shopProductQuantityChanges
 *
 * @property ShopCmsContentElement   $tradeOffers
 */
class ShopProduct extends \skeeks\cms\models\Core
{
    const TYPE_SIMPLE = 'simple';
    const TYPE_OFFERS = 'offers';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%shop_product}}';
    }

    /**
     * @return array
     */
    static public function possibleProductTypes()
    {
        return [
            static::TYPE_SIMPLE => \Yii::t('skeeks/shop/app','Plain'),
            static::TYPE_OFFERS => \Yii::t('skeeks/shop/app','With quotations'),
        ];
    }

    static public $instances = [];

    /**
     * @param CmsContentElement $cmsContentElement
     * @return static
     */
    static public function getInstanceByContentElement(CmsContentElement $cmsContentElement)
    {
        if ($self = ArrayHelper::getValue(static::$instances, $cmsContentElement->id))
        {
            return $self;
        }

        /**
         * @version SkeekS CMS > 2.6.0
         */
        if ($cmsContentElement instanceof \skeeks\cms\shop\models\ShopCmsContentElement)
        {
            static::$instances[$cmsContentElement->id] = $cmsContentElement->shopProduct;
            return $cmsContentElement->shopProduct;
        }

        if (!$self = static::find()->where(['id' => $cmsContentElement->id])->one())
        {
            $self = new static([
                'id' => $cmsContentElement->id
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

        $this->on(self::EVENT_BEFORE_INSERT,    [$this, "_beforeSaveEvent"]);
        $this->on(self::EVENT_BEFORE_UPDATE,    [$this, "_beforeSaveEvent"]);

        $this->on(self::EVENT_AFTER_INSERT,    [$this, "_afterSaveEvent"]);
        $this->on(self::EVENT_AFTER_UPDATE,    [$this, "_afterSaveEvent"]);

        $this->on(self::EVENT_AFTER_INSERT,    [$this, "_updateParentAfterInsert"]);

        $this->on(self::EVENT_AFTER_INSERT,    [$this, "_logQuantityInsert"]);
        $this->on(self::EVENT_BEFORE_UPDATE,    [$this, "_logQuantityUpdate"]);
    }

    public function _logQuantityInsert($event)
    {
        $log                            = new ShopProductQuantityChange();

        $log->shop_product_id           = $this->id;
        $log->quantity                  = $this->quantity;
        $log->quantity_reserved         = $this->quantity_reserved;
        $log->measure_id                = $this->measure_id;
        $log->measure_ratio             = $this->measure_ratio;

        $log->save();
    }

    public function _logQuantityUpdate($event)
    {
        if (
            $this->isAttributeChanged('quantity', false)
            || $this->isAttributeChanged('quantity_reserved', false)
            || $this->isAttributeChanged('measure_id', false)
            || $this->isAttributeChanged('measure_ratio', false)
        )
        {
            $log                            = new ShopProductQuantityChange();

            $log->shop_product_id           = $this->id;
            $log->quantity                  = $this->quantity;
            $log->quantity_reserved         = $this->quantity_reserved;
            $log->measure_id                = $this->measure_id;
            $log->measure_ratio             = $this->measure_ratio;

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
        if ($this->isAttributeChanged('product_type'))
        {
            //Выставили что у него есть предложения
            if ($this->product_type == self::TYPE_OFFERS)
            {
                if (!$this->getTradeOffers()->all())
                {
                    $this->product_type = self::TYPE_SIMPLE;
                }
            } else if ($this->product_type == self::TYPE_SIMPLE) //Если указали что товар простой, значит у него не должно быть предложений
            {
                if ($this->getTradeOffers()->all())
                {
                    $this->product_type = self::TYPE_OFFERS;
                }
            }
        }
    }

    /**
     * После сохранения следим за ценами создаем если нет
     * @param $event
     */
    public function _afterSaveEvent($event)
    {
        //Prices update
        if ($this->_baseProductPriceCurrency || $this->_baseProductPriceValue)
        {
            /**
             * @var $baseProductPrice ShopProductPrice
             */
            $baseProductPrice = $this->getBaseProductPrice()->one();

            if (!$baseProductPrice)
            {
                $baseProductPrice                   = new ShopProductPrice([
                    'product_id' => $this->id
                ]);

                $baseProductPrice->type_price_id    = \Yii::$app->shop->baseTypePrice->id;

                if ($this->_baseProductPriceValue)
                {
                    $baseProductPrice->price            = $this->_baseProductPriceValue;
                }

                if ($this->_baseProductPriceCurrency)
                {
                    $baseProductPrice->currency_code            = $this->_baseProductPriceCurrency;
                }

                $baseProductPrice->save();
            } else
            {
                $isChanged = false;
                //Установка и сохранение только если что то изменилось
                if ($this->_baseProductPriceValue && $this->_baseProductPriceValue != $baseProductPrice->price)
                {
                    $baseProductPrice->price            = $this->_baseProductPriceValue;
                    $isChanged = true;
                }

                if ($this->_baseProductPriceCurrency && $this->_baseProductPriceCurrency != $baseProductPrice->currency_code)
                {
                    $baseProductPrice->currency_code            = $this->_baseProductPriceCurrency;
                    $isChanged = true;
                }

                if ($isChanged)
                {
                    $baseProductPrice->save();
                }
            }


        } else
        {
            if (!$this->baseProductPrice)
            {
                $baseProductPrice                   = new ShopProductPrice([
                    'product_id' => $this->id
                ]);

                $baseProductPrice->type_price_id    = \Yii::$app->shop->baseTypePrice->id;
                $baseProductPrice->save();
            }
        }
    }


    /**
     * Если втавленный элемент является дочерним для другого то родительскому нужно изменить тип
     * @param $event
     */
    public function _updateParentAfterInsert($event)
    {
        //Если есть родительский элемент
        if ($this->cmsContentElement->parent_content_element_id)
        {
            $parentProduct = $this->cmsContentElement->parentContentElement->shopProduct;
            $parentProduct->setAttribute('product_type', self::TYPE_OFFERS);
            $parentProduct->save();
        }
    }






    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'recur_scheme_length', 'trial_price_id', 'vat_id', 'measure_id'], 'integer'],
            [['quantity', 'weight', 'purchasing_price', 'quantity_reserved', 'width', 'length', 'height', 'measure_ratio'], 'number'],
            [['quantity_trace', 'price_type', 'recur_scheme_type', 'without_order', 'select_best_price', 'vat_included', 'can_buy_zero', 'negative_amount_trace', 'barcode_multi', 'subscribe'], 'string', 'max' => 1],
            [['tmp_id'], 'string', 'max' => 40],
            [['purchasing_currency'], 'string', 'max' => 3],
            [['quantity_trace', 'can_buy_zero', 'negative_amount_trace'], 'default', 'value' => Cms::BOOL_N],
            [['weight', 'width', 'length', 'height', 'purchasing_price'], 'default', 'value' => 0],
            [['subscribe'], 'default', 'value' => Cms::BOOL_Y],
            [['measure_ratio'], 'default', 'value' => 1],
            [['measure_ratio'], 'number', 'min' => 0.0001, 'max' => 9999999],
            [['purchasing_currency'], 'default', 'value' => Yii::$app->money->currencyCode],

            [['baseProductPriceValue'], 'number'],
            [['baseProductPriceCurrency'], 'string', 'max' => 3],

            [['vat_included'], 'default', 'value' => Cms::BOOL_Y],
            [['measure_id'], 'default', 'value' => function()
            {
                return (int) Measure::find()->def()->one()->id;
            }],

            [['product_type'], 'string', 'max' => 10],
            [['product_type'], 'default', 'value' => static::TYPE_SIMPLE],

            [['quantity'], 'default', 'value' => 1],
            [['quantity_reserved'], 'default', 'value' => 0],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'                        => \Yii::t('skeeks/shop/app', 'ID'),
            'created_by'                => \Yii::t('skeeks/shop/app', 'Created By'),
            'updated_by'                => \Yii::t('skeeks/shop/app', 'Updated By'),
            'created_at'                => \Yii::t('skeeks/shop/app', 'Created At'),
            'updated_at'                => \Yii::t('skeeks/shop/app', 'Updated At'),
            'quantity'                  => \Yii::t('skeeks/shop/app', 'Available quantity'),
            'quantity_trace'            => \Yii::t('skeeks/shop/app', 'Include quantitative account'),
            'weight'                    => \Yii::t('skeeks/shop/app', 'Weight (gramm)'),
            'price_type'                => \Yii::t('skeeks/shop/app', 'Price Type'),
            'recur_scheme_length'       => \Yii::t('skeeks/shop/app', 'Recur Scheme Length'),
            'recur_scheme_type'         => \Yii::t('skeeks/shop/app', 'Recur Scheme Type'),
            'trial_price_id'            => \Yii::t('skeeks/shop/app', 'Trial Price ID'),
            'without_order'             => \Yii::t('skeeks/shop/app', 'Without Order'),
            'select_best_price'         => \Yii::t('skeeks/shop/app', 'Select Best Price'),
            'vat_id'                    => \Yii::t('skeeks/shop/app', 'VAT rate'),
            'vat_included'              => \Yii::t('skeeks/shop/app', 'VAT included in the price'),
            'tmp_id'                    => \Yii::t('skeeks/shop/app', 'Tmp ID'),
            'can_buy_zero'              => \Yii::t('skeeks/shop/app', 'Allow purchase if product is absent'),
            'negative_amount_trace'     => \Yii::t('skeeks/shop/app', 'Allow negative quantity'),
            'barcode_multi'             => \Yii::t('skeeks/shop/app', 'Barcode Multi'),
            'purchasing_price'          => \Yii::t('skeeks/shop/app', 'Purchase price'),
            'purchasing_currency'       => \Yii::t('skeeks/shop/app', 'Currency purchase price'),
            'quantity_reserved'         => \Yii::t('skeeks/shop/app', 'Reserved quantity'),
            'measure_id'                => \Yii::t('skeeks/shop/app', 'Unit of measurement'),
            'measure_ratio'             => \Yii::t('skeeks/shop/app', 'The coefficient unit'),
            'width'                     => \Yii::t('skeeks/shop/app', 'Width (mm)'),
            'length'                    => \Yii::t('skeeks/shop/app', 'Length (mm)'),
            'height'                    => \Yii::t('skeeks/shop/app', 'Height (mm)'),
            'subscribe'                 => \Yii::t('skeeks/shop/app', 'Allow subscription without explanation'),
            'product_type'              => \Yii::t('skeeks/shop/app', 'Product type'),
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
        if ($this->isNewRecord)
        {
            return false;
        }

        $shopViewdProduct                   = new ShopViewedProduct();
        $shopViewdProduct->name             = $this->cmsContentElement->name;
        $shopViewdProduct->shop_product_id  = $this->id;
        $shopViewdProduct->site_id          = \Yii::$app->cms->site->id;
        $shopViewdProduct->shop_fuser_id    = \Yii::$app->shop->shopFuser->id;

        return $shopViewdProduct->save();
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMeasure()
    {
        return $this->hasOne(Measure::className(), ['id' => 'measure_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCmsContentElement()
    {
        return $this->hasOne(ShopCmsContentElement::className(), ['id' => 'id']);
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTrialPrice()
    {
        return $this->hasOne(ShopTypePrice::className(), ['id' => 'trial_price_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVat()
    {
        return $this->hasOne(ShopVat::className(), ['id' => 'vat_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPurchasingCurrency()
    {
        return $this->hasOne(Currency::className(), ['code' => 'purchasing_currency']);
    }
    


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopProductPrices()
    {
        return $this->hasMany(ShopProductPrice::className(), ['product_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopViewedProducts()
    {
        return $this->hasMany(ShopViewedProduct::className(), ['shop_product_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopProductQuantityChanges()
    {
        return $this->hasMany(ShopProductQuantityChange::className(), ['shop_product_id' => 'id'])->orderBy(['created_at' => SORT_DESC]);
    }


    /**
     *
     * Базовая цена по умолчанию
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBaseProductPrice()
    {
        return $this->hasOne(ShopProductPrice::className(), [
            'product_id' => 'id'
        ])->andWhere(['type_price_id' => \Yii::$app->shop->baseTypePrice->id]);
    }

    /**
     * Цены доступные к просмотру
     *
     * @param null $shopFuser
     * @return $this
     */
    public function getViewProductPrices($shopFuser = null)
    {
        if ($shopFuser === null)
        {
            $shopFuser = \Yii::$app->shop->shopFuser;
        }

        return $this->hasMany(ShopProductPrice::className(), [
            'product_id' => 'id'
        ])->andWhere(['type_price_id' => ArrayHelper::map($shopFuser->viewTypePrices, 'id', 'id')] )->orderBy(['price' => SORT_ASC]);
    }

    /**
     *
     * Лучшая цена по которой может купить этот товар пользователь, среди всех доступных
     * Операемся на курс
     *
     * @param null $shopFuser
     * @return $this
     */
    public function getMinProductPrice($shopFuser = null)
    {
        if ($shopFuser === null)
        {
            $shopFuser = \Yii::$app->shop->shopFuser;
        }


        return $this->hasOne(ShopProductPrice::className(), [
            'product_id' => 'id'
        ])
            ->select(['shop_product_price.*', 'realPrice' => '( (SELECT course FROM `money_currency` WHERE `money_currency`.`code` = `shop_product_price`.`currency_code`) * `shop_product_price`.`price` )'])
            ->leftJoin('money_currency', '`money_currency`.`code` = `shop_product_price`.`currency_code`')

            ->orWhere([
                'and',
                ['>', 'price', 0],
                ['type_price_id' => ArrayHelper::map($shopFuser->buyTypePrices, 'id', 'id')]
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
        if ($this->_baseProductPriceValue)
        {
            return $this->_baseProductPriceValue;
        } else
        {
            $this->_baseProductPriceValue = $this->baseProductPrice ? $this->baseProductPrice->price : null;
        }

        return $this->_baseProductPriceValue;
    }

    /**
     * Валюта базовой цены
     *
     * @return string
     */
    public function getBaseProductPriceCurrency()
    {
        if ($this->_baseProductPriceCurrency)
        {
            return $this->_baseProductPriceCurrency;
        } else
        {
            $this->_baseProductPriceCurrency = $this->baseProductPrice ? $this->baseProductPrice->currency_code : \Yii::$app->money->currencyCode;
        }

        return $this->_baseProductPriceCurrency;
    }



    private $_baseProductPriceValue     = null;
    private $_baseProductPriceCurrency  = null;

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
     * @param $value
     * @return $this
     */
    public function setBaseProductPriceCurrency($value)
    {
        $this->_baseProductPriceCurrency = $value;
        return $this;
    }


    /**
     * Товар с предложениями?
     * @return bool
     */
    public function isTradeOffers()
    {
        return (bool) ($this->product_type == \skeeks\cms\shop\models\ShopProduct::TYPE_OFFERS);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTradeOffers()
    {
        return $this->hasMany(ShopCmsContentElement::className(), ['parent_content_element_id' => 'id'])->orderBy(['priority' => SORT_ASC]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductOffers()
    {
       /* return $this->hasMany(static::className(), ['parent_content_element_id' => 'id'])
            ->via('cmsContentElement')
            ->orderBy(['priority' => SORT_ASC]);*/
    }
}