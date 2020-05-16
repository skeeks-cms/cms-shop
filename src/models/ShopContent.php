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
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%shop_content}}".
 *
 * @property integer    $id
 * @property integer    $created_by
 * @property integer    $updated_by
 * @property integer    $created_at
 * @property integer    $updated_at
 * @property integer    $content_id
 * @property integer    $children_content_id
 *
 * @property CmsContent $cmsContent
 * @property CmsContent $cmsContentForOffers
 */
class ShopContent extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%shop_content}}';
    }

    /**
     *
     */
    public function init()
    {
        parent::init();

        $this->on(self::EVENT_AFTER_INSERT, [$this, '_updateCmsContent']);
        $this->on(self::EVENT_AFTER_UPDATE, [$this, '_updateCmsContent']);
    }


    public function _updateCmsContent($e)
    {
        $this->content->is_visible = false;
        $this->content->save(false);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [
                ['content_id', 'children_content_id'],
                'integer',
            ],
            [['content_id'], 'required'],
            [['content_id'], 'unique'],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'content_id'          => \Yii::t('skeeks/shop/app', 'Content'),
            'children_content_id' => \Yii::t('skeeks/shop/app', 'Trade offers'),
        ]);
    }



    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCmsContentForOffers()
    {
        return $this->hasOne(CmsContent::class, ['id' => 'children_content_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCmsContent()
    {
        return $this->hasOne(CmsContent::class, ['id' => 'content_id']);
    }

    /**
     * @deprecated
     * @return \yii\db\ActiveQuery
     */
    public function getOfferContent()
    {
        return $this->getCmsContentForOffers();
    }

    /**
     * @deprecated
     * @return \yii\db\ActiveQuery
     */
    public function getChildrenContent()
    {
        return $this->getCmsContentForOffers();
    }

    /**
     * @deprecated
     * @return \yii\db\ActiveQuery
     */
    public function getContent()
    {
        return $this->getCmsContent();
    }

    /**
     * @return string
     */
    public function asText()
    {
        return $this->cmsContent->asText;
    }
}