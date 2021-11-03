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
use yii\base\Exception;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "shop_import_cms_site".
 *
 * @property int       $id
 * @property int|null  $created_by
 * @property int|null  $updated_by
 * @property int|null  $created_at
 * @property int|null  $updated_at
 *
 * @property int       $cms_site_id Сайт получатель
 * @property int       $sender_shop_store_id
 * @property int       $receiver_shop_store_id
 *
 * @property int       $priority сортировка
 *
 * @property CmsSite   $cmsSite
 * @property ShopStore $senderShopStore
 * @property ShopStore $receiverShopStore
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

    public function init()
    {
        $this->on(self::EVENT_AFTER_INSERT, function() {


            $shopStore = new ShopStore();

            $shopStore->cms_site_id = $this->cms_site_id;
            $shopStore->is_supplier = 1;
            $shopStore->name = $this->senderShopStore->name;
            $shopStore->description = $this->senderShopStore->description;

            if (!$shopStore->save()) {
                $this->delete();
                /*print_r($shopStore->errors);
                die('22222');*/
                //throw new Exception("Ошибка добавления поставщика-склада: " . print_r($shopStore->errors, true));
                return false;
            } 



            $this->receiver_shop_store_id = $shopStore->id;
            $this->save();



        });

        return parent::init();
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [

            [
                [
                    'created_by',
                    'updated_by',
                    'created_at',
                    'updated_at',
                    'cms_site_id',
                    'sender_shop_store_id',
                    'receiver_shop_store_id',
                    'priority',
                ],
                'integer',
            ],
            [['cms_site_id'], 'exist', 'skipOnError' => true, 'targetClass' => CmsSite::className(), 'targetAttribute' => ['cms_site_id' => 'id']],

            [
                'cms_site_id',
                'default',
                'value' => function () {
                    if (\Yii::$app->skeeks->site) {
                        return \Yii::$app->skeeks->site->id;
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
            'cms_site_id'             => 'Сайт получатель',
            'sender_shop_store_id'    => 'Поставщик',
            'receiver_shop_store_id'  => 'Поставщик/Склад получатель',
            'priority'                => 'Приоритет',
        ]);
    }
    public function attributeHints()
    {
        return ArrayHelper::merge(parent::attributeHints(), [
            'extra_charge'           => 'Если выбрано 100% то розничная цена на вашем сайте будет такой же, как выбранная цена у поставщика',
            'priority'               => 'Чем ниже приоритет тем важнее этот поставщик',
            'sender_shop_store_id'   => 'Данные о товарах собираются от этого поставщика',
            'receiver_shop_store_id' => 'Это склад на вашем сайте, на него будут заведены позиции',
        ]);
    }


    /**
     * Gets query for [[ReceiverCmsSite]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCmsSite()
    {
        return $this->hasOne(CmsSite::className(), ['id' => 'cms_site_id']);
    }

    /**
     * Gets query for [[SenderCmsSite]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSenderShopStore()
    {
        return $this->hasOne(ShopStore::className(), ['id' => 'sender_shop_store_id']);
    }
    /**
     * Gets query for [[SenderCmsSite]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getReceiverShopStore()
    {
        return $this->hasOne(ShopStore::className(), ['id' => 'receiver_shop_store_id']);
    }
}