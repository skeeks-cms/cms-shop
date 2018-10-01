<?php

namespace skeeks\cms\shop\models;

use skeeks\cms\money\models\MoneyCurrency;
use skeeks\cms\money\Money;
use skeeks\modules\cms\money\models\Currency;
use yii\base\Exception;
use yii\console\Application;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%shop_product_price}}".
 *
 * @property integer                  $id
 * @property integer                  $created_by
 * @property integer                  $updated_by
 * @property integer                  $created_at
 * @property integer                  $updated_at
 * @property integer                  $product_id
 * @property integer                  $type_price_id
 * @property string                   $price
 * @property string                   $currency_code
 * @property integer                  $quantity_from
 * @property integer                  $quantity_to
 * @property string                   $tmp_id
 *
 * @property Currency                 $currency
 * @property ShopProduct              $product
 * @property ShopTypePrice            $typePrice
 * @property Money                    $money
 * @property ShopProductPriceChange[] $shopProductPriceChanges
 */
class ShopProductPrice extends \skeeks\cms\models\Core
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%shop_product_price}}';
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->on(self::EVENT_AFTER_INSERT, [$this, "afterInstertCallback"]);
        $this->on(self::EVENT_BEFORE_UPDATE, [$this, "afterUpdateCallback"]);

        $this->on(self::EVENT_AFTER_INSERT, [$this, "afterSaveEvent"]);
        $this->on(self::EVENT_AFTER_UPDATE, [$this, "afterSaveEvent"]);


        $this->on(self::EVENT_BEFORE_INSERT, [$this, '_logPrice']);

    }


    public function _logPrice()
    {
        \Yii::info('Change price'
            .'produt = '.$this->product->id
            .'data = '.print_r($this->toArray(), true)
            .'is console = '.(\Yii::$app instanceof Application ? 1 : 0)
            .'$_POST = '.print_r($_POST, true)
            .'$_GET = '.print_r($_GET, true)
            .'$_REQUEST = '.print_r($_REQUEST, true)
            .'$_SESSION = '.print_r($_SESSION, true)
            .'$_SERVER = '.print_r($_SERVER, true)
            , static::class);
    }


    public function afterSaveEvent()
    {
        //Обновление цены у родительского элемента если она есть
        if ($this->product->cmsContentElement->parent_content_element_id) {

            $parentProduct = $this->product->cmsContentElement->parentContentElement->shopProduct;
            //Если родитель является офером
            $shopParentContent = ShopContent::find()->where(['content_id' => $parentProduct->cmsContentElement->content_id])->one();

            if (!$shopParentContent) {
                return;
            }

            if (!$shopParentContent->children_content_id) {
                return;
            }

            if ($parentProduct && $shopParentContent->children_content_id && $shopParentContent->children_content_id == $this->product->cmsContentElement->content_id) {
                $minPriceValue = $this->price;
                $minPriceCurrency = $this->currency_code;

                //У родительского элемента уже есть предложения
                if ($offers = $parentProduct->tradeOffers) {
                    //Все цены оферов этого типа
                    $minPrice = ShopProductPrice::find()
                        ->where([
                            'product_id' => ArrayHelper::map($offers, 'id', 'id'),
                        ])
                        ->andWhere([
                            'type_price_id' => $this->type_price_id,
                        ])
                        ->orderBy(['price' => SORT_ASC])->one();

                    if ($minPrice) {
                        $minPriceValue = $minPrice->price;
                        $minPriceCurrency = $minPrice->currency_code;
                    }

                }


                $query = $parentProduct->getShopProductPrices()->andWhere([
                    'type_price_id' => $this->type_price_id,
                ]);
                /**
                 * @var $price self
                 */
                if ($price = $query->one()) {
                    $price->price = $minPriceValue;
                    $price->currency_code = $minPriceCurrency;
                    if (!$price->save()) {
                        throw new Exception(print_r($price->errors, true));
                    } else {
                        //var_dump($minPriceValue);die;
                    }
                }

            }

        }
    }

    public function afterInstertCallback()
    {
        $shopProductPriceChange = new ShopProductPriceChange();

        $shopProductPriceChange->price = $this->price;
        $shopProductPriceChange->currency_code = $this->currency_code;
        $shopProductPriceChange->quantity_from = $this->quantity_from;
        $shopProductPriceChange->quantity_to = $this->quantity_to;

        if ($shopProductPriceChange->save()) {
            $shopProductPriceChange->link('shopProductPrice', $this);
        }
    }

    public function afterUpdateCallback()
    {
        if ($this->isAttributeChanged('price') || $this->isAttributeChanged('currency_code') || $this->isAttributeChanged('quantity_from') || $this->isAttributeChanged('quantity_to')) {
            $shopProductPriceChange = new ShopProductPriceChange();

            $shopProductPriceChange->price = $this->price;
            $shopProductPriceChange->currency_code = $this->currency_code;
            $shopProductPriceChange->quantity_from = $this->quantity_from;
            $shopProductPriceChange->quantity_to = $this->quantity_to;

            if ($shopProductPriceChange->save()) {
                $shopProductPriceChange->link('shopProductPrice', $this);
            }
        }

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
                    'product_id',
                    'type_price_id',
                    'quantity_from',
                    'quantity_to',
                ],
                'integer',
            ],
            [['product_id', 'type_price_id'], 'required'],
            [['price'], 'number'],
            [['currency_code'], 'string', 'max' => 3],
            [['tmp_id'], 'string', 'max' => 40],
            [['currency_code'], 'default', 'value' => \Yii::$app->money->currencyCode],
            [['price'], 'default', 'value' => 0.00],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'            => \Yii::t('skeeks/shop/app', 'ID'),
            'created_by'    => \Yii::t('skeeks/shop/app', 'Created By'),
            'updated_by'    => \Yii::t('skeeks/shop/app', 'Updated By'),
            'created_at'    => \Yii::t('skeeks/shop/app', 'Created At'),
            'updated_at'    => \Yii::t('skeeks/shop/app', 'Updated At'),
            'product_id'    => \Yii::t('skeeks/shop/app', 'Product ID'),
            'type_price_id' => \Yii::t('skeeks/shop/app', 'Type Price ID'),
            'price'         => \Yii::t('skeeks/shop/app', 'Price'),
            'currency_code' => \Yii::t('skeeks/shop/app', 'Currency Code'),
            'quantity_from' => \Yii::t('skeeks/shop/app', 'Quantity From'),
            'quantity_to'   => \Yii::t('skeeks/shop/app', 'Quantity To'),
            'tmp_id'        => \Yii::t('skeeks/shop/app', 'Tmp ID'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCurrency()
    {
        return $this->hasOne(MoneyCurrency::class, ['code' => 'currency_code']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(ShopProduct::class, ['id' => 'product_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTypePrice()
    {
        return $this->hasOne(ShopTypePrice::class, ['id' => 'type_price_id']);
    }

    /**
     * @return Money
     */
    public function getMoney()
    {
        return new Money($this->price, $this->currency_code);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopProductPriceChanges()
    {
        return $this->hasMany(ShopProductPriceChange::class,
            ['shop_product_price_id' => 'id'])->orderBy(['created_at' => SORT_DESC]);
    }
}