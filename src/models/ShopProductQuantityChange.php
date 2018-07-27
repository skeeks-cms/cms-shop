<?php

namespace skeeks\cms\shop\models;

use skeeks\cms\measure\models\Measure;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%shop_product_quantity_change}}".
 *
 * @property integer     $id
 * @property integer     $created_by
 * @property integer     $updated_by
 * @property integer     $created_at
 * @property integer     $updated_at
 * @property integer     $shop_product_id
 * @property double      $quantity
 * @property double      $quantity_reserved
 * @property integer     $measure_id
 * @property double      $measure_ratio
 *
 * @property ShopProduct $shopProduct
 * @property Measure     $measure
 */
class ShopProductQuantityChange extends \skeeks\cms\models\Core
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%shop_product_quantity_change}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'shop_product_id', 'measure_id'], 'integer'],
            [['shop_product_id'], 'required'],
            [['quantity', 'quantity_reserved', 'measure_ratio'], 'number'],
            [
                ['measure_id'],
                'exist',
                'skipOnError'     => true,
                'targetClass'     => Measure::className(),
                'targetAttribute' => ['measure_id' => 'id'],
            ],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::rules(), [
            'id'                => Yii::t('skeeks/shop/app', 'ID'),
            'shop_product_id'   => Yii::t('skeeks/shop/app', 'Shop Product ID'),
            'quantity'          => Yii::t('skeeks/shop/app', 'Available quantity'),
            'quantity_reserved' => Yii::t('skeeks/shop/app', 'Reserved quantity'),
            'measure_id'        => Yii::t('skeeks/shop/app', 'Unit of measurement'),
            'measure_ratio'     => Yii::t('skeeks/shop/app', 'The coefficient unit'),
        ]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMeasure()
    {
        return $this->hasOne(Measure::className(), ['id' => 'measure_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopProduct()
    {
        return $this->hasOne(ShopProduct::className(), ['id' => 'shop_product_id']);
    }
}