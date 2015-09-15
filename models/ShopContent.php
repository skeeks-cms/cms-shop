<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 15.09.2015
 */
namespace skeeks\cms\shop\models;

use skeeks\cms\models\CmsContent;
use Yii;

/**
 * This is the model class for table "{{%shop_content}}".
 *
 * @property integer $id
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $content_id
 * @property string $yandex_export
 * @property string $subscription
 * @property integer $vat_id
 *
 * @property CmsContent $content
 * @property ShopVat $vat
 */
class ShopContent extends \skeeks\cms\models\Core
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%shop_content}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'content_id', 'vat_id'], 'integer'],
            [['content_id'], 'required'],
            [['content_id'], 'unique'],
            [['yandex_export', 'subscription'], 'string', 'max' => 1]
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
            'content_id' => Yii::t('app', 'Контент'),
            'yandex_export' => Yii::t('app', 'Экспортировать в Яндекс.Товары'),
            'subscription' => Yii::t('app', 'Subscription'),
            'vat_id' => Yii::t('app', 'Vat ID'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContent()
    {
        return $this->hasOne(CmsContent::className(), ['id' => 'content_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVat()
    {
        return $this->hasOne(ShopVat::className(), ['id' => 'vat_id']);
    }

}