<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 21.12.2016
 */
namespace skeeks\cms\shop\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%shop_quantity_notice_email}}".
 *
 * @property integer $id
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $shop_product_id
 * @property string $email
 * @property string $name
 * @property integer $is_notified
 * @property integer $notified_at
 * @property integer $shop_fuser_id
 *
 * @property ShopFuser $shopFuser
 * @property ShopProduct $shopProduct
 */
class ShopQuantityNoticeEmail extends \skeeks\cms\models\Core
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%shop_quantity_notice_email}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'shop_product_id', 'is_notified', 'notified_at', 'shop_fuser_id'], 'integer'],
            [['shop_product_id', 'email'], 'required'],
            [['email', 'name'], 'string', 'max' => 255],
            [['shop_fuser_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopFuser::className(), 'targetAttribute' => ['shop_fuser_id' => 'id']],
            [['shop_product_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopProduct::className(), 'targetAttribute' => ['shop_product_id' => 'id']],

            [['email'], 'email'],
            [['shop_fuser_id'], 'default', 'value' => function()
            {
                return \Yii::$app->shop->shopFuser->id;
            }],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'id' => Yii::t('skeeks/shop/app', 'ID'),
            'created_by' => Yii::t('skeeks/shop/app', 'Created By'),
            'updated_by' => Yii::t('skeeks/shop/app', 'Updated By'),
            'created_at' => Yii::t('skeeks/shop/app', 'Created At'),
            'updated_at' => Yii::t('skeeks/shop/app', 'Updated At'),
            'shop_product_id' => Yii::t('skeeks/shop/app', 'Shop Product ID'),
            'email' => Yii::t('skeeks/shop/app', 'Email'),
            'name' => Yii::t('skeeks/shop/app', 'Customer name'),
            'is_notified' => Yii::t('skeeks/shop/app', 'Is notified'),
            'notified_at' => Yii::t('skeeks/shop/app', 'Notified At'),
            'shop_fuser_id' => Yii::t('skeeks/shop/app', 'Shop Fuser ID'),
        ]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopFuser()
    {
        return $this->hasOne(ShopFuser::className(), ['id' => 'shop_fuser_id']);
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopProduct()
    {
        return $this->hasOne(ShopProduct::className(), ['id' => 'shop_product_id']);
    }

}