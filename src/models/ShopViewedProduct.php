<?php

namespace skeeks\cms\shop\models;

use skeeks\cms\base\ActiveRecord;
use skeeks\cms\query\CmsActiveQuery;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%shop_viewed_product}}".
 *
 * @property integer     $id
 * @property integer     $created_by
 * @property integer     $updated_by
 * @property integer     $created_at
 * @property integer     $updated_at
 * @property integer     $shop_user_id
 * @property integer     $shop_product_id
 *
 * @property ShopUser    $shopUser
 * @property ShopProduct $shopProduct
 */
class ShopViewedProduct extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%shop_viewed_product}}';
    }
    
    public function behaviors()
    {
        $result = parent::behaviors();
        ArrayHelper::remove($result, HasUserLog::class);

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [
                ['created_by', 'updated_by', 'created_at', 'updated_at', 'shop_user_id', 'shop_product_id'],
                'integer',
            ],
            [['shop_user_id', 'shop_product_id'], 'required'],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'shop_user_id'    => \Yii::t('skeeks/shop/app', 'Shop Fuser ID'),
            'shop_product_id' => \Yii::t('skeeks/shop/app', 'Shop Product ID'),
        ]);
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopUser()
    {
        return $this->hasOne(ShopUser::class, ['id' => 'shop_user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopProduct()
    {
        return $this->hasOne(ShopProduct::class, ['id' => 'shop_product_id']);
    }
}