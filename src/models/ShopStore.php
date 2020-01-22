<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */

namespace skeeks\cms\shop\models;

use skeeks\cms\models\CmsStorageFile;
use skeeks\cms\models\StorageFile;
use yii\helpers\ArrayHelper;

/**
 * @property string         $name
 * @property string         $description
 * @property int            $cms_image_id
 * @property bool           $is_active
 * @property bool           $shop_supplier_id
 * @property string|null    $external_id
 *
 * @property CmsStorageFile $cmsImage
 * @property ShopSupplier   $shopSupplier
 *
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class ShopStore extends \skeeks\cms\base\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%shop_store}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['created_by', 'updated_by', 'created_at', 'updated_at'], 'integer'],

            [['name'], 'string', 'max' => 255],
            [['name'], 'required'],
            [['name', 'shop_supplier_id'], 'unique', 'targetAttribute' => ['name', 'shop_supplier_id']],

            [['description'], 'string'],

            [['is_active'], 'integer'],

            [['cms_image_id'], 'safe'],
            [['shop_supplier_id'], 'integer'],

            [['external_id', 'shop_supplier_id'], 'unique', 'targetAttribute' => ['external_id', 'shop_supplier_id']],
            [['external_id'], 'string'],
            [['external_id'], 'default', 'value' => null],

        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [

            'name'             => "Название",
            'description'      => "Описание",
            'cms_image_id'     => "Изображение",
            'shop_supplier_id' => "Поставщик",
            'is_active'        => "Активность",
            'external_id'      => "ID из внешней системы",
        ]);
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCmsImage()
    {
        return $this->hasOne(StorageFile::class, ['id' => 'cms_image_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopSupplier()
    {
        return $this->hasOne(ShopSupplier::class, ['id' => 'shop_supplier_id']);
    }

}