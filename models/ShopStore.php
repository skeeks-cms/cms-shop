<?php

namespace skeeks\cms\shop\models;

use skeeks\cms\kladr\models\KladrLocation;
use skeeks\cms\models\CmsSite;
use skeeks\cms\models\StorageFile;
use Yii;

/**
 * This is the model class for table "{{%shop_store}}".
 *
 * @property integer $id
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $name
 * @property string $active
 * @property string $address
 * @property string $description
 * @property string $gps_n
 * @property string $gps_s
 * @property integer $image_id
 * @property integer $location_id
 * @property string $phone
 * @property string $schedule
 * @property string $xml_id
 * @property integer $priority
 * @property string $email
 * @property string $issuing_center
 * @property string $shipping_center
 * @property string $site_code
 *
 * @property KladrLocation $location
 * @property StorageFile $image
 * @property CmsSite $site
 */
class ShopStore extends \skeeks\cms\models\Core
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%shop_store}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'image_id', 'location_id', 'priority'], 'integer'],
            [['name', 'address'], 'required'],
            [['description'], 'string'],
            [['name', 'address', 'phone', 'schedule', 'xml_id', 'email'], 'string', 'max' => 255],
            [['active', 'issuing_center', 'shipping_center'], 'string', 'max' => 1],
            [['gps_n', 'gps_s', 'site_code'], 'string', 'max' => 15]
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
            'name' => Yii::t('app', 'Name'),
            'active' => Yii::t('app', 'Active'),
            'address' => Yii::t('app', 'Address'),
            'description' => Yii::t('app', 'Description'),
            'gps_n' => Yii::t('app', 'Gps N'),
            'gps_s' => Yii::t('app', 'Gps S'),
            'image_id' => Yii::t('app', 'Image ID'),
            'location_id' => Yii::t('app', 'Location ID'),
            'phone' => Yii::t('app', 'Phone'),
            'schedule' => Yii::t('app', 'Schedule'),
            'xml_id' => Yii::t('app', 'Xml ID'),
            'priority' => Yii::t('app', 'Priority'),
            'email' => Yii::t('app', 'Email'),
            'issuing_center' => Yii::t('app', 'Issuing Center'),
            'shipping_center' => Yii::t('app', 'Shipping Center'),
            'site_code' => Yii::t('app', 'Site Code'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLocation()
    {
        return $this->hasOne(KladrLocation::className(), ['id' => 'location_id']);
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getImage()
    {
        return $this->hasOne(StorageFile::className(), ['id' => 'image_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSite()
    {
        return $this->hasOne(CmsSite::className(), ['code' => 'site_code']);
    }
}