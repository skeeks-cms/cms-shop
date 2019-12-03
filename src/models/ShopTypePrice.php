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
 * @property string  $code
 * @property string  $name
 * @property string  $description
 * @property integer $priority
 *
 * ***
 *
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
            [['name'], 'required'],
            [['description'], 'string'],
            [['name'], 'string', 'max' => 255],
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
}