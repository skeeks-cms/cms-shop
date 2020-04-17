<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */

namespace skeeks\cms\shop\models;

use skeeks\cms\base\ActiveRecord;
use skeeks\cms\models\CmsSite;
use skeeks\cms\models\CmsTree;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "shop_import_cms_site".
 *
 * @property int           $id
 * @property int|null      $created_by
 * @property int|null      $updated_by
 * @property int|null      $created_at
 * @property int|null      $updated_at
 * @property int           $receiver_cms_site_id Сайт получатель
 * @property int           $receiver_shop_type_price_id Цена на сайте получателе
 * @property int|null      $receiver_cms_tree_id Раздел на сайте получателе
 * @property int           $sender_cms_site_id Сайт отправитель
 * @property int           $sender_shop_type_price_id Цена на сайте отправителе
 * @property int           $extra_charge Наценка/Уценка
 *
 * @property CmsSite       $receiverCmsSite
 * @property CmsTree       $receiverCmsTree
 * @property ShopTypePrice $receiverShopTypePrice
 * @property CmsSite       $senderCmsSite
 * @property ShopTypePrice $senderShopTypePrice
 */
class ShopImportCmsSite extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'shop_import_cms_site';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [
                ['created_by', 'updated_by', 'created_at', 'updated_at', 'receiver_cms_site_id', 'receiver_shop_type_price_id', 'receiver_cms_tree_id', 'sender_cms_site_id', 'sender_shop_type_price_id', 'extra_charge'],
                'integer',
            ],
            [['receiver_shop_type_price_id', 'sender_cms_site_id', 'sender_shop_type_price_id'], 'required'],
            [['sender_shop_type_price_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopTypePrice::className(), 'targetAttribute' => ['sender_shop_type_price_id' => 'id']],
            [['receiver_cms_site_id'], 'exist', 'skipOnError' => true, 'targetClass' => CmsSite::className(), 'targetAttribute' => ['receiver_cms_site_id' => 'id']],
            [['receiver_cms_tree_id'], 'exist', 'skipOnError' => true, 'targetClass' => CmsTree::className(), 'targetAttribute' => ['receiver_cms_tree_id' => 'id']],
            [['receiver_shop_type_price_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopTypePrice::className(), 'targetAttribute' => ['receiver_shop_type_price_id' => 'id']],
            [['sender_cms_site_id'], 'exist', 'skipOnError' => true, 'targetClass' => CmsSite::className(), 'targetAttribute' => ['sender_cms_site_id' => 'id']],

            [
                'receiver_cms_site_id',
                'default',
                'value' => function () {
                    if (\Yii::$app->cms->site) {
                        return \Yii::$app->cms->site->id;
                    }
                },
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'receiver_cms_site_id' => 'Сайт получатель',
            'receiver_shop_type_price_id' => 'Цена на сайте получателе',
            'receiver_cms_tree_id' => 'Раздел на сайте получателе',
            'sender_cms_site_id' => 'Поставщик',
            'sender_shop_type_price_id' => 'Цена на сайте отправителе',
            'extra_charge' => 'Наценка/Уценка',
        ]);
    }


    /**
     * Gets query for [[ReceiverCmsSite]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getReceiverCmsSite()
    {
        return $this->hasOne(CmsSite::className(), ['id' => 'receiver_cms_site_id']);
    }

    /**
     * Gets query for [[ReceiverCmsTree]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getReceiverCmsTree()
    {
        return $this->hasOne(CmsTree::className(), ['id' => 'receiver_cms_tree_id']);
    }

    /**
     * Gets query for [[ReceiverShopTypePrice]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getReceiverShopTypePrice()
    {
        return $this->hasOne(ShopTypePrice::className(), ['id' => 'receiver_shop_type_price_id']);
    }

    /**
     * Gets query for [[SenderCmsSite]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSenderCmsSite()
    {
        return $this->hasOne(CmsSite::className(), ['id' => 'sender_cms_site_id']);
    }

    /**
     * Gets query for [[SenderShopTypePrice]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSenderShopTypePrice()
    {
        return $this->hasOne(ShopTypePrice::className(), ['id' => 'sender_shop_type_price_id']);
    }
}