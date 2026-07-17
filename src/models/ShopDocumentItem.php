<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 */

namespace skeeks\cms\shop\models;

use skeeks\cms\models\behaviors\HasJsonFieldsBehavior;
use skeeks\cms\money\models\MoneyCurrency;
use skeeks\cms\money\Money;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%shop_document_item}}".
 *
 * @property int           $id
 * @property int|null      $created_by
 * @property int|null      $updated_by
 * @property int|null      $created_at
 * @property int|null      $updated_at
 * @property int           $shop_document_id
 * @property int|null      $shop_product_id
 * @property int|null      $source_shop_bill_id
 * @property int|null      $source_shop_bill_item_id
 * @property string        $name
 * @property string|null   $measure_name
 * @property string        $quantity
 * @property string        $price
 * @property string        $amount
 * @property string        $discount_amount
 * @property string|null   $discount_value
 * @property string|null   $discount_name
 * @property string        $currency_code
 * @property string|null   $vat_name
 * @property array|null    $extra_data
 * @property int|null      $sort
 *
 * @property ShopDocument  $document
 * @property ShopProduct   $shopProduct
 * @property ShopBill      $sourceShopBill
 * @property ShopBillItem  $sourceShopBillItem
 * @property MoneyCurrency $currency
 * @property Money         $money
 * @property Money         $priceMoney
 */
class ShopDocumentItem extends \skeeks\cms\base\ActiveRecord
{
    public static function tableName()
    {
        return '{{%shop_document_item}}';
    }

