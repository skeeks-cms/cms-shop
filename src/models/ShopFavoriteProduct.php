<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 14.09.2015
 */

namespace skeeks\cms\shop\models;

use skeeks\cms\base\ActiveRecord;
use skeeks\modules\cms\money\models\Currency;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "shop_favorite_product".
 *
 * @property int         $id
 * @property int|null    $created_at
 * @property int         $shop_user_id
 * @property int         $shop_product_id
 *
 * @property ShopUser    $shopUser
 * @property ShopProduct $shopProduct
 */
class ShopFavoriteProduct extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%shop_favorite_product}}';
    }


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['created_at', 'shop_user_id', 'shop_product_id'], 'integer'],
            [['shop_user_id', 'shop_product_id'], 'required'],
            [['shop_user_id', 'shop_product_id'], 'unique', 'targetAttribute' => ['shop_user_id', 'shop_product_id']],
            [['shop_user_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopUser::className(), 'targetAttribute' => ['shop_user_id' => 'id']],
            [['shop_product_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopProduct::className(), 'targetAttribute' => ['shop_product_id' => 'id']],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'shop_user_id'    => 'Shop Cart ID',
            'shop_product_id' => 'Shop Product ID',
        ]);
    }


    /**
     * Gets query for [[ShopCart]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getShopUser()
    {
        return $this->hasOne(ShopUser::className(), ['id' => 'shop_user_id']);
    }

    /**
     * Gets query for [[ShopProduct]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getShopProduct()
    {
        return $this->hasOne(ShopProduct::className(), ['id' => 'shop_product_id']);
    }
}