<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 31.08.2015
 */
namespace skeeks\cms\shop\models;

use skeeks\cms\models\CmsSite;
use Yii;

/**
 * This is the model class for table "{{%shop_person_type_site}}".
 *
 * @property integer $person_type_id
 * @property string $site_code
 *
 * @property CmsSite $site
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
            [['person_type_id', 'site_code'], 'required'],
            [['person_type_id'], 'integer'],
            [['site_code'], 'string', 'max' => 15],
            [['person_type_id', 'site_code'], 'unique', 'targetAttribute' => ['person_type_id', 'site_code'], 'message' => \Yii::t('skeeks/shop/app', 'The combination of Person Type ID and Site Code
     has already been taken.')]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'person_type_id'    => \Yii::t('skeeks/shop/app', 'Person type ID'),
            'site_code'         => \Yii::t('skeeks/shop/app', 'Site code'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSite()
    {
        return $this->hasOne(CmsSite::className(), ['code' => 'site_code']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPersonType()
    {
        return $this->hasOne(ShopPersonType::className(), ['id' => 'person_type_id']);
    }
}