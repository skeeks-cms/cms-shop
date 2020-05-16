<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 15.09.2015
 */

namespace skeeks\cms\shop\models;

use skeeks\cms\base\ActiveRecord;
use skeeks\cms\models\CmsContent;
use skeeks\cms\models\CmsContentProperty;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%shop_content}}".
 *
 * @property integer    $id
 * @property integer    $created_by
 * @property integer    $updated_by
 * @property integer    $created_at
 * @property integer    $updated_at
 * @property integer    $cms_content_property_id
 *
 * @property CmsContentProperty $cmsContentProperty
 */
class ShopOfferProperty extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%shop_offer_property}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [
                ['cms_content_property_id'],
                'integer',
            ],
            [['cms_content_property_id'], 'required'],
            [['cms_content_property_id'], 'unique'],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'cms_content_property_id'          => \Yii::t('skeeks/shop/app', 'Свойство'),
        ]);
    }



    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCmsContentProperty()
    {
        return $this->hasOne(CmsContentProperty::class, ['id' => 'cms_content_property_id']);
    }

    /**
     * @return string
     */
    public function asText()
    {
        return $this->cmsContentProperty->asText;
    }

    /**
     * @return \skeeks\cms\query\CmsActiveQuery
     */
    static public function findCmsContentProperties()
    {
        $q = CmsContentProperty::find()
            ->join("INNER JOIN", ShopOfferProperty::tableName(), [ShopOfferProperty::tableName() . ".cms_content_property_id" => new Expression(CmsContentProperty::tableName() . ".id")]);
        return $q;
    }
}