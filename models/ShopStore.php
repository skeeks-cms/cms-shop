<?php

namespace skeeks\cms\shop\models;

use skeeks\cms\components\Cms;
use skeeks\cms\kladr\models\KladrLocation;
use skeeks\cms\models\behaviors\HasStorageFile;
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
     * @return array
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            HasStorageFile::className() =>
            [
                'class'     => HasStorageFile::className(),
                'fields'    => ['image_id']
            ],
        ]);
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
            [['gps_n', 'gps_s', 'site_code'], 'string', 'max' => 15],
            [['priority',], 'default', 'value' =>  1],
            [['active', 'issuing_center', 'shipping_center'], 'default', 'value' =>  Cms::BOOL_Y],
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
            'name'              => \skeeks\cms\shop\Module::t('app', 'Name'),
            'active'            => \skeeks\cms\shop\Module::t('app', 'Active'),
            'address'           => \skeeks\cms\shop\Module::t('app', 'Address'),
            'description'       => \skeeks\cms\shop\Module::t('app', 'Description'),
            'gps_n'             => \skeeks\cms\shop\Module::t('app', 'Gps N'),
            'gps_s'             => \skeeks\cms\shop\Module::t('app', 'Gps S'),
            'image_id'          => \skeeks\cms\shop\Module::t('app', 'Image ID'),
            'location_id'       => \skeeks\cms\shop\Module::t('app', 'Location ID'),
            'phone'             => \skeeks\cms\shop\Module::t('app', 'Phone'),
            'schedule'          => \skeeks\cms\shop\Module::t('app', 'Schedule'),
            'xml_id'            => \skeeks\cms\shop\Module::t('app', 'Xml ID'),
            'priority'          => \skeeks\cms\shop\Module::t('app', 'Priority'),
            'email'             => \skeeks\cms\shop\Module::t('app', 'Email'),
            'issuing_center'    => \skeeks\cms\shop\Module::t('app', 'Issuing Center'),
            'shipping_center'   => \skeeks\cms\shop\Module::t('app', 'Shipping Center'),
            'site_code'         => \skeeks\cms\shop\Module::t('app', 'Site'),
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