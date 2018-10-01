<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

namespace skeeks\cms\shop\models;

use skeeks\cms\components\Cms;
use skeeks\cms\models\CmsSite;
use yii\base\Exception;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%shop_person_type}}".
 *
 * @property integer                   $id
 * @property integer                   $created_by
 * @property integer                   $updated_by
 * @property integer                   $created_at
 * @property integer                   $updated_at
 * @property string                    $name
 * @property integer                   $priority
 * @property string                    $active
 *
 * @property string[]                  $siteCodes
 *
 * @property ShopPersonTypeSite[]      $shopPersonTypeSites
 * @property CmsSite[]                 $sites
 *
 * @property ShopBuyer[]               $shopBuyers
 * @property ShopOrder[]               $shopOrders
 * @property ShopPaySystemPersonType[] $shopPaySystemPersonTypes
 * @property ShopPaySystem[]           $paySystems
 * @property CmsUser                   $createdBy
 * @property CmsUser                   $updatedBy
 * @property ShopPersonTypeProperty[]  $shopPersonTypeProperties
 * @property ShopTaxRate[]             $shopTaxRates
 */
class ShopPersonType extends \skeeks\cms\models\Core
{

    protected $_siteCodes = [];
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%shop_person_type}}';
    }
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->on(self::EVENT_AFTER_INSERT, [$this, "afterSaveEvent"]);
        $this->on(self::EVENT_AFTER_UPDATE, [$this, "afterSaveEvent"]);

        $this->on(self::EVENT_BEFORE_UPDATE, [$this, "beforeSaveEvent"]);
    }

    /**
     * @param $event
     */
    public function beforeSaveEvent($event)
    {
        if ($this->isAttributeChanged('active') && $this->active == Cms::BOOL_N) {
            if (!static::find()->active()->andWhere(['!=', 'id', $this->id])->count()) {
                throw new Exception("Requires at least one active payer type");
            }
        }
    }

    /**
     * @param $event
     */
    public function afterSaveEvent($event)
    {
        if ($this->_siteCodes) {
            //Для начала удаляем текущие связи
            $allSites = $this->getShopPersonTypeSites()->all();
            if ($allSites) {
                foreach ($allSites as $siteRelation) {
                    $siteRelation->delete();
                }
            }

            //добавляем новые
            foreach ($this->_siteCodes as $code) {
                $shopTypeSite = $this->getShopPersonTypeSites()->andWhere(['site_code' => $code])->one();
                //Такой связи еще нет
                if (!$shopTypeSite) {
                    $shopTypeSite = new ShopPersonTypeSite();

                    $shopTypeSite->site_code = $code;
                    $shopTypeSite->person_type_id = $this->id;

                    $shopTypeSite->save();
                }
            }

        }
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopPersonTypeSites()
    {
        return $this->hasMany(ShopPersonTypeSite::class, ['person_type_id' => 'id']);
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
            [['siteCodes'], 'safe'],
            [['active'], 'default', 'value' => Cms::BOOL_Y],
            [['active'], 'validateActive'],
        ]);
    }
    public function validateActive($attribute)
    {
        if ($this->$attribute == Cms::BOOL_N && !static::find()->active()->andWhere(['!=', 'id', $this->id])->count()) {
            $this->addError($attribute,
                \Yii::t('skeeks/shop/app', 'It is necessary at least to leave one active payer type in the site'));
        }
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'name'      => \Yii::t('skeeks/shop/app', 'Name'),
            'priority'  => \Yii::t('skeeks/shop/app', 'Priority'),
            'active'    => \Yii::t('skeeks/shop/app', 'Active'),
            'siteCodes' => \Yii::t('skeeks/shop/app', 'Sites'),
        ]);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopBuyers()
    {
        return $this->hasMany(ShopBuyer::class, ['shop_person_type_id' => 'id']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopOrders()
    {
        return $this->hasMany(ShopOrder::class, ['person_type_id' => 'id']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopPaySystemPersonTypes()
    {
        return $this->hasMany(ShopPaySystemPersonType::class, ['person_type_id' => 'id']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPaySystems()
    {
        return $this->hasMany(ShopPaySystem::class, ['id' => 'pay_system_id'])
            ->viaTable('shop_pay_system_person_type', ['person_type_id' => 'id']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopPersonTypeProperties()
    {
        return $this->hasMany(ShopPersonTypeProperty::class,
            ['shop_person_type_id' => 'id'])->orderBy(['priority' => SORT_ASC]);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopTaxRates()
    {
        return $this->hasMany(ShopTaxRate::class, ['person_type_id' => 'id']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSites()
    {
        return $this->hasMany(CmsSite::class, ['code' => 'site_code'])->viaTable('{{%shop_person_type_site}}',
            ['person_type_id' => 'id']);
    }


    /**
     * @return string[]
     */
    public function getSiteCodes()
    {
        return (array)ArrayHelper::map($this->sites, 'code', 'code');
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


    /**
     * @return ShopBuyer
     * @throws InvalidParamException
     */
    public function createModelShopBuyer()
    {
        if ($this->isNewRecord) {
            throw new InvalidParamException;
        }

        return new ShopBuyer([
            'shop_person_type_id' => (int)$this->id,
        ]);
    }


}