<?php

namespace skeeks\cms\shop\models;

use skeeks\cms\components\Cms;
use yii\base\Event;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%shop_type_price}}".
 *
 * @property integer $id
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $created_at
 * @property integer $updated_at
 * @property string|null  $external_id
 * @property string  $name
 * @property string  $description
 * @property integer $priority
 * @property integer|null $shop_supplier_id
 *
 * ***
 *
 * @property ShopSupplier  $shopSupplier
 * @property string  $buyPermissionName
 * @property string  $viewPermissionName
 */
class ShopTypePrice extends \skeeks\cms\models\Core
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%shop_type_price}}';
    }


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['priority'], 'integer'],
            [['shop_supplier_id'], 'integer'],
            [['shop_supplier_id'], 'default', 'value' => null],
            [['name'], 'required'],
            [['description'], 'string'],
            [['name'], 'string', 'max' => 255],
            
            [['external_id', 'shop_supplier_id'], 'unique', 'targetAttribute' => ['external_id', 'shop_supplier_id']],
            [['external_id'], 'string'],
            [['external_id'], 'default', 'value' => null],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'name'        => \Yii::t('skeeks/shop/app', 'Name'),
            'description' => \Yii::t('skeeks/shop/app', 'Description'),
            'priority'    => \Yii::t('skeeks/shop/app', 'Priority'),
            'shop_supplier_id'    => \Yii::t('skeeks/shop/app', 'Поставщик'),
            'external_id'      => "ID из внешней системы",
        ]);
    }


    /**
     * @return string
     */
    public function getViewPermissionName()
    {
        return "view-shop-type-price-".$this->id;
    }

    /**
     * @return string
     */
    public function getBuyPermissionName()
    {
        return "view-shop-type-price-".$this->id;
    }

    /**
     * @deprecated
     * @return bool
     */
    public function getIsDefault()
    {
        return (bool)($this->id == \Yii::$app->shop->baseTypePrice->id);
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopSupplier()
    {
        return $this->hasOne(ShopSupplier::class, ['id' => 'shop_supplier_id']);
    }
}