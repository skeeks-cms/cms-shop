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
 * @property integer     $shop_store_id
 * @property integer     $shop_product_id
 * @property float       $quantity
 *
 * @property ShopProduct $shopProduct
 * @property ShopStore   $shopStore
 *
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class ShopStoreProduct extends \skeeks\cms\base\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%shop_store_product}}';
    }


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [

            [['created_by', 'updated_by', 'created_at', 'updated_at'], 'integer'],

            [['shop_store_id', 'shop_product_id'], 'unique', 'targetAttribute' => ['shop_store_id', 'shop_product_id']],
            [['quantity'], 'number'],

            [['shop_store_id'], 'integer'],
            [['shop_product_id'], 'integer'],
            [['quantity'], 'default', 'value' => 0],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'shop_store_id'   => "Склад",
            'shop_product_id' => "Товар",
            'quantity'        => "Количество",
        ]);
    }



    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopProduct()
    {
        return $this->hasOne(ShopProduct::class, ['id' => 'shop_product_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopStore()
    {
        return $this->hasOne(ShopStore::class, ['id' => 'shop_store_id']);
    }
}