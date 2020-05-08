<?php

namespace skeeks\cms\shop\models;

use skeeks\cms\base\ActiveRecord;
use skeeks\cms\measure\models\Measure;
use yii\helpers\ArrayHelper;


/**
 * This is the model class for table "shop_product_relation".
 *
 * @property int         $id
 * @property int|null    $created_by
 * @property int|null    $updated_by
 * @property int|null    $created_at
 * @property int|null    $updated_at
 * @property int         $shop_product1_id
 * @property int         $shop_product2_id
 *
 * @property CmsUser     $createdBy
 * @property ShopProduct $shopProduct1
 * @property ShopProduct $shopProduct2
 * @property CmsUser     $updatedBy
 */
class ShopProductRelation extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'shop_product_relation';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'shop_product1_id', 'shop_product2_id'], 'integer'],
            [['shop_product1_id', 'shop_product2_id'], 'required'],
            [['shop_product1_id', 'shop_product2_id'], 'unique', 'targetAttribute' => ['shop_product1_id', 'shop_product2_id']],
            [['shop_product2_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopProduct::className(), 'targetAttribute' => ['shop_product2_id' => 'id']],
            [['shop_product1_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopProduct::className(), 'targetAttribute' => ['shop_product1_id' => 'id']],

            [['shop_product1_id', 'shop_product2_id'], function($attribute) {
                if ($this->shop_product1_id == $this->shop_product2_id) {
                    $this->addError($attribute, "Товар нельзя привязать к себе же");
                }
            }],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'shop_product1_id' => 'Shop Product1 ID',
            'shop_product2_id' => 'Shop Product2 ID',
        ]);
    }



    /**
     * Gets query for [[ShopProduct1]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getShopProduct1()
    {
        return $this->hasOne(ShopProduct::className(), ['id' => 'shop_product1_id']);
    }

    /**
     * Gets query for [[ShopProduct2]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getShopProduct2()
    {
        return $this->hasOne(ShopProduct::className(), ['id' => 'shop_product2_id']);
    }

}