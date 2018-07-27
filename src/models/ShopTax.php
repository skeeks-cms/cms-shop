<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 31.08.2015
 */

namespace skeeks\cms\shop\models;

use skeeks\cms\models\CmsSite;
use skeeks\cms\models\Core;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%shop_tax}}".
 *
 * @property integer $id
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $created_at
 * @property integer $updated_at
 * @property string  $name
 * @property string  $description
 * @property string  $code
 * @property string  $site_code
 *
 * @property CmsSite $site
 */
class ShopTax extends Core
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%shop_tax}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['created_by', 'updated_by', 'created_at', 'updated_at'], 'integer'],
            [['name', 'code'], 'required'],
            [['description'], 'string'],
            [['name'], 'string', 'max' => 255],
            [['code'], 'string', 'max' => 50],
            [['site_code'], 'string', 'max' => 15],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'id'          => \Yii::t('skeeks/shop/app', 'ID'),
            'created_by'  => \Yii::t('skeeks/shop/app', 'Created By'),
            'updated_by'  => \Yii::t('skeeks/shop/app', 'Updated By'),
            'created_at'  => \Yii::t('skeeks/shop/app', 'Created At'),
            'updated_at'  => \Yii::t('skeeks/shop/app', 'Updated At'),
            'name'        => \Yii::t('skeeks/shop/app', 'Name'),
            'description' => \Yii::t('skeeks/shop/app', 'Description'),
            'code'        => \Yii::t('skeeks/shop/app', 'Code'),
            'site_code'   => \Yii::t('skeeks/shop/app', 'Site'),
        ]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSite()
    {
        return $this->hasOne(CmsSite::className(), ['code' => 'site_code']);
    }
}