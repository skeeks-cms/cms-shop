<?php

namespace skeeks\cms\shop\models;

use skeeks\cms\base\ActiveRecord;
use skeeks\cms\models\CmsLead;
use yii\base\Exception;
use yii\helpers\ArrayHelper;

/** Financial result of a partner lead. The lead workflow belongs to skeeks/cms. */
class ShopPartnerLead extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%shop_partner_lead}}';
    }

    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['cms_lead_id', 'shop_bonus_transaction_id'], 'integer'],
            [['cms_lead_id', 'reward_value'], 'required'],
            [['cms_lead_id'], 'unique'],
            [['reward_value'], 'number', 'min' => 0.01],
            [['cms_lead_id'], 'exist', 'targetClass' => CmsLead::class, 'targetAttribute' => 'id'],
            [['shop_bonus_transaction_id'], 'exist', 'skipOnEmpty' => true, 'targetClass' => ShopBonusTransaction::class, 'targetAttribute' => 'id'],
        ]);
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        if (!$insert || $this->shop_bonus_transaction_id) {
            return true;
        }

        $lead = $this->cmsLead;
        if (!$lead || !$lead->partner_id || $lead->status !== CmsLead::STATUS_SUCCESS) {
            throw new Exception('Начисление возможно только по успешному партнёрскому лиду.');
        }

        $transaction = new ShopBonusTransaction();
        $transaction->cms_site_id = $lead->cms_site_id;
        $transaction->cms_user_id = $lead->partner_id;
        $transaction->is_debit = 0;
        $transaction->value = (float)$this->reward_value;
        $transaction->comment = 'Партнёрская программа: вознаграждение по лиду «'.$lead->name.'»';
        if (!$transaction->save()) {
            throw new Exception('Не удалось начислить бонусы: '.print_r($transaction->errors, true));
        }
        $this->shop_bonus_transaction_id = $transaction->id;

        return true;
    }

    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'cms_lead_id' => 'Лид',
            'reward_value' => 'Вознаграждение, бонусов',
            'shop_bonus_transaction_id' => 'Транзакция начисления',
        ]);
    }

    public function getCmsLead()
    {
        return $this->hasOne(CmsLead::class, ['id' => 'cms_lead_id']);
    }

    public function getBonusTransaction()
    {
        return $this->hasOne(ShopBonusTransaction::class, ['id' => 'shop_bonus_transaction_id']);
    }
}
