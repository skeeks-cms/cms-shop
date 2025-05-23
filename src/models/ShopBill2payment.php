<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */

namespace skeeks\cms\shop\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "crm_client_map".
 *
 * @property int         $id
 * @property int         $created_by
 * @property int         $created_at
 * @property int         $shop_bill_id Сделка
 * @property int         $shop_payment_id Платеж
 *
 * @property ShopBill    $bill
 * @property ShopPayment $payment
 */
class ShopBill2payment extends \skeeks\cms\base\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%shop_bill2payment}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['shop_bill_id', 'shop_payment_id'], 'integer'],
            [['shop_bill_id', 'shop_payment_id'], 'required'],
            [['shop_bill_id', 'shop_payment_id'], 'unique', 'targetAttribute' => ['shop_bill_id', 'shop_payment_id']],
        ]);
    }


    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'shop_payment_id' => Yii::t('app', 'Платеж'),
            'shop_bill_id'    => Yii::t('app', 'Счет'),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeHints()
    {
        return ArrayHelper::merge(parent::attributeHints(), [
        ]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBill()
    {
        return $this->hasOne(ShopBill::class, ['id' => 'shop_bill_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPayment()
    {
        return $this->hasOne(ShopPayment::class, ['id' => 'shop_payment_id']);
    }
}