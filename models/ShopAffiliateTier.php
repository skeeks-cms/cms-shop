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
            'id'            => \skeeks\cms\shop\Module::t('app', 'ID'),
            'created_by'    => \skeeks\cms\shop\Module::t('app', 'Created By'),
            'updated_by'    => \skeeks\cms\shop\Module::t('app', 'Updated By'),
            'created_at'    => \skeeks\cms\shop\Module::t('app', 'Created At'),
            'updated_at'    => \skeeks\cms\shop\Module::t('app', 'Updated At'),
            'site_code'     => \skeeks\cms\shop\Module::t('app', 'Site'),
            'rate1'         => \skeeks\cms\shop\Module::t('app', 'Commission affiliate Level 1'),
            'rate2'         => \skeeks\cms\shop\Module::t('app', 'Commission affiliate Level 2'),
            'rate3'         => \skeeks\cms\shop\Module::t('app', 'Commission affiliate Level 3'),
            'rate4'         => \skeeks\cms\shop\Module::t('app', 'Commission affiliate Level 4'),
            'rate5'         => \skeeks\cms\shop\Module::t('app', 'Commission affiliate Level 5'),
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