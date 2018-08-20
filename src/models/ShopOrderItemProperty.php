<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */

namespace skeeks\cms\shop\models;

/**
 * This is the model class for table "{{%shop_basket_props}}".
 *
 * @property integer    $id
 * @property integer    $created_by
 * @property integer    $updated_by
 * @property integer    $created_at
 * @property integer    $updated_at
 * @property integer    $shop_order_item_id
 * @property string     $name
 * @property string     $value
 * @property string     $code
 * @property integer    $priority
 *
 * @property ShopOrderItem $shopOrderItem
 */
class ShopOrderItemProperty extends \skeeks\cms\models\Core
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%shop_order_item_property}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'shop_order_item_id', 'priority'], 'integer'],
            [['shop_order_item_id', 'name'], 'required'],
            [['name', 'value', 'code'], 'string', 'max' => 255],
            [['priority'], 'default', 'value' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'             => \Yii::t('skeeks/shop/app', 'ID'),
            'created_by'     => \Yii::t('skeeks/shop/app', 'Created By'),
            'updated_by'     => \Yii::t('skeeks/shop/app', 'Updated By'),
            'created_at'     => \Yii::t('skeeks/shop/app', 'Created At'),
            'updated_at'     => \Yii::t('skeeks/shop/app', 'Updated At'),
            'shop_order_item_id' => \Yii::t('skeeks/shop/app', 'Shop Basket ID'),
            'name'           => \Yii::t('skeeks/shop/app', 'Name'),
            'value'          => \Yii::t('skeeks/shop/app', 'Value'),
            'code'           => \Yii::t('skeeks/shop/app', 'Code'),
            'priority'       => \Yii::t('skeeks/shop/app', 'Priority'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopOrderItem()
    {
        return $this->hasOne(ShopOrderItem::class, ['id' => 'shop_order_item_id']);
    }
}