    public function init()
    {
        parent::init();

        $this->on(self::EVENT_AFTER_FIND, function () {
            $this->quantity = (float)$this->quantity;
            $this->price = (float)$this->price;
            $this->amount = (float)$this->amount;
            $this->discount_amount = (float)$this->discount_amount;
        });

        $this->on(self::EVENT_BEFORE_INSERT, [$this, 'recalculate']);
        $this->on(self::EVENT_BEFORE_UPDATE, [$this, 'recalculate']);
    }

    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            HasJsonFieldsBehavior::class => [
                'class'  => HasJsonFieldsBehavior::class,
                'fields' => ['extra_data'],
            ],
        ]);
    }

    public function rules()
    {
        return [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'shop_document_id', 'shop_product_id', 'source_shop_bill_id', 'source_shop_bill_item_id', 'sort'], 'integer'],
            [['shop_document_id', 'name'], 'required'],
            [['quantity', 'price', 'amount', 'discount_amount'], 'number'],
            [['extra_data'], 'safe'],
            [['name'], 'string', 'max' => 255],
            [['discount_name'], 'string', 'max' => 255],
            [['discount_value'], 'string', 'max' => 32],
            [['measure_name'], 'string', 'max' => 50],
            [['currency_code'], 'string', 'max' => 3],
            [['vat_name'], 'string', 'max' => 32],
            [['quantity'], 'default', 'value' => 1],
            [['price', 'amount', 'discount_amount'], 'default', 'value' => 0],
            [['discount_value', 'discount_name'], 'default', 'value' => null],
            [['measure_name'], 'default', 'value' => 'шт'],
            [['currency_code'], 'default', 'value' => \Yii::$app->money->currencyCode],
            [['vat_name'], 'default', 'value' => 'Без НДС'],
            [['shop_document_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopDocument::class, 'targetAttribute' => ['shop_document_id' => 'id']],
            [['shop_product_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopProduct::class, 'targetAttribute' => ['shop_product_id' => 'id']],
            [['source_shop_bill_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopBill::class, 'targetAttribute' => ['source_shop_bill_id' => 'id']],
            [['source_shop_bill_item_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopBillItem::class, 'targetAttribute' => ['source_shop_bill_item_id' => 'id']],
            [['currency_code'], 'exist', 'skipOnError' => true, 'targetClass' => MoneyCurrency::class, 'targetAttribute' => ['currency_code' => 'code']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id'               => \Yii::t('skeeks/shop/app', 'ID'),
            'shop_document_id' => \Yii::t('skeeks/shop/app', 'Документ'),
            'shop_product_id'  => \Yii::t('skeeks/shop/app', 'Товар или услуга'),
            'source_shop_bill_id' => \Yii::t('skeeks/shop/app', 'Исходный счет'),
            'source_shop_bill_item_id' => \Yii::t('skeeks/shop/app', 'Исходная позиция счета'),
            'name'             => \Yii::t('skeeks/shop/app', 'Наименование'),
            'measure_name'     => \Yii::t('skeeks/shop/app', 'Ед. изм.'),
            'quantity'         => \Yii::t('skeeks/shop/app', 'Кол-во'),
            'price'            => \Yii::t('skeeks/shop/app', 'Цена'),
            'amount'           => \Yii::t('skeeks/shop/app', 'Сумма'),
            'discount_amount'  => \Yii::t('skeeks/shop/app', 'Скидка'),
            'discount_value'   => \Yii::t('skeeks/shop/app', 'Скидка'),
            'discount_name'    => \Yii::t('skeeks/shop/app', 'Название скидки'),
            'currency_code'    => \Yii::t('skeeks/shop/app', 'Валюта'),
            'vat_name'         => \Yii::t('skeeks/shop/app', 'НДС'),
            'extra_data'       => \Yii::t('skeeks/shop/app', 'Специфичные данные позиции'),
            'sort'             => \Yii::t('skeeks/shop/app', 'Сортировка'),
        ];
    }

    public function recalculate($event = null)
    {
        $this->quantity = (float)$this->quantity ?: 1;
        $this->price = (float)$this->price;
        $baseAmount = max($this->quantity * $this->price, 0);
        $this->discount_amount = min(max((float)$this->discount_amount, 0), $baseAmount);
        $this->amount = round($baseAmount - $this->discount_amount, 4);
    }

    public function getDocument()
    {
        return $this->hasOne(ShopDocument::class, ['id' => 'shop_document_id']);
    }

    public function getShopProduct()
    {
        return $this->hasOne(ShopProduct::class, ['id' => 'shop_product_id']);
    }

    public function getSourceShopBill()
    {
        return $this->hasOne(ShopBill::class, ['id' => 'source_shop_bill_id']);
    }

    public function getSourceShopBillItem()
    {
        return $this->hasOne(ShopBillItem::class, ['id' => 'source_shop_bill_item_id']);
    }

    public function getCurrency()
    {
        return $this->hasOne(MoneyCurrency::class, ['code' => 'currency_code']);
    }

    public function getMoney()
    {
        return new Money($this->amount, (string)$this->currency_code);
    }

    public function getPriceMoney()
    {
        return new Money($this->price, (string)$this->currency_code);
    }

    public function getDiscountMoney()
    {
        return new Money($this->discount_amount, (string)$this->currency_code);
    }

    public function getAmountWithoutDiscount()
    {
        return round(((float)$this->quantity ?: 1) * (float)$this->price, 4);
    }

    /**
     * Unit price represented by the final line amount after discount.
     * This keeps printed and electronic document totals arithmetically equal.
     *
     * @return float
     */
    public function getUnitPriceAfterDiscount()
    {
        $quantity = (float)$this->quantity;

        return $quantity > 0
            ? round((float)$this->amount / $quantity, 4)
            : (float)$this->price;
    }

    public function asArray()
    {
        return [
            'id'                       => $this->id,
            'shop_product_id'          => $this->shop_product_id,
            'source_shop_bill_id'      => $this->source_shop_bill_id,
            'source_shop_bill_item_id' => $this->source_shop_bill_item_id,
            'name'                     => $this->name,
            'measure_name'             => $this->measure_name,
            'quantity'                 => $this->quantity,
            'price'                    => $this->price,
            'amount'                   => $this->amount,
            'discount_amount'          => $this->discount_amount,
            'discount_value'           => $this->discount_value,
            'discount_name'            => $this->discount_name,
            'currency_code'            => $this->currency_code,
            'vat_name'                 => $this->vat_name,
            'extra_data'               => $this->extra_data,
        ];
    }
}
