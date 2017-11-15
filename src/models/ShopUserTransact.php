<?php

namespace skeeks\cms\shop\models;

use skeeks\cms\models\CmsUser;
use skeeks\cms\shop\Module;
use skeeks\modules\cms\money\models\Currency;
use skeeks\modules\cms\money\Money;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%shop_user_transact}}".
 *
 * @property integer $id
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $cms_user_id
 * @property integer $shop_order_id
 * @property string $amount
 * @property string $currency_code
 * @property string $debit
 * @property string $description
 * @property string $notes
 *
 * @property Currency $currency
 * @property CmsUser $cmsUser
 * @property ShopOrder $shopOrder
 *
 * @property string descriptionText
 * @property Money $money
 */
class ShopUserTransact extends \skeeks\cms\models\Core
{
    const ORDER_PAY = "ORDER_PAY";
    const OUT_CHARGE_OFF = "OUT_CHARGE_OFF";

    static public function descriptions()
    {
        return [
            self::ORDER_PAY => \Yii::t('skeeks/shop/app', 'Payment order'),
            self::OUT_CHARGE_OFF => \Yii::t('skeeks/shop/app', 'Deposit money'),
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%shop_user_transact}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'cms_user_id', 'shop_order_id'], 'integer'],
            [['cms_user_id', 'currency_code', 'description'], 'required'],
            [['amount'], 'number'],
            [['notes'], 'string'],
            [['currency_code'], 'string', 'max' => 3],
            [['debit'], 'string', 'max' => 1],
            [['description'], 'string', 'max' => 255]
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
            'cms_user_id' => \Yii::t('skeeks/shop/app', 'Cms User ID'),
            'shop_order_id' => \Yii::t('skeeks/shop/app', 'Shop Order ID'),
            'amount' => \Yii::t('skeeks/shop/app', 'Amount'),
            'currency_code' => \Yii::t('skeeks/shop/app', 'Currency Code'),
            'debit' => \Yii::t('skeeks/shop/app', 'Debit'),
            'description' => \Yii::t('skeeks/shop/app', 'Description'),
            'notes' => \Yii::t('skeeks/shop/app', 'Notes'),
            'descriptionText' => \Yii::t('skeeks/shop/app', 'Description'),
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
    public function getCmsUser()
    {
        return $this->hasOne(CmsUser::className(), ['id' => 'cms_user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopOrder()
    {
        return $this->hasOne(ShopOrder::className(), ['id' => 'shop_order_id']);
    }

    /**
     * Итоговая стоимость заказа
     *
     * @return Money
     */
    public function getMoney()
    {
        return Money::fromString($this->amount, $this->currency_code);
    }

    /**
     * @return string
     */
    public function getDescriptionText()
    {
        return (string)ArrayHelper::getValue(self::descriptions(), $this->description);
    }

}