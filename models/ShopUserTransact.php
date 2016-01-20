<?php

namespace skeeks\cms\shop\models;

use skeeks\cms\models\CmsUser;
use skeeks\modules\cms\money\models\Currency;
use Yii;

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
 */
class ShopUserTransact extends \skeeks\cms\models\Core
{
    const ORDER_PAY         = "ORDER_PAY";
    const OUT_CHARGE_OFF    = "OUT_CHARGE_OFF";
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
            'id'                => \skeeks\cms\shop\Module::t('app', 'ID'),
            'created_by'        => \skeeks\cms\shop\Module::t('app', 'Created By'),
            'updated_by'        => \skeeks\cms\shop\Module::t('app', 'Updated By'),
            'created_at'        => \skeeks\cms\shop\Module::t('app', 'Created At'),
            'updated_at'        => \skeeks\cms\shop\Module::t('app', 'Updated At'),
            'cms_user_id'       => \skeeks\cms\shop\Module::t('app', 'Cms User ID'),
            'shop_order_id'     => \skeeks\cms\shop\Module::t('app', 'Shop Order ID'),
            'amount'            => \skeeks\cms\shop\Module::t('app', 'Amount'),
            'currency_code'     => \skeeks\cms\shop\Module::t('app', 'Currency Code'),
            'debit'             => \skeeks\cms\shop\Module::t('app', 'Debit'),
            'description'       => \skeeks\cms\shop\Module::t('app', 'Description'),
            'notes'             => \skeeks\cms\shop\Module::t('app', 'Notes'),
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
}