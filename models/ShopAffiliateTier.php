<?php

namespace skeeks\cms\shop\models;

use skeeks\cms\models\CmsSite;
use Yii;

/**
 * This is the model class for table "{{%shop_affiliate_tier}}".
 *
 * @property integer $id
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $site_code
 * @property string $rate1
 * @property string $rate2
 * @property string $rate3
 * @property string $rate4
 * @property string $rate5
 *
 * @property CmsSite $site
 */
class ShopAffiliateTier extends \skeeks\cms\models\Core
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%shop_affiliate_tier}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_by', 'updated_by', 'created_at', 'updated_at'], 'integer'],
            [['site_code'], 'required'],
            [['rate1', 'rate2', 'rate3', 'rate4', 'rate5'], 'number'],
            [['rate1', 'rate2', 'rate3', 'rate4', 'rate5'], 'default', 'value' => 0],
            [['site_code'], 'string', 'max' => 15],
            [['site_code'], 'unique']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'created_by' => Yii::t('app', 'Created By'),
            'updated_by' => Yii::t('app', 'Updated By'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'site_code' => Yii::t('app', 'Site'),
            'rate1' => Yii::t('app', 'Комиссия аффилиата 1 уровня'),
            'rate2' => Yii::t('app', 'Комиссия аффилиата 2 уровня'),
            'rate3' => Yii::t('app', 'Комиссия аффилиата 3 уровня'),
            'rate4' => Yii::t('app', 'Комиссия аффилиата 4 уровня'),
            'rate5' => Yii::t('app', 'Комиссия аффилиата 5 уровня'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSite()
    {
        return $this->hasOne(CmsSite::className(), ['code' => 'site_code']);
    }

}