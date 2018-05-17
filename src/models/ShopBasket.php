<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 09.10.2015
 */

namespace skeeks\cms\shop\models;

use skeeks\cms\components\Cms;
use skeeks\cms\models\behaviors\HasJsonFieldsBehavior;
use skeeks\cms\models\CmsSite;
use skeeks\cms\models\StorageFile;
use skeeks\cms\relatedProperties\models\RelatedPropertyModel;
use skeeks\modules\cms\catalog\models\Product;
use skeeks\modules\cms\money\Currency;
use skeeks\cms\money\Money;
use Yii;
use yii\base\Exception;
use yii\helpers\Json;
use yii\helpers\Url;

/**
 * This is the model class for table "{{%shop_basket}}".
 *
 * @property integer $id
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $fuser_id
 * @property integer $order_id
 * @property integer $product_id
 * @property integer $product_price_id
 * @property string $price
 * @property string $currency_code
 * @property string $weight
 * @property string $quantity
 * @property integer $site_id
 * @property string $delay
 * @property string $name
 * @property string $can_buy
 * @property string $callback_func
 * @property string $notes
 * @property string $order_callback_func
 * @property string $detail_page_url
 * @property string $discount_price
 * @property string $cancel_callback_func
 * @property string $pay_callback_func
 * @property string $catalog_xml_id
 * @property string $product_xml_id
 * @property string $discount_name
 * @property string $discount_value
 * @property string $discount_coupon
 * @property string $vat_rate
 * @property string $subscribe
 * @property string $barcode_multi
 * @property string $reserved
 * @property double $reserve_quantity
 * @property string $deducted
 * @property string $custom_price
 * @property string $dimensions
 * @property integer $type
 * @property integer $set_parent_id
 * @property string $measure_name
 * @property integer $measure_code
 * @property string $recommendation
 *
 *
 * @property StorageFile $image
 * @property string $url
 * @property string $absoluteUrl
 *
 * @property Currency $currency
 * @property ShopFuser $fuser
 * @property ShopOrder $order
 * @property ShopProduct $product
 * @property ShopProductPrice $productPrice
 * @property CmsSite $site
 * @property ShopBasketProps[] $shopBasketProps
 *
 * @property Money $money
 * @property Money $moneyOriginal
 * @property Money $moneyDiscount
 * @property Money $moneyVat
 */
