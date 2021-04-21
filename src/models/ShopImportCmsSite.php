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
 *
 * @property int       $extra_charge Наценка/Уценка
 *
 * @property int       $purchasing_extra_charge Наценка/Уценка
 *
 * @property int       $priority сортировка
 *
 * @property CmsSite   $cmsSite
 * @property ShopStore $senderShopStore
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
                [
                    'purchasing_extra_charge',
                    'created_by',
                    'updated_by',
                    'created_at',
                    'updated_at',
                    'cms_site_id',
                    'extra_charge',
                    'sender_shop_store_id',
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
            'sender_shop_store_id'    => 'Поставщик/Склад',
            'purchasing_extra_charge' => 'Наценка/Уценка закупочной цены',
            'extra_charge'            => 'Наценка/Уценка',
            'priority'                => 'Приоритет',
        ]);
    }
    public function attributeHints()
    {
        return ArrayHelper::merge(parent::attributeHints(), [
            'extra_charge' => 'Если выбрано 100% то розничная цена на вашем сайте будет такой же, как выбранная цена у поставщика',
            'priority'     => 'Чем ниже приоритет тем важнее этот поставщик',
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
}