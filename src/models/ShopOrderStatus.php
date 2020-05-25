<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

namespace skeeks\cms\shop\models;

use skeeks\cms\models\Core;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%shop_order_status}}".
 *
 * @property string      $name
 * @property string|null $description
 * @property integer     $priority
 * @property string|null $color
 * @property string|null $bg_color
 * @property string|null $email_notify_description
 * @property string|null $order_page_description
 *
 * @property ShopOrder[] $shopOrders
 */
class ShopOrderStatus extends Core
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%shop_order_status}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), []);
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'name'        => \Yii::t('skeeks/shop/app', 'Name'),

            'description' => \Yii::t('skeeks/shop/app', 'Описание'),
            'priority'    => \Yii::t('skeeks/shop/app', 'Priority'),

            'color'       => \Yii::t('skeeks/shop/app', 'Цвет названия статуса'),
            'bg_color'       => \Yii::t('skeeks/shop/app', 'Цвет фона статуса'),

            'email_notify_description'       => \Yii::t('skeeks/shop/app', 'Дополнительный текст email уведомления'),
            'order_page_description'       => \Yii::t('skeeks/shop/app', 'Дополнительный текст на странице заказа'),
        ]);
    }
    /**
     * @inheritdoc
     */
    public function attributeHints()
    {
        return array_merge(parent::attributeLabels(), [
            'description' => \Yii::t('skeeks/shop/app', 'Короткая расшфировка статуса заказа'),

            'email_notify_description'       => \Yii::t('skeeks/shop/app', 'Этот текст получают клиенты в email уведомлении.'),
            'order_page_description'       => \Yii::t('skeeks/shop/app', 'Этот текст отображается клиенту на странице с заказом, в случае этого статуса'),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['priority'], 'integer'],
            [['name'], 'required'],
            [['description'], 'string', 'max' => 255],
            [['order_page_description'], 'string'],
            [['email_notify_description'], 'string'],

            [['name'], 'string', 'max' => 255],

            [['color'], 'string', 'max' => 32],
            [['bg_color'], 'string', 'max' => 32],

            [['description', 'color', 'bg_color', 'order_page_description', 'email_notify_description'], 'default', 'value' => null],
        ]);
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopOrders()
    {
        return $this->hasMany(ShopOrder::class, ['shop_order_status_id' => 'id']);
    }
}