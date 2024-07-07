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

/**
 * This is the model class for table "{{%shop_product}}".
 *
 * @property integer                 $id
 * @property integer                 $created_at
 *
 * @property ShopProduct[]           $shopProducts
 */
class ShopProductModel extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%shop_product_model}}';
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopProducts()
    {
        return $this->hasMany(ShopProduct::class, ['shop_product_model_id' => 'id']);
    }

}