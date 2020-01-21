<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */

namespace skeeks\cms\shop\models;

use skeeks\cms\models\behaviors\HasJsonFieldsBehavior;
use skeeks\cms\models\behaviors\HasStorageFile;
use skeeks\cms\models\StorageFile;
use skeeks\cms\money\models\MoneyCurrency;
use skeeks\cms\money\Money;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * @property integer|null $created_at
 * @property integer|null $updated_at
 * @property integer|null $created_by
 * @property integer|null $updated_by
 * @property string       $name
 * @property string|null  $description
 * @property string|null  $description_internal
 * @property integer|null $cms_image_id
 * @property integer      $is_active
 *
 * @property StorageFile  $cmsImage
 * @property ShopStore[]  $shopStores
 *
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class ShopSupplier extends \skeeks\cms\base\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%shop_supplier}}';
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            HasStorageFile::class => [
                'class'  => HasStorageFile::class,
                'fields' => ['cms_image_id'],
            ],
        ]);
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
            [['name'], 'unique'],

            [['description_internal'], 'string'],
            [['description'], 'string'],

            [['is_active'], 'integer'],

            [['cms_image_id'], 'safe'],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'name'         => "Название",
            'description'  => "Описание",
            'description_internal'  => "Описание (внутреннее)",
            'cms_image_id' => "Изображение",
            'is_active'    => "Активность",
        ]);
    }
    /**
     * {@inheritdoc}
     */
    public function attributeHints()
    {
        return ArrayHelper::merge(parent::attributeHints(), [
            'description_internal'  => "Это описание не видят клиенты",
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
    public function getShopStores()
    {
        return $this->hasMany(ShopStore::class, ['shop_supplier_id' => 'id']);
    }
}