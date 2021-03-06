<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 31.08.2015
 */

namespace skeeks\cms\shop\models;

use skeeks\cms\models\CmsSite;

/**
 * This is the model class for table "{{%shop_person_type_site}}".
 *
 * @property integer        $person_type_id
 * @property integer         $cms_site_id
 *
 * @property CmsSite        $site
 * @property ShopPersonType $personType
 */
class ShopPersonTypeSite extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%shop_person_type_site}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['person_type_id', 'cms_site_id'], 'required'],
            [['person_type_id'], 'integer'],
            [['cms_site_id'], 'integer'],
            [
                ['person_type_id', 'cms_site_id'],
                'unique',
                'targetAttribute' => ['person_type_id', 'cms_site_id'],
                'message'         => \Yii::t('skeeks/shop/app', 'The combination of Person Type ID and Site Code
     has already been taken.'),
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'person_type_id' => \Yii::t('skeeks/shop/app', 'Person type ID'),
            'cms_site_id'      => \Yii::t('skeeks/shop/app', 'Site'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSite()
    {
        return $this->hasOne(CmsSite::class, ['id' => 'cms_site_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPersonType()
    {
        return $this->hasOne(ShopPersonType::class, ['id' => 'person_type_id']);
    }
}