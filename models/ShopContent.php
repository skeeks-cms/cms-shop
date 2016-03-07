<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 15.09.2015
 */
namespace skeeks\cms\shop\models;

use skeeks\cms\components\Cms;
use skeeks\cms\models\CmsContent;
use Yii;

/**
 * This is the model class for table "{{%shop_content}}".
 *
 * @property integer $id
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $content_id
 * @property string $yandex_export
 * @property string $subscription
 * @property integer $vat_id
 * @property integer $children_content_id
 *
 * @property CmsContent $content
 * @property ShopVat $vat
 * @property CmsContent $childrenContent
 */
class ShopContent extends \skeeks\cms\models\Core
{
    public function init()
    {
        parent::init();

        $this->on(self::EVENT_AFTER_INSERT, [$this, '_updateCmsContent']);
        $this->on(self::EVENT_AFTER_UPDATE, [$this, '_updateCmsContent']);
    }

    public function _updateCmsContent($e)
    {
        $this->content->visible = Cms::BOOL_N;
        $this->content->save();
    }


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%shop_content}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'content_id', 'vat_id', 'children_content_id'], 'integer'],
            [['content_id'], 'required'],
            [['content_id'], 'unique'],
            [['yandex_export', 'subscription'], 'string', 'max' => 1],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'            => \skeeks\cms\shop\Module::t('app', 'ID'),
            'created_by'    => \skeeks\cms\shop\Module::t('app', 'Created By'),
            'updated_by'    => \skeeks\cms\shop\Module::t('app', 'Updated By'),
            'created_at'    => \skeeks\cms\shop\Module::t('app', 'Created At'),
            'updated_at'    => \skeeks\cms\shop\Module::t('app', 'Updated At'),
            'content_id'    => \skeeks\cms\shop\Module::t('app', 'Content'),
            'yandex_export' => \skeeks\cms\shop\Module::t('app', 'Export to Yandex.Products'),
            'subscription'  => \skeeks\cms\shop\Module::t('app', 'Subscription'),
            'vat_id'        => \skeeks\cms\shop\Module::t('app', 'Vat ID'),
            'children_content_id' => \skeeks\cms\shop\Module::t('app', 'Trade offers'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getChildrenContent()
    {
        return $this->hasOne(CmsContent::className(), ['id' => 'children_content_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContent()
    {
        return $this->hasOne(CmsContent::className(), ['id' => 'content_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVat()
    {
        return $this->hasOne(ShopVat::className(), ['id' => 'vat_id']);
    }

}