class ShopBasket extends \skeeks\cms\models\Core
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%shop_basket}}';
    }

    /*public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            HasJsonFieldsBehavior::className() => [
                'class'     => HasJsonFieldsBehavior::className(),
                'fields'    => ['dimensions']
            ]
        ]);
    }*/
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        //$this->on(self::EVENT_BEFORE_INSERT,    [$this, "beforeSaveEvent"]);
        $this->on(self::EVENT_BEFORE_UPDATE, [$this, "beforeSaveEvent"]);

        $this->on(self::EVENT_AFTER_INSERT, [$this, "afterSaveCallback"]);
        $this->on(self::EVENT_AFTER_UPDATE, [$this, "afterSaveCallback"]);
        $this->on(self::EVENT_AFTER_DELETE, [$this, "afterSaveCallback"]);
    }

    public function afterSaveCallback($event)
    {
        //Эта позиция привязана к заказу, после ее обновления нужно обновить заказ целиком
        if ($this->order) {
            $this->order->recalculate()->save();
        }
    }

    /**
     * @param $event
     */
    public function beforeSaveEvent($event)
    {
        if ($this->isAttributeChanged('price')) {
            /*if ( round($this->getAttribute('price'), 4) != round($this->getOldAttribute('price'), 4))
            {
                throw new Exception("TODO: реализовать");
            }*/
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
                    'fuser_id',
                    'order_id',
                    'product_id',
                    'product_price_id',
                    'type',
                    'set_parent_id',
                    'measure_code'
                ],
                'integer'
            ],
            [['name'], 'required'],
            [['price', 'weight', 'quantity', 'discount_price', 'vat_rate', 'reserve_quantity'], 'number'],
            [['quantity'], 'number', 'max' => \Yii::$app->shop->maxQuantity, 'min' => \Yii::$app->shop->minQuantity],
            [['currency_code'], 'string', 'max' => 3],
            [['site_id'], 'integer'],
            [
                ['delay', 'can_buy', 'subscribe', 'barcode_multi', 'reserved', 'deducted', 'custom_price'],
                'string',
                'max' => 1
            ],
            [
                [
                    'name',
                    'callback_func',
                    'notes',
                    'order_callback_func',
                    'detail_page_url',
                    'cancel_callback_func',
                    'pay_callback_func',
                    'discount_name',
                    'dimensions',
                    'recommendation'
                ],
                'string',
                'max' => 255
            ],
            [['catalog_xml_id', 'product_xml_id'], 'string', 'max' => 100],
            [['discount_value', 'discount_coupon'], 'string', 'max' => 32],
            [['measure_name'], 'string', 'max' => 50],

            [['quantity'], 'default', 'value' => 1],
            [['site_id'], 'default', 'value' => \Yii::$app->cms->site->id],
            [['currency_code'], 'default', 'value' => \Yii::$app->money->currencyCode],
            [['price'], 'default', 'value' => 0]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => \Yii::t('skeeks/shop/app', 'ID'),
            'created_by' => \Yii::t('skeeks/shop/app', 'Created By'),
            'updated_by' => \Yii::t('skeeks/shop/app', 'Updated By'),
            'created_at' => \Yii::t('skeeks/shop/app', 'Created At'),
            'updated_at' => \Yii::t('skeeks/shop/app', 'Updated At'),
            'fuser_id' => \Yii::t('skeeks/shop/app', 'Fuser ID'),
            'order_id' => \Yii::t('skeeks/shop/app', 'Order ID'),
            'product_id' => \Yii::t('skeeks/shop/app', 'Product'),
            'product_price_id' => \Yii::t('skeeks/shop/app', 'Product Price ID'),
            'price' => \Yii::t('skeeks/shop/app', 'Price'),
            'currency_code' => \Yii::t('skeeks/shop/app', 'Currency Code'),
            'weight' => \Yii::t('skeeks/shop/app', 'Weight'),
            'quantity' => \Yii::t('skeeks/shop/app', 'Amount'),
            'site_id' => \Yii::t('skeeks/shop/app', 'Site'),
            'delay' => \Yii::t('skeeks/shop/app', 'Delay'),
            'name' => \Yii::t('skeeks/shop/app', 'Name'),
            'can_buy' => \Yii::t('skeeks/shop/app', 'Can Buy'),
            'callback_func' => \Yii::t('skeeks/shop/app', 'Callback Func'),
            'notes' => \Yii::t('skeeks/shop/app', 'Notes'),
            'order_callback_func' => \Yii::t('skeeks/shop/app', 'Order Callback Func'),
            'detail_page_url' => \Yii::t('skeeks/shop/app', 'Detail Page Url'),
            'discount_price' => \Yii::t('skeeks/shop/app', 'Discount Price'),
            'cancel_callback_func' => \Yii::t('skeeks/shop/app', 'Cancel Callback Func'),
            'pay_callback_func' => \Yii::t('skeeks/shop/app', 'Pay Callback Func'),
            'catalog_xml_id' => \Yii::t('skeeks/shop/app', 'Catalog Xml ID'),
            'product_xml_id' => \Yii::t('skeeks/shop/app', 'Product Xml ID'),
            'discount_name' => \Yii::t('skeeks/shop/app', 'Discount Name'),
            'discount_value' => \Yii::t('skeeks/shop/app', 'Discount Value'),
            'discount_coupon' => \Yii::t('skeeks/shop/app', 'Discount Coupon'),
            'vat_rate' => \Yii::t('skeeks/shop/app', 'Vat Rate'),
            'subscribe' => \Yii::t('skeeks/shop/app', 'Subscribe'),
            'barcode_multi' => \Yii::t('skeeks/shop/app', 'Barcode Multi'),
            'reserved' => \Yii::t('skeeks/shop/app', 'Reserved'),
            'reserve_quantity' => \Yii::t('skeeks/shop/app', 'Reserve Quantity'),
            'deducted' => \Yii::t('skeeks/shop/app', 'Deducted'),
            'custom_price' => \Yii::t('skeeks/shop/app', 'Custom Price'),
            'dimensions' => \Yii::t('skeeks/shop/app', 'Dimensions'),
            'type' => \Yii::t('skeeks/shop/app', 'Type'),
            'set_parent_id' => \Yii::t('skeeks/shop/app', 'Set Parent ID'),
            'measure_name' => \Yii::t('skeeks/shop/app', 'Measure Name'),
            'measure_code' => \Yii::t('skeeks/shop/app', 'Measure Code'),
            'recommendation' => \Yii::t('skeeks/shop/app', 'Recommendation'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCurrency()
    {
        return $this->hasOne(Currency::className(), ['code' => 'currency_code']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopBasketProps()
    {
        return $this->hasMany(ShopBasketProps::className(), ['shop_basket_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFuser()
    {
        return $this->hasOne(ShopFuser::className(), ['id' => 'fuser_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrder()
    {
        return $this->hasOne(ShopOrder::className(), ['id' => 'order_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(ShopProduct::className(), ['id' => 'product_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductPrice()
    {
        return $this->hasOne(ShopProductPrice::className(), ['id' => 'product_price_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSite()
    {
        return $this->hasOne(CmsSite::className(), ['id' => 'site_id']);
    }


    /**
     * Итоговая стоимость одной позиции  включая скидки и наценки
     *
     * @return Money
     */
    public function getMoney()
    {
        return new Money((string)$this->price, $this->currency_code);
    }

    /**
     * Итоговая стоимость позиции без скидок и наценок
     * Цена товара в момент укладки товара в корзину
     *
     * @return Money
     */
    public function getMoneyOriginal()
    {
        return new Money((string)($this->price + $this->discount_price), $this->currency_code);
    }


    /**
     * Итоговая стоимость скидки
     * @return Money
     */
    public function getMoneyDiscount()
    {
        return new Money((string)$this->discount_price, $this->currency_code);
    }


    /**
     *
     * Пересчет состояния позиции согласно текущемим данным
     *
     *
     * @return $this
     */
    public function recalculate()
    {
        if (!$this->product) {
            return $this;
        }


        $product = $this->product;
        $parentElement = $product->cmsContentElement->parentContentElement;


        $productPrice = $product->minProductPrice;
        $productPriceMoney = $productPrice->money->convertToCurrency(\Yii::$app->money->getCurrencyObject());

        $this->measure_name = $product->measure->symbol_rus;
        $this->measure_code = $product->measure->code;
        $this->product_price_id = $productPrice->id;
        $this->notes = $productPrice->typePrice->name;

        $this->name = $parentElement ? $parentElement->name : $product->cmsContentElement->name;
        $this->weight = $product->weight;
        $this->site_id = \Yii::$app->cms->site->id; //TODO: неправильно


        $this->dimensions = Json::encode([
            'height' => $product->height,
            'width' => $product->width,
            'length' => $product->length,
        ]);

        //Рассчет налогов
        if ($product->vat && (float)$product->vat->rate > 0) {
            $this->vat_rate = $product->vat->rate;

            if ($product->vat_included == Cms::BOOL_Y) {
                $this->price = $productPriceMoney->getValue();
            } else {
                $this->price = $productPriceMoney->getValue() * $this->vat_rate;
            }

        } else {
            $this->price = $productPriceMoney->getValue();
        }

        $this->currency_code = $productPriceMoney->getCurrency()->getCurrencyCode();


        //Проверка скидок
        /**
         * @var ShopDiscount $shopDiscount
         */
        $shopDiscounts = ShopDiscount::find()
            ->active()
            ->orderBy(['shop_discount.priority' => SORT_ASC])
            ->leftJoin('shop_discount2type_price', 'shop_discount2type_price.discount_id = shop_discount.id')
            ->andWhere([
                'or',
                ['shop_discount.site_id' => ""],
                ['shop_discount.site_id' => null],
                ['shop_discount.site_id' => \Yii::$app->cms->site->id]
            ])
            ->andWhere([
                'shop_discount2type_price.type_price_id' => $this->productPrice->typePrice->id,
            ])
            ->all();


        if ($shopDiscounts) {
            foreach ($shopDiscounts as $key => $shopDiscount) {
                if (!\Yii::$app->user->can($shopDiscount->permissionName)) {
                    unset($shopDiscounts[$key]);
                }
            }
        }

        if ($this->fuser && $this->fuser->discountCoupons) {
            foreach ($this->fuser->discountCoupons as $discountCoupon) {
                $shopDiscounts[] = $discountCoupon->shopDiscount;
            }
        }


        $price = $this->price;
        $this->discount_price = 0;
        $this->discount_value = "";
        $this->discount_name = "";

        if ($shopDiscounts) {
            $discountNames = [];
            $discountPercent = 0;

            foreach ($shopDiscounts as $shopDiscount) {
                $discountNames[] = $shopDiscount->name;

                if ($shopDiscount->value_type == ShopDiscount::VALUE_TYPE_P) {
                    $percent = $shopDiscount->value / 100;
                    $discountPercent = $discountPercent + $percent;

                    $discountPrice = $price * $percent;
                    $this->price = $this->price - $discountPrice;
                    $this->discount_price = $this->discount_price + $discountPrice;

                    //Нужно остановится и не применять другие скидки
                    if ($shopDiscount->last_discount === Cms::BOOL_Y) {
                        break;
                    }
                }
            }

            $this->discount_name = implode(" + ", $discountNames);
            $this->discount_value = \Yii::$app->formatter->asPercent($discountPercent);
        }

        //Если это предложение, нужно добавить свойства
        if ($parentElement && !$this->isNewRecord) {


            if ($product->cmsContentElement->name != $parentElement->name) {
                $basketProperty = new ShopBasketProps();
                $basketProperty->shop_basket_id = $this->id;
                $basketProperty->code = 'name';
                $basketProperty->value = $product->cmsContentElement->name;
                $basketProperty->name = \Yii::t('skeeks/cms', 'name');

                $basketProperty->save();
            }

            if ($properties = $product->cmsContentElement->relatedPropertiesModel->toArray()) {
                foreach ($properties as $code => $value) {
                    if (!$this->getShopBasketProps()->andWhere(['code' => $code])->count() && $value) {
                        $property = $product->cmsContentElement->relatedPropertiesModel->getRelatedProperty($code);

                        $basketProperty = new ShopBasketProps();
                        $basketProperty->shop_basket_id = $this->id;
                        $basketProperty->code = $code;
                        $basketProperty->value = $product->cmsContentElement->relatedPropertiesModel->getSmartAttribute($code);
                        $basketProperty->name = $property->name;

                        $basketProperty->save();
                    }
                }
            }

        }


        return $this;
    }

    /**
     * Значение налога за одну единицу товара
     *
     * @return Money
     */
    public function getMoneyVat()
    {
        if ((float)$this->vat_rate == 0) {
            return new Money("0", $this->currency_code);
        }

        $value = $this->money->getValue();
        $calculateValue = $value - ($value * 100 / 118);

        return new Money((string)$calculateValue, $this->currency_code);
    }


    /**
     * @return string
     */
    public function getUrl()
    {
        if ($this->product) {
            //Это предложение у него есть родительский элемент
            if ($parent = $this->product->cmsContentElement->parentContentElement) {
                return $parent->url;
            } else {
                return $this->product->cmsContentElement->url;
            }
        }

        return $this->detail_page_url;
    }

    /**
     * @return string
     */
    public function getAbsoluteUrl()
    {
        if ($this->product) {
            //Это предложение у него есть родительский элемент
            if ($parent = $this->product->cmsContentElement->parentContentElement) {
                return $parent->absoluteUrl;
            } else {
                return $this->product->cmsContentElement->absoluteUrl;
            }
        }

        return Url::home() . $this->detail_page_url;
    }

    /**
     * @return null|\skeeks\cms\models\CmsStorageFile
     */
    public function getImage()
    {
        if ($this->product) {
            //Это предложение у него есть родительский элемент
            if ($parent = $this->product->cmsContentElement->parentContentElement) {
                return $parent->image;
            } else {
                return $this->product->cmsContentElement->image;
            }
        }

        return null;
    }
}