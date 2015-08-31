<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */
namespace skeeks\cms\shop\models;

use skeeks\cms\models\CmsSite;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%shop_person_type}}".
 *
 * @property integer $id
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $name
 * @property integer $priority
 * @property string $active
 *
 * @property string[] $siteCodes
 *
 * @property ShopPersonTypeSite[]   $shopPersonTypeSites
 * @property CmsSite[]              $sites
 */
class ShopPersonType extends \skeeks\cms\models\Core
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%shop_person_type}}';
    }

    protected $_siteCodes = [];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->on(self::EVENT_AFTER_INSERT,    [$this, "afterSaveEvent"]);
        $this->on(self::EVENT_AFTER_UPDATE,    [$this, "afterSaveEvent"]);
    }

    /**
     * @param $event
     */
    public function afterSaveEvent($event)
    {
        if ($this->_siteCodes)
        {
            //Для начала удаляем текущие связи
            $allSites = $this->getShopPersonTypeSites()->all();
            if ($allSites)
            {
                foreach ($allSites as $siteRelation)
                {
                    $siteRelation->delete();
                }
            }

            //добавляем новые
            foreach ($this->_siteCodes as $code)
            {
                $shopTypeSite = $this->getShopPersonTypeSites()->andWhere(['site_code' => $code])->one();
                //Такой связи еще нет
                if (!$shopTypeSite)
                {
                    $shopTypeSite                   = new ShopPersonTypeSite();

                    $shopTypeSite->site_code        = $code;
                    $shopTypeSite->person_type_id   = $this->id;

                    $shopTypeSite->save();
                }
            }

        }
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['priority'], 'integer'],
            [['priority'], 'default', 'value' => 100],
            [['name'], 'required'],
            [['name'], 'string', 'max' => 255],
            [['active'], 'string', 'max' => 1],
            [['name'], 'unique'],
            [['siteCodes'], 'safe']
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'name' => Yii::t('app', 'Name'),
            'priority' => Yii::t('app', 'Priority'),
            'active' => Yii::t('app', 'Active'),
            'siteCodes' => Yii::t('app', 'Сайты'),
        ]);
    }




    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopPersonTypeSites()
    {
        return $this->hasMany(ShopPersonTypeSite::className(), ['person_type_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSites()
    {
        return $this->hasMany(CmsSite::className(), ['code' => 'site_code'])->viaTable('{{%shop_person_type_site}}', ['person_type_id' => 'id']);
    }


    /**
     * @return string[]
     */
    public function getSiteCodes()
    {
        return (array) ArrayHelper::map($this->sites, 'code', 'code');
    }


    /**
     * @param array $codes
     * @return $this
     */
    public function setSiteCodes($codes = [])
    {
        $this->_siteCodes = $codes;
        return $this;
    }



}