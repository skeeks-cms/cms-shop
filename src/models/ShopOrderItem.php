<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 09.10.2015
 */

namespace skeeks\cms\shop\models;

use skeeks\cms\base\ActiveRecord;
use skeeks\cms\models\StorageFile;
use skeeks\cms\money\models\MoneyCurrency;
use skeeks\cms\money\Money;
use skeeks\modules\cms\catalog\models\Product;
use yii\base\Exception;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\helpers\Url;

/**
 * This is the model class for table "{{%shop_basket}}".
 *
 * @property integer                 $id
 * @property integer                 $created_by
 * @property integer                 $updated_by
 * @property integer                 $created_at
 * @property integer                 $updated_at
 * @property integer                 $shop_order_id
 * @property integer                 $shop_product_id
 * @property integer                 $shop_product_price_id
 * @property string                  $amount
 * @property string                  $currency_code
 * @property string                  $weight
 * @property string                  $quantity
 * @property string                  $name
 * @property string                  $notes
 * @property string                  $discount_amount
 * @property string                  $discount_name
 * @property string                  $discount_value
 * @property string                  $vat_rate
 * @property double                  $reserve_quantity
 * @property string                  $dimensions
 * @property string                  $measure_name
 * @property integer                 $measure_code
 *
 * ***
 *
 * @property float                   $discount_percent
 * @property float                   $discount_percent_round
 *
 * @property StorageFile             $image
 * @property string                  $url
 * @property string                  $absoluteUrl
 *
 * @property ShopOrder               $shopOrder
 * @property shopProduct             $shopProduct
 * @property shopProductPrice        $shopProductPrice
 *
 * @property MoneyCurrency           $currency
 * @property ShopOrderItemProperty[] $shopOrderItemProperties
 *
 * @property Money                   $money  базовая цена
 * @property Money                   $moneyWithDiscount цена позиции с учетом скидки

 * @property Money                   $moneyDiscount цена скидки
 * @property Money                   $totalMoneyDiscount суммарная скидка
 *
 * @property Money                   $totalMoney итоговая цена
 * @property Money                   $totalMoneyWithDiscount итоговая цена с учетом скидки

 * @property Money                   $moneyVat
 */
