<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */

namespace skeeks\cms\shop\models;

use skeeks\cms\models\CmsUser;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "shop_store_product_move".
 *
 * @property int              $id
 * @property int|null         $created_at
 * @property int|null         $updated_at
 * @property int|null         $created_by
 * @property int|null         $updated_by
 * @property int              $is_active Документ проведен?
 * @property int              $shop_store_doc_move_id Документ
 * @property string           $product_name Название товара
 * @property int|null         $shop_store_product_id Товар
 * @property float            $quantity Количество
 * @property float            $price Цена
 *
 * @property CmsUser          $createdBy
 * @property ShopStoreDocMove $shopStoreDocMove
 * @property ShopStoreProduct $shopStoreProduct
 * @property CmsUser          $updatedBy
 */
class ShopStoreProductMove extends \skeeks\cms\base\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'shop_store_product_move';
    }

    public function init()
    {
        $this->on(self::EVENT_AFTER_FIND, [$this, "_afterFind"]);
        return parent::init();
    }

    public function _afterFind($event)
    {
        $this->quantity = (float)$this->quantity;
        $this->price = (float)$this->price;
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['created_at', 'updated_at', 'created_by', 'updated_by', 'is_active', 'shop_store_doc_move_id', 'shop_store_product_id'], 'integer'],
            [['shop_store_doc_move_id', 'product_name'], 'required'],
            [['quantity', 'price'], 'number'],
            [['product_name'], 'string', 'max' => 255],
            [['shop_store_product_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopStoreProduct::className(), 'targetAttribute' => ['shop_store_product_id' => 'id']],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => CmsUser::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['shop_store_doc_move_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopStoreDocMove::className(), 'targetAttribute' => ['shop_store_doc_move_id' => 'id']],
            [['updated_by'], 'exist', 'skipOnError' => true, 'targetClass' => CmsUser::className(), 'targetAttribute' => ['updated_by' => 'id']],

            [['quantity'], function() {
                if ($this->quantity == 0) {
                    $this->addError("Количество движения товара должно быть не 0!");
                    return false;
                }
            }]
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'is_active'              => 'Документ проведен?',
            'shop_store_doc_move_id' => 'Документ',
            'product_name'           => 'Название товара',
            'shop_store_product_id'  => 'Товар',
            'quantity'               => 'Количество',
            'price'                  => 'Цена',
        ]);
    }

    /**
     * Gets query for [[ShopStoreDocMove]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getShopStoreDocMove()
    {
        return $this->hasOne(ShopStoreDocMove::className(), ['id' => 'shop_store_doc_move_id']);
    }

    /**
     * Gets query for [[ShopStoreProduct]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getShopStoreProduct()
    {
        return $this->hasOne(ShopStoreProduct::className(), ['id' => 'shop_store_product_id']);
    }

}