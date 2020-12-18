<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 31.08.2015
 */

namespace skeeks\cms\shop\models;

use skeeks\cms\components\Cms;
use skeeks\cms\models\behaviors\Serialize;
use skeeks\cms\models\CmsSite;
use skeeks\cms\models\Core;
use skeeks\cms\shop\components\PaySystemHandlerComponent;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%shop_pay_system}}".
 *
 * @property string                    $name
 * @property integer                   $priority
 * @property integer                    $is_active
 * @property string                    $description
 * @property string                    $component
 * @property string                    $component_config
 * @property integer                   $cms_site_id
 *
 * @property ShopPaySystemPersonType[] $shopPaySystemPersonTypes
 * @property ShopPersonType[]          $personTypes
 *
 * @property PaySystemHandlerComponent $paySystemHandler
 * @property PaySystemHandlerComponent $handler
 *
 * @property CmsSite                   $сmsSite
 */
class ShopPaySystem extends Core
{
    protected $_personTypes = [];
    protected $_handler = null;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%shop_pay_system}}';
    }
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            Serialize::class =>
                [
                    'class'  => Serialize::class,
                    'fields' => ['component_config'],
                ],
        ]);
    }
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->on(self::EVENT_AFTER_INSERT, [$this, "afterSaveEvent"]);
        $this->on(self::EVENT_AFTER_UPDATE, [$this, "afterSaveEvent"]);
    }
    /**
     * @param $event
     */
    public function afterSaveEvent($event)
    {
        if ($this->_personTypes) {
            //Для начала удаляем текущие связи
            $all = $this->getShopPaySystemPersonTypes()->all();
            if ($all) {
                foreach ($all as $one) {
                    $one->delete();
                }
            }

            //добавляем новые
            foreach ($this->_personTypes as $id) {
                $shopTypeSite = $this->getShopPaySystemPersonTypes()->andWhere(['person_type_id' => $id])->one();
                //Такой связи еще нет
                if (!$shopTypeSite) {
                    $shopTypeSite = new ShopPaySystemPersonType();

                    $shopTypeSite->pay_system_id = $this->id;
                    $shopTypeSite->person_type_id = $id;

                    $shopTypeSite->save();
                }
            }

        }
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopPaySystemPersonTypes()
    {
        return $this->hasMany(ShopPaySystemPersonType::class, ['pay_system_id' => 'id']);
    }
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'priority','cms_site_id'], 'integer'],
            [['name'], 'required'],
            [['description'], 'string'],
            [['component_config'], 'safe'],
            [['name', 'component'], 'string', 'max' => 255],
            [['is_active'], 'integer'],
            [['personTypeIds'], 'safe'],
            [['priority'], 'default', 'value' => 100],
            
            [
                'cms_site_id',
                'default',
                'value' => function () {
                    if (\Yii::$app->skeeks->site) {
                        return \Yii::$app->skeeks->site->id;
                    }
                },
            ],
        ]);
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'name'          => \Yii::t('skeeks/shop/app', 'Name'),
            'priority'      => \Yii::t('skeeks/shop/app', 'Priority'),
            'is_active'        => \Yii::t('skeeks/shop/app', 'Active'),
            'description'   => \Yii::t('skeeks/shop/app', 'Description'),
            'personTypeIds' => \Yii::t('skeeks/shop/app', 'Payers'),
            'component'     => \Yii::t('skeeks/shop/app', 'Handler'),
            'cms_site_id'   => \Yii::t('skeeks/shop/app', 'Site'),
        ]);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPersonTypes()
    {
        return $this->hasMany(ShopPersonType::class,
            ['id' => 'person_type_id'])->viaTable('{{%shop_pay_system_person_type}}', ['pay_system_id' => 'id']);
    }
    /**
     * @return int[]
     */
    public function getPersonTypeIds()
    {
        return (array)ArrayHelper::map($this->personTypes, 'id', 'id');
    }
    /**
     * @param array $codes
     * @return $this
     */
    public function setPersonTypeIds($ids = [])
    {
        $this->_personTypes = $ids;
        return $this;
    }
    /**
     * @return null|PaySystemHandlerComponent
     * @throws \yii\base\InvalidConfigException
     */
    public function getPaySystemHandler()
    {
        return $this->handler;
    }
    /**
     * @return PaySystemHandlerComponent
     * @throws \skeeks\cms\import\InvalidParamException
     */
    public function getHandler()
    {
        if ($this->_handler !== null) {
            return $this->_handler;
        }

        if ($this->component) {
            try {
                /**
                 * @var $component PropertyType
                 */
                /*$foundComponent = \Yii::$app->cms->getRelatedHandler($this->component);
                //TODO:: Подумать! Нужно чтобы создавался новый экземляр класса потому что в него передается property объект. В то же время хотелось бы чтобы объект handler собирался согласно настройкам конфига.
                $component = clone $foundComponent;*/

                $component = \Yii::createObject($this->component);
                $component->load($this->component_config, "");

                $this->_handler = $component;
                return $this->_handler;
            } catch (\Exception $e) {
                \Yii::error("Related property handler not found '{$this->component}'", self::class);
                return null;
            }

        }

        return null;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCmsSite()
    {
        return $this->hasOne(CmsSite::class, ['id' => 'cms_site_id']);
    }
}