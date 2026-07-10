<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 */

namespace skeeks\cms\shop\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * Link between accounting documents and bills.
 *
 * @property int          $id
 * @property int|null     $created_by
 * @property int|null     $created_at
 * @property int          $shop_document_id
 * @property int          $shop_bill_id
 *
 * @property ShopDocument $document
 * @property ShopBill     $bill
 */
class ShopDocument2bill extends \skeeks\cms\base\ActiveRecord
{
    public static function tableName()
    {
        return '{{%shop_document2bill}}';
    }

    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['shop_document_id', 'shop_bill_id'], 'integer'],
            [['shop_document_id', 'shop_bill_id'], 'required'],
            [['shop_document_id', 'shop_bill_id'], 'unique', 'targetAttribute' => ['shop_document_id', 'shop_bill_id']],
            [['shop_document_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopDocument::class, 'targetAttribute' => ['shop_document_id' => 'id']],
            [['shop_bill_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopBill::class, 'targetAttribute' => ['shop_bill_id' => 'id']],
        ]);
    }

    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'shop_document_id' => Yii::t('skeeks/shop/app', 'Документ'),
            'shop_bill_id'     => Yii::t('skeeks/shop/app', 'Счет'),
        ]);
    }

    public function getDocument()
    {
        return $this->hasOne(ShopDocument::class, ['id' => 'shop_document_id']);
    }

    public function getBill()
    {
        return $this->hasOne(ShopBill::class, ['id' => 'shop_bill_id']);
    }
}
