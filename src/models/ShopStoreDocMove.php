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
 * This is the model class for table "shop_store_doc_move".
 *
 * @property int                    $id
 * @property int|null               $created_at
 * @property int|null               $updated_at
 * @property int|null               $created_by
 * @property int|null               $updated_by
 * @property int                    $is_active Документ проведен?
 * @property string                 $doc_type Тип операции
 * @property int                    $shop_store_id Магазин
 * @property int|null               $shop_order_id Заказ
 * @property int|null               $client_cms_user_id Клиент
 * @property string|null            $comment Комментарий
 *
 * @property string                 $docTypeAsText
 *
 * @property CmsUser                $clientCmsUser
 * @property ShopOrder              $shopOrder
 * @property ShopStore              $shopStore
 * @property ShopStoreProductMove[] $shopStoreProductMoves
 */
class ShopStoreDocMove extends \skeeks\cms\base\ActiveRecord
{
    const DOCTYPE_CORRECTION = "correction";
    const DOCTYPE_SALE = "sale";
    const DOCTYPE_RETURN = "return";
    const DOCTYPE_INVENTORY = "inventory";
    const DOCTYPE_POSTING = "posting";
    const DOCTYPE_WRITEOFF = "writeoff";

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'shop_store_doc_move';
    }



    /**
     * @return string[]
     */
    static public function docTypes()
    {
        return [
            static::DOCTYPE_SALE       => "Продажа",
            static::DOCTYPE_RETURN     => "Возврат",
            static::DOCTYPE_CORRECTION => "Корректировка",
            static::DOCTYPE_INVENTORY  => "Инвентаризация",
            static::DOCTYPE_POSTING    => "Оприходирование",
            static::DOCTYPE_WRITEOFF   => "Списание",
        ];
    }

    /**
     * @return string
     */
    public function getDocTypeAsText()
    {
        return (string)ArrayHelper::getValue(static::docTypes(), $this->doc_type);
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['created_at', 'updated_at', 'created_by', 'updated_by', 'is_active', 'shop_store_id', 'shop_order_id', 'client_cms_user_id'], 'integer'],
            [['shop_store_id'], 'required'],
            [['comment'], 'string'],
            [['doc_type'], 'string', 'max' => 255],
            [['client_cms_user_id'], 'exist', 'skipOnError' => true, 'targetClass' => CmsUser::className(), 'targetAttribute' => ['client_cms_user_id' => 'id']],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => CmsUser::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['shop_order_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopOrder::className(), 'targetAttribute' => ['shop_order_id' => 'id']],
            [['shop_store_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopStore::className(), 'targetAttribute' => ['shop_store_id' => 'id']],
            [['updated_by'], 'exist', 'skipOnError' => true, 'targetClass' => CmsUser::className(), 'targetAttribute' => ['updated_by' => 'id']],

            [['doc_type'], 'default', 'value' => static::DOCTYPE_CORRECTION],
            
            [['is_active'], function($attribute) {
                if (!$this->shopStoreProductMoves && $this->is_active) {
                    $this->addError($attribute, "Нельзя провести этот документ, потому что в нем нет товаров!");
                    return false;
                }
            }],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'created_at'          => 'Время',
            'is_active'          => 'Документ проведен?',
            'doc_type'           => 'Документ',
            'shop_store_id'      => 'Магазин',
            'shop_order_id'      => 'Заказ',
            'client_cms_user_id' => 'Клиент',
            'comment'            => 'Комментарий',
        ]);
    }

    /**
     * Gets query for [[ClientCmsUser]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getClientCmsUser()
    {
        return $this->hasOne(\Yii::$app->user->identityClass, ['id' => 'client_cms_user_id']);
    }


    /**
     * Gets query for [[ShopOrder]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getShopOrder()
    {
        return $this->hasOne(ShopOrder::className(), ['id' => 'shop_order_id']);
    }

    /**
     * Gets query for [[ShopStore]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getShopStore()
    {
        return $this->hasOne(ShopStore::className(), ['id' => 'shop_store_id']);
    }

    /**
     * Gets query for [[ShopStoreProductMoves]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getShopStoreProductMoves()
    {
        return $this->hasMany(ShopStoreProductMove::className(), ['shop_store_doc_move_id' => 'id']);
    }

    public function asText()
    {
        return $this->docTypeAsText." №".$this->id;
    }

}