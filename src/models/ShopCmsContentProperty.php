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
 * @property integer    $is_offer_property
 * @property integer    $cms_content_property_id
 *
 * @property CmsContentProperty $cmsContentProperty
 */
class ShopCmsContentProperty extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%shop_cms_content_property}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [
                ['cms_content_property_id', 'is_offer_property'],
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
            'is_offer_property'          => \Yii::t('skeeks/shop/app', 'Свойство предложения?'),
        ]);
    }
    /**
     * @inheritdoc
     */
    public function attributeHints()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'is_offer_property'          => \Yii::t('skeeks/shop/app', 'Если это свойство является свойством предложения, то оно будет показываться в сложных карточках.'),
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
            ->join("INNER JOIN", ShopCmsContentProperty::tableName(), [ShopCmsContentProperty::tableName() . ".cms_content_property_id" => new Expression(CmsContentProperty::tableName() . ".id")])
            ->andWhere([ShopCmsContentProperty::tableName() . ".is_offer_property" => 1]);

        return $q;
    }
}