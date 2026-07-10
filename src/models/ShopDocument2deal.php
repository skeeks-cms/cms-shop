<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 */

namespace skeeks\cms\shop\models;

use skeeks\cms\models\CmsDeal;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * Link between accounting documents and deals.
 *
 * @property int          $id
 * @property int|null     $created_by
 * @property int|null     $created_at
 * @property int          $shop_document_id
 * @property int          $cms_deal_id
 *
 * @property ShopDocument $document
 * @property CmsDeal      $deal
 */
class ShopDocument2deal extends \skeeks\cms\base\ActiveRecord
{
    public static function tableName()
    {
        return '{{%shop_document2deal}}';
    }

    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['shop_document_id', 'cms_deal_id'], 'integer'],
            [['shop_document_id', 'cms_deal_id'], 'required'],
            [['shop_document_id', 'cms_deal_id'], 'unique', 'targetAttribute' => ['shop_document_id', 'cms_deal_id']],
            [['shop_document_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopDocument::class, 'targetAttribute' => ['shop_document_id' => 'id']],
            [['cms_deal_id'], 'exist', 'skipOnError' => true, 'targetClass' => CmsDeal::class, 'targetAttribute' => ['cms_deal_id' => 'id']],
        ]);
    }

    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'shop_document_id' => Yii::t('skeeks/shop/app', 'Документ'),
            'cms_deal_id'      => Yii::t('skeeks/shop/app', 'Сделка'),
        ]);
    }

    public function getDocument()
    {
        return $this->hasOne(ShopDocument::class, ['id' => 'shop_document_id']);
    }

    public function getDeal()
    {
        return $this->hasOne(CmsDeal::class, ['id' => 'cms_deal_id']);
    }
}
