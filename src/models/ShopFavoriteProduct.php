<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 14.09.2015
 */

namespace skeeks\cms\shop\models;

use skeeks\cms\base\ActiveRecord;
use skeeks\cms\components\Cms;
use skeeks\cms\measure\models\CmsMeasure;
use skeeks\cms\models\behaviors\HasJsonFieldsBehavior;
use skeeks\cms\models\CmsContentElement;
use skeeks\cms\models\CmsSite;
use skeeks\modules\cms\money\models\Currency;
use yii\db\AfterSaveEvent;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * This is the model class for table "shop_favorite_product".
 *
 * @property int $id
 * @property int|null $created_at
 * @property int $shop_cart_id
 * @property int $shop_product_id
 * @property int $cms_site_id
 *
 * @property CmsSite $cmsSite
 * @property ShopCart $shopCart
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
            [['created_at', 'shop_cart_id', 'shop_product_id', 'cms_site_id'], 'integer'],
            [['shop_cart_id', 'shop_product_id', 'cms_site_id'], 'required'],
            [['shop_cart_id', 'shop_product_id'], 'unique', 'targetAttribute' => ['shop_cart_id', 'shop_product_id']],
            [['cms_site_id'], 'exist', 'skipOnError' => true, 'targetClass' => CmsSite::className(), 'targetAttribute' => ['cms_site_id' => 'id']],
            [['shop_cart_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopCart::className(), 'targetAttribute' => ['shop_cart_id' => 'id']],
            [['shop_product_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopProduct::className(), 'targetAttribute' => ['shop_product_id' => 'id']],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'shop_cart_id' => 'Shop Cart ID',
            'shop_product_id' => 'Shop Product ID',
            'cms_site_id' => 'Cms Site ID',
        ]);
    }

    /**
     * Gets query for [[CmsSite]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCmsSite()
    {
        return $this->hasOne(CmsSite::className(), ['id' => 'cms_site_id']);
    }

    /**
     * Gets query for [[ShopCart]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getShopCart()
    {
        return $this->hasOne(ShopCart::className(), ['id' => 'shop_cart_id']);
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