class ShopOrderItem extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%shop_order_item}}';
    }

    /*public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            HasJsonFieldsBehavior::class => [
                'class'     => HasJsonFieldsBehavior::class,
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

        $this->on(self::EVENT_AFTER_FIND, function () {
            $this->quantity = (float)$this->quantity;
            $this->discount_amount = (float)$this->discount_amount;
            $this->amount = (float)$this->amount;
        });

        $this->on(self::EVENT_AFTER_INSERT, [$this, "afterSaveCallback"]);
        $this->on(self::EVENT_AFTER_UPDATE, [$this, "afterSaveCallback"]);
        $this->on(self::EVENT_AFTER_DELETE, [$this, "afterSaveCallback"]);
    }

    public function afterSaveCallback($event)
    {
        //Эта позиция привязана к заказу, после ее обновления нужно обновить заказ целиком
        if ($this->shopOrder) {

            $order = $this->shopOrder;
            $order->recalculate();
            try {
                if (!$order->save(false)) {
                    \Yii::error("not saved: " . print_r($this->shopOrder->errors, true));
                    throw new Exception(print_r($this->shopOrder->errors, true));
                } else {
                    //\Yii::error("saved: " . print_r($order->toArray(), true));
                }
            } catch (\Exception $exception) {
                print_r($exception->getMessage());die;
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
                    'shop_order_id',
                    'shop_product_id',
                    'shop_product_price_id',
                    'measure_code',
                ],
                'integer',
            ],
            [['name'], 'required'],
            [['amount', 'weight', 'quantity', 'discount_amount', 'vat_rate', 'reserve_quantity'], 'number'],
            [['quantity'], 'number', 'max' => \Yii::$app->shop->maxQuantity, 'min' => \Yii::$app->shop->minQuantity],
            [['currency_code'], 'string', 'max' => 3],

            [
                [
                    'name',
                    'notes',
                    'discount_name',
                    'dimensions',
                ],
                'string',
                'max' => 255,
            ],
            [['discount_value'], 'string', 'max' => 32],
            [['measure_name'], 'string', 'max' => 50],

            [['quantity'], 'default', 'value' => 1],
            [['currency_code'], 'default', 'value' => \Yii::$app->money->currencyCode],
            [['amount'], 'default', 'value' => 0],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'                    => \Yii::t('skeeks/shop/app', 'ID'),
            'created_by'            => \Yii::t('skeeks/shop/app', 'Created By'),
            'updated_by'            => \Yii::t('skeeks/shop/app', 'Updated By'),
            'created_at'            => \Yii::t('skeeks/shop/app', 'Created At'),
            'updated_at'            => \Yii::t('skeeks/shop/app', 'Updated At'),
            'shop_order_id'         => \Yii::t('skeeks/shop/app', 'Order ID'),
            'shop_product_id'       => \Yii::t('skeeks/shop/app', 'Product'),
            'shop_product_price_id' => \Yii::t('skeeks/shop/app', 'Product Price ID'),
            'amount'                => \Yii::t('skeeks/shop/app', 'Price'),
            'currency_code'         => \Yii::t('skeeks/shop/app', 'Валюта'),
            'weight'                => \Yii::t('skeeks/shop/app', 'Weight'),
            'quantity'              => \Yii::t('skeeks/shop/app', 'Amount'),
            'name'                  => \Yii::t('skeeks/shop/app', 'Name'),
            'notes'                 => \Yii::t('skeeks/shop/app', 'Примечание к цене'),
            'discount_amount'       => \Yii::t('skeeks/shop/app', 'Discount Price'),
            'discount_name'         => \Yii::t('skeeks/shop/app', 'Discount Name'),
            'discount_value'        => \Yii::t('skeeks/shop/app', 'Discount Value'),
            'vat_rate'              => \Yii::t('skeeks/shop/app', 'Vat Rate'),
            'reserve_quantity'      => \Yii::t('skeeks/shop/app', 'Reserve Quantity'),
            'dimensions'            => \Yii::t('skeeks/shop/app', 'Dimensions'),
            'measure_name'          => \Yii::t('skeeks/shop/app', 'Measure Name'),
            'measure_code'          => \Yii::t('skeeks/shop/app', 'Measure Code'),
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
    public function getShopOrder()
    {
        return $this->hasOne(ShopOrder::class, ['id' => 'shop_order_id']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopProduct()
    {
        return $this->hasOne(ShopProduct::class, ['id' => 'shop_product_id']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopProductPrice()
    {
        return $this->hasOne(ShopProductPrice::class, ['id' => 'shop_product_price_id']);
    }

    /**
     * Итоговая стоимость одной позиции  включая скидки и наценки
     *
     * @return Money
     */
    public function getMoney()
    {
        return new Money((string)$this->amount, $this->currency_code);
    }
    /**
     * Итоговая стоимость позиции без скидок и наценок
     * Цена товара в момент укладки товара в корзину
     *
     * TODO: тут ошибка переписать
     * @deprecated
     * @return Money
     */
    public function getMoneyOriginal()
    {
        return new Money((string)($this->amount + $this->discount_amount), $this->currency_code);
    }
    /**
     * Итоговая стоимость скидки
     * @return Money
     */
    public function getMoneyDiscount()
    {
        return new Money((string)$this->discount_amount, $this->currency_code);
    }
    /**
     * Итоговая стоимость скидки
     * @return Money
     */
    public function getTotalMoneyWithDiscount()
    {
        return $this->totalMoney->sub($this->totalMoneyDiscount);
    }
    /**
     * Итоговая стоимость скидки
     * @return Money
     */
    public function getTotalMoneyDiscount()
    {
        return $this->moneyDiscount->mul($this->quantity);
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
        if (!$this->shopProduct) {
            return $this;
        }

        $priceHelper = $this->shopOrder->getProductPriceHelper($this->shopProduct->cmsContentElement);
        /*$priceHelper = new ProductPriceHelper([
            'shopCmsContentElement' => $this->shopProduct->cmsContentElement,
            'shopCart' => $this->cart,
        ]);*/

        $product = $this->shopProduct;

        $parentElement = null;
        if ($product->shopProductWhithOffers) {
            $parentElement = $product->shopProductWhithOffers->cmsContentElement;
        }


        $productPrice = $product->minProductPrice ? $product->minProductPrice : $product->baseProductPrice;
        $productPriceMoney = $productPrice->money->convertToCurrency(\Yii::$app->money->currencyCode);

        $this->measure_name = $product->measure->symbol;
        $this->measure_code = $product->measure->code;
        $this->shop_product_price_id = $productPrice->id;
        $this->notes = $productPrice->typePrice->name;

        $this->name = $product->cmsContentElement->productName;
        //$this->name = $parentElement ? $parentElement->name : $product->cmsContentElement->name;
        $this->weight = $product->weight;


        $this->dimensions = Json::encode([
            'height' => $product->height,
            'width'  => $product->width,
            'length' => $product->length,
        ]);

        $this->currency_code = $productPriceMoney->currency->code;

        //Проверка скидок
        $this->discount_amount = 0;
        $this->discount_value = "";
        $this->discount_name = "";
        $this->amount = $priceHelper->minMoney->convertToCurrency(\Yii::$app->money->currencyCode)->amount;

        if ($priceHelper->hasDiscount) {
            $this->discount_amount = $priceHelper->discountMoney->convertToCurrency(\Yii::$app->money->currencyCode)->amount;
            if ($this->discount_amount) {
                $this->amount = $this->amount + $this->discount_amount;
            }
            $this->discount_name = implode(" + ", ArrayHelper::map($priceHelper->applyedDiscounts, 'id', 'name'));
            $this->discount_value = \Yii::$app->formatter->asPercent($priceHelper->percent);
        }

        //Если это предложение, нужно добавить свойства
        if ($parentElement && !$this->isNewRecord) {

            $element = $product->cmsContentElement;
            $properties = $product->cmsContentElement->relatedPropertiesModel->toArray();
            if ($element->mainCmsContentElement) {
                $element = $element->mainCmsContentElement;
                $properties = $element->relatedPropertiesModel->toArray();
            }

            if ($properties) {
                foreach ($properties as $code => $value) {

                    if (in_array($code, (array)ArrayHelper::map(\Yii::$app->shop->offerCmsContentProperties, "code", 'code'))) {
                        if (!$this->getShopOrderItemProperties()->andWhere(['code' => $code])->count() && $value) {
                            $property = $element->relatedPropertiesModel->getRelatedProperty($code);

                            $val = $element->relatedPropertiesModel->getAttributeAsText($code);
                            if ($property->cmsMeasure) {
                                $val = $val.$property->cmsMeasure->symbol;
                            }

                            $basketProperty = new ShopOrderItemProperty();
                            $basketProperty->shop_order_item_id = $this->id;
                            $basketProperty->code = $code;
                            $basketProperty->value = $val;
                            $basketProperty->name = $property->name;

                            $basketProperty->save();
                        }
                    }
                }
            }
        }


        return $this;
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopOrderItemProperties()
    {
        return $this->hasMany(ShopOrderItemProperty::class, ['shop_order_item_id' => 'id']);
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
        if ($this->shopProduct) {
            //Это предложение у него есть родительский элемент
            if ($parent = $this->shopProduct->shopProductWhithOffers) {
                return $this->shopProduct->cmsContentElement->url;
                //return $parent->cmsContentElement->url;
            } else {
                return $this->shopProduct->cmsContentElement->url;
            }
        }

        return "";
    }

    /**
     * @return string
     */
    public function getAbsoluteUrl()
    {
        if ($this->shopProduct) {
            //Это предложение у него есть родительский элемент
            if ($parent = $this->shopProduct->shopProductWhithOffers) {
                return $parent->cmsContentElement->absoluteUrl;
            } else {
                return $this->shopProduct->cmsContentElement->absoluteUrl;
            }
        }

        return Url::home()."";
    }

    /**
     * @return null|\skeeks\cms\models\CmsStorageFile
     */
    public function getImage()
    {
        if ($this->shopProduct) {
            //Это предложение у него есть родительский элемент
            return $this->shopProduct->cmsContentElement->mainProductImage;
        }

        return null;
    }


    /**
     * @return shopProduct
     * @deprecated
     */
    public function getProduct()
    {
        return $this->shopProduct;
    }

    /**
     * @return ShopOrderItemProperty[]
     * @deprecated
     */
    public function getShopBasketProps()
    {
        return $this->getShopOrderItemProperties();
    }

    /**
     * @return array
     */
    public function extraFields()
    {
        return [
            'itemMoney',
            'itemMoneyWithDiscount',

            'itemDiscountMoney',
            'itemTotalDiscountMoney',

            'itemTotalMoney',
            'itemTotalMoneyWithDiscount',

            'discount_percent',
            'discount_percent_round',
        ];
    }

    /**
     * @return array
     */
    public function getItemMoney()
    {
        return $this->money->jsonSerialize();
    }

    /**
     * @return array
     */
    public function getItemMoneyWithDiscount()
    {
        return $this->moneyWithDiscount->jsonSerialize();
    }

    /**
     * @return array
     */
    public function getItemTotalMoneyWithDiscount()
    {
        return $this->totalMoneyWithDiscount->jsonSerialize();
    }

    /**
     * Цена с учетом скидки
     * @return Money
     */
    public function getMoneyWithDiscount()
    {
        return $this->money->sub($this->moneyDiscount);
    }
    /**
     * @return array
     */
    public function getItemTotalMoney()
    {
        return $this->totalMoney->jsonSerialize();
    }

    /**
     * @return array
     */
    public function getTotalMoney()
    {
        return $this->money->mul($this->quantity);
    }
    /**
     * @return array
     */
    public function getItemDiscountMoney()
    {
        return $this->moneyDiscount->jsonSerialize();
    }
    /**
     * @return array
     */
    public function getItemTotalDiscountMoney()
    {
        return $this->totalMoneyDiscount->jsonSerialize();
    }

    /**
     * @return array
     */
    public function getDiscount_percent()
    {
        $percent = 0;

        if ($this->discount_amount) {
            $percent = $this->discount_amount * 100 / $this->amount;
        }

        return $percent;
    }

    /**
     * @return array
     */
    public function getDiscount_percent_round()
    {
        return round($this->discount_percent, 2);
    